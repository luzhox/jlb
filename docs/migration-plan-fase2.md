# Migration Plan — Fase 2

> **Audiencia**: `frontend-lead` (implementa) + revisor humano + `wp-security` para auditoría.
> **Propósito**: planificar la migración de los 5 módulos rediseñados (`hero`, `cta`, `blog`, `formulario`, `testimonios`) sin romper páginas en producción.
> **Alcance**: solo nuevos campos ACF aprobados (Q7 cerrada, ver §7 design-system) + nuevos partials PHP. **No** se borra ningún campo viejo en esta fase.

---

## 0. Principios

1. **Backwards-compatible primero.** Los módulos viejos siguen funcionando hasta que un humano confirme que la migración es correcta.
2. **Convivencia transitoria.** Cada módulo soporta los campos viejos y los nuevos en el mismo render. Si el campo nuevo está vacío, cae al viejo.
3. **Sin destruir datos.** El script de migración escribe campos nuevos pero no borra los viejos. La eliminación es una operación posterior, manual, post-validación.
4. **ACF source-of-truth = `inc/acf-modules.php`.** Los nuevos campos se añaden ahí — nunca por `acf-json/`.
5. **Una migración por módulo, en orden.** No batch global; minimizamos blast radius.

---

## 1. Tabla campo viejo → campo nuevo

### 1.1 Hero (`modules/hero/hero.php`)

| Campo actual            | Campo nuevo                | Tipo              | Acción                                    |
|-------------------------|----------------------------|-------------------|-------------------------------------------|
| (nuevo)                 | `overline_manuscrito`      | Text              | **Añadir.** Caveat overline opcional sobre H1. |
| (nuevo)                 | `tipo_fondo`               | Select (`image`/`video`) | **Añadir.** Default `image` para no romper. |
| (nuevo)                 | `video_fondo`              | File (mp4/webm)   | **Añadir.** Solo aplica si `tipo_fondo='video'`. |
| `sliderhero` (repeater) | (sin cambio)               | Repeater          | Conservar tal cual. El rediseño afecta look, no data. |
| `sliderhero.imagen_de_escritorio` | (sin cambio)     | Image             | Conservar. |
| `sliderhero.imagen_de_mobile`     | (sin cambio)     | Image             | Conservar. |
| `sliderhero.texto`      | (sin cambio)               | WYSIWYG           | Conservar — el rediseño usa el primer `<h1>` del WYSIWYG como display. |
| `sliderhero.boton`      | (sin cambio)               | Link              | Conservar. |
| `sliderhero.overlay`    | (sin cambio)               | Color             | Conservar — pero en variante `tipo_fondo=video` se ignora a favor del `--gradient-card-fade` del sistema. |
| `ver_mas`               | (sin cambio)               | Link              | Conservar. |

**Comportamiento**: si `tipo_fondo='video'` y `video_fondo` está set, el hero renderiza `<video>` full-bleed y los `imagen_de_escritorio/mobile` actúan como **poster** (mejora LCP). Si `tipo_fondo='image'` (default), comportamiento idéntico al actual.

### 1.2 CTA (`modules/cta/cta.php`)

| Campo actual           | Campo nuevo                 | Tipo                              | Acción                                              |
|------------------------|-----------------------------|-----------------------------------|-----------------------------------------------------|
| `titulo`               | (sin cambio)                | Text                              | Conservar. |
| `subtitulo`            | (sin cambio)                | Textarea                          | Conservar. |
| `boton_principal`      | (sin cambio)                | Link                              | Conservar. |
| `boton_secundario`     | (sin cambio)                | Link                              | Conservar. |
| `imagen_fondo`         | (sin cambio)                | Image                             | Conservar — usado como poster en variante `video`. |
| `overlay_color`        | (sin cambio)                | Color                             | Conservar — solo se aplica en variante legacy. |
| `alineacion`           | (sin cambio)                | Select                            | Conservar. |
| `fondo` (`primary`/`dark`/`light`) | `variante` (`surface`/`brand`/`video`) | Select | **Añadir nuevo + mapear.** Ver mapping abajo.       |
| (nuevo)                | `cube_visible`              | True/False (default `false`)      | **Añadir.** Activa lucky cube decorativo (solo en `brand` y `video`). |
| (nuevo)                | `video_fondo`               | File                              | **Añadir.** Solo aplica si `variante='video'`.      |

**Mapping de `fondo` → `variante`** (lo aplica el script de migración):

| `fondo` (viejo) | `variante` (nuevo) |
|-----------------|--------------------|
| `primary`       | `brand`            |
| `dark`          | `brand`            |
| `light`         | `surface`          |
| (vacío)         | `surface`          |

