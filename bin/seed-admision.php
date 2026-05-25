<?php
/**
 * Seeder de la página "Admisión" (Figma 4126:212).
 * Crea/actualiza la página, sube imágenes y puebla los 5 módulos ACF.
 *
 * Uso: wp eval-file bin/seed-admision.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_field')) { WP_CLI::error('ACF Pro no activo.'); }

function jlb_adm_upload($filename, $alt = '') {
    $path = get_template_directory() . '/assets/figma-home/admision/' . $filename;
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

$hero_img = jlb_adm_upload('hero.jpg', 'Niños en el aula del colegio Jean Le Boulch');
$gal_w    = jlb_adm_upload('galeria-ancha.jpg', 'Estudiantes en actividad escolar');
$gal_n    = jlb_adm_upload('galeria-angosta.png', 'Estudiante del colegio Jean Le Boulch');

$page = get_page_by_path('admision', OBJECT, 'page');
if ($page) {
    $pid = (int) $page->ID;
    wp_update_post(array('ID' => $pid, 'post_status' => 'publish', 'post_title' => 'Admisión'));
    WP_CLI::log("Página 'Admisión' existe (ID $pid).");
} else {
    $pid = wp_insert_post(array('post_title' => 'Admisión', 'post_name' => 'admision', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => ''), true);
    if (is_wp_error($pid)) { WP_CLI::error('No pude crear la página: ' . $pid->get_error_message()); }
    WP_CLI::log("Página 'Admisión' creada (ID $pid).");
}

$modules = array(
    array(
        'acf_fc_layout' => 'jlb_admision_hero',
        'titulo'    => 'Educación integral para tu hijo',
        'subtitulo' => 'Les invitamos a ser parte de la familia Jean Le Boulch',
        'imagen'    => $hero_img,
        'botones'   => array(
            array('texto' => 'Contáctanos', 'url' => '#contacto', 'target' => '_self'),
            array('texto' => 'Proceso de admisión', 'url' => '#proceso', 'target' => '_self'),
        ),
    ),
    array(
        'acf_fc_layout' => 'jlb_proceso',
        'eyebrow' => 'Proceso de admisión',
        'titulo'  => 'Conoce cómo postular en 3 simples pasos',
        'pasos'   => array(
            array(
                'etiqueta' => '1. Recepción',
                'intro'    => 'Presentar la solicitud de vacante con los datos correspondientes cumpliendo los requisitos:',
                'requisitos' => array(
                    array('texto' => 'DNI del postulante (copia simple)'),
                    array('texto' => 'DNI de los padres (copia simple)'),
                    array('texto' => 'Partida de nacimiento del postulante'),
                    array('texto' => 'Informe académico (libreta de notas) del año anterior'),
                ),
            ),
            array(
                'etiqueta' => '2. Entrevistas',
                'intro'    => 'Entrevista personal con el equipo psicopedagógico y evaluación del postulante según su nivel.',
                'requisitos' => array(
                    array('texto' => 'Entrevista con los padres de familia'),
                    array('texto' => 'Evaluación del postulante según el grado'),
                ),
            ),
            array(
                'etiqueta' => '3. Resultados',
                'intro'    => 'Comunicación de resultados y reserva de vacante del postulante admitido.',
                'requisitos' => array(
                    array('texto' => 'Comunicación de resultados por correo'),
                    array('texto' => 'Reserva de vacante y matrícula'),
                ),
            ),
        ),
    ),
    array(
        'acf_fc_layout' => 'jlb_cuota',
        'eyebrow' => 'Cuota de ingreso',
        'titulo'  => 'Conoce cuál sería la cuota de ingreso',
        'ver_condiciones' => array('title' => 'Ver condiciones', 'url' => '#condiciones', 'target' => '_self'),
        'niveles' => array(
            array('nombre' => 'Inicial',    'cuota_contado' => 'US$3500', 'cuota_cuotas' => 'US$5000', 'ahorro' => '$1500'),
            array('nombre' => 'Primaria',   'cuota_contado' => 'US$4000', 'cuota_cuotas' => 'US$5500', 'ahorro' => '$1500'),
            array('nombre' => 'Secundaria', 'cuota_contado' => 'US$4500', 'cuota_cuotas' => 'US$6000', 'ahorro' => '$1500'),
        ),
    ),
    array(
        'acf_fc_layout' => 'jlb_galeria',
        'imagen_ancha'   => $gal_w,
        'imagen_angosta' => $gal_n,
    ),
    array(
        'acf_fc_layout' => 'jlb_faq',
        'titulo' => 'Preguntas frecuentes',
        'preguntas' => array(
            array('pregunta' => '¿Hay una fecha límite para la solicitud de ingreso?', 'respuesta' => 'El proceso de admisión permanece abierto durante el año mientras existan vacantes por nivel. Te recomendamos iniciar tu solicitud cuanto antes.'),
            array('pregunta' => '¿Se ofrece algún tipo de beca o ayuda financiera?', 'respuesta' => 'Contamos con un programa de apoyo evaluado caso por caso. Escríbenos para conocer los requisitos y condiciones vigentes.'),
            array('pregunta' => '¿Cuáles son las pensiones y otros costos asociados?', 'respuesta' => 'La cuota de ingreso y las pensiones varían según el nivel educativo. Usa la calculadora de esta página o solicita el detalle a admisión.'),
            array('pregunta' => '¿El colegio ofrece programas de educación especial?', 'respuesta' => 'Brindamos acompañamiento psicopedagógico e inclusión educativa según las necesidades de cada estudiante.'),
        ),
    ),
);

$ok = update_field('modules', $modules, $pid);
clean_post_cache($pid);
WP_CLI::log('update_field(modules) => ' . var_export($ok, true));
WP_CLI::success('Admisión poblada (ID ' . $pid . '). URL: ' . get_permalink($pid));
