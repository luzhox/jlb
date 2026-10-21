<?php
/**
 * Módulo: jlb_principios — Principios educativos (página Nosotros).
 *
 * Eyebrow + título KG + grid de tarjetas (3 col desktop / 2 tablet / 1 mobile).
 * Cada tarjeta: número con gradiente + título + texto. Entrada GSAP en batch.
 *
 * Datos (dual-mode flex/$args):
 *   eyebrow     string
 *   titulo      string
 *   principios  repeater de { titulo, texto }
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$eyebrow = (string) $get('eyebrow');
$titulo  = (string) $get('titulo');

$items = array();
if ($in_flex) {
    if (have_rows('principios')) {
        while (have_rows('principios')) {
            the_row();
            $items[] = array(
                'titulo' => (string) get_sub_field('titulo'),
                'texto'  => (string) get_sub_field('texto'),
            );
        }
    }
} else {
    foreach ((array) ($args['principios'] ?? array()) as $p) {
        $items[] = array(
            'titulo' => (string) ($p['titulo'] ?? ''),
            'texto'  => (string) ($p['texto'] ?? ''),
        );
    }
}

$items = array_values(array_filter($items, function ($p) {
    return $p['titulo'] !== '';
}));

if (empty($items)) {
    return;
}

$uid = wp_unique_id('jlb-principios-');
?>
<section class="jlb-principios" id="principios" aria-labelledby="<?php echo esc_attr($uid); ?>-t">
    <div class="jlb-container">
        <?php if ($eyebrow): ?>
            <p class="jlb-principios__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if ($titulo): ?>
            <h2 class="jlb-principios__title" id="<?php echo esc_attr($uid); ?>-t"><?php echo esc_html($titulo); ?></h2>
        <?php endif; ?>

        <ul class="jlb-principios__grid" data-gsap-batch=".jlb-principios__card">
            <?php foreach ($items as $i => $p): ?>
                <li class="jlb-principios__card">
                    <span class="jlb-principios__num" aria-hidden="true"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    <h3 class="jlb-principios__card-title"><?php echo esc_html($p['titulo']); ?></h3>
                    <?php if ($p['texto']): ?>
                        <p class="jlb-principios__text"><?php echo esc_html($p['texto']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
