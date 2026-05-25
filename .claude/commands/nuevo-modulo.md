---
description: Scaffold completo de un módulo ACF Flexible Content
argument-hint: <slug-kebab> "<descripción>"
---

# /nuevo-modulo $ARGUMENTS

Scaffold un módulo ACF Flexible Content nuevo siguiendo el patrón canónico del boilerplate.

## Argumentos esperados

- **`<slug-kebab>`** — nombre del módulo en kebab-case (ej. `testimonios-grid`). Se usa para el
  directorio y el archivo. Internamente se convertirá a snake_case (`testimonios_grid`) para el
  `name` del layout ACF — recuerda que `the_modules_loop()` hace la conversión automática.
- **`"<descripción>"`** — descripción corta entre comillas. Va al `label` del layout y al
  comentario del header del archivo PHP.

Si los argumentos vienen vacíos o malformados, pregunta al usuario por slug y descripción antes
de tocar nada.

## Lo que debes hacer

### 1. Validaciones previas

- Confirma que `modules/<slug>/` NO existe ya. Si existe, aborta y avisa al usuario.
- Confirma que `inc/acf-modules.php` no tiene ya un layout con `name => <slug_snake>`.
- Valida que el slug es kebab-case válido (`^[a-z][a-z0-9-]*$`). Sin guion bajo, sin mayúsculas,
  sin tildes, sin espacios.

### 2. Crear `modules/<slug>/<slug>.php`

Usa esta plantilla canónica. **Adapta** los campos al dominio descrito en `"<descripción>"`,
pero **respeta**:

- Header con descripción.
- Early return si el campo principal está vacío.
- Escape de toda salida.
- Sección con clase BEM `<slug>` y atributos `data-gsap` cuando aplique.
- Uso de `template-parts/atoms/image.php` y `template-parts/atoms/button.php` cuando corresponda.

```php
<?php
/**
 * Módulo: <Descripción humana>
 *
 * Campos ACF (sub-fields del flexible `modules`, layout `<slug_snake>`):
 *   - <campo_1> (tipo)
 *   - <campo_2> (tipo)
 */

if (!defined('ABSPATH')) exit;

$titulo = get_sub_field('titulo');
$items  = get_sub_field('items');

// Early return: si el campo principal está vacío, no renderizar nada.
if (empty($titulo) && empty($items)) {
    return;
}
?>

<section class="<slug>" data-gsap="fade-up">
    <div class="container">

        <?php if (!empty($titulo)) : ?>
            <h2 class="<slug>__title"><?php echo esc_html($titulo); ?></h2>
        <?php endif; ?>

        <?php if (!empty($items) && is_array($items)) : ?>
            <ul class="<slug>__list">
                <?php foreach ($items as $i => $item) : ?>
                    <li class="<slug>__item" data-gsap-batch=".<slug>__item" data-gsap-delay="<?php echo esc_attr(0.1 * $i); ?>">
                        <?php // Renderizar campos del item, todos escapados. ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
</section>
```

> Reemplaza `<slug>` por el slug real en kebab-case. NO uses snake_case en clases CSS.

### 3. Registrar el layout en `inc/acf-modules.php`

Lee primero `inc/acf-modules.php` para localizar el array `'layouts' => [...]` del field group
`group_bp_componentes`. Añade una nueva entrada al final de ese array (antes del cierre `]`),
siguiendo el estilo del resto. Esquema mínimo:

```php
[
    'key'         => 'layout_<slug_snake>',
    'name'        => '<slug_snake>',
    'label'       => '<Descripción humana>',
    'display'     => 'block',
    'sub_fields'  => [
        // Define aquí los sub-fields que correspondan al dominio.
        // Cada uno con 'key' único: 'field_<slug_snake>_<campo>'.
    ],
    'min'         => '',
    'max'         => '',
],
```

**Crítico:**
- `name` debe ser snake_case (`<slug>` con `-` reemplazados por `_`).
- `key` debe ser único globalmente — prefija con `layout_` y `field_` según corresponda.
- **Nunca** uses `name => 'modules'` en un sub-field — rompería el contrato (ver
  `inc/acf-modules.php:46` y CLAUDE.md sección "ACF source-of-truth contract").
- **No** exportes a `acf-json/` — el filtro `acf/settings/load_json` los ignora a propósito.

### 4. (Opcional, sugerir) Estilos SASS

Si el módulo lo amerita, sugiere crear `styles/sass/organisms/_<slug>.scss` e importarlo en
`styles/sass/style.scss`. **No** lo crees automáticamente — pregunta primero, porque muchos
módulos pueden resolverse solo con clases Tailwind.

### 5. Reporte final al usuario

Devuelve un resumen breve:

```
✅ Módulo `<slug>` creado.

Archivos:
  - modules/<slug>/<slug>.php  (nuevo)
  - inc/acf-modules.php        (editado: layout añadido)

Próximos pasos:
  1. Ir a WP Admin → editar una página → añadir el módulo y llenar datos.
  2. Validar visualmente en /demo/ o en la página de prueba.
  3. Antes de commitear, correr `/qa-modulo <slug>`.
```

## Qué NO hacer

- No exportar a `acf-json/`.
- No nombrar ningún sub-field como `modules`.
- No usar `the_field()` sin escape en campos de texto.
- No renderizar `<section>` vacíos — siempre early return.
- No mezclar snake_case y kebab-case fuera de la convención (ACF=snake, filesystem/CSS=kebab).
