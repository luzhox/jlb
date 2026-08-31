<?php
/**
 * Importa el material REAL de los niveles (Drive "Contenidos/Niveles") a los
 * 4 CPT nivel ya sembrados por bin/seed-niveles.php.
 *
 * Mapeo por nivel:
 *   Foto portada  → hero_imagen
 *   Foto 1        → gal_ancha
 *   Foto 2        → gal_angosta
 *   Foto 3        → gal_full (fila inferior)
 *   "Aplica hoy…" → obj_card_imagen (card CTA; imagen distinta por nivel)
 * Bachillerato solo trae portada — el resto de sus campos no se toca.
 *
 * Espera archivos staged nivel-<slug>-*.{jpg,png}; el directorio staged se
 * pasa como 1er argumento del eval-file.
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-niveles-material.php "<dir-staged>"
 */

if (!defined('ABSPATH')) {
    exit('Run via wp eval-file.');
}
if (!function_exists('update_field')) {
    WP_CLI::error('ACF Pro no activo.');
}

$staged = isset($args[0]) ? rtrim($args[0], '/') : '';
if ($staged === '' || !is_dir($staged)) {
    WP_CLI::error("Directorio staged no encontrado: {$staged}");
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/** Sube un archivo staged (jpg o png, el que exista) a la mediateca. Reejecutable. */
function jlb_niv_upload($staged, $base, $title) {
    $path = '';
    foreach (array('jpg', 'png') as $ext) {
        if (file_exists("{$staged}/{$base}.{$ext}")) {
            $path = "{$staged}/{$base}.{$ext}";
            break;
        }
    }
    if ($path === '') {
        return 0; // material no incluido para este nivel — silencioso
    }
    $prev = get_posts(array(
        'post_type' => 'attachment', 'numberposts' => 1, 'fields' => 'ids',
        'title' => $title,
    ));
    if ($prev) {
        return (int) $prev[0];
    }
    $tmp = wp_tempnam(basename($path));
    copy($path, $tmp);
    $id = media_handle_sideload(array('name' => basename($path), 'tmp_name' => $tmp), 0, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);
        WP_CLI::warning("Fallo subiendo {$base}: " . $id->get_error_message());
        return 0;
    }
    return (int) $id;
}

$niveles = array(
    'inicial'      => 'Inicial',
    'primaria'     => 'Primaria',
    'secundaria'   => 'Secundaria',
    'bachillerato' => 'Bachiller', // matchea "Bachiller" y "Bachillerato"
);

foreach ($niveles as $slug => $kw) {
    $pid = 0;
    foreach (get_posts(array('post_type' => 'nivel', 'numberposts' => -1)) as $p) {
        if (mb_stripos($p->post_title, $kw) !== false) {
            $pid = (int) $p->ID;
            break;
        }
    }
    if (!$pid) {
        WP_CLI::warning("Nivel '{$kw}' no encontrado — omitido.");
        continue;
    }

    $map = array(
        'hero_imagen'     => jlb_niv_upload($staged, "nivel-{$slug}-portada", "Nivel {$kw} — portada"),
        'gal_ancha'       => jlb_niv_upload($staged, "nivel-{$slug}-foto1", "Nivel {$kw} — foto 1"),
        'gal_angosta'     => jlb_niv_upload($staged, "nivel-{$slug}-foto2", "Nivel {$kw} — foto 2"),
        'gal_full'        => jlb_niv_upload($staged, "nivel-{$slug}-foto3", "Nivel {$kw} — foto 3"),
        'obj_card_imagen' => jlb_niv_upload($staged, "nivel-{$slug}-aplica", "Nivel {$kw} — card Aplica hoy"),
    );

    $set = array();
    foreach ($map as $field => $att) {
        if ($att) {
            update_field($field, $att, $pid);
            $set[] = "{$field}={$att}";
        }
    }
    WP_CLI::log("OK nivel {$pid} ({$kw}): " . ($set ? implode(' ', $set) : 'sin archivos'));
}

WP_CLI::success('Material de niveles importado.');
