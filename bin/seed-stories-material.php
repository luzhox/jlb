<?php
/**
 * Carga los videos testimoniales del Drive ("Contenidos/Feed/04 Videos
 * testimoniales") como STORIES del módulo jlb_experience del home (repeater
 * `stories`: etiqueta + video_url MP4 self-hosted + poster vertical extraído
 * con ffmpeg). Reemplaza el contenido previo del repeater.
 *
 * Espera archivos staged story-<slug>.mp4 + story-<slug>-poster.jpg;
 * el directorio staged se pasa como 1er argumento del eval-file.
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-stories-material.php "<dir-staged>"
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

$front_id = (int) get_option('page_on_front');
if (!$front_id) {
    WP_CLI::error('No hay página frontal configurada.');
}

function jlb_story_upload($path, $title) {
    if (!file_exists($path)) {
        WP_CLI::warning('No existe staged: ' . basename($path));
        return 0;
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
        WP_CLI::warning('Fallo subiendo ' . basename($path) . ': ' . $id->get_error_message());
        return 0;
    }
    return (int) $id;
}

// Orden = orden de la tira.
$defs = array(
    array('slug' => 'experiencia-jlb', 'etiqueta' => 'Experiencia JLB'),
    array('slug' => 'padres',          'etiqueta' => 'Padres de familia'),
    array('slug' => 'nicole',          'etiqueta' => 'Nicole · Exalumna'),
    array('slug' => 'sebastian',       'etiqueta' => 'Sebastián · Exalumno'),
);

$stories = array();
foreach ($defs as $d) {
    $vid    = jlb_story_upload("{$staged}/story-{$d['slug']}.mp4", "Story — {$d['etiqueta']} (video)");
    $poster = jlb_story_upload("{$staged}/story-{$d['slug']}-poster.jpg", "Story — {$d['etiqueta']} (poster)");
    if (!$vid) {
        continue;
    }
    $stories[] = array(
        'etiqueta'  => $d['etiqueta'],
        'video_url' => wp_get_attachment_url($vid),
        'poster'    => $poster,
    );
    WP_CLI::log("story {$d['slug']}: video={$vid} poster={$poster}");
}
if (!$stories) {
    WP_CLI::error('Ningún video staged encontrado.');
}

$rows = get_field('modules', $front_id);
if (!$rows) {
    WP_CLI::error('El home no tiene módulos.');
}
$done = false;
foreach ($rows as $i => $row) {
    if ($row['acf_fc_layout'] === 'jlb_experience') {
        $rows[$i]['stories'] = $stories;
        $done = true;
        break;
    }
}
if (!$done) {
    WP_CLI::error('No hay módulo jlb_experience en el home.');
}
update_field('modules', $rows, $front_id);

WP_CLI::success(count($stories) . ' stories cargadas en jlb_experience.');
