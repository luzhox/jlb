<?php
/**
 * Seeder del Blog JLB (Figma 4175:533 listado + 4177:961 artículo).
 * - Crea la página "Blog" y la asigna como page_for_posts.
 * - Crea categorías y 8 artículos ficticios con imagen destacada.
 * - El primero ("¿Arte en casa o en colegio?") trae el contenido rico del Figma
 *   (párrafos + 2 imágenes en columnas + video con play → lightbox).
 *
 * Uso: wp eval-file bin/seed-blog.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }

function jlb_blog_upload($filename, $alt = '') {
    $path = get_template_directory() . '/assets/figma-home/blog/' . $filename;
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

function jlb_cat($name) {
    $t = term_exists($name, 'category');
    if (!$t) { $t = wp_insert_term($name, 'category'); }
    if (is_wp_error($t)) return 0;
    return (int) (is_array($t) ? $t['term_id'] : $t);
}

// ── Página "Blog" como page_for_posts ───────────────────────────────────────
$blog = get_page_by_path('blog', OBJECT, 'page');
if (!$blog) {
    $bid = wp_insert_post(array('post_title' => 'Blog', 'post_name' => 'blog', 'post_status' => 'publish', 'post_type' => 'page'), true);
    if (is_wp_error($bid)) { WP_CLI::error('No pude crear la página Blog: ' . $bid->get_error_message()); }
} else {
    $bid = (int) $blog->ID;
}
update_option('show_on_front', 'page');
update_option('page_for_posts', $bid);
WP_CLI::log("Página Blog = ID $bid (page_for_posts).");

// ── Imágenes ─────────────────────────────────────────────────────────────────
$img_arte = jlb_blog_upload('arte-door.jpg', 'Puerta roja de madera');
$img1     = jlb_blog_upload('blog-1.jpg', 'Paisaje al atardecer');
$img2     = jlb_blog_upload('blog-2.jpg', 'Actividad escolar');
$img3     = jlb_blog_upload('blog-3.jpg', 'Montañas y cielo');
$rel1     = jlb_blog_upload('rel-1.jpg', 'Paisaje montañoso');
$rel2     = jlb_blog_upload('rel-2.jpg', 'Ciudad al horizonte');
$pool     = array_values(array_filter(array($img1, $img2, $img3, $rel1, $rel2, $img_arte)));

// ── Categorías ───────────────────────────────────────────────────────────────
$c_reflex = jlb_cat('Reflexiones');
$c_filo   = jlb_cat('Filosofía');
$c_noti   = jlb_cat('Noticias');
$c_com    = jlb_cat('Comunidad');
$c_arte   = jlb_cat('Arte');

// Play del artículo (Figma 4177:1273): círculo blanco + triángulo con gradiente
// teal→morado. Es el asset exacto del diseño (assets/figma-home/blog/play.svg).
$play = '<img class="jlb-article__play" src="' . esc_url(get_template_directory_uri() . '/assets/figma-home/blog/play.svg') . '" alt="" width="110" height="110">';

$lorem = "Promovemos el aprendizaje a través de la experimentación, la reflexión y el trabajo en equipo. Cada experiencia busca despertar la curiosidad de nuestros estudiantes y conectarlos con su entorno de manera significativa.";
$lorem2 = "En el Colegio Jean Le Boulch entendemos que educar va más allá de transmitir contenidos: se trata de acompañar el desarrollo integral de cada niño, atendiendo sus dimensiones corporal, cognitiva, social y emocional.";

// Contenido rico del artículo principal (Figma 4177:961).
$arte_content = ''
    . '<!-- wp:paragraph --><p>Muchas personas, escépticas o no, se preguntan el porqué del arte, qué es lo que se enseña y qué logran con eso. El arte, en realidad, es un mundo que se puede apreciar en muchos aspectos. Así pues, se puede encontrar arte dentro de las matemáticas, ciencias, comunicación y otros varios cursos. La pregunta es ¿Arte en casa o en colegio?</p><!-- /wp:paragraph -->'
    . '<!-- wp:paragraph --><p><strong>El arte es una actividad personal que va más allá de lo estético donde no se juzga, sino se trata de comunicar y expresar sentimientos.</strong> Ahora bien, en el colegio tenemos todas las herramientas y espacios necesarios para expresarnos como el auditorio, las canchas de deporte, la piscina, el salón de arte.</p><!-- /wp:paragraph -->'
    . '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column">' . ($img_arte ? '<figure class="wp-block-image"><img src="' . esc_url(wp_get_attachment_image_url($img_arte, 'large')) . '" alt="Detalle de la puerta"></figure>' : '') . '</div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column">' . ($img1 ? '<figure class="wp-block-image"><img src="' . esc_url(wp_get_attachment_image_url($img1, 'large')) . '" alt="Espacio de creación"></figure>' : '') . '</div><!-- /wp:column --></div><!-- /wp:columns -->'
    . '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><p>Si bien, el sistema de virtualización no ha logrado adaptarse en la vida cotidiana de todos los alumnos, esto no puede ser impedimento para crear una manifestación artística, apreciarla y, finalmente, reflexionar sobre ella. El arte es una forma de comunicación que expresa sensibilidad y un nivel profundo de reflexión.</p></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><figure class="jlb-article__video"><a href="https://www.youtube.com/watch?v=ScMzIvxBSi4" data-jlb-video="https://www.youtube.com/watch?v=ScMzIvxBSi4" aria-label="Reproducir video">' . ($img3 ? '<img src="' . esc_url(wp_get_attachment_image_url($img3, 'large')) . '" alt="Video del artículo">' : '') . $play . '</a></figure></div><!-- /wp:column --></div><!-- /wp:columns -->'
    . '<!-- wp:paragraph --><p>Más que pensar dónde podemos hacer arte, pensemos en qué beneficios podemos aprender al activar nuestra creatividad dentro de casa, del colegio o de cualquier otro lugar.</p><!-- /wp:paragraph -->';

// ── 8 artículos ──────────────────────────────────────────────────────────────
$posts = array(
    array('t' => '¿Arte en casa o en colegio?', 'c' => $c_arte, 'img' => $img_arte, 'content' => $arte_content,
        'aut' => 'José Taramona', 'rol' => 'Director General',
        'ex' => 'Muchas personas se preguntan el porqué del arte, qué es lo que se enseña y qué logran con eso. El arte, en realidad, es un mundo que se puede apreciar en muchos aspectos.'),
    array('t' => 'La felicidad', 'c' => $c_reflex, 'aut' => 'María Fernández', 'rol' => 'Psicopedagoga',
        'ex' => 'Quién no ha cantado o al menos escuchado esta canción sobre la tan anhelada felicidad. ¿Qué es? ¿Será un estado concreto de nuestras emociones o un continuo de sentimientos?'),
    array('t' => 'Pensar juntos más allá de las fronteras', 'c' => $c_filo, 'aut' => 'Equipo de Filosofía', 'rol' => 'Área de Humanidades',
        'ex' => 'Desde inicios de mayo, los alumnos del Colegio han participado del encuentro virtual de filosofía “Voces y juventudes desde una Hispanoamérica pensante”.'),
    array('t' => 'Convivencia y democracia en el aula', 'c' => $c_com,
        'ex' => 'Promovemos espacios donde recogemos y damos soluciones a las propuestas y problemáticas de los niños, denominados reuniones de convivencia.'),
    array('t' => 'Aprender explorando el entorno', 'c' => $c_reflex,
        'ex' => 'La experimentación y la exploración del entorno son el motor del aprendizaje significativo en cada uno de nuestros niveles educativos.'),
    array('t' => 'El valor del juego en la infancia', 'c' => $c_reflex,
        'ex' => 'El juego no es solo diversión: es la forma natural en que los niños comprenden el mundo, regulan emociones y construyen vínculos.'),
    array('t' => 'Tecnología con propósito', 'c' => $c_noti,
        'ex' => 'Integramos herramientas digitales al servicio del aprendizaje, siempre acompañadas de criterio, reflexión y trabajo colaborativo.'),
    array('t' => 'Una comunidad que acompaña', 'c' => $c_com,
        'ex' => 'Familias, docentes y estudiantes formamos una comunidad que acompaña el desarrollo integral de cada niño y niña.'),
);

$created = 0;
foreach ($posts as $i => $p) {
    $existing = get_page_by_path(sanitize_title($p['t']), OBJECT, 'post');
    $content  = $p['content'] ?? ('<!-- wp:paragraph --><p>' . $p['ex'] . '</p><!-- /wp:paragraph -->'
        . '<!-- wp:paragraph --><p>' . $lorem . '</p><!-- /wp:paragraph -->'
        . '<!-- wp:paragraph --><p>' . $lorem2 . '</p><!-- /wp:paragraph -->');
    $img = $p['img'] ?? $pool[$i % max(1, count($pool))];
    $date = date('Y-m-d H:i:s', strtotime('-' . ($i * 5 + 2) . ' days'));

    $arr = array(
        'post_title'   => $p['t'],
        'post_name'    => sanitize_title($p['t']),
        'post_content' => $content,
        'post_excerpt' => $p['ex'],
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'post_date'    => $date,
        'post_author'  => 1,
    );
    if ($existing) { $arr['ID'] = (int) $existing->ID; }
    $id = wp_insert_post($arr, true);
    if (is_wp_error($id)) { WP_CLI::warning('post falló: ' . $p['t']); continue; }
    if ($p['c']) { wp_set_post_categories($id, array($p['c'])); }
    if ($img)    { set_post_thumbnail($id, $img); }
    update_post_meta($id, '_jlb_autor', $p['aut'] ?? 'Equipo Jean Le Boulch');
    update_post_meta($id, '_jlb_autor_rol', $p['rol'] ?? '');
    $created++;
}

// Limpia el post de ejemplo "Hello world" si existe.
$hw = get_page_by_path('hello-world', OBJECT, 'post');
if ($hw) { wp_trash_post($hw->ID); }

flush_rewrite_rules(false);
WP_CLI::success("Blog poblado: $created artículos. Listado: " . get_permalink($bid));
