<?php
/**
 * Módulo: jlb_experience — Hero de video + propuesta educativa + grid de videos.
 *
 * Datos:
 *   hero_imagen        array{url,alt}
 *   hero_video_url     string  (URL YouTube/Vimeo/MP4 → lightbox)
 *   hero_titulo        string
 *   propuesta_titulo   string
 *   propuesta_texto    string (parrafo)
 *   items              array of array{ titulo, imagen{url,alt}, video_url }
 *
 * El play usa el mismo SVG que el slider de testimoniales (consistencia visual)
 * y toda la card/hero es clickeable (overlay <a> con data-jlb-video → lightbox).
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$hero_imagen      = $get('hero_imagen', null);
$hero_video       = $get('hero_video_url');
$hero_titulo      = $get('hero_titulo');
$propuesta_titulo = $get('propuesta_titulo');
$propuesta_texto  = $get('propuesta_texto');
$items            = $get('items', array());

if (!$hero_titulo && !$propuesta_titulo && empty($items)) {
    return;
}

// SVG play — mismo que el slider de testimoniales (círculo blanco + triángulo
// con gradiente teal→morado). Se inyecta inline para conservar el gradient.
$svg_dir     = get_template_directory() . '/assets/figma-home/';
$svg_play    = file_exists($svg_dir . 'testimonial-play.svg') ? file_get_contents($svg_dir . 'testimonial-play.svg') : '';
$svg_allowed = array(
    'svg'            => array('preserveaspectratio' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'overflow' => true, 'style' => true, 'xmlns' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true),
    'g'              => array('id' => true, 'fill' => true, 'transform' => true, 'opacity' => true),
    'path'           => array('id' => true, 'd' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true),
    'circle'         => array('id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
    'defs'           => array(),
    'lineargradient' => array('id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true, 'gradienttransform' => true),
    'stop'           => array('offset' => true, 'stop-color' => true, 'stop-opacity' => true),
);
?>
<section class="jlb-experience" id="experiencias" aria-labelledby="jlb-experience-title">
    <?php if ($hero_titulo || !empty($hero_imagen)): ?>
        <div class="jlb-experience__hero" data-gsap="fade" data-gsap-duration="1.2">
            <?php if (!empty($hero_imagen['url'])): ?>
                <img src="<?php echo esc_url($hero_imagen['url']); ?>"
                    alt="<?php echo esc_attr($hero_imagen['alt'] ?? ''); ?>"
                    loading="lazy">
            <?php endif; ?>

            <?php if ($svg_play): ?>
                <span class="jlb-experience__play" aria-hidden="true"><?php echo wp_kses($svg_play, $svg_allowed); ?></span>
            <?php endif; ?>

            <?php if ($hero_titulo): ?>
                <h2 id="jlb-experience-title"><?php echo esc_html($hero_titulo); ?></h2>
            <?php endif; ?>

            <?php if ($hero_video): ?>
                <a class="jlb-experience__hero-link"
                    href="<?php echo esc_url($hero_video); ?>"
                    data-jlb-video="<?php echo esc_url($hero_video); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Reproducir video: %s', 'boilerplate'), $hero_titulo ?: __('Experiencia JLB', 'boilerplate'))); ?>"></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($propuesta_titulo || $propuesta_texto): ?>
        <div class="jlb-container jlb-proposal" id="propuesta">
            <?php if ($propuesta_titulo): ?>
                <div class="jlb-proposal__head" data-gsap="fade-right">
                    <h2><?php echo esc_html($propuesta_titulo); ?></h2>
                    <span class="jlb-proposal__arrow" aria-hidden="true">
                        <svg width="22" height="38" viewBox="0 0 22 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 3l16 16-16 16" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            <?php endif; ?>

            <?php if ($propuesta_texto): ?>
                <p data-gsap="fade-left" data-gsap-delay="0.12"><?php echo esc_html($propuesta_texto); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($items) && is_array($items)): ?>
        <div class="jlb-container">
            <div class="jlb-video-grid" data-gsap-batch=".jlb-video-card">
                <?php foreach ($items as $item):
                    $item_title = $item['titulo'] ?? '';
                    $item_img   = $item['imagen'] ?? null;
                    $item_video = $item['video_url'] ?? '';
                ?>
                    <article class="jlb-video-card">
                        <?php if (!empty($item_img['url'])): ?>
                            <img src="<?php echo esc_url($item_img['url']); ?>"
                                alt="<?php echo esc_attr($item_img['alt'] ?? $item_title); ?>"
                                loading="lazy">
                        <?php endif; ?>

                        <?php if ($svg_play): ?>
                            <span class="jlb-video-card__play" aria-hidden="true"><?php echo wp_kses($svg_play, $svg_allowed); ?></span>
                        <?php endif; ?>

                        <span class="jlb-video-card__title"><?php echo esc_html($item_title); ?></span>

                        <?php if ($item_video): ?>
                            <a class="jlb-video-card__overlay"
                                href="<?php echo esc_url($item_video); ?>"
                                data-jlb-video="<?php echo esc_url($item_video); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Reproducir %s', 'boilerplate'), $item_title)); ?>"></a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
