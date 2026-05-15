<?php
/**
 * scripts/migrate-acf-fase2.php
 *
 * Migración idempotente de los campos ACF de Fase 2 para los módulos:
 *   - hero        (+overline_manuscrito, tipo_fondo)
 *   - cta         (+variante, cube_visible, video_fondo)
 *   - testimonios (+items.destacado)
 *
 * NO toca campos viejos (sliderhero, fondo, etc.). La convivencia es total:
 * los módulos PHP leen el campo nuevo; si está vacío, mapean inline desde
 * el viejo. La eliminación de los viejos es una Fase 3 separada.
 *
 * USO:
 *   # DRY RUN (default activo) — ningún cambio se persiste:
 *   wp eval-file wp-content/themes/boilerplate-wordpress/scripts/migrate-acf-fase2.php
 *
 *   # PRODUCTION RUN — pasa --no-dry-run como argumento posicional:
 *   wp eval-file wp-content/themes/boilerplate-wordpress/scripts/migrate-acf-fase2.php --no-dry-run
 *
 *   # Filtrar por módulo:
 *   wp eval-file wp-content/themes/boilerplate-wordpress/scripts/migrate-acf-fase2.php --module=cta
 *   wp eval-file wp-content/themes/boilerplate-wordpress/scripts/migrate-acf-fase2.php --module=hero --no-dry-run
 *
 *   # También se puede definir constantes ANTES del eval (compat con plan original):
 *   define('BP_MIGRATE_DRY_RUN', false);
 *   define('BP_MIGRATE_MODULES', 'cta,testimonios');
 *
 * Args reconocidos (posicionales tras `eval-file`):
 *   --no-dry-run            Ejecuta de verdad. Default: dry run (true).
 *   --dry-run               Fuerza dry run (redundante con default).
 *   --module=<slug>         Filtra módulo. Slugs: hero, cta, testimonios, all.
 *
 * SALIDA (loguea cada acción):
 *   [post=123] hero  idx=0 → tipo_fondo='swiper' (default Fase 2)
 *   [post=123] cta   idx=1 → variante='brand' (de fondo='primary')
 *   ...
 *   ─── Migración completada. Posts tocados: 7, Saltados: 12.
 */

// ── Detectar args (CLI WP-CLI) ──────────────────────────────────────────────
$argv = isset($args) && is_array($args) ? $args : (isset($GLOBALS['argv']) ? $GLOBALS['argv'] : array());

$dry_run     = true;
$only_modules = array('hero', 'cta', 'testimonios');

foreach ((array) $argv as $arg) {
    if ($arg === '--no-dry-run') {
        $dry_run = false;
    } elseif ($arg === '--dry-run') {
        $dry_run = true;
    } elseif (strpos($arg, '--module=') === 0) {
        $val = substr($arg, strlen('--module='));
        if ($val && $val !== 'all') {
            $only_modules = array_map('trim', explode(',', $val));
        }
    }
}

// Constantes (override de los flags) — compatibilidad con plan original.
if (defined('BP_MIGRATE_DRY_RUN')) {
    $dry_run = (bool) BP_MIGRATE_DRY_RUN;
}
if (defined('BP_MIGRATE_MODULES')) {
    $val = (string) BP_MIGRATE_MODULES;
    if ($val !== '' && $val !== 'all') {
        $only_modules = array_map('trim', explode(',', $val));
    }
}

$is_wpcli = defined('WP_CLI') && class_exists('WP_CLI');
$log = function ($msg, $level = 'log') use ($is_wpcli) {
    if ($is_wpcli) {
        if ($level === 'success') WP_CLI::success($msg);
        elseif ($level === 'warning') WP_CLI::warning($msg);
        elseif ($level === 'error') WP_CLI::error($msg);
        else WP_CLI::log($msg);
    } else {
        echo $msg . "\n";
    }
};

// ── Pre-flight ──────────────────────────────────────────────────────────────
if (!function_exists('get_field') || !function_exists('update_field')) {
    $log('ACF no está activo. Aborto.', 'error');
    return;
}

$log('─────────────────────────────────────────────');
$log('Migración ACF Fase 2 — boilerplate-wordpress');
$log(sprintf('Modo: %s', $dry_run ? 'DRY RUN (no escribe)' : 'PRODUCTION (escribe)'));
$log(sprintf('Módulos: %s', implode(', ', $only_modules)));
$log('─────────────────────────────────────────────');

