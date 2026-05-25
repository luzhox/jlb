# Bug ACF Pro 6.8.1 + WP 6.9.4 — wrapper `<strong>` inesperado

> **Fecha del descubrimiento:** 2026-05-19
> **Versión ACF Pro afectada:** 6.8.1
> **Versión WordPress:** 6.9.4
> **Stack:** Local Flywheel · PHP 8.x · Theme JLB
> **Estado:** Mitigado vía patch JS+CSS en `assets/admin/`; bug raíz sin resolver (origen del wrapper desconocido).

## TL;DR

En esta instalación, ACF Pro renderiza cada `.layout` de Flexible Content
envuelto en un `<strong>` inesperado:

```html
<div class="layout">
    <strong>                              ← origen desconocido (no es ACF source)
        <input hidden>
        <div class="acf-fc-layout-actions-wrap">...</div>
        <div class="acf-fields">...</div>
    </strong>
</div>
```

Eso rompe **todos los selectores con `>` direct-child** que ACF Pro usa
internamente, produciendo dos bugs visibles:

1. **Collapse roto** — `e.children("input").attr("name").replace(...)` lanza
   `TypeError: Cannot read properties of undefined (reading 'replace')` en
   `renderLayout`. Eso aborta el flujo `closeLayout → renderLayout` justo
   después del `addClass("-collapsed")` y rompe **todos los event handlers
   subsecuentes** en la página.
2. **Drag-and-drop roto** — el `mouseover: onHover` lazy de ACF (que llama
   `addSortable`) no dispara, así que jQuery UI sortable nunca se inicializa.
   Y aunque se inicialice, el handle selector
   `> .acf-fc-layout-actions-wrap .acf-fc-layout-handle` falla por el mismo
   problema del `>`.

El **CSS** nativo de ACF Pro (`acf-pro-input.min.css`) tampoco aplica:
`.acf-flexible-content .layout.-collapsed > .acf-table { display: none }`
no matchea porque `.acf-fields` no es hijo directo de `.layout` — está
dentro del wrapper.

## Origen del wrapper

**Desconocido.** No es del source de ACF Pro
(`/wp-content/plugins/advanced-custom-fields-pro/pro/fields/class-acf-field-flexible-content.php`),
ni del tema JLB. Posibles culpables:

- Un plugin que filtre `the_content` o equivalente sobre el output del metabox.
- Un sanitizador HTML de WP que cierra mal una etiqueta.
- Una corrupción en el HTML del layout que arranca con `<strong>` sin cerrar
  y el parser del navegador lo extiende a todo el contenido.

Pendiente investigar. Mientras tanto el patch es defensivo.

## Patch — 3 capas (`assets/admin/acf-collapse-patch.js` + `.css`)

### Capa 1 — Wrap defensivo del prototipo `renderLayout`

Sustituye el método para validar que `e.children("input").attr("name")` existe
antes de delegar al original. Si no existe, retorna silencioso (la llamada AJAX
para refrescar el título-resumen se omite — el plegado funciona igual).

```js
fcType.prototype.renderLayout = function (e) {
    try {
        if (!e || typeof e.children !== 'function') return;
        var $input = e.children('input');
        if (!$input.length || !$input.attr('name')) return;
        return originalRender.apply(this, arguments);
    } catch (err) {
        console.warn('[JLB ACF patch] renderLayout suppressed:', err.message);
    }
};
```

### Capa 2 — Click handler capture-phase para collapse

`document.addEventListener('click', ..., true)` intercepta clics en
`[data-name="collapse-layout"]` **antes** de que llegue el handler de ACF.
Aplica `classList.toggle('-collapsed')` y hace `stopImmediatePropagation()`
para evitar que el handler roto de ACF intente correr.

```js
document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-name="collapse-layout"]');
    if (!trigger) return;
    var layout = trigger.closest('.layout');
    if (!layout) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    layout.classList.toggle('-collapsed');
}, true); // capture phase
```

También cubre `.acf-fc-collapse-all` y `.acf-fc-expand-all`.

### Capa 3 — Override de `addSortable` + init manual

Reescribe `fcType.prototype.addSortable` para usar `handle: '.acf-fc-layout-handle'`
(descendiente, sin `>`). Además, inicializa sortable manualmente al cargar la
página (no esperamos al lazy `mouseover` que no dispara), y usa
`MutationObserver` para re-inicializar si aparecen nuevos flex fields
(Gutenberg, append acciones).

