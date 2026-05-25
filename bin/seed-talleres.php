<?php
/**
 * Seeder de 5 talleres ficticios (CPT `taller`, Figma 4131:452).
 * Reutiliza imágenes ya presentes en el tema (assets/figma-home/*) como pool.
 *
 * Uso: wp eval-file bin/seed-talleres.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_field')) { WP_CLI::error('ACF Pro no activo.'); }

$JLB_T_UP = array();
function jlb_t_upload($relpath, $alt = '') {
    global $JLB_T_UP;
    if (isset($JLB_T_UP[$relpath])) { return $JLB_T_UP[$relpath]; }
    $path = get_template_directory() . '/' . ltrim($relpath, '/');
    if (!file_exists($path)) { WP_CLI::warning("No existe: $relpath"); return 0; }
    $base = basename($path);
    $ex = get_posts(array('post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE'))));
    if ($ex) { $JLB_T_UP[$relpath] = (int) $ex[0]; return (int) $ex[0]; }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $up = wp_upload_dir();
    $target = trailingslashit($up['path']) . $base;
    if (!copy($path, $target)) { WP_CLI::warning("copy falló: $base"); return 0; }
    $ft = wp_check_filetype($base, null);
    $id = wp_insert_attachment(array('guid' => trailingslashit($up['url']) . $base, 'post_mime_type' => $ft['type'],
        'post_title' => sanitize_file_name(pathinfo($base, PATHINFO_FILENAME)), 'post_status' => 'inherit'), $target);
    if (is_wp_error($id) || !$id) { return 0; }
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $target));
    if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt);
    $JLB_T_UP[$relpath] = (int) $id;
    return (int) $id;
}

$video = 'https://www.youtube.com/watch?v=ScMzIvxBSi4';
$btn   = array('title' => 'Ir a admisión', 'url' => '/admision/', 'target' => '');

// 5 talleres: [título, slug, subtítulo, cursos[], objetivos[], pool de imágenes]
$talleres = array(
    array(
        't' => 'Taller de Música', 's' => 'taller-de-musica',
        'sub' => 'Desarrollamos sus habilidades musicales incentivando la creatividad y concentración.',
        'cursos' => array('Lectura musical', 'Ensambles musicales', 'Armonía', 'Piano', 'Guitarra', 'Bajo'),
        'obj_t' => 'Tener un plan para lograr el desarrollo de tu engreído',
        'puntos' => array('Logramos niños felices, listos para afrontar nuevos retos', 'Desarrollamos sus habilidades de forma personalizada', 'Hacemos que acepten su individualidad y su capacidad de trabajar en equipo'),
        'q' => 'Me motivaron a conseguir mis objetivos', 'autor' => 'Nicolás',
        'imgs' => array('assets/figma-home/blog/blog-1.jpg', 'assets/figma-home/blog/blog-2.jpg', 'assets/figma-home/admision/galeria-ancha.jpg', 'assets/figma-home/blog/blog-3.jpg'),
    ),
    array(
        't' => 'Taller de Danza', 's' => 'taller-de-danza',
        'sub' => 'Fomentamos la expresión corporal, el ritmo y la disciplina a través del movimiento.',
        'cursos' => array('Danza moderna', 'Ballet', 'Folclore', 'Ritmo y percusión', 'Expresión corporal', 'Coreografía'),
        'obj_t' => 'Un camino para crecer con cuerpo, mente y emoción',
        'puntos' => array('Mejoramos la coordinación y la conciencia corporal', 'Fortalecemos la autoestima y la confianza en escena', 'Trabajamos la constancia y el trabajo en equipo'),
        'q' => 'Aprendí a confiar en mí sobre el escenario', 'autor' => 'Valentina',
        'imgs' => array('assets/figma-home/blog/blog-2.jpg', 'assets/figma-home/blog/rel-1.jpg', 'assets/figma-home/admision/galeria-angosta.png', 'assets/figma-home/blog/blog-1.jpg'),
    ),
    array(
        't' => 'Taller de Teatro', 's' => 'taller-de-teatro',
        'sub' => 'Potenciamos la creatividad, la voz y la seguridad mediante el juego dramático.',
        'cursos' => array('Improvisación', 'Voz y dicción', 'Expresión escénica', 'Montaje teatral', 'Mimo', 'Guion'),
        'obj_t' => 'Formar comunicadores seguros y creativos',
        'puntos' => array('Desarrollamos la expresión oral y corporal', 'Estimulamos la empatía y el trabajo colaborativo', 'Reforzamos la seguridad para hablar en público'),
        'q' => 'Perdí el miedo a expresarme', 'autor' => 'Mateo',
        'imgs' => array('assets/figma-home/blog/arte-door.jpg', 'assets/figma-home/blog/blog-3.jpg', 'assets/figma-home/blog/rel-2.jpg', 'assets/figma-home/blog/blog-2.jpg'),
    ),
    array(
        't' => 'Taller de Robótica', 's' => 'taller-de-robotica',
        'sub' => 'Introducimos el pensamiento lógico y la tecnología con proyectos prácticos.',
        'cursos' => array('Lógica de programación', 'Electrónica básica', 'Sensores y motores', 'Diseño 3D', 'Pensamiento computacional', 'Proyectos'),
        'obj_t' => 'Aprender creando y resolviendo problemas reales',
        'puntos' => array('Fomentamos el pensamiento lógico y crítico', 'Aprenden haciendo con proyectos colaborativos', 'Conectan la tecnología con su entorno cotidiano'),
        'q' => 'Construí mi primer robot y me encantó', 'autor' => 'Lucía',
        'imgs' => array('assets/figma-home/experiencias/hero.jpg', 'assets/figma-home/blog/blog-1.jpg', 'assets/figma-home/openday/hero.jpg', 'assets/figma-home/blog/rel-1.jpg'),
    ),
    array(
        't' => 'Taller de Artes Plásticas', 's' => 'taller-de-artes-plasticas',
        'sub' => 'Exploramos el color, la forma y los materiales para expresar ideas y emociones.',
        'cursos' => array('Dibujo', 'Pintura', 'Modelado', 'Collage', 'Color y composición', 'Proyecto final'),
        'obj_t' => 'Despertar la sensibilidad y la creatividad',
        'puntos' => array('Estimulamos la creatividad y la observación', 'Desarrollamos la motricidad fina y la paciencia', 'Valoramos la expresión personal de cada niño'),
        'q' => 'Descubrí que puedo crear lo que imagino', 'autor' => 'Emma',
        'imgs' => array('assets/figma-home/blog/arte-door.jpg', 'assets/figma-home/blog/rel-1.jpg', 'assets/figma-home/admision/hero.jpg', 'assets/figma-home/blog/blog-3.jpg'),
    ),
);

$created = 0;
foreach ($talleres as $w) {
    $img0 = jlb_t_upload($w['imgs'][0], $w['t']);
    $img1 = jlb_t_upload($w['imgs'][1], $w['t']);
    $img2 = jlb_t_upload($w['imgs'][2], $w['t']);
    $img3 = jlb_t_upload($w['imgs'][3], $w['t']);

    $existing = get_page_by_path($w['s'], OBJECT, 'taller');
    $arr = array('post_title' => $w['t'], 'post_name' => $w['s'], 'post_status' => 'publish', 'post_type' => 'taller', 'post_author' => 1);
    if ($existing) { $arr['ID'] = (int) $existing->ID; }
    $pid = wp_insert_post($arr, true);
    if (is_wp_error($pid)) { WP_CLI::warning('taller falló: ' . $w['t']); continue; }
    if ($img0) { set_post_thumbnail($pid, $img0); }

    update_field('hero_subtitulo', $w['sub'], $pid);
    update_field('hero_imagen', $img0, $pid);
    update_field('hero_botones', array(
        array('texto' => 'Contáctanos', 'url' => '#contacto'),
        array('texto' => 'Más información', 'url' => '#info'),
    ), $pid);

    update_field('plan_eyebrow', 'Plan de estudios', $pid);
    update_field('plan_titulo', 'Creamos las bases de un aprendizaje integral', $pid);
    update_field('plan_etiqueta', 'Sílabo del taller', $pid);
    update_field('cursos', array_map(function ($c) { return array('nombre' => $c); }, $w['cursos']), $pid);

    update_field('video_url', $video, $pid);
    update_field('video_poster', $img1, $pid);

    update_field('obj_eyebrow', 'Objetivos', $pid);
    update_field('obj_titulo', $w['obj_t'], $pid);
    update_field('obj_puntos', array_map(function ($p) { return array('texto' => $p); }, $w['puntos']), $pid);
    update_field('obj_card_imagen', $img2, $pid);
    update_field('obj_card_texto', 'Aplica hoy y potencia el aprendizaje de tu engreído', $pid);
    update_field('obj_card_boton', $btn, $pid);

    update_field('gal_ancha', $img1, $pid);
    update_field('gal_angosta', $img3, $pid);

    update_field('testi_quote', $w['q'], $pid);
    update_field('testi_texto', 'Gracias a este taller descubrí nuevas habilidades y aprendí a disfrutar el proceso junto a mis compañeros y profesores.', $pid);
    update_field('testi_autor', $w['autor'], $pid);
    update_field('testi_rol', 'Ex alumno', $pid);
    update_field('testi_imagen', $img3, $pid);
    update_field('testi_video_url', $video, $pid);

    $created++;
    WP_CLI::log("  · {$w['t']} (ID $pid)");
}

flush_rewrite_rules(false);
WP_CLI::success("Talleres poblados: $created. Ej: " . get_permalink(get_page_by_path('taller-de-musica', OBJECT, 'taller')));