El script **no** borra `fondo`. Convivencia: el módulo lee primero `variante`; si está vacío cae a `fondo` con el mismo mapping inline.

### 1.3 Blog (`modules/blog/blog.php`)

No requiere campos ACF nuevos para el rediseño. **El cambio es 100 % visual + estructural**:

| Cambio                                     | Acción                                                                  |
|--------------------------------------------|-------------------------------------------------------------------------|
| Cards pasan a variante `hairline`          | Refactor del render (usa `template-parts/molecules/card.php` extendido). |
| Eliminar botón "Leer más" del card         | Refactor del render — la card entera es un anchor con `.stretched-link`.|
| Imagen aspect-ratio 16:10 + radius solo arriba | Refactor del CSS / clases Tailwind del partial card.                |
| Badge categoría reemplaza el chip viejo    | Refactor del partial card; data viene de `wp_get_post_terms()`.         |

No hay migración de datos. La migración es **solo de código**.

### 1.4 Formulario (`modules/formulario/formulario.php`)

No requiere campos ACF nuevos. Cambios:

| Cambio                                                   | Acción                                       |
|----------------------------------------------------------|----------------------------------------------|
| Override CSS de CF7 para usar `.input-shadcn` y `.form-label` | CSS en `src/main.css` mapeando clases CF7 → sistema. |
| Layout interno (label arriba, helper/error inline)       | Refactor del PHP / clases Tailwind del módulo. |
| Botón submit con `kresna-dark` o `brand`                 | Refactor PHP — usar `template-parts/atoms/button.php`. |

Migración de **código** únicamente.

### 1.5 Testimonios (`modules/testimonios/testimonios.php`)

| Campo actual              | Campo nuevo               | Tipo                       | Acción                                   |
|---------------------------|---------------------------|----------------------------|------------------------------------------|
| `titulo`                  | (sin cambio)              | Text                       | Conservar. |
| `subtitulo`               | (sin cambio)              | Text                       | Conservar. |
| `items` (repeater)        | (sin cambio)              | Repeater                   | Conservar. |
| `items.nombre`            | (sin cambio)              | Text                       | Conservar. |
| `items.cargo`             | (sin cambio)              | Text                       | Conservar. |
| `items.empresa`           | (sin cambio)              | Text                       | Conservar. |
| `items.foto`              | (sin cambio)              | Image                      | Conservar. |
| `items.testimonio`        | (sin cambio)              | Textarea                   | Conservar. |
| `items.calificacion`      | (sin cambio)              | Number                     | Conservar. |
| (nuevo en items)          | `destacado`               | True/False (default `false`)| **Añadir.** Marca un testimonio para card surface elevada (1 por sección recomendado). |

---

## 2. Adiciones a `inc/acf-modules.php`

Diff conceptual (no aplicar todavía — Fase 2):

```php
// Hero — añadir tras 'sliderhero' y antes de 'ver_mas'
array('key' => 'field_bp_hero_overline',  'label' => 'Overline manuscrito', 'name' => 'overline_manuscrito', 'type' => 'text'),
array('key' => 'field_bp_hero_tipo_fondo','label' => 'Tipo de fondo', 'name' => 'tipo_fondo', 'type' => 'select', 'choices' => array('image' => 'Imagen', 'video' => 'Vídeo'), 'default_value' => 'image'),
array('key' => 'field_bp_hero_video',     'label' => 'Vídeo de fondo', 'name' => 'video_fondo', 'type' => 'file', 'mime_types' => 'mp4,webm', 'conditional_logic' => array(array(array('field' => 'field_bp_hero_tipo_fondo', 'operator' => '==', 'value' => 'video')))),

// CTA — añadir tras 'fondo'
array('key' => 'field_bp_cta_variante',     'label' => 'Variante', 'name' => 'variante', 'type' => 'select', 'choices' => array('surface' => 'Surface (gris)', 'brand' => 'Brand (azul)', 'video' => 'Video'), 'default_value' => 'brand'),
array('key' => 'field_bp_cta_cube_visible','label' => '¿Mostrar lucky cube?', 'name' => 'cube_visible', 'type' => 'true_false', 'default_value' => 0),
array('key' => 'field_bp_cta_video',        'label' => 'Vídeo de fondo', 'name' => 'video_fondo', 'type' => 'file', 'mime_types' => 'mp4,webm', 'conditional_logic' => array(array(array('field' => 'field_bp_cta_variante', 'operator' => '==', 'value' => 'video')))),

// Testimonios items — añadir al final del array sub_fields del repeater 'items'
array('key' => 'field_bp_test_destacado', 'label' => 'Testimonio destacado', 'name' => 'destacado', 'type' => 'true_false', 'default_value' => 0),
```

