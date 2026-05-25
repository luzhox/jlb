<?php
/**
 * Módulo: jlb_timeline — Línea de tiempo / historia (página Nosotros).
 *
 * Eje vertical de una columna (inspirado en Pestalozzi, bajo tokens JLB):
 * cada hito = año (KG con gradiente) + punto sobre el eje + tarjeta con título,
 * texto y una imagen opcional. Entrada con GSAP (data-gsap fade-up).
 *
 * Datos (dual-mode flex/$args):
 *   eyebrow  string
 *   titulo   string
 *   hitos    repeater de { anio, titulo, texto, imagen{url,alt} }
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$eyebrow = (string) $get('eyebrow');
$titulo  = (string) $get('titulo');

$hitos = array();
if ($in_flex) {
    if (have_rows('hitos')) {
        while (have_rows('hitos')) {
            the_row();
            $img = get_sub_field('imagen');
            $hitos[] = array(
                'anio'   => (string) get_sub_field('anio'),
                'titulo' => (string) get_sub_field('titulo'),
                'texto'  => (string) get_sub_field('texto'),
                'imagen' => is_array($img) ? $img : null,
            );
        }
    }
} else {
    foreach ((array) ($args['hitos'] ?? array()) as $h) {
        $hitos[] = array(
            'anio'   => (string) ($h['anio'] ?? ''),
            'titulo' => (string) ($h['titulo'] ?? ''),
            'texto'  => (string) ($h['texto'] ?? ''),
            'imagen' => isset($h['imagen']) && is_array($h['imagen']) ? $h['imagen'] : null,
        );
    }
}

$hitos = array_values(array_filter($hitos, function ($h) {
    return $h['anio'] !== '' || $h['titulo'] !== '';
}));

if (empty($hitos)) {
    return;
}

$uid = wp_unique_id('jlb-timeline-');
?>
<section class="jlb-timeline" id="historia" aria-labelledby="<?php echo esc_attr($uid); ?>-t">
    <div class="jlb-container">
        <?php if ($eyebrow): ?>
            <p class="jlb-timeline__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if ($titulo): ?>
            <h2 class="jlb-timeline__title" id="<?php echo esc_attr($uid); ?>-t"><?php echo esc_html($titulo); ?></h2>
        <?php endif; ?>

        <ol class="jlb-timeline__list">
            <?php foreach ($hitos as $h): ?>
                <li class="jlb-timeline__item" data-gsap="fade-up">
                    <div class="jlb-timeline__aside">
                        <?php if ($h['anio']): ?>
                            <span class="jlb-timeline__year"><?php echo esc_html($h['anio']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="jlb-timeline__content">
                        <?php if ($h['titulo']): ?>
                            <h3 class="jlb-timeline__hito"><?php echo esc_html($h['titulo']); ?></h3>
                        <?php endif; ?>
                        <?php if ($h['texto']): ?>
                            <p class="jlb-timeline__texto"><?php echo esc_html($h['texto']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($h['imagen']['url'])): ?>
                            <figure class="jlb-timeline__img">
                                <img src="<?php echo esc_url($h['imagen']['url']); ?>"
                                    alt="<?php echo esc_attr($h['imagen']['alt'] ?: $h['titulo']); ?>"
                                    loading="lazy" decoding="async">
                            </figure>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
