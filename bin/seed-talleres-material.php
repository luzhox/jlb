<?php
/**
 * Importa el material REAL de los talleres (Drive "Contenidos/Talleres") a los
 * 5 CPT taller ya sembrados por bin/seed-talleres.php.
 *
 * Mapeo por taller:
 *   Foto portada      → hero_imagen
 *   Foto 1 / Foto 2   → gal_ancha / gal_angosta
 *   "Aplica hoy…"     → obj_card_imagen (card CTA)
 *   Video *.mp4       → video_url (MP4 self-hosted) — solo Arte y Robótica.
 *                       El poster vertical (frame 1080×1920 extraído con
 *                       ffmpeg) activa el layout vertical de la sección video.
 *
 * Espera los archivos ya staged con nombres limpios (taller-<slug>-*.{jpg,png,mp4});
 * el directorio staged se pasa como 1er argumento del eval-file.
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-talleres-material.php "<dir-staged>"
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

/** Sube un archivo staged a la mediateca (copia temporal: sideload borra el tmp). */
function jlb_mat_upload($staged, $file, $title) {
    $path = $staged . '/' . $file;
    if (!file_exists($path)) {
        WP_CLI::warning("No existe: {$file} — omitido.");
        return 0;
    }
    // ¿Ya importado? (reejecutable sin duplicar)
    $prev = get_posts(array(
        'post_type' => 'attachment', 'numberposts' => 1, 'fields' => 'ids',
        'title' => $title,
    ));
    if ($prev) {
        return (int) $prev[0];
    }
    $tmp = wp_tempnam($file);
    copy($path, $tmp);
    $id = media_handle_sideload(array('name' => $file, 'tmp_name' => $tmp), 0, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);
        WP_CLI::warning("Fallo subiendo {$file}: " . $id->get_error_message());
        return 0;
    }
    return (int) $id;
}

/** Primer taller cuyo título contiene la palabra clave. */
function jlb_mat_taller($keyword) {
    foreach (get_posts(array('post_type' => 'taller', 'numberposts' => -1)) as $p) {
        if (mb_stripos($p->post_title, $keyword) !== false) {
            return (int) $p->ID;
        }
    }
    return 0;
}

$talleres = array(
    'arte'     => array('kw' => 'Artes', 'video' => true,
        'copy_t' => 'El arte en acción',
        'copy_x' => 'Un vistazo al taller: color, imaginación y manos a la obra. Así exploran y crean nuestros estudiantes en cada sesión.'),
    'robotica' => array('kw' => 'Robótica', 'video' => true,
        'copy_t' => 'Robótica en acción',
        'copy_x' => 'Construir, programar y probar: así se ve una sesión del taller, donde las ideas se convierten en proyectos reales.'),
    'teatro'   => array('kw' => 'Teatro', 'video' => false),
    'danza'    => array('kw' => 'Danza', 'video' => false),
    'musica'   => array('kw' => 'Música', 'video' => false),
);

foreach ($talleres as $slug => $t) {
    $pid = jlb_mat_taller($t['kw']);
    if (!$pid) {
        WP_CLI::warning("Taller '{$t['kw']}' no encontrado — omitido.");
        continue;
    }

    $ext1 = file_exists("{$staged}/taller-{$slug}-foto1.png") ? 'png' : 'jpg';
    $ext2 = file_exists("{$staged}/taller-{$slug}-foto2.png") ? 'png' : 'jpg';

    $portada = jlb_mat_upload($staged, "taller-{$slug}-portada.jpg", "Taller {$t['kw']} — portada");
    $foto1   = jlb_mat_upload($staged, "taller-{$slug}-foto1.{$ext1}", "Taller {$t['kw']} — foto 1");
    $foto2   = jlb_mat_upload($staged, "taller-{$slug}-foto2.{$ext2}", "Taller {$t['kw']} — foto 2");
    $aplica  = jlb_mat_upload($staged, "taller-{$slug}-aplica.jpg", "Talleres — card Aplica hoy");

    if ($portada) update_field('hero_imagen', $portada, $pid);
    if ($foto1)   update_field('gal_ancha', $foto1, $pid);
    if ($foto2)   update_field('gal_angosta', $foto2, $pid);
    if ($aplica)  update_field('obj_card_imagen', $aplica, $pid);

    if ($t['video']) {
        $vid    = jlb_mat_upload($staged, "taller-{$slug}-video.mp4", "Taller {$t['kw']} — video");
        $poster = jlb_mat_upload($staged, "taller-{$slug}-video-poster.jpg", "Taller {$t['kw']} — video poster");
        if ($vid) {
            update_field('video_url', wp_get_attachment_url($vid), $pid);
        }
        if ($poster) update_field('video_poster', $poster, $pid);
        update_field('video_eyebrow', 'Video', $pid);
        update_field('video_titulo', $t['copy_t'], $pid);
        update_field('video_texto', $t['copy_x'], $pid);
    }

    WP_CLI::log("OK taller {$pid} ({$t['kw']}): portada={$portada} foto1={$foto1} foto2={$foto2} aplica={$aplica}");
}

WP_CLI::success('Material de talleres importado.');
