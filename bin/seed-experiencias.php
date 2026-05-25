<?php
/**
 * Seeder de la página "Experiencias innovadoras" (Figma 4179:1360).
 * Crea/actualiza la página, sube imágenes y puebla los módulos ACF:
 *   [jlb_admision_hero] (reutilizado) + [jlb_experiencias] (3 filas).
 *
 * Uso: wp eval-file bin/seed-experiencias.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_field')) { WP_CLI::error('ACF Pro no activo.'); }

function jlb_exp_upload($filename, $alt = '') {
    $path = get_template_directory() . '/assets/figma-home/experiencias/' . $filename;
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

$hero_img = jlb_exp_upload('hero.jpg', 'Estudiantes en una experiencia de aprendizaje');
$exp1     = jlb_exp_upload('exp-1.jpg', 'Reunión de convivencia en el aula');
$exp2     = jlb_exp_upload('exp-2.jpg', 'Alumnos explorando y experimentando');
$exp3     = jlb_exp_upload('exp-3.jpg', 'Actividad de desarrollo integral');

$page = get_page_by_path('experiencias-innovadoras', OBJECT, 'page');
if ($page) {
    $pid = (int) $page->ID;
    wp_update_post(array('ID' => $pid, 'post_status' => 'publish', 'post_title' => 'Experiencias innovadoras'));
    WP_CLI::log("Página 'Experiencias innovadoras' existe (ID $pid).");
} else {
    $pid = wp_insert_post(array('post_title' => 'Experiencias innovadoras', 'post_name' => 'experiencias-innovadoras', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => ''), true);
    if (is_wp_error($pid)) { WP_CLI::error('No pude crear la página: ' . $pid->get_error_message()); }
    WP_CLI::log("Página 'Experiencias innovadoras' creada (ID $pid).");
}

// Video de muestra para el lightbox (reemplazable desde wp-admin).
$video = 'https://www.youtube.com/watch?v=ScMzIvxBSi4';
$btn   = array('title' => 'Ver más información', 'url' => '#', 'target' => '');

$modules = array(
    array(
        'acf_fc_layout' => 'jlb_admision_hero',
        'titulo'    => 'Experiencias Innovadoras',
        'subtitulo' => 'Experiencias que marcan un cambio y llevan a la reflexión y aprendizaje integral del alumno.',
        'imagen'    => $hero_img,
        'botones'   => array(
            array('texto' => 'Ver experiencias', 'url' => '#experiencias', 'target' => '_self'),
        ),
    ),
    array(
        'acf_fc_layout' => 'jlb_experiencias',
        'experiencias'  => array(
            array(
                'titulo'    => 'Convivencia y democracia',
                'texto'     => 'Promovemos espacios en el aula donde recogemos y damos soluciones a las diversas propuestas y problemáticas de los niños, denominados reuniones de convivencia.',
                'video_url' => 'https://www.youtube.com/watch?v=BgYUJqKGt9M',
                'imagen'    => $exp1,
                'boton'     => $btn,
            ),
            array(
                'titulo'    => 'Filosofía en niños',
                'texto'     => 'Promovemos el aprendizaje de los alumnos a través de la experimentación, reflexión, investigación, exploración de su entorno y trabajo en equipo.',
                'video_url' => 'https://www.youtube.com/watch?v=Ox5HNcJZatk',
                'imagen'    => $exp2,
                'boton'     => $btn,
            ),
            array(
                'titulo'    => 'Desarrollo integral',
                'texto'     => 'Consideramos a la persona como una unidad indivisible: corporal, cognitivo, social y emocional. En todas nuestras actividades fomentamos el desarrollo de estas dimensiones.',
                'video_url' => 'https://www.youtube.com/watch?v=u5pnYeQzEco',
                'imagen'    => $exp3,
                'boton'     => $btn,
            ),
        ),
    ),
);

$ok = update_field('modules', $modules, $pid);
clean_post_cache($pid);
WP_CLI::log('update_field(modules) => ' . var_export($ok, true));
WP_CLI::success('Experiencias innovadoras poblada (ID ' . $pid . '). URL: ' . get_permalink($pid));
