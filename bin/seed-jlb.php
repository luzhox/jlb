<?php
/**
 * Seeder Jean Le Boulch — pobla WordPress con el contenido del Figma.
 *
 * Cómo ejecutarlo:
 *
 *   1. Abre Local → click derecho en el sitio → "Open site shell".
 *   2. cd app/public/wp-content/themes/jlb
 *   3. wp eval-file bin/seed-jlb.php
 *
 * Idempotente: si ya corriste el script, vuelve a correrlo y solo actualizará.
 *   - Crea/actualiza la página "Inicio" y la asigna como front-page.
 *   - Sube las imágenes de assets/figma-home/ a Media Library (sin duplicar).
 *   - Rellena los 6 módulos ACF Flexible Content.
 *   - Crea el menú "Menú principal JLB" con 8 items + submenú "Niveles".
 *   - Llena los campos de la Options Page del footer.
 *   - Crea 3 posts de noticias de ejemplo (categoría "Noticias").
 *
 * Requisitos: WP-CLI + ACF Pro activo + el theme JLB activo.
 *
 * @package boilerplate-wordpress
 */

if (!defined('WP_CLI') || !WP_CLI) {
    fwrite(STDERR, "Este script debe ejecutarse vía WP-CLI (wp eval-file ...).\n");
    exit(1);
}

if (!function_exists('acf_add_local_field_group') || !function_exists('update_field')) {
    WP_CLI::error('ACF Pro no está activo. Activa el plugin antes de correr el seeder.');
}

WP_CLI::log('==> Seeder JLB iniciado.');

// ─────────────────────────────────────────────────────────────────────────────
// 1. Subida de imágenes a Media Library
// ─────────────────────────────────────────────────────────────────────────────

$theme_dir  = get_template_directory();
$assets_dir = $theme_dir . '/assets/figma-home';

if (!is_dir($assets_dir)) {
    WP_CLI::error("No se encontró assets/figma-home/ en: $assets_dir");
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

/**
 * Sube un archivo de assets/figma-home/ a Media Library. Idempotente vía meta.
 *
 * @param string $filename Solo el nombre (sin path), ej. "hero.png".
 * @return int Attachment ID, o 0 si falló.
 */
function jlb_seed_upload($filename) {
    static $cache = array();
    if (isset($cache[$filename])) return $cache[$filename];

    $path = get_template_directory() . '/assets/figma-home/' . $filename;
    if (!file_exists($path)) {
        WP_CLI::warning("Imagen no encontrada: $filename");
        return 0;
    }

    // Buscar attachment ya existente por meta marker para idempotencia.
    $existing = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array('key' => '_jlb_seed_source', 'value' => $filename),
        ),
        'fields'         => 'ids',
    ));
    if (!empty($existing)) {
        $cache[$filename] = (int) $existing[0];
        return $cache[$filename];
    }

    // Copiar a uploads dir vía wp_handle_sideload.
    $tmp_dir  = wp_upload_dir();
    $tmp_file = $tmp_dir['path'] . '/' . wp_unique_filename($tmp_dir['path'], $filename);
    if (!copy($path, $tmp_file)) {
        WP_CLI::warning("No pude copiar: $filename");
        return 0;
    }

    $filetype = wp_check_filetype($tmp_file, null);
    $attachment = array(
        'post_mime_type' => $filetype['type'] ?: 'image/png',
        'post_title'     => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment($attachment, $tmp_file);
    if (is_wp_error($attach_id) || !$attach_id) {
        WP_CLI::warning("wp_insert_attachment falló: $filename");
        return 0;
    }

    $meta = wp_generate_attachment_metadata($attach_id, $tmp_file);
    wp_update_attachment_metadata($attach_id, $meta);
    update_post_meta($attach_id, '_jlb_seed_source', $filename);

    WP_CLI::log("  · Subida: $filename → ID $attach_id");
    $cache[$filename] = (int) $attach_id;
    return $cache[$filename];
}

/**
 * Genera el array {url, alt, ID, width, height} que ACF Pro entrega para
 * un campo image (return_format=array). Útil para fields que esperan ese shape.
 */
