<?php
/**
 * Front page — Jean Le Boulch.
 *
 * Homologada bajo la convención del boilerplate:
 *   1. Usa get_header('jlb') / get_footer('jlb') para cargar el chrome JLB.
 *   2. Cada sección Figma es un módulo ACF (modules/jlb-*).
 *   3. Si la página de portada tiene módulos ACF cargados, se renderizan
 *      con the_modules_loop(). Si no, cae al fallback estático Figma
 *      llamando cada módulo directo vía get_template_part() con $args.
 *
 * Para editar el contenido desde wp-admin: asignar esta plantilla al
 * front-page y agregar los módulos JLB · * desde Componentes de Página.
 */

get_header('jlb');

// ── Render dinámico vía ACF ─────────────────────────────────────────────────
$has_modules = false;
if (have_posts()) {
    the_post();
    if (function_exists('get_field') && get_field('modules') !== null) {
        $has_modules = true;
        the_modules_loop();
    }
    rewind_posts();
}

// ── Fallback estático Figma ─────────────────────────────────────────────────
if (!$has_modules):
    $asset_base = get_template_directory_uri() . '/assets/figma-home/';
    $asset = function ($file) use ($asset_base) {
        return $asset_base . ltrim($file, '/');
    };

    // jlb_hero — Admisión
    get_template_part('modules/jlb-hero/jlb-hero', null, array(
        'eyebrow' => '2025 - 2026',
        'titulo'  => 'Admisión',
        'texto'   => 'Asumimos con honestidad la responsabilidad de la formación integral de nuestros estudiantes y los acompañamos desde la curiosidad.',
        'boton_principal'  => array('title' => 'Propuesta educativa', 'url' => '#propuesta', 'target' => '_self'),
        'boton_secundario' => array('title' => 'Más información',    'url' => '#contacto',   'target' => '_self'),
        'imagen' => array(
            'url'    => $asset('hero.png'),
            'alt'    => 'Niños explorando la naturaleza con lupas',
            'width'  => 1360,
            'height' => 1241,
        ),
    ));

    // jlb_niveles — 4 niveles educativos
    get_template_part('modules/jlb-niveles/jlb-niveles', null, array(
        'titulo' => 'Nuestros niveles educativos',
        'items'  => array(
            array(
                'titulo' => 'Inicial',
                'imagen' => array('url' => $asset('level-inicial.png'), 'alt' => 'Inicial'),
                'link'   => array('url' => '#contacto', 'target' => '_self'),
                'wide'   => false,
            ),
            array(
                'titulo' => 'Primaria',
                'imagen' => array('url' => $asset('video-aprendizaje.png'), 'alt' => 'Primaria'),
                'link'   => array('url' => '#contacto', 'target' => '_self'),
                'wide'   => false,
            ),
            array(
                'titulo' => 'Secundaria',
                'imagen' => array('url' => $asset('level-wide.png'), 'alt' => 'Secundaria'),
                'link'   => array('url' => '#contacto', 'target' => '_self'),
                'wide'   => false,
            ),
            array(
                'titulo' => 'Bachiller',
                'imagen' => array('url' => $asset('video-convivencia.png'), 'alt' => 'Bachiller'),
                'link'   => array('url' => '#contacto', 'target' => '_self'),
                'wide'   => true,
            ),
        ),
    ));

    // jlb_manifesto — Párrafo grande con palabras destacadas
    get_template_part('modules/jlb-manifesto/jlb-manifesto', null, array(
        'anchor' => 'colegio',
        'texto'  => '<p>En el colegio Jean Le Boulch, apuntamos al <strong>desarrollo integral de los alumnos</strong> y buscamos que <strong>construyan conocimientos</strong> a través de la <strong>experimentación, exploración, reflexión, investigación</strong> y <strong>trabajo en equipo.</strong></p>',
    ));

    // jlb_experience — Hero video + propuesta + grid
    get_template_part('modules/jlb-experience/jlb-experience', null, array(
        'hero_imagen' => array(
            'url' => $asset('video-psicomotricidad.png'),
            'alt' => 'Estudiante participando en una actividad psicomotriz',
        ),
        'hero_titulo'      => 'Conoce la experiencia JLB',
        'propuesta_titulo' => 'Propuesta educativa',
        'propuesta_texto'  => 'Buscamos que nuestra comunidad se desarrolle en un ambiente agradable y saludable que facilite el aprendizaje y forme personas autónomas, empáticas, críticas y capaces de comunicar sus argumentos.',
        'items' => array(
            array('titulo' => 'Aprendizaje vivencial',     'imagen' => array('url' => $asset('video-aprendizaje.png'),    'alt' => 'Aprendizaje vivencial')),
            array('titulo' => 'Convivencia y democracia',  'imagen' => array('url' => $asset('video-convivencia.png'),    'alt' => 'Convivencia y democracia')),
            array('titulo' => 'Psicomotricidad',           'imagen' => array('url' => $asset('video-psicomotricidad.png'), 'alt' => 'Psicomotricidad')),
            array('titulo' => 'Arte',                      'imagen' => array('url' => $asset('video-arte.png'),            'alt' => 'Arte')),
            array('titulo' => 'Desarrollo integral',       'imagen' => array('url' => $asset('video-desarrollo.png'),      'alt' => 'Desarrollo integral')),
            array('titulo' => 'Filosofía con niños',       'imagen' => array('url' => $asset('video-filosofia.png'),       'alt' => 'Filosofía con niños')),
        ),
    ));

    // jlb_testimoniales — Slider (Figma 4219:978)
    get_template_part('modules/jlb-testimoniales/jlb-testimoniales', null, array(
        'kicker'                  => 'Testimoniales',
        'mostrar_arco_decorativo' => true,
        'items' => array(
            array(
                'imagen'       => array(
                    'url'    => $asset('testimonial-nicole.png'),
                    'alt'    => 'Nicole, ex alumna del colegio Jean Le Boulch',
                    'width'  => 512,
                    'height' => 504,
                ),
                'video_url'    => '',
                'titulo'       => 'Me motivaron a conseguir mis objetivos',
                'cita'         => 'En Jean Le Boulch encontré profesores que confiaron en mí desde el primer día. Hoy aplico todo lo que aprendí — la curiosidad, el pensamiento crítico y el trabajo en equipo — en mi carrera profesional.',
                'autor_nombre' => 'Nicole',
                'autor_rol'    => 'Ex Alumna',
            ),
        ),
    ));

    // jlb_testimonio_padres
    get_template_part('modules/jlb-testimonio-padres/jlb-testimonio-padres', null, array(
        'kicker'        => 'Lo que dicen',
        'titulo'        => 'los padres',
        'cita'          => 'En Jean Le Boulch le permitieron hacer una mejor toma de decisiones según su crecimiento profesional y socialmente autónomo.',
        'cita_autor'    => 'Pool de la Barra',
        'card_imagen'   => array('url' => $asset('news-arte.png'), 'alt' => 'Madre de familia compartiendo su testimonio'),
        'card_etiqueta' => 'Testimonio',
        'card_titulo'   => 'Me motivaron a conseguir mis objetivos',
        'card_meta'     => 'Nicole - Ex alumna',
    ));

    // jlb_noticias — 3 cards
    get_template_part('modules/jlb-noticias/jlb-noticias', null, array(
        'titulo' => 'Noticias',
        'items'  => array(
            array(
                'titulo'   => 'La felicidad',
                'fecha'    => '21/01/2026',
                'etiqueta' => 'Noticias',
                'imagen'   => array('url' => $asset('news-felicidad.png'), 'alt' => 'La felicidad'),
                'link'     => array('url' => '#blog', 'target' => '_self'),
            ),
            array(
                'titulo'   => '¿Arte en casa o en colegio?',
                'fecha'    => '21/01/2026',
                'etiqueta' => 'Noticias',
                'imagen'   => array('url' => $asset('news-arte.png'), 'alt' => '¿Arte en casa o en colegio?'),
                'link'     => array('url' => '#blog', 'target' => '_self'),
            ),
            array(
                'titulo'   => 'Pensar juntos más allá de las fronteras',
                'fecha'    => '21/01/2026',
                'etiqueta' => 'Noticias',
                'imagen'   => array('url' => $asset('news-fronteras.png'), 'alt' => 'Pensar juntos más allá de las fronteras'),
                'link'     => array('url' => '#blog', 'target' => '_self'),
            ),
        ),
    ));
endif;

get_footer('jlb');
