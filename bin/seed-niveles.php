<?php
/**
 * Seeder de los niveles (CPT `nivel`, Figma 4110:116): Inicial, Primaria,
 * Secundaria. Reutiliza imágenes limpias del tema como pool (sin play horneado).
 *
 * Uso: wp eval-file bin/seed-niveles.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_field')) { WP_CLI::error('ACF Pro no activo.'); }

$JLB_N_UP = array();
function jlb_n_upload($relpath, $alt = '') {
    global $JLB_N_UP;
    if (isset($JLB_N_UP[$relpath])) { return $JLB_N_UP[$relpath]; }
    $path = get_template_directory() . '/' . ltrim($relpath, '/');
    if (!file_exists($path)) { WP_CLI::warning("No existe: $relpath"); return 0; }
    $base = basename($path);
    $ex = get_posts(array('post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE'))));
    if ($ex) { $JLB_N_UP[$relpath] = (int) $ex[0]; return (int) $ex[0]; }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $up = wp_upload_dir();
    $target = trailingslashit($up['path']) . $base;
    if (!copy($path, $target)) { return 0; }
    $ft = wp_check_filetype($base, null);
    $id = wp_insert_attachment(array('guid' => trailingslashit($up['url']) . $base, 'post_mime_type' => $ft['type'],
        'post_title' => sanitize_file_name(pathinfo($base, PATHINFO_FILENAME)), 'post_status' => 'inherit'), $target);
    if (is_wp_error($id) || !$id) { return 0; }
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $target));
    if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt);
    $JLB_N_UP[$relpath] = (int) $id;
    return (int) $id;
}

function jlb_n_grupos($areas, $talleres) {
    return array(
        array('etiqueta' => 'Áreas Curriculares', 'cursos' => array_map(function ($c) { return array('nombre' => $c); }, $areas)),
        array('etiqueta' => 'Talleres', 'cursos' => array_map(function ($c) { return array('nombre' => $c); }, $talleres)),
    );
}

$video = 'https://www.youtube.com/watch?v=ScMzIvxBSi4';
$btn   = array('title' => 'Ir a admisión', 'url' => '/admision/', 'target' => '');

$niveles = array(
    array(
        't' => 'Inicial', 's' => 'inicial',
        'sub' => 'Desarrollamos sus habilidades creativas y lógicas en un entorno de juego y descubrimiento.',
        'areas' => array('Comunicación', 'Matemática inicial', 'Ciencia y ambiente', 'Inglés', 'Personal Social', 'Psicomotricidad'),
        'talleres' => array('Talleres deportivos', 'Música'),
        'obj_t' => 'Tener un plan para lograr el desarrollo de tu engreído',
        'puntos' => array('Logramos niños felices, listos para afrontar nuevos retos', 'Desarrollamos sus habilidades de forma personalizada', 'Hacemos que acepten su individualidad y su capacidad de trabajar en equipo'),
        'q' => 'Me motivaron a conseguir mis objetivos', 'autor' => 'Lucía',
        'imgs' => array('assets/figma-home/admision/galeria-ancha.jpg', 'assets/figma-home/blog/blog-1.jpg', 'assets/figma-home/admision/galeria-angosta.png', 'assets/figma-home/blog/blog-2.jpg', 'assets/figma-home/blog/blog-3.jpg', 'assets/figma-home/experiencias/hero.jpg'),
    ),
    array(
        't' => 'Primaria', 's' => 'primaria',
        'sub' => 'Consolidamos las bases del aprendizaje integral combinando análisis y comprensión.',
        'areas' => array('Matemáticas', 'Comunicación', 'Ciencia', 'Lenguaje extranjera (Inglés)', 'Personal Social', 'Arte'),
        'talleres' => array('Talleres deportivos', 'Música'),
        'obj_t' => 'Un plan para crecer con autonomía y pensamiento crítico',
        'puntos' => array('Formamos estudiantes autónomos y curiosos', 'Acompañamos su aprendizaje de forma personalizada', 'Fomentamos el trabajo en equipo y los valores'),
        'q' => 'Aquí aprendí a aprender por mí mismo', 'autor' => 'Mateo',
        'imgs' => array('assets/figma-home/blog/blog-2.jpg', 'assets/figma-home/admision/galeria-ancha.jpg', 'assets/figma-home/blog/rel-1.jpg', 'assets/figma-home/blog/arte-door.jpg', 'assets/figma-home/openday/hero.jpg', 'assets/figma-home/blog/blog-1.jpg'),
    ),
    array(
        't' => 'Secundaria', 's' => 'secundaria',
        'sub' => 'Preparamos a nuestros estudiantes para los retos académicos y personales del futuro.',
        'areas' => array('Matemáticas', 'Comunicación', 'Ciencias', 'Inglés avanzado', 'Ciencias Sociales', 'Tecnología'),
        'talleres' => array('Deportes', 'Arte y música'),
        'obj_t' => 'Un plan para formar líderes íntegros y competentes',
        'puntos' => array('Desarrollamos el pensamiento crítico y la investigación', 'Orientamos su proyecto de vida y vocación', 'Fortalecemos el liderazgo y la responsabilidad social'),
        'q' => 'Me prepararon para la universidad y para la vida', 'autor' => 'Valentina',
        'imgs' => array('assets/figma-home/blog/arte-door.jpg', 'assets/figma-home/blog/blog-3.jpg', 'assets/figma-home/blog/rel-2.jpg', 'assets/figma-home/admision/galeria-angosta.png', 'assets/figma-home/blog/blog-2.jpg', 'assets/figma-home/admision/hero.jpg'),
    ),
    array(
        't' => 'Bachillerato', 's' => 'bachillerato',
        'sub' => 'Acompañamos la transición a la educación superior con rigor académico y orientación vocacional.',
        'areas' => array('Matemática avanzada', 'Comunicación y redacción', 'Física y Química', 'Inglés', 'Economía', 'Investigación'),
        'talleres' => array('Deportes', 'Arte y música'),
        'obj_t' => 'Un plan para dar el salto a la educación superior con seguridad',
        'puntos' => array('Reforzamos competencias clave para la universidad', 'Brindamos orientación vocacional personalizada', 'Desarrollamos autonomía, criterio y liderazgo'),
        'q' => 'Llegué a la universidad con bases sólidas', 'autor' => 'Diego',
        'imgs' => array('assets/figma-home/admision/hero.jpg', 'assets/figma-home/blog/blog-1.jpg', 'assets/figma-home/blog/rel-1.jpg', 'assets/figma-home/blog/blog-3.jpg', 'assets/figma-home/admision/galeria-ancha.jpg', 'assets/figma-home/blog/arte-door.jpg'),
    ),
);

$created = 0;
foreach ($niveles as $n) {
    $i0 = jlb_n_upload($n['imgs'][0], $n['t']); // hero
    $i1 = jlb_n_upload($n['imgs'][1], $n['t']); // obj card
    $i2 = jlb_n_upload($n['imgs'][2], $n['t']); // gal ancha
    $i3 = jlb_n_upload($n['imgs'][3], $n['t']); // gal angosta
    $i4 = jlb_n_upload($n['imgs'][4], $n['t']); // gal full
    $i5 = jlb_n_upload($n['imgs'][5], $n['t']); // testi

    $existing = get_page_by_path($n['s'], OBJECT, 'nivel');
    $arr = array('post_title' => $n['t'], 'post_name' => $n['s'], 'post_status' => 'publish', 'post_type' => 'nivel', 'post_author' => 1);
    if ($existing) { $arr['ID'] = (int) $existing->ID; }
    $pid = wp_insert_post($arr, true);
    if (is_wp_error($pid)) { WP_CLI::warning('nivel falló: ' . $n['t']); continue; }
    if ($i0) { set_post_thumbnail($pid, $i0); }

    update_field('hero_subtitulo', $n['sub'], $pid);
    update_field('hero_imagen', $i0, $pid);
    update_field('hero_botones', array(
        array('texto' => 'Contáctanos', 'url' => '#contacto'),
        array('texto' => 'Más información', 'url' => '#info'),
    ), $pid);

    update_field('plan_eyebrow', 'Plan de estudios', $pid);
    update_field('plan_titulo', 'Creamos las bases de un aprendizaje integral', $pid);
    update_field('plan_grupos', jlb_n_grupos($n['areas'], $n['talleres']), $pid);

    update_field('obj_eyebrow', 'Objetivos', $pid);
    update_field('obj_titulo', $n['obj_t'], $pid);
    update_field('obj_puntos', array_map(function ($p) { return array('texto' => $p); }, $n['puntos']), $pid);
    update_field('obj_card_imagen', $i1, $pid);
    update_field('obj_card_texto', 'Aplica hoy y potencia el aprendizaje de tu engreído', $pid);
    update_field('obj_card_boton', $btn, $pid);

    update_field('gal_ancha', $i2, $pid);
    update_field('gal_angosta', $i3, $pid);
    update_field('gal_full', $i4, $pid);

    update_field('testi_quote', $n['q'], $pid);
    update_field('testi_texto', 'En Jean Le Boulch las herramientas y el acompañamiento me ayudaron a crecer con seguridad y a proyectarme con solidez.', $pid);
    update_field('testi_autor', $n['autor'], $pid);
    update_field('testi_rol', 'Padres', $pid);
    update_field('testi_imagen', $i5, $pid);
    update_field('testi_video_url', $video, $pid);

    $created++;
    WP_CLI::log("  · {$n['t']} (ID $pid)");
}

flush_rewrite_rules(false);
WP_CLI::success("Niveles poblados: $created. Ej: " . get_permalink(get_page_by_path('inicial', OBJECT, 'nivel')));