function jlb_seed_image_array($filename, $alt = '') {
    $id = jlb_seed_upload($filename);
    if (!$id) return array();
    $src = wp_get_attachment_image_src($id, 'full');
    return array(
        'ID'     => $id,
        'id'     => $id,
        'url'    => $src ? $src[0] : '',
        'alt'    => $alt,
        'width'  => $src ? $src[1] : '',
        'height' => $src ? $src[2] : '',
    );
}

WP_CLI::log('==> 1/5 Subiendo imágenes a Media Library...');
$imgs = array(
    'hero'                    => jlb_seed_image_array('hero.png', 'Niños explorando la naturaleza con lupas'),
    'level-inicial'           => jlb_seed_image_array('level-inicial.png', 'Inicial'),
    'video-aprendizaje'       => jlb_seed_image_array('video-aprendizaje.png', 'Aprendizaje vivencial'),
    'level-wide'              => jlb_seed_image_array('level-wide.png', 'Secundaria'),
    'video-convivencia'       => jlb_seed_image_array('video-convivencia.png', 'Convivencia y democracia'),
    'video-psicomotricidad'   => jlb_seed_image_array('video-psicomotricidad.png', 'Psicomotricidad'),
    'video-arte'              => jlb_seed_image_array('video-arte.png', 'Arte'),
    'video-desarrollo'        => jlb_seed_image_array('video-desarrollo.png', 'Desarrollo integral'),
    'video-filosofia'         => jlb_seed_image_array('video-filosofia.png', 'Filosofía con niños'),
    'news-felicidad'          => jlb_seed_image_array('news-felicidad.png', 'La felicidad'),
    'news-arte'               => jlb_seed_image_array('news-arte.png', '¿Arte en casa o en colegio?'),
    'news-fronteras'          => jlb_seed_image_array('news-fronteras.png', 'Pensar juntos más allá de las fronteras'),
    'testimonial-nicole'      => jlb_seed_image_array('testimonial-nicole.png', 'Nicole, Ex Alumna del colegio Jean Le Boulch'),
    'logo'                    => jlb_seed_image_array('logo.svg', 'Logo Jean Le Boulch'),
);

// ─────────────────────────────────────────────────────────────────────────────
// 2. Página "Inicio" + asignación como front-page
// ─────────────────────────────────────────────────────────────────────────────

WP_CLI::log('==> 2/5 Creando página "Inicio"...');

$home_page = get_page_by_path('inicio', OBJECT, 'page');
if (!$home_page) {
    // Por si quedó algún post con título "Inicio" no-slug.
    $existing = get_posts(array('post_type' => 'page', 'title' => 'Inicio', 'posts_per_page' => 1));
    $home_page = !empty($existing) ? $existing[0] : null;
}

if ($home_page) {
    $home_id = $home_page->ID;
    WP_CLI::log("  · Página 'Inicio' ya existe (ID $home_id), actualizo módulos.");
} else {
    $home_id = wp_insert_post(array(
        'post_title'   => 'Inicio',
        'post_name'    => 'inicio',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '',
    ), true);
    if (is_wp_error($home_id)) {
        WP_CLI::error('No pude crear la página "Inicio": ' . $home_id->get_error_message());
    }
    WP_CLI::log("  · Página 'Inicio' creada (ID $home_id).");
}

update_option('show_on_front', 'page');
update_option('page_on_front', (int) $home_id);
WP_CLI::log('  · Asignada como front-page (Ajustes → Lectura).');

// ─────────────────────────────────────────────────────────────────────────────
// 3. Poblar módulos ACF Flexible Content
// ─────────────────────────────────────────────────────────────────────────────

WP_CLI::log('==> 3/5 Poblando módulos ACF...');

