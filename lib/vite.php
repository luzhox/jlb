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
 * Devuelve true cuando se deben servir los assets desde el Vite dev server.
 *
 * Dos modos (en este orden de prioridad):
 *   1) Override explícito: si `VITE_DEV_SERVER` está definida en wp-config.php,
 *      manda su valor (true = dev, false = producción). Útil para forzar un modo.
 *   2) Auto-detección (recomendado, sin tocar wp-config): si la constante NO
 *      está definida y el entorno es `local`, se activa el modo dev cuando Vite
 *      está corriendo, detectado por el hot file `.vite-hot` que el dev server
 *      crea al arrancar y borra al cerrar (ver vite.config.js). Así el modo
 *      sigue a `npm run dev` / `npm run build` automáticamente.
 *
 * En producción (WP_ENVIRONMENT_TYPE != 'local') nunca entra en dev salvo que
 * se defina explícitamente la constante.
 */
function bp_is_vite_dev(): bool {
    if (defined('VITE_DEV_SERVER')) {
        return VITE_DEV_SERVER === true;
    }

    $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
    if ($env === 'local') {
        return file_exists(get_template_directory() . '/.vite-hot');
    }

    return false;
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
