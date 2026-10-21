<?php
/**
 * Seeder de la página "Nosotros" (bienvenida + historia/línea de tiempo).
 * Reutiliza jlb_admision_hero (hero) + jlb_manifesto (bienvenida) + jlb_principios + jlb_timeline
 * + jlb_experiencias (infraestructura). Textos: Word del cliente corregido 2026-10-08.
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

// Textos corregidos por el cliente ("Página Web 2026 (textos corregidos 8-10-26).docx").
$manifiesto = '<p>En el <strong>Colegio Jean Le Boulch</strong> formamos desde la <strong>Educación por el Movimiento</strong>, la filosofía y metodología del educador y médico francés Jean Le Boulch. Esta propuesta busca el <strong>desarrollo integral</strong> de la persona en todas sus dimensiones: motriz, cognitiva, social, emocional y ética.</p>'
    . '<p>Nuestros estudiantes <strong>construyen su propio aprendizaje</strong> mediante la experimentación, la exploración, la reflexión, la investigación y el <strong>trabajo en equipo</strong>, en un ambiente <strong>democrático, creativo y saludable</strong>. A lo largo de estas décadas hemos adaptado esta propuesta a nuestro contexto con una construcción propia, para formar personas capaces de elaborar y comunicar sus propios argumentos y opiniones.</p>';

$principios = array(
    array('titulo' => 'Desarrollo integral',
        'texto' => 'Entendemos a la persona como una unidad indivisible. Por eso, en cada actividad atendemos por igual sus dimensiones motriz, cognitiva, social, emocional y ética. Incluso en el trabajo académico, cada aprendizaje parte de la experiencia, despierta la motivación, invita a la reflexión y se construye en equipo.'),
    array('titulo' => 'Aprendizaje vivencial',
        'texto' => 'Aprendemos haciendo. Nuestros estudiantes construyen conocimiento explorando su entorno, experimentando e investigando.'),
    array('titulo' => 'Convivencia y democracia',
        'texto' => 'La escuela es un espacio de convivencia donde las voces de niños y adolescentes son escuchadas. En las reuniones de convivencia en el aula, el consejo estudiantil y las asambleas, los estudiantes plantean propuestas y problemas, los dialogan y acuerdan soluciones, y así viven la democracia en lo cotidiano.'),
    array('titulo' => 'Psicomotricidad',
        'texto' => 'El movimiento es el punto de partida del aprendizaje. Al moverse e interactuar con su entorno y con los demás, cada estudiante conoce su cuerpo, aprende a controlarlo y desarrolla la capacidad de actuar sobre el mundo, integrando lo motriz, lo emocional, lo social y lo cognitivo.'),
    array('titulo' => 'Arte',
        'texto' => 'Los estudiantes exploran y participan en diversas manifestaciones artísticas, como forma de expresar su sensibilidad y desarrollar su creatividad.'),
    array('titulo' => 'Filosofía con niños',
        'texto' => 'Desde Inicial hasta el último año de Secundaria, dialogamos, reflexionamos e investigamos con nuestros estudiantes sobre las preguntas que les interesan y el mundo que los rodea.'),
);

$hitos = array(
    array('anio' => '1984', 'titulo' => 'Fundación', 'imagen' => $h[1],
        'texto' => 'La profesora Silvia Bravo Heredia funda el Colegio Jean Le Boulch, que inicia clases en un pequeño local en Lince con Inicial y los primeros grados de Primaria.'),
    array('anio' => '1987', 'titulo' => 'Consolidación de Primaria', 'imagen' => 0,
        'texto' => 'El Ministerio de Educación autoriza el funcionamiento completo del nivel de Primaria.'),
    array('anio' => '1990', 'titulo' => 'Inicio de Secundaria', 'imagen' => $h[3],
        'texto' => 'Se autoriza el nivel de Secundaria, con lo que el colegio completa la Educación Básica Regular.'),
    array('anio' => '1993', 'titulo' => 'Nuevo campus en La Molina', 'imagen' => $h[4],
        'texto' => 'El colegio se traslada a su local propio en La Molina, donde funciona hasta hoy.'),
    array('anio' => '1994', 'titulo' => 'Primera promoción', 'imagen' => 0,
        'texto' => 'Egresa la primera promoción de quinto año de Secundaria.'),
    array('anio' => '1995', 'titulo' => 'Campos deportivos', 'imagen' => $h[5],
        'texto' => 'Se adquieren los campos deportivos frente al colegio, que fortalecen el trabajo en psicomotricidad, deporte y vida al aire libre.'),
    array('anio' => '1995, 1996 y 1998', 'titulo' => 'Jean Le Boulch en el Perú', 'imagen' => $img_dr,
        'texto' => 'Invitado por el colegio, Jean Le Boulch visita el Perú en tres ocasiones. Dicta talleres para nuestros docentes, trabaja con nuestros estudiantes y ofrece seminarios para padres de familia y público en general, además de conferencias en la Universidad Ricardo Palma, la PUCP y La Cantuta. Visita también Cusco, Pisco y Huaraz, estas dos últimas como sedes de formación e intercambio para nuestros directivos y docentes.'),
    array('anio' => '2007', 'titulo' => 'Nueva Dirección General', 'imagen' => $h[6],
        'texto' => 'Asume la Dirección General José Taramona Bravo, sociólogo de la educación, quien participó en el proyecto desde sus primeros años.'),
    array('anio' => '2010', 'titulo' => 'Área de Investigación y Desarrollo', 'imagen' => 0,
        'texto' => 'Se crea el Área de Investigación y Desarrollo, que impulsa la actualización docente y el intercambio de experiencias educativas con otros países.'),
    array('anio' => '2024', 'titulo' => '40 años', 'imagen' => $h[7],
        'texto' => 'El colegio celebra 40 años de trayectoria y reafirma su compromiso con la formación integral, la innovación pedagógica y el desarrollo humano.'),
    array('anio' => '2025', 'titulo' => 'Camino al Bachillerato Internacional', 'imagen' => $h[8],
        'texto' => 'El colegio inicia su candidatura como Colegio Solicitante* del Programa del Diploma del Bachillerato Internacional (IB), con el objetivo de ofrecerlo a partir de 2028.'),
);

$nota_ib = '<p>El Colegio Jean Le Boulch ha adquirido la categoría de colegio solicitante para impartir el Programa del Diploma (PD) y desea obtener la autorización para convertirse en Colegio del Mundo del IB. Estos son colegios que comparten una filosofía común: un compromiso con una educación internacional rigurosa y de calidad que el Colegio Jean Le Boulch considera que es importante para el alumnado.</p>'
    . '<p>*Solo los colegios autorizados por la Organización del IB pueden ofrecer cualquiera de sus cuatro programas académicos: el Programa de la Escuela Primaria (PEP), el Programa de los Años Intermedios (PAI), el Programa del Diploma (PD) o el Programa de Orientación Profesional (POP). La categoría de colegio solicitante no garantiza que el colegio obtenga la autorización. Para obtener más información sobre el IB y sus programas, visite <a href="https://www.ibo.org/es/" target="_blank" rel="noopener">www.ibo.org/es</a></p>';

// Infraestructura: reutiliza la foto y el recorrido virtual del hero de Open Day.
$tour_img = 0; $tour_url = 'https://www.youtube.com/watch?v=ScMzIvxBSi4';
$openday  = get_page_by_path('open-day', OBJECT, 'page');
foreach ($openday ? (array) get_field('modules', $openday->ID) : array() as $row) {
    if (($row['acf_fc_layout'] ?? '') === 'jlb_admision_hero') {
        $tour_img = (int) ($row['imagen']['ID'] ?? 0);
        if (!empty($row['video_url'])) { $tour_url = $row['video_url']; }
        break;
    }
}

$modules = array(
    array(
        'acf_fc_layout' => 'jlb_admision_hero',
        'eyebrow'       => 'Nosotros',
        'titulo'        => 'Conócenos',
        'subtitulo'     => 'Más de 42 años formando niños y jóvenes autónomos, empáticos y críticos.',
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
        'acf_fc_layout' => 'jlb_principios',
        'titulo'        => 'Principios educativos',
        'principios'    => $principios,
    ),
    array(
        'acf_fc_layout' => 'jlb_timeline',
        'eyebrow'       => 'Nuestra historia',
        'titulo'        => '42 años de historia',
        'intro'         => 'El Colegio Jean Le Boulch nació en 1984 como una propuesta alternativa a la educación tradicional, inspirada en la Educación por el Movimiento del educador francés Jean Le Boulch. Desde entonces, hemos construido un proyecto propio centrado en el desarrollo integral de cada estudiante.',
        'hitos'         => $hitos,
        'nota'          => $nota_ib,
    ),
    array(
        'acf_fc_layout'  => 'jlb_experiencias',
        'titulo_seccion' => 'Infraestructura',
        'intro_seccion'  => 'Diseñamos nuestros espacios de modo que nuestros alumnos logran aprender en un ambiente cómodo y agradable.',
        'experiencias'   => array(
            array(
                'titulo'    => 'Arquitectura',
                'texto'     => 'Nuestra propuesta educativa dialoga con nuestras instalaciones, por eso, cada espacio ha sido construido pensando en nuestros principios y, sobre todo, en nuestros niños y niñas. Como escuela, buscamos generar un ambiente acogedor para toda nuestra comunidad, donde se promueva el aprendizaje y nuestros alumnos y alumnas se sientan a gusto; por ende, el espacio físico juega un rol muy importante en esta misión. Nuestro diseño arquitectónico busca generar una atmósfera acogedora y cálida con entornos lúdicos que faciliten el trabajo en equipo, la exploración y el intercambio entre todos los miembros. En el Jean Le Boulch, nos preocupamos por brindar un espacio en donde nuestros alumnos se sientan cómodos y seguros. ¡Conoce más sobre nuestras instalaciones en el Tour Virtual!',
                'video_url' => $tour_url,
                'imagen'    => $tour_img,
            ),
        ),
    ),
);

$ok = update_field('modules', $modules, $pid);
clean_post_cache($pid);
WP_CLI::log('update_field(modules) => ' . var_export($ok, true));
WP_CLI::success('Nosotros poblada (ID ' . $pid . '). URL: ' . get_permalink($pid));
