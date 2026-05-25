<?php
/**
 * Módulo: jlb_experiencias — Lista de experiencias destacadas (Figma 4179:1360).
 *
 * Filas alternadas media/texto. Cada fila: media (imagen poster 558×398 con play
 * centrado que abre el video en el lightbox — data-jlb-video) + bloque de texto
 * (título KG + párrafo + botón "Ver más información" tipo píldora gradiente).
 * La fila impar invierte el orden (imagen a la izquierda, texto a la derecha).
 *
 * Datos (dual-mode flex/$args):
 *   experiencias  repeater de:
 *     titulo     string
 *     texto      string (textarea)
 *     video_url  string  (URL YouTube/Vimeo/MP4 → lightbox; opcional)
 *     imagen     array{url,alt}
 *     boton      array{url,title,target}  (link ACF; opcional)
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();

$experiencias = array();
if ($in_flex) {
    if (have_rows('experiencias')) {
        while (have_rows('experiencias')) {
            the_row();
            $img = get_sub_field('imagen');
            $experiencias[] = array(
                'titulo'    => (string) get_sub_field('titulo'),
                'texto'     => (string) get_sub_field('texto'),
                'video_url' => (string) get_sub_field('video_url'),
                'imagen'    => is_array($img) ? $img : null,
                'boton'     => get_sub_field('boton'),
            );
        }
    }
} else {
    foreach ((array) ($args['experiencias'] ?? array()) as $e) {
        $experiencias[] = array(
            'titulo'    => (string) ($e['titulo'] ?? ''),
            'texto'     => (string) ($e['texto'] ?? ''),
            'video_url' => (string) ($e['video_url'] ?? ''),
            'imagen'    => isset($e['imagen']) && is_array($e['imagen']) ? $e['imagen'] : null,
            'boton'     => $e['boton'] ?? null,
        );
    }
}

// Filtra filas vacías (sin título ni imagen).
$experiencias = array_values(array_filter($experiencias, function ($e) {
    return $e['titulo'] !== '' || !empty($e['imagen']['url']);
}));

if (empty($experiencias)) {
    return;
}

// Play SVG compartido con el slider de testimoniales y jlb_experience (círculo
// blanco + triángulo con gradiente teal→morado). Inline para conservar el gradient.
$svg_play    = '';
$svg_path    = get_template_directory() . '/assets/figma-home/testimonial-play.svg';
if (file_exists($svg_path)) {
    $svg_play = file_get_contents($svg_path);
}
$svg_allowed = array(
    'svg'            => array('preserveaspectratio' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'overflow' => true, 'style' => true, 'xmlns' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true),
    'g'              => array('id' => true, 'fill' => true, 'transform' => true, 'opacity' => true),
    'path'           => array('id' => true, 'd' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true),
    'circle'         => array('id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
    'defs'           => array(),
    'lineargradient' => array('id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true, 'gradienttransform' => true),
    'stop'           => array('offset' => true, 'stop-color' => true, 'stop-opacity' => true),
);

$ext_icon = '<svg class="jlb-link-external__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">'
    . '<path d="M8 16L16 8M16 8H9.5M16 8V14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="jlb-experiencias" aria-label="<?php echo esc_attr__('Experiencias', 'boilerplate'); ?>">
    <div class="jlb-container">
        <?php foreach ($experiencias as $i => $e):
            $reverse = ($i % 2 === 1); // impar: imagen izquierda, texto derecha
            $uid     = 'jlb-exp-' . $i;
            $has_vid = $e['video_url'] !== '';
            $blank   = !empty($e['boton']['target']) && $e['boton']['target'] === '_blank';
        ?>
            <article class="jlb-experiencias__row<?php echo $reverse ? ' jlb-experiencias__row--reverse' : ''; ?>"
                data-gsap="fade-up">
                <div class="jlb-experiencias__media">
                    <?php if (!empty($e['imagen']['url'])):
                        $media_tag = $has_vid ? 'a' : 'figure';
                    ?>
                        <<?php echo $media_tag; ?> class="jlb-experiencias__frame"
                            <?php if ($has_vid): ?>
                                href="<?php echo esc_url($e['video_url']); ?>"
                                data-jlb-video="<?php echo esc_url($e['video_url']); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Reproducir video: %s', 'boilerplate'), $e['titulo'])); ?>"
                            <?php endif; ?>>
                            <img src="<?php echo esc_url($e['imagen']['url']); ?>"
                                alt="<?php echo esc_attr($e['imagen']['alt'] ?? ''); ?>"
                                loading="lazy" decoding="async">
                            <?php if ($has_vid && $svg_play): ?>
                                <span class="jlb-experiencias__play" aria-hidden="true"><?php echo wp_kses($svg_play, $svg_allowed); ?></span>
                            <?php endif; ?>
                        </<?php echo $media_tag; ?>>
                    <?php endif; ?>
                </div>

                <div class="jlb-experiencias__body">
                    <?php if ($e['titulo']): ?>
                        <h2 class="jlb-experiencias__title" id="<?php echo esc_attr($uid); ?>"><?php echo esc_html($e['titulo']); ?></h2>
                    <?php endif; ?>
                    <?php if ($e['texto']): ?>
                        <p class="jlb-experiencias__text"><?php echo esc_html($e['texto']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($e['boton']['url'])): ?>
                        <a class="jlb-link-external jlb-link-external--gradient"
                            href="<?php echo esc_url($e['boton']['url']); ?>"
                            target="<?php echo esc_attr($e['boton']['target'] ?: '_self'); ?>"
                            <?php echo $blank ? 'rel="noopener noreferrer"' : ''; ?>>
                            <span><?php echo esc_html($e['boton']['title'] ?: __('Ver más información', 'boilerplate')); ?></span>
                            <?php echo $ext_icon; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
