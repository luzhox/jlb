<?php
/**
 * Módulo: jlb_testimonio_padres — Carrusel de testimonios de padres.
 *
 * Layout: título "Lo que dicen los padres" a la izquierda + carrusel de citas
 * (cita centrada con comilla grande + autor en script rojo) con dots de
 * paginación. Auto-rota (Swiper). Ver src/jlbPadres.js.
 *
 * Datos:
 *   kicker   string ("Lo que dicen")
 *   titulo   string ("los padres")
 *   citas    repeater de { cita, autor }
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$kicker = $get('kicker');
$titulo = $get('titulo');

// Recolecta las citas (dual-mode: have_rows en flex, array en $args).
$citas = array();
if ($in_flex) {
    if (have_rows('citas')) {
        while (have_rows('citas')) {
            the_row();
            $c = (string) get_sub_field('cita');
            if ($c === '') continue;
            $citas[] = array('cita' => $c, 'autor' => (string) get_sub_field('autor'));
        }
    }
} else {
    foreach ((array) ($args['citas'] ?? array()) as $row) {
        if (!empty($row['cita'])) {
            $citas[] = array('cita' => (string) $row['cita'], 'autor' => (string) ($row['autor'] ?? ''));
        }
    }
}

if (empty($citas)) {
    return;
}

// Comilla decorativa grande (mismo SVG que el slider de testimoniales).
$svg_dir     = get_template_directory() . '/assets/figma-home/';
$svg_quote   = file_exists($svg_dir . 'testimonial-quote.svg') ? file_get_contents($svg_dir . 'testimonial-quote.svg') : '';
$svg_allowed = array(
    'svg'  => array('preserveaspectratio' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'overflow' => true, 'style' => true, 'xmlns' => true, 'aria-hidden' => true),
    'path' => array('id' => true, 'd' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'opacity' => true),
);

$total = count($citas);
?>
<section class="jlb-testimonial" aria-labelledby="jlb-testimonial-title">
    <div class="jlb-container jlb-testimonial__inner">
        <div class="jlb-testimonial__head" data-gsap="fade-right">
            <?php if ($kicker): ?>
                <p class="jlb-kicker"><?php echo esc_html($kicker); ?></p>
            <?php endif; ?>

            <?php if ($titulo): ?>
                <h2 id="jlb-testimonial-title"><?php echo esc_html($titulo); ?></h2>
            <?php endif; ?>
        </div>

        <div class="jlb-testimonial__carousel" data-gsap="fade-up" data-gsap-delay="0.15">
            <div class="jlb-testimonial__swiper swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($citas as $i => $c): ?>
                        <div class="swiper-slide jlb-testimonial__slide"
                            role="group"
                            aria-roledescription="slide"
                            aria-label="<?php echo esc_attr(sprintf(__('Testimonio %1$d de %2$d', 'boilerplate'), $i + 1, $total)); ?>">
                            <blockquote>
                                <?php if ($svg_quote): ?>
                                    <span class="jlb-testimonial__quote-mark" aria-hidden="true"><?php echo wp_kses($svg_quote, $svg_allowed); ?></span>
                                <?php endif; ?>
                                <p><?php echo esc_html($c['cita']); ?></p>
                                <?php if ($c['autor']): ?>
                                    <cite><?php echo esc_html($c['autor']); ?></cite>
                                <?php endif; ?>
                            </blockquote>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($total > 1): ?>
                <div class="jlb-testimonial__dots" aria-label="<?php esc_attr_e('Paginación de testimonios', 'boilerplate'); ?>"></div>
            <?php endif; ?>
        </div>
    </div>
</section>