---

## 3. Script de migración: `scripts/migrate-acf-fase2.php`

Diseñado para correr con `wp eval-file`. Idempotente (puede correr varias veces sin duplicar). No borra meta viejo. Loguea cada acción por `post_id`.

```php
<?php
/**
 * scripts/migrate-acf-fase2.php
 *
 * Uso (desde la raíz del Local site):
 *   wp eval-file wp-content/themes/boilerplate-wordpress/scripts/migrate-acf-fase2.php
 *
 * Args opcionales (vía constantes definidas antes del eval):
 *   define('BP_MIGRATE_DRY_RUN', true);  // simula, no escribe
 *   define('BP_MIGRATE_MODULES', 'cta,testimonios'); // solo estos
 */

if (!function_exists('get_field') || !function_exists('update_field')) {
    WP_CLI::error('ACF no está activo. Aborto.');
}

$dry_run        = defined('BP_MIGRATE_DRY_RUN') && BP_MIGRATE_DRY_RUN;
$only_modules   = defined('BP_MIGRATE_MODULES')
    ? array_map('trim', explode(',', BP_MIGRATE_MODULES))
    : array('hero', 'cta', 'testimonios');

$cta_fondo_to_variante = array(
    'primary' => 'brand',
    'dark'    => 'brand',
    'light'   => 'surface',
    ''        => 'surface',
);

$query = new WP_Query(array(
    'post_type'      => array('page', 'post'),
    'posts_per_page' => -1,
    'post_status'    => array('publish', 'draft', 'private'),
    'fields'         => 'ids',
    'no_found_rows'  => true,
));

$migrated = 0;
$skipped  = 0;

foreach ($query->posts as $post_id) {
    $modules = get_field('modules', $post_id);
    if (!$modules || !is_array($modules)) {
        $skipped++;
        continue;
    }

    $changed = false;
    foreach ($modules as $idx => $row) {
        $layout = $row['acf_fc_layout'] ?? '';

        // ── HERO ────────────────────────────────────────────────────────────
        if ($layout === 'hero' && in_array('hero', $only_modules, true)) {
            // Campos NUEVOS sin equivalente viejo: solo crear placeholder vacío
            // si no existe ya, para evitar warnings PHP en el render.
            if (!array_key_exists('overline_manuscrito', $row)) {
                $modules[$idx]['overline_manuscrito'] = '';
                $changed = true;
                WP_CLI::log("post={$post_id} layout=hero idx={$idx} → overline_manuscrito=''");
            }
            if (!array_key_exists('tipo_fondo', $row)) {
                $modules[$idx]['tipo_fondo'] = 'image';
                $changed = true;
                WP_CLI::log("post={$post_id} layout=hero idx={$idx} → tipo_fondo='image'");
            }
        }

        // ── CTA ─────────────────────────────────────────────────────────────
        if ($layout === 'cta' && in_array('cta', $only_modules, true)) {
            $fondo_old = isset($row['fondo']) ? (string) $row['fondo'] : '';
            $variante_new = $cta_fondo_to_variante[$fondo_old] ?? 'surface';

            if (empty($row['variante'])) {
                $modules[$idx]['variante'] = $variante_new;
                $changed = true;
                WP_CLI::log("post={$post_id} layout=cta idx={$idx} → variante='{$variante_new}' (de fondo='{$fondo_old}')");
            }
            if (!array_key_exists('cube_visible', $row)) {
                $modules[$idx]['cube_visible'] = false;
                $changed = true;
            }
        }

        // ── TESTIMONIOS ─────────────────────────────────────────────────────
        if ($layout === 'testimonios' && in_array('testimonios', $only_modules, true)) {
            if (!empty($row['items']) && is_array($row['items'])) {
                foreach ($row['items'] as $i => $item) {
                    if (!array_key_exists('destacado', $item)) {
                        $modules[$idx]['items'][$i]['destacado'] = false;
                        $changed = true;
                    }
                }
            }
        }
    }

    if ($changed && !$dry_run) {
        update_field('modules', $modules, $post_id);
        WP_CLI::success("post={$post_id} actualizado.");
        $migrated++;
    } elseif ($changed && $dry_run) {
        WP_CLI::warning("post={$post_id} cambiaría (dry-run).");
        $migrated++;
    } else {
        $skipped++;
    }
}

WP_CLI::log("─────────────────────────────────");
WP_CLI::log("Migración completada. Tocados: {$migrated}, Saltados: {$skipped}");
if ($dry_run) {
    WP_CLI::warning("DRY RUN — ningún cambio se persistió.");
}
```