$modules = array(

    // ── 1. JLB Hero ─────────────────────────────────────────────────────────
    array(
        'acf_fc_layout'    => 'jlb_hero',
        'eyebrow'          => '2025 - 2026',
        'titulo'           => 'Admisión',
        'texto'            => 'Asumimos con honestidad la responsabilidad de la formación integral de nuestros estudiantes y los acompañamos desde la curiosidad.',
        'boton_principal'  => array('title' => 'Propuesta educativa', 'url' => '#propuesta', 'target' => '_self'),
        'boton_secundario' => array('title' => 'Más información',     'url' => '#contacto',  'target' => '_self'),
        'imagen'           => $imgs['hero'],
    ),

    // ── 2. JLB Niveles ──────────────────────────────────────────────────────
    array(
        'acf_fc_layout' => 'jlb_niveles',
        'titulo'        => 'Nuestros niveles educativos',
        'items'         => array(
            array('titulo' => 'Inicial',     'imagen' => $imgs['level-inicial'],     'link' => array('url' => home_url('/niveles/inicial/'),      'target' => '_self'), 'wide' => false),
            array('titulo' => 'Primaria',    'imagen' => $imgs['video-aprendizaje'], 'link' => array('url' => home_url('/niveles/primaria/'),     'target' => '_self'), 'wide' => false),
            array('titulo' => 'Secundaria',  'imagen' => $imgs['level-wide'],        'link' => array('url' => home_url('/niveles/secundaria/'),   'target' => '_self'), 'wide' => false),
            array('titulo' => 'Bachiller',   'imagen' => $imgs['video-convivencia'], 'link' => array('url' => home_url('/niveles/bachillerato/'), 'target' => '_self'), 'wide' => true),
        ),
    ),

    // ── 3. JLB Manifesto ────────────────────────────────────────────────────
    array(
        'acf_fc_layout' => 'jlb_manifesto',
        'anchor'        => 'colegio',
        'texto'         => '<p>En el colegio Jean Le Boulch, apuntamos al <strong>desarrollo integral de los alumnos</strong> y buscamos que <strong>construyan conocimientos</strong> a través de la <strong>experimentación, exploración, reflexión, investigación</strong> y <strong>trabajo en equipo.</strong></p>',
    ),

    // ── 4. JLB Experience ───────────────────────────────────────────────────
    array(
        'acf_fc_layout'    => 'jlb_experience',
        'hero_imagen'      => $imgs['video-psicomotricidad'],
        'hero_titulo'      => 'Conoce la experiencia JLB',
        'propuesta_titulo' => 'Propuesta educativa',
        'propuesta_texto'  => 'Buscamos que nuestra comunidad se desarrolle en un ambiente agradable y saludable que facilite el aprendizaje y forme personas autónomas, empáticas, críticas y capaces de comunicar sus argumentos.',
        // video_url: "Principios educativos" del canal JLB (jlb.edu.pe/conocenos).
        'items'            => array(
            array('titulo' => 'Aprendizaje vivencial',    'imagen' => $imgs['video-aprendizaje'],     'video_url' => 'https://www.youtube.com/watch?v=foH38Zt09wI'),
            array('titulo' => 'Convivencia y democracia', 'imagen' => $imgs['video-convivencia'],     'video_url' => 'https://www.youtube.com/watch?v=BgYUJqKGt9M'),
            array('titulo' => 'Psicomotricidad',          'imagen' => $imgs['video-psicomotricidad'], 'video_url' => 'https://www.youtube.com/watch?v=xU1gRtI_dEM'),
            array('titulo' => 'Arte',                     'imagen' => $imgs['video-arte'],            'video_url' => 'https://www.youtube.com/watch?v=Jo0hFGU8-l4'),
            array('titulo' => 'Desarrollo integral',      'imagen' => $imgs['video-desarrollo'],      'video_url' => 'https://www.youtube.com/watch?v=u5pnYeQzEco'),
            array('titulo' => 'Filosofía con niños',      'imagen' => $imgs['video-filosofia'],       'video_url' => 'https://www.youtube.com/watch?v=Ox5HNcJZatk'),
        ),
    ),

    // ── 5. JLB Testimoniales (slider) ───────────────────────────────────────
    array(
        'acf_fc_layout'           => 'jlb_testimoniales',
        'kicker'                  => 'Testimoniales',
        'mostrar_arco_decorativo' => true,
        'items'                   => array(
            array(
                'imagen'       => $imgs['testimonial-nicole'],
                'video_url'    => '', // el cliente pega la URL del video (YouTube/Vimeo) → activa el play + lightbox
                'titulo'       => 'Me motivaron a conseguir mis objetivos',
                'cita'         => 'En Jean Le Boulch las herramientas base que me dieron me ayudaron a seguir un crecimiento profesional y proyectarme solidamente.',
                'autor_nombre' => 'Nicole',
                'autor_rol'    => 'Ex Alumna',
            ),
            array(
                'imagen'       => $imgs['testimonial-nicole'],
                'video_url'    => '',
                'titulo'       => 'Encontré mi vocación aquí',
                'cita'         => 'Los profesores confiaron en mí desde el primer día. Eso marcó la diferencia y me dio la confianza para elegir mi camino.',
                'autor_nombre' => 'Carla',
                'autor_rol'    => 'Ex Alumna',
            ),
            array(
                'imagen'       => $imgs['testimonial-nicole'],
                'video_url'    => '',
                'titulo'       => 'Aprendí a pensar por mí mismo',
                'cita'         => 'La metodología de Jean Le Boulch me enseñó a cuestionar, debatir y construir argumentos. Llegué a la universidad con esa ventaja.',
                'autor_nombre' => 'Diego',
                'autor_rol'    => 'Ex Alumno',
            ),
        ),
    ),

    // ── 6. JLB Testimonio padres ────────────────────────────────────────────
    // Sin card: en Figma esta sección es solo título + cita centrada + autor.
    array(
        'acf_fc_layout' => 'jlb_testimonio_padres',
        'kicker'        => 'Lo que dicen',
        'titulo'        => 'los padres',
        'cita'          => 'En Jean Le Boulch le permitieron hacer una mejor toma de decisiones según su crecimiento profesional y socialmente autónomo.',
        'cita_autor'    => 'Pool de la Barra',
    ),

    // ── 7. JLB Noticias ─────────────────────────────────────────────────────
    array(
        'acf_fc_layout' => 'jlb_noticias',
        'titulo'        => 'Noticias',
        'items'         => array(
            array('titulo' => 'La felicidad',                          'fecha' => '21/01/2026', 'etiqueta' => 'Noticias', 'imagen' => $imgs['news-felicidad'], 'link' => array('url' => '#blog', 'target' => '_self')),
            array('titulo' => '¿Arte en casa o en colegio?',           'fecha' => '21/01/2026', 'etiqueta' => 'Noticias', 'imagen' => $imgs['news-arte'],      'link' => array('url' => '#blog', 'target' => '_self')),
            array('titulo' => 'Pensar juntos más allá de las fronteras', 'fecha' => '21/01/2026', 'etiqueta' => 'Noticias', 'imagen' => $imgs['news-fronteras'], 'link' => array('url' => '#blog', 'target' => '_self')),
        ),
    ),
);