// ── Mapping CTA fondo → variante ────────────────────────────────────────────
$cta_fondo_to_variante = array(
    'primary' => 'brand',
    'dark'    => 'dark',
    'light'   => 'surface',
    ''        => 'surface',
);

// ── Query — todos los posts/páginas con flexible 'modules' ──────────────────
$query = new WP_Query(array(
    'post_type'      => array('page', 'post'),
    'posts_per_page' => -1,
    'post_status'    => array('publish', 'draft', 'private', 'pending', 'future'),
    'fields'         => 'ids',
    'no_found_rows'  => true,
));

$migrated = 0;
$skipped  = 0;
$rows_changed = 0;

foreach ($query->posts as $post_id) {
    $modules = get_field('modules', $post_id);
    if (!$modules || !is_array($modules)) {
        $skipped++;
        continue;
    }

    $changed = false;

    foreach ($modules as $idx => $row) {
        $layout = isset($row['acf_fc_layout']) ? (string) $row['acf_fc_layout'] : '';

        // ── HERO ────────────────────────────────────────────────────────────
        if ($layout === 'hero' && in_array('hero', $only_modules, true)) {
            if (!array_key_exists('overline_manuscrito', $row)) {
                $modules[$idx]['overline_manuscrito'] = '';
                $changed = true;
                $rows_changed++;
                $log(sprintf('[post=%d] hero  idx=%d → overline_manuscrito=\'\'', $post_id, $idx));
            }
            if (empty($row['tipo_fondo'])) {
                $modules[$idx]['tipo_fondo'] = 'swiper';
                $changed = true;
                $rows_changed++;
                $log(sprintf('[post=%d] hero  idx=%d → tipo_fondo=\'swiper\' (preserva slider legacy)', $post_id, $idx));
            }
            if (!array_key_exists('video_fondo', $row)) {
                $modules[$idx]['video_fondo'] = '';
                $changed = true;
                // No incrementamos rows_changed: es solo placeholder vacío.
            }
        }

        // ── CTA ─────────────────────────────────────────────────────────────
        if ($layout === 'cta' && in_array('cta', $only_modules, true)) {
            $fondo_old = isset($row['fondo']) ? (string) $row['fondo'] : '';
            $variante_new = $cta_fondo_to_variante[$fondo_old] ?? 'surface';

            if (empty($row['variante'])) {
                $modules[$idx]['variante'] = $variante_new;
                $changed = true;
                $rows_changed++;
                $log(sprintf('[post=%d] cta   idx=%d → variante=\'%s\' (de fondo=\'%s\')', $post_id, $idx, $variante_new, $fondo_old));
            }
            if (!array_key_exists('cube_visible', $row)) {
                $modules[$idx]['cube_visible'] = false;
                $changed = true;
                $rows_changed++;
                $log(sprintf('[post=%d] cta   idx=%d → cube_visible=false', $post_id, $idx));
            }
            if (!array_key_exists('video_fondo', $row)) {
                $modules[$idx]['video_fondo'] = '';
                $changed = true;
                // Placeholder vacío; no contamos.
            }
        }

        // ── TESTIMONIOS ─────────────────────────────────────────────────────
        if ($layout === 'testimonios' && in_array('testimonios', $only_modules, true)) {
            if (!empty($row['items']) && is_array($row['items'])) {
                foreach ($row['items'] as $i => $item) {
                    if (!array_key_exists('destacado', $item)) {
                        $modules[$idx]['items'][$i]['destacado'] = false;
                        $changed = true;
                        $rows_changed++;
                        $log(sprintf('[post=%d] testimonios idx=%d item=%d → destacado=false', $post_id, $idx, $i));
                    }
                }
            }
        }
    }

    if ($changed) {
        if (!$dry_run) {
            update_field('modules', $modules, $post_id);
            $log(sprintf('[post=%d] guardado.', $post_id), 'success');
        } else {
            $log(sprintf('[post=%d] cambiaría (DRY RUN).', $post_id), 'warning');
        }
        $migrated++;
    } else {
        $skipped++;
    }
}

$log('─────────────────────────────────────────────');
$log(sprintf('Migración completada. Posts tocados: %d, Saltados: %d, Filas migradas: %d', $migrated, $skipped, $rows_changed));
if ($dry_run) {
    $log('DRY RUN — ningún cambio se persistió. Re-ejecuta con --no-dry-run para aplicar.', 'warning');
}
