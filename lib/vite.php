<?php
/**
 * Vite — WordPress asset helper
 *
 * Detecta modo desarrollo vs producción via WP_ENVIRONMENT_TYPE.
 *
 * En wp-config.php (local):
 *   define('WP_ENVIRONMENT_TYPE', 'local');
 *
 * En producción:
 *   define('WP_ENVIRONMENT_TYPE', 'production');
 */

define('VITE_PORT', 5173);
define('VITE_HOST', 'http://localhost:' . VITE_PORT);

/**
 * Devuelve true SOLO cuando el Vite dev server está corriendo.
 *
 * Para activar el modo dev server, añade en wp-config.php:
 *   define('VITE_DEV_SERVER', true);
 *
 * Y luego ejecuta: npm run dev
 *
 * Por defecto siempre usa el manifest de producción (build/).
 */
function bp_is_vite_dev(): bool {
    return defined('VITE_DEV_SERVER') && VITE_DEV_SERVER === true;
}

function bp_vite_manifest(): array {
    static $manifest = null;

    if ($manifest === null) {
        $path = get_template_directory() . '/build/.vite/manifest.json';
        $manifest = file_exists($path)
            ? (array) json_decode(file_get_contents($path), true)
            : array();
    }

    return $manifest;
}

/**
 * URL del asset JS/CSS desde el manifest de producción.
 *
 * @param string $entry  Ruta relativa al entry, ej: 'src/main.js'
 */
function bp_vite_asset(string $entry): string {
    if (bp_is_vite_dev()) {
        return VITE_HOST . '/' . ltrim($entry, '/');
    }

    $manifest = bp_vite_manifest();
    $file     = $manifest[$entry]['file'] ?? '';

    return $file
        ? get_template_directory_uri() . '/build/' . $file
        : '';
}

/**
 * URL del CSS generado por Vite para un entry JS dado.
 * Retorna vacío en dev (Vite inyecta CSS via HMR).
 *
 * @param string $entry  Ruta del entry JS, ej: 'src/main.js'
 */
function bp_vite_css(string $entry): string {
    if (bp_is_vite_dev()) {
        return ''; // Vite HMR inyecta CSS automáticamente
    }

    $manifest  = bp_vite_manifest();
    $css_files = $manifest[$entry]['css'] ?? array();

    return !empty($css_files)
        ? get_template_directory_uri() . '/build/' . $css_files[0]
        : '';
}

/**
 * Registra el filtro para añadir type="module" a scripts Vite.
 */
function bp_vite_module_tag(string $handle): void {
    add_filter('script_loader_tag', function (string $tag, string $h) use ($handle): string {
        if ($h === $handle) {
            return str_replace('<script ', '<script type="module" crossorigin ', $tag);
        }
        return $tag;
    }, 10, 2);
}
