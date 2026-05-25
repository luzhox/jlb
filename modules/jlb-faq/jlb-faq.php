<?php
/**
 * Módulo: jlb_faq — Preguntas frecuentes (acordeón) (Figma 4131:436+).
 *
 * Título centrado + filas acordeón (pregunta + chevron). Todas cerradas por
 * defecto. Patrón ARIA disclosure; un panel abierto a la vez.
 *
 * Datos (dual-mode flex/$args):
 *   titulo      string ("Preguntas frecuentes")
 *   preguntas   repeater de { pregunta, respuesta (html) }
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$titulo = $get('titulo', __('Preguntas frecuentes', 'boilerplate'));

$preguntas = array();
if ($in_flex) {
    if (have_rows('preguntas')) {
        while (have_rows('preguntas')) {
            the_row();
            $q = (string) get_sub_field('pregunta');
            if ($q === '') continue;
            $preguntas[] = array('pregunta' => $q, 'respuesta' => (string) get_sub_field('respuesta'));
        }
    }
} else {
    foreach ((array) ($args['preguntas'] ?? array()) as $p) {
        if (!empty($p['pregunta'])) {
            $preguntas[] = array('pregunta' => (string) $p['pregunta'], 'respuesta' => (string) ($p['respuesta'] ?? ''));
        }
    }
}

if (empty($preguntas)) {
    return;
}

$uid = wp_unique_id('jlb-faq-');
?>
<section class="jlb-faq" id="faq" aria-labelledby="<?php echo esc_attr($uid); ?>-title">
    <div class="jlb-container">
        <?php if ($titulo): ?>
            <h2 class="jlb-faq__title" id="<?php echo esc_attr($uid); ?>-title" data-gsap="fade-up"><?php echo esc_html($titulo); ?></h2>
        <?php endif; ?>

        <div class="jlb-faq__list" data-jlb-faq data-gsap-batch=".jlb-faq__item">
            <?php foreach ($preguntas as $i => $p): ?>
                <div class="jlb-faq__item">
                    <h3 class="jlb-faq__heading">
                        <button type="button"
                            class="jlb-faq__q"
                            id="<?php echo esc_attr($uid . '-q-' . $i); ?>"
                            aria-expanded="false"
                            aria-controls="<?php echo esc_attr($uid . '-a-' . $i); ?>">
                            <span><?php echo esc_html($p['pregunta']); ?></span>
                            <svg class="jlb-faq__chevron" width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                                <path d="M6 8.5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </h3>
                    <div class="jlb-faq__a"
                        id="<?php echo esc_attr($uid . '-a-' . $i); ?>"
                        role="region"
                        aria-labelledby="<?php echo esc_attr($uid . '-q-' . $i); ?>"
                        hidden>
                        <div class="jlb-faq__a-inner"><?php echo wp_kses_post(wpautop($p['respuesta'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