**Notas críticas del script**:

- Solo escribe campos **nuevos**. Nunca toca `fondo`, `sliderhero`, etc.
- `update_field('modules', ...)` reescribe todo el flexible — por eso primero leemos el array completo, mutamos, y guardamos. ACF acepta esto como una operación atómica.
- Idempotente: el `array_key_exists` y el `empty($row['variante'])` evitan reescrituras.
- DRY RUN obligatorio en primer pase para auditar el log.

---

## 4. Orden recomendado de implementación

| # | Módulo         | Razón del orden                                                                                              |
|---|----------------|-------------------------------------------------------------------------------------------------------------|
| 1 | **Blog**       | No requiere migración ACF. Cambio puramente de código. Permite validar el nuevo design system en una pieza simple. |
| 2 | **Testimonios**| 1 campo nuevo (booleano), bajo riesgo. Default `false` es no-op visual. Sirve para validar el script de migración con un caso fácil. |
| 3 | **CTA**        | Mapping `fondo`→`variante` requiere lógica. Antes de hero porque el cube reusa pieza ya creada en footer (Fase 1). |
| 4 | **Hero**       | Mayor superficie visual. Coordinar con `seo-manager` (LCP del vídeo). Slides existentes deben renderizar idénticos en `tipo_fondo=image`. |
| 5 | **Formulario** | Refactor de CSS de CF7 — depende de que los atoms shadcn (`input.php`, `button.php`) estén estables y validados. |

---

## 5. Estrategia de rollback

Cada módulo tiene 3 puntos de retroceso:

### 5.1 Antes de migrar (preventivo)

- **Snapshot de la BD** (`wp db export pre-fase2-{module}.sql`) ANTES de correr el script de migración.
- **Snapshot de Customizer mods** no aplica aquí (los nuevos campos son ACF, no Customizer).
- Tag git: `pre-fase2-{module}` antes de mergear el código del módulo.

### 5.2 Durante (DRY RUN)

- Correr siempre `BP_MIGRATE_DRY_RUN=true` antes del run real. Auditar el log.
- Si el log muestra mappings inesperados (ej. `fondo='primary'` mapeado a `brand` cuando el cliente lo quería como `surface`), ajustar `$cta_fondo_to_variante` y re-correr DRY RUN.

### 5.3 Después de migrar (curativo)

- **Datos**: los campos viejos siguen en la BD. Para revertir, simplemente desplegar el código viejo del módulo: leerá `fondo` en lugar de `variante` y todo vuelve.
- **Código**: `git revert <merge-commit>` deshace el módulo y los partials nuevos.
- **ACF**: los campos nuevos quedan declarados en `inc/acf-modules.php`. Si se hace rollback de código, esos campos quedarán con valor pero no se leerán → no hace daño.
- **Borrado defensivo**: la limpieza de campos viejos (drop `fondo`, etc.) **no se hace en Fase 2**. Es una Fase 3 separada con auditoría manual de qué páginas usan qué.

### 5.4 Botón de pánico

```bash
# Si todo se rompe en producción:
wp db import pre-fase2-{module}.sql           # restaura BD
git checkout pre-fase2-{module} -- modules/   # restaura módulos
# (no tocar inc/acf-modules.php — los campos nuevos no rompen aunque existan)
```

---

## 6. Checklist por módulo (gate de PR)

Cada PR de Fase 2 debe cumplir:

- [ ] Campo nuevo declarado en `inc/acf-modules.php` con `conditional_logic` correcto.
- [ ] Render PHP del módulo lee primero el campo nuevo, fallback al viejo (convivencia).
- [ ] Salida temprana si el campo principal está vacío (invariante boilerplate).
- [ ] Todo output escapado (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`).
- [ ] El `page-demo.php` muestra el módulo nuevo + el viejo lado a lado para visual diff.
- [ ] DRY RUN del script ejecutado y log adjunto al PR.
- [ ] Tag git `pre-fase2-{module}` creado.
- [ ] `wp-security` revisó el partial PHP nuevo.
- [ ] `seo-manager` revisó performance (LCP) si el módulo afecta hero.

---

## 7. Coordinación con Customizer footer

Recordatorio: el footer Kresna (Fase 1) **no usa ACF** — sus textos viven en Customizer (`inc/customizer-footer.php`). No mezclar la migración de los 7 campos ACF de Fase 2 con cambios al Customizer footer; son scopes independientes.

Si un cliente quiere convertir parte del footer a ACF (ej. CTA configurable por post), eso es una decisión de Fase 3, fuera del alcance de este plan.