$ok = update_field('modules', $modules, (int) $home_id);
if ($ok) {
    WP_CLI::log('  · 6 módulos cargados en Inicio.');
} else {
    WP_CLI::warning('  · update_field(modules) devolvió false. Verifica que ACF esté activo y el field group registrado.');
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. Menú principal
// ─────────────────────────────────────────────────────────────────────────────

WP_CLI::log('==> 4/5 Creando menú "Menú principal JLB"...');

$menu_name = 'Menú principal JLB';
$menu      = wp_get_nav_menu_object($menu_name);
if (!$menu) {
    $menu_id = wp_create_nav_menu($menu_name);
    if (is_wp_error($menu_id)) {
        WP_CLI::error('No pude crear el menú: ' . $menu_id->get_error_message());
    }
    WP_CLI::log("  · Menú creado (ID $menu_id).");
} else {
    $menu_id = (int) $menu->term_id;
    // Limpiar items previos para re-seed limpio.
    $existing = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
    if (is_array($existing)) {
        foreach ($existing as $item) {
            wp_delete_post((int) $item->ID, true);
        }
    }
    WP_CLI::log("  · Menú existente (ID $menu_id) reseteado.");
}

$items = array(
    array('label' => 'Nuestro colegio',         'url' => '#colegio',      'children' => array()),
    array('label' => 'Niveles',                 'url' => '#niveles',      'children' => array(
        array('label' => 'Inicial',    'url' => '#inicial'),
        array('label' => 'Primaria',   'url' => '#primaria'),
        array('label' => 'Secundaria', 'url' => '#secundaria'),
        array('label' => 'Bachiller',  'url' => '#bachiller'),
    )),
    array('label' => 'Talleres',                'url' => '#talleres',     'children' => array()),
    array('label' => 'Experiencias innovadoras','url' => '#experiencias', 'children' => array()),
    array('label' => 'Blog',                    'url' => '#blog',         'children' => array()),
    array('label' => 'Open Day',                'url' => '#open-day',     'children' => array()),
    array('label' => 'Intranet',                'url' => '#intranet',     'children' => array()),
    array('label' => 'Admisión',                'url' => '#admision',     'children' => array()),
);

foreach ($items as $item) {
    $parent_id = wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title'   => $item['label'],
        'menu-item-url'     => $item['url'],
        'menu-item-status'  => 'publish',
        'menu-item-type'    => 'custom',
    ));
    if (is_wp_error($parent_id)) {
        WP_CLI::warning('No pude crear el item ' . $item['label']);
        continue;
    }
    foreach ($item['children'] as $child) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title'     => $child['label'],
            'menu-item-url'       => $child['url'],
            'menu-item-status'    => 'publish',
            'menu-item-type'      => 'custom',
            'menu-item-parent-id' => (int) $parent_id,
        ));
    }
}

