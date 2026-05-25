<?php
/**
 * Seeder de la página "Open Day" (Figma 4172:416).
 * Crea/actualiza la página, sube la imagen del hero y puebla los módulos ACF:
 *   [jlb_admision_hero] (reutilizado: eyebrow + video) + [jlb_open_day_form].
 *
 * Uso: wp eval-file bin/seed-openday.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_field')) { WP_CLI::error('ACF Pro no activo.'); }

function jlb_od_upload($filename, $alt = '') {
    $path = get_template_directory() . '/assets/figma-home/openday/' . $filename;
    if (!file_exists($path)) { WP_CLI::warning("No existe: $path"); return 0; }
    $base = basename($path);
    $ex = get_posts(array(
        'post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE')),
    ));
    if ($ex) { $id = (int) $ex[0]; if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt); return $id; }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $up = wp_upload_dir();
    $target = trailingslashit($up['path']) . $base;
    if (!copy($path, $target)) { WP_CLI::warning("copy falló: $base"); return 0; }
    $ft = wp_check_filetype($base, null);
    $id = wp_insert_attachment(array(
        'guid' => trailingslashit($up['url']) . $base, 'post_mime_type' => $ft['type'],
        'post_title' => sanitize_file_name(pathinfo($base, PATHINFO_FILENAME)), 'post_status' => 'inherit',
    ), $target);
    if (is_wp_error($id) || !$id) { WP_CLI::warning("insert falló: $base"); return 0; }
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $target));
    if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt);
    WP_CLI::log("  · Subida $base → ID $id");
    return (int) $id;
}

$hero_img  = jlb_od_upload('hero.jpg', 'Instalaciones del Colegio Jean Le Boulch');
$logo_img  = jlb_od_upload('open-day-logo.png', '¡Open Day!');

$page = get_page_by_path('open-day', OBJECT, 'page');
if ($page) {
    $pid = (int) $page->ID;
    wp_update_post(array('ID' => $pid, 'post_status' => 'publish', 'post_title' => 'Open Day'));
    WP_CLI::log("Página 'Open Day' existe (ID $pid).");
} else {
    $pid = wp_insert_post(array('post_title' => 'Open Day', 'post_name' => 'open-day', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => ''), true);
    if (is_wp_error($pid)) { WP_CLI::error('No pude crear la página: ' . $pid->get_error_message()); }
    WP_CLI::log("Página 'Open Day' creada (ID $pid).");
}

$modules = array(
    array(
        'acf_fc_layout' => 'jlb_admision_hero',
        'eyebrow'       => 'Admisión 2026 - 2027',
        'titulo'        => '¡Open Day!',
        'titulo_imagen' => $logo_img,
        'subtitulo'     => "Conoce nuestra propuesta educativa\nSábado 22 de noviembre de 9 a.m. – 2 p.m.",
        'imagen'        => $hero_img,
        'video_url'     => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
        'video_caption' => 'Ver recorrido virtual',
        'botones'       => array(
            array('texto' => 'Inscríbete ahora', 'url' => '#open-day-form', 'target' => '_self'),
        ),
    ),
    array(
        'acf_fc_layout' => 'jlb_open_day_form',
        'titulo'        => '',
    ),
);

$ok = update_field('modules', $modules, $pid);
clean_post_cache($pid);
WP_CLI::log('update_field(modules) => ' . var_export($ok, true));
WP_CLI::success('Open Day poblada (ID ' . $pid . '). URL: ' . get_permalink($pid));
