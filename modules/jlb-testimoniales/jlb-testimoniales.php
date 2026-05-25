<?php
/**
 * Módulo: jlb_testimoniales — Slider de testimoniales JLB (Figma 4219:978).
 *
 * Card blanca con dos columnas: media (imagen + play opcional) + body
 * (kicker, título KG, comillas, cita, autor, flechas slider).
 * Arco decorativo opcional en esquina superior derecha del frame.
 *
 * Datos:
 *   kicker                   string  (default "Testimoniales")
 *   mostrar_arco_decorativo  bool    (default true)
 *   items[]                  repeater
 *     - imagen                array{url, alt, width, height}
 *     - video_url             string (URL del video; si vacío oculta el play)
 *     - titulo                string
 *     - cita                  textarea
 *     - autor_nombre          string
 *     - autor_rol             string
 *
 * Soporta dual-mode:
 *   · dentro de have_rows('modules') → get_sub_field()
 *   · invocado vía get_template_part() → $args
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$kicker      = $get('kicker', __('Testimoniales', 'boilerplate'));
$mostrar_arc = (bool) $get('mostrar_arco_decorativo', true);
$items       = $get('items', array());

if (empty($items) || !is_array($items)) {
    return;
}

$slider_id   = wp_unique_id('jlb-testimoniales-');
$total       = count($items);
$has_nav     = $total > 1;

// SVGs decorativos inline desde assets/figma-home/.
// Se inyectan así (en vez de <img>) para poder estilarlos por CSS y conservar el gradient.
$svg_dir = get_template_directory() . '/assets/figma-home/';
$svg_quote   = file_exists($svg_dir . 'testimonial-quote.svg')     ? file_get_contents($svg_dir . 'testimonial-quote.svg')     : '';
$svg_play    = file_exists($svg_dir . 'testimonial-play.svg')      ? file_get_contents($svg_dir . 'testimonial-play.svg')      : '';
$svg_arrow_l = file_exists($svg_dir . 'testimonial-arrow-1.svg')   ? file_get_contents($svg_dir . 'testimonial-arrow-1.svg')   : '';
$svg_arrow_r = file_exists($svg_dir . 'testimonial-arrow-2.svg')   ? file_get_contents($svg_dir . 'testimonial-arrow-2.svg')   : '';
$svg_arrowbg = file_exists($svg_dir . 'testimonial-arrow-bg.svg')  ? file_get_contents($svg_dir . 'testimonial-arrow-bg.svg')  : '';
$svg_arc     = file_exists($svg_dir . 'testimonial-arc-decor.svg') ? file_get_contents($svg_dir . 'testimonial-arc-decor.svg') : '';

// wp_kses permitido para SVG inline (mantiene viewBox, paths, gradients).
$svg_allowed = array(
    'svg'            => array('preserveaspectratio' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'overflow' => true, 'style' => true, 'xmlns' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true),
    'g'              => array('id' => true, 'fill' => true, 'transform' => true, 'opacity' => true),
    'path'           => array('id' => true, 'd' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true),
    'circle'         => array('id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
    'rect'           => array('id' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true),
    'defs'           => array(),
    'lineargradient' => array('id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true, 'gradienttransform' => true),
    'radialgradient' => array('id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'gradientunits' => true),
    'stop'           => array('offset' => true, 'stop-color' => true, 'stop-opacity' => true),
    'title'          => array(),
);
?>
<section class="jlb-testimoniales"
    id="testimoniales"
    aria-labelledby="<?php echo esc_attr($slider_id); ?>-title">


    <div class="jlb-container">
    <?php if ($mostrar_arc && $svg_arc): ?>
        <div class="jlb-testimoniales__decor"
            aria-hidden="true"
            data-gsap="fade-right"
            data-gsap-delay="0.15">
            <?php echo wp_kses($svg_arc, $svg_allowed); ?>
        </div>
    <?php endif; ?>

        <article class="jlb-testimoniales__card"
            data-gsap="fade-up">

            <div class="jlb-testimoniales__swiper swiper <?php echo esc_attr($slider_id); ?>"
                aria-roledescription="carousel"
                aria-label="<?php esc_attr_e('Slider de testimoniales', 'boilerplate'); ?>">

                <div class="swiper-wrapper">
                    <?php foreach ($items as $index => $item):
                        $imagen   = $item['imagen']        ?? null;
                        $video    = $item['video_url']     ?? '';
                        $t_titulo = $item['titulo']        ?? '';
                        $t_cita   = $item['cita']          ?? '';
                        $t_nombre = $item['autor_nombre']  ?? '';
                        $t_rol    = $item['autor_rol']     ?? '';
                        $is_first = $index === 0;
                    ?>
                        <div class="swiper-slide jlb-testimoniales__slide"
                            role="group"
                            aria-roledescription="slide"
                            aria-label="<?php echo esc_attr(sprintf(__('Testimonio %1$d de %2$d', 'boilerplate'), $index + 1, $total)); ?>">

                            <?php if (!empty($imagen['url'])): ?>
                                <div class="jlb-testimoniales__media">
                                    <img src="<?php echo esc_url($imagen['url']); ?>"
                                        alt="<?php echo esc_attr($imagen['alt'] ?? $t_nombre); ?>"
                                        <?php if (!empty($imagen['width'])): ?>width="<?php echo esc_attr($imagen['width']); ?>"<?php endif; ?>
                                        <?php if (!empty($imagen['height'])): ?>height="<?php echo esc_attr($imagen['height']); ?>"<?php endif; ?>
                                        loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                                        <?php echo $is_first ? 'fetchpriority="high"' : ''; ?>>

                                    <span class="jlb-testimoniales__media-overlay" aria-hidden="true"></span>

                                    <?php if ($svg_play): ?>
                                        <?php if ($video): ?>
                                            <a class="jlb-testimoniales__play"
                                                href="<?php echo esc_url($video); ?>"
                                                data-jlb-video="<?php echo esc_url($video); ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label="<?php echo esc_attr(sprintf(__('Reproducir video del testimonio de %s', 'boilerplate'), $t_nombre)); ?>">
                                                <?php echo wp_kses($svg_play, $svg_allowed); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="jlb-testimoniales__play" aria-hidden="true">
                                                <?php echo wp_kses($svg_play, $svg_allowed); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="jlb-testimoniales__body">
                                <?php if ($kicker): ?>
                                    <p class="jlb-testimoniales__kicker"><?php echo esc_html($kicker); ?></p>
                                <?php endif; ?>

                                <?php if ($t_titulo): ?>
                                    <h2 class="jlb-testimoniales__title"
                                        <?php echo $is_first ? 'id="' . esc_attr($slider_id) . '-title"' : ''; ?>>
                                        <?php echo esc_html($t_titulo); ?>
                                    </h2>
                                <?php endif; ?>

                                <?php if ($t_cita): ?>
                                    <blockquote class="jlb-testimoniales__quote">
                                        <?php if ($svg_quote): ?>
                                            <span class="jlb-testimoniales__quote-mark" aria-hidden="true">
                                                <?php echo wp_kses($svg_quote, $svg_allowed); ?>
                                            </span>
                                        <?php endif; ?>
                                        <p><?php echo esc_html($t_cita); ?></p>
                                    </blockquote>
                                <?php endif; ?>

                                <?php // Fila inferior: flechas a la izquierda + autor a la derecha (Figma). ?>
                                <div class="jlb-testimoniales__actions">
                                    <?php if ($has_nav): ?>
                                        <div class="jlb-testimoniales__nav" aria-hidden="true">
                                            <button type="button"
                                                class="jlb-testimoniales__arrow jlb-testimoniales__prev"
                                                aria-label="<?php esc_attr_e('Testimonio anterior', 'boilerplate'); ?>">
                                                <span class="jlb-testimoniales__arrow-bg" aria-hidden="true">
                                                    <?php echo wp_kses($svg_arrowbg, $svg_allowed); ?>
                                                </span>
                                                <span class="jlb-testimoniales__arrow-icon" aria-hidden="true">
                                                    <?php echo wp_kses($svg_arrow_l, $svg_allowed); ?>
                                                </span>
                                            </button>
                                            <button type="button"
                                                class="jlb-testimoniales__arrow jlb-testimoniales__next"
                                                aria-label="<?php esc_attr_e('Testimonio siguiente', 'boilerplate'); ?>">
                                                <span class="jlb-testimoniales__arrow-bg" aria-hidden="true">
                                                    <?php echo wp_kses($svg_arrowbg, $svg_allowed); ?>
                                                </span>
                                                <span class="jlb-testimoniales__arrow-icon" aria-hidden="true">
                                                    <?php echo wp_kses($svg_arrow_r, $svg_allowed); ?>
                                                </span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($t_nombre || $t_rol): ?>
                                        <p class="jlb-testimoniales__author">
                                            <?php if ($t_nombre): ?>
                                                <span class="jlb-testimoniales__author-name"><?php echo esc_html($t_nombre); ?></span>
                                            <?php endif; ?>
                                            <?php if ($t_nombre && $t_rol): ?>
                                                <span class="jlb-testimoniales__author-sep" aria-hidden="true"> &mdash; </span>
                                            <?php endif; ?>
                                            <?php if ($t_rol): ?>
                                                <span class="jlb-testimoniales__author-role"><?php echo esc_html($t_rol); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>
    </div>
</section>