$locations = get_theme_mod('nav_menu_locations', array());
$locations['menu_principal'] = (int) $menu_id;
set_theme_mod('nav_menu_locations', $locations);
WP_CLI::log('  · Asignado a la location "menu_principal".');

// ─────────────────────────────────────────────────────────────────────────────
// 5. Footer Options
// ─────────────────────────────────────────────────────────────────────────────

WP_CLI::log('==> 5/5 Poblando "Ajustes del sitio" (footer + identidad)...');

if (!empty($imgs['logo']['ID'])) {
    update_field('logo_header', (int) $imgs['logo']['ID'], 'option');
    update_field('jlb_logo_footer', (int) $imgs['logo']['ID'], 'option');
}

update_field('jlb_footer_address_title', 'Visítanos en:', 'option');
update_field('jlb_footer_address',       'Jr. Rodrigo de Triana 150-154, Santa Patricia 3ra Etapa, La Molina', 'option');
update_field('jlb_footer_phones_title',  'Llámanos:', 'option');
update_field('jlb_footer_phones', array(
    array('label' => 'Admisión',  'number' => '976-369-407', 'whatsapp' => '51976369407'),
    array('label' => 'Talleres',  'number' => '976-369-417', 'whatsapp' => '51976369417'),
    array('label' => 'Consultas', 'number' => '976-369-496', 'whatsapp' => '51976369496'),
), 'option');

update_field('jlb_footer_socials_title', 'Síguenos en:', 'option');
update_field('jlb_footer_socials', array(
    array('name' => 'instagram', 'label' => 'IG', 'url' => 'https://www.instagram.com/jlb.colegio/'),
    array('name' => 'facebook',  'label' => 'FB', 'url' => 'https://www.facebook.com/colegiojeanleboulch/'),
), 'option');

update_field('jlb_footer_email_title', 'Escríbenos:', 'option');
update_field('jlb_footer_email',       'informes@jlb.edu.pe', 'option');

update_field('jlb_footer_copy', '{year} Todos los derechos reservados – Colegio Jean Le Boulch La Molina', 'option');
// Orden según Figma: Términos → Privacidad → Cookies.
update_field('jlb_footer_legal', array(
    array('label' => 'Términos y condiciones', 'url' => '#terminos'),
    array('label' => 'Política de privacidad', 'url' => '#privacidad'),
    array('label' => 'Política de cookies',    'url' => '#cookies'),
), 'option');

WP_CLI::log('  · Footer y identidad poblados.');

// ─────────────────────────────────────────────────────────────────────────────
// Fin
// ─────────────────────────────────────────────────────────────────────────────

WP_CLI::success('Seed completado. Visita la home para ver el contenido editable desde wp-admin → Páginas → Inicio.');
WP_CLI::log('');
WP_CLI::log('Para editar:');
WP_CLI::log('  · Contenido home → wp-admin → Páginas → Inicio → "Componentes de Página"');
WP_CLI::log('  · Menú principal → wp-admin → Apariencia → Menús');
WP_CLI::log('  · Footer + logos → wp-admin → Ajustes del sitio (menú lateral)');
