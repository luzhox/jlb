<?php
/**
 * Seeder de la página "Nosotros" (bienvenida + historia/línea de tiempo).
 * Reutiliza jlb_admision_hero (hero) + jlb_manifesto (bienvenida) + jlb_timeline (nuevo).
 * Contenido de la historia: "40 años de historia" (Word del cliente).
 *
 * Uso: wp eval-file bin/seed-nosotros.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_field')) { WP_CLI::error('ACF Pro no activo.'); }

function jlb_nos_upload($filename, $alt = '') {
    $path = get_template_directory() . '/assets/figma-home/nosotros/' . $filename;
    if (!file_exists($path)) { WP_CLI::warning("No existe: $filename"); return 0; }
    $base = basename($path);
    $ex = get_posts(array('post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE'))));
    if ($ex) { $id = (int) $ex[0]; if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt); return $id; }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $up = wp_upload_dir();
    $target = trailingslashit($up['path']) . $base;
    if (!copy($path, $target)) { WP_CLI::warning("copy falló: $base"); return 0; }
    $ft = wp_check_filetype($base, null);
    if (empty($ft['type'])) { WP_CLI::warning("tipo no permitido: $base"); return 0; }
    $id = wp_insert_attachment(array('guid' => trailingslashit($up['url']) . $base, 'post_mime_type' => $ft['type'],
        'post_title' => sanitize_file_name(pathinfo($base, PATHINFO_FILENAME)), 'post_status' => 'inherit'), $target);
    if (is_wp_error($id) || !$id) { return 0; }
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $target));
    if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt);
    return (int) $id;
}

$img_bienvenida = jlb_nos_upload('bienvenida.webp', 'Estudiantes del Colegio Jean Le Boulch');
$h = array();
foreach (array(1,2,3,4,5,6,7,8) as $i) { $h[$i] = jlb_nos_upload("historia-$i.webp", 'Historia del Colegio Jean Le Boulch'); }
$img_dr = jlb_nos_upload('jlb-dr.webp', 'Dr. Jean Le Boulch');

$page = get_page_by_path('nosotros', OBJECT, 'page');
if ($page) {
    $pid = (int) $page->ID;
    wp_update_post(array('ID' => $pid, 'post_status' => 'publish', 'post_title' => 'Nosotros'));
} else {
    $pid = wp_insert_post(array('post_title' => 'Nosotros', 'post_name' => 'nosotros', 'post_status' => 'publish', 'post_type' => 'page'), true);
    if (is_wp_error($pid)) { WP_CLI::error('No pude crear la página: ' . $pid->get_error_message()); }
}

// Bienvenida (basada en la propuesta educativa de jlb.edu.pe/conocenos).
$manifiesto = '<p>El <strong>Colegio Jean Le Boulch</strong> forma personas a través del <strong>movimiento</strong> y el <strong>desarrollo integral</strong>. Inspirados en la pedagogía del educador francés Jean Le Boulch, buscamos que cada estudiante <strong>construya su propio aprendizaje</strong> mediante la experimentación, la exploración, la reflexión, la investigación y el <strong>trabajo en equipo</strong>, en un ambiente <strong>democrático, creativo y saludable</strong>.</p>';

$hitos = array(
    array('anio' => '1983', 'titulo' => 'Fundación', 'imagen' => $h[1],
        'texto' => 'Se crea oficialmente el Colegio Jean Le Boulch, fundado por la profesora Silvia Bravo Heredia, inspirado en la propuesta del educador francés Jean Le Boulch: la educación por el movimiento y el desarrollo integral del estudiante.'),
    array('anio' => '1984', 'titulo' => 'Inicio de actividades', 'imagen' => $h[2],
        'texto' => 'El colegio inicia clases en un local pequeño en el distrito de Lince, ofreciendo educación inicial y los primeros grados de primaria.'),
    array('anio' => '1987', 'titulo' => 'Consolidación del nivel primario', 'imagen' => 0,
        'texto' => 'El Ministerio de Educación autoriza el funcionamiento completo del nivel de educación primaria.'),
    array('anio' => '1990', 'titulo' => 'Expansión educativa', 'imagen' => $h[3],
        'texto' => 'Se autoriza el funcionamiento del nivel secundario, completando la oferta de educación básica.'),
    array('anio' => '1993', 'titulo' => 'Nuevo campus en La Molina', 'imagen' => $h[4],
        'texto' => 'El colegio se traslada a su local propio en el distrito de La Molina, donde continúa funcionando hasta la actualidad.'),
    array('anio' => '1994', 'titulo' => 'Primera promoción', 'imagen' => 0,
        'texto' => 'Egresa la primera promoción de 5to año de secundaria.'),
    array('anio' => '1995', 'titulo' => 'Desarrollo de infraestructura', 'imagen' => $h[5],
        'texto' => 'Se adquieren los campos deportivos frente al colegio, fortaleciendo el enfoque en la psicomotricidad, el deporte y el desarrollo integral, y consolidando una propuesta basada en aprendizaje vivencial, arte y convivencia democrática.'),
    array('anio' => '1995–1998', 'titulo' => 'Visitas del Dr. Jean Le Boulch', 'imagen' => $img_dr,
        'texto' => 'El Dr. Jean Le Boulch realiza tres visitas al Perú: dicta talleres de formación con nuestros docentes y niños, y una serie de conferencias magistrales en distintas universidades y para el público en general.'),
    array('anio' => '2007', 'titulo' => 'Cambio de Dirección', 'imagen' => $h[6],
        'texto' => 'Asume la Dirección General el Sr. José Taramona Bravo, quien participó en el proyecto desde los primeros años.'),
    array('anio' => '2010', 'titulo' => 'Innovación pedagógica', 'imagen' => 0,
        'texto' => 'Se crea el Área de Investigación y Desarrollo, impulsando la actualización docente y el intercambio de experiencias educativas con otros países.'),
    array('anio' => '2024', 'titulo' => '40 años formando personas', 'imagen' => $h[7],
        'texto' => 'El Colegio Jean Le Boulch celebra 40 años de trayectoria, reafirmando su compromiso con la formación integral, la innovación pedagógica y el desarrollo humano.'),
    array('anio' => '2025', 'titulo' => 'Camino al Bachillerato Internacional', 'imagen' => $h[8],
        'texto' => 'El colegio inicia la gestión para acceder al Bachillerato Internacional (IB), proyectando su incorporación para el 2028.'),
);

$modules = array(
    array(
        'acf_fc_layout' => 'jlb_admision_hero',
        'eyebrow'       => 'Nosotros',
        'titulo'        => 'Conócenos',
        'subtitulo'     => 'Más de 40 años formando niños y jóvenes autónomos, empáticos y críticos, a través del movimiento y el desarrollo integral.',
        'imagen'        => $img_bienvenida,
        'botones'       => array(
            array('texto' => 'Ver nuestra historia', 'url' => '#historia', 'target' => '_self'),
        ),
    ),
    array(
        'acf_fc_layout' => 'jlb_manifesto',
        'anchor'        => 'colegio',
        'texto'         => $manifiesto,
    ),
    array(
        'acf_fc_layout' => 'jlb_timeline',
        'eyebrow'       => 'Nuestra historia',
        'titulo'        => '42 años de historia',
        'hitos'         => array_map(function ($x) {
            return array('anio' => $x['anio'], 'titulo' => $x['titulo'], 'texto' => $x['texto'], 'imagen' => $x['imagen']);
        }, $hitos),
    ),
);

$ok = update_field('modules', $modules, $pid);
clean_post_cache($pid);
WP_CLI::log('update_field(modules) => ' . var_export($ok, true));
WP_CLI::success('Nosotros poblada (ID ' . $pid . '). URL: ' . get_permalink($pid));