```js
fcType.prototype.addSortable = function (field) {
    if (String(field.get('max')) === '1') return;
    var $wrap = field.$layoutsWrap();
    try { $wrap.sortable('destroy'); } catch {}
    $wrap.sortable({
        items: '> .layout',
        handle: '.acf-fc-layout-handle',   // SIN `>` — atraviesa el <strong>
        forceHelperSize: true,
        zIndex: 9999,
        forcePlaceholderSize: true,
        scroll: true,
        stop: () => field.render && field.render(),
        update: () => field.$input && field.$input().trigger('change'),
    });
};
```

### CSS — `assets/admin/acf-collapse-patch.css`

```css
.acf-flexible-content .layout.-collapsed .acf-fields,
.acf-flexible-content .layout.-collapsed .acf-table,
.acf-flexible-content .layout.-collapsed .acf-fields-wrap {
    display: none !important;
}
```

Selector **descendiente** (sin `>`), `!important` para ganar a cualquier
override de tema/plugin.

## Enqueue (`inc/acf-admin-patches.php`)

El patch se enqueueá solo en pantallas relevantes (post / page / options
del sitio JLB). Usa `filemtime()` para cache-busting automático:

```php
add_action('admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if (!$screen) return;
    $relevant = array('post', 'page');
    if (!in_array($screen->base, $relevant, true) &&
        strpos((string) $screen->id, 'jlb-site-settings') === false) {
        return;
    }
    $js  = get_template_directory() . '/assets/admin/acf-collapse-patch.js';
    $css = get_template_directory() . '/assets/admin/acf-collapse-patch.css';
    wp_enqueue_script('jlb-acf-collapse-patch',
        get_template_directory_uri() . '/assets/admin/acf-collapse-patch.js',
        array('acf-input'), filemtime($js), true);
    wp_enqueue_style('jlb-acf-collapse-patch',
        get_template_directory_uri() . '/assets/admin/acf-collapse-patch.css',
        array('acf-input'), filemtime($css));
});
```

## Validación reproducible

Dos scripts Playwright en `qa/` que cualquiera puede correr:

```bash
node qa/playwright-acf-collapse.mjs   # valida plegado
node qa/playwright-acf-drag.mjs       # valida drag-and-drop
```

Loguean automáticamente con `qa/.env` (gitignoreado) y devuelven exit 0 si
todo OK, 1 si algo falla. Generan screenshots en `qa/*.png`.

## Cómo verificar si el bug sigue existiendo

Si en el futuro ACF Pro se actualiza o el wrapper desaparece:

1. Comentar el `require_once('inc/acf-admin-patches.php');` en `functions.php`.
2. Hard refresh wp-admin en una página con flexible content.
3. Click en colapsar un módulo. Si funciona sin error en consola → bug
   resuelto upstream, eliminar el patch.
4. Si sigue roto → mantener el patch.

## Cómo identificar el origen del wrapper (pendiente)

Estrategias para futura investigación:

1. **Desactivar plugins uno por uno** (queda `duplicate-page` como sospechoso
   no descartado).
2. **Inspeccionar la respuesta AJAX** de ACF en la pestaña Network del
   navegador al crear un layout — ¿el HTML ya viene con `<strong>` desde
   el servidor?
3. **Filtros activos en `the_content`** y `wp_kses_post` — `var_dump`
   `apply_filters` en `inc/etc.php` para ver qué corre.
4. **Otro tema activado** en una BD limpia — si el wrapper desaparece, es
   tema-relacionado.

## Lecciones para futuro

- Los **selectores con `>` direct-child son frágiles** ante wrappers
  inesperados. Si vas a depender de la estructura DOM de un plugin de
  terceros, prefiere selectores descendientes con `:scope` cuando sea
  posible.
- Los **bugs JS en wp-admin pueden encadenarse**: una excepción no atrapada
  en un handler rompe los event listeners de toda la página (incluyendo los
  que no tienen relación con el handler que falló). Trip-wrap defensivo
  selectivo es razonable mitigación.
- **El bug del usuario fue real desde el principio.** Mis primeras hipótesis
  (`collapsed` config, datos huérfanos) fueron especulativas. Lo que
  funcionó fue **leer el source minificado de ACF Pro** y **validar el DOM
  real con Playwright**. Lección: ante bug de plugin de terceros, ir
  directo al source.

## Referencias

- Source ACF Pro: `wp-content/plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-pro-input.min.js`
- Source CSS ACF Pro: `wp-content/plugins/advanced-custom-fields-pro/assets/build/css/pro/acf-pro-input.min.css`
- Patch: `assets/admin/acf-collapse-patch.js` + `.css`
- Enqueue: `inc/acf-admin-patches.php`
- Tests: `qa/playwright-acf-collapse.mjs` + `qa/playwright-acf-drag.mjs`
