<?php
/**
 * Módulo: jlb_proceso — Proceso de admisión (stepper/tabs) (Figma 4129/4130).
 *
 * Eyebrow rojo + título + stepper: pestañas a la izquierda (paso activo en rojo
 * con flecha), panel de detalle a la derecha (intro + lista de requisitos).
 * Patrón ARIA tabs; en mobile las pestañas se apilan sobre el panel.
 *
 * Datos (dual-mode flex/$args):
 *   eyebrow    string  ("Proceso de admisión")
 *   titulo     string  ("Conoce cómo postular en 3 simples pasos")
 *   pasos      repeater de { etiqueta, intro (wysiwyg/html), requisitos[] }
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$eyebrow = $get('eyebrow');
$titulo  = $get('titulo');

$pasos = array();
if ($in_flex) {
    if (have_rows('pasos')) {
        while (have_rows('pasos')) {
            the_row();
            $reqs = array();
            if (have_rows('requisitos')) {
                while (have_rows('requisitos')) {
                    the_row();
                    $t = (string) get_sub_field('texto');
                    if ($t !== '') $reqs[] = $t;
                }
            }
            $pasos[] = array(
                'etiqueta'   => (string) get_sub_field('etiqueta'),
                'intro'      => (string) get_sub_field('intro'),
                'requisitos' => $reqs,
            );
        }
    }
} else {
    foreach ((array) ($args['pasos'] ?? array()) as $p) {
        $pasos[] = array(
            'etiqueta'   => (string) ($p['etiqueta'] ?? ''),
            'intro'      => (string) ($p['intro'] ?? ''),
            'requisitos' => (array) ($p['requisitos'] ?? array()),
        );
    }
}

if (empty($pasos)) {
    return;
}

$uid = wp_unique_id('jlb-proceso-');
?>
<section class="jlb-proceso" id="proceso" aria-labelledby="<?php echo esc_attr($uid); ?>-title">
    <div class="jlb-container">
        <?php if ($eyebrow): ?>
            <p class="jlb-eyebrow-red" data-gsap="fade-up"><span><?php echo esc_html($eyebrow); ?></span></p>
        <?php endif; ?>

        <?php if ($titulo): ?>
            <h2 class="jlb-proceso__title" id="<?php echo esc_attr($uid); ?>-title" data-gsap="fade-up" data-gsap-delay="0.1">
                <?php echo esc_html($titulo); ?>
            </h2>
        <?php endif; ?>

        <div class="jlb-proceso__stepper" data-jlb-tabs>
            <div class="jlb-proceso__tabs" role="tablist" aria-label="<?php echo esc_attr($titulo ?: __('Pasos del proceso', 'boilerplate')); ?>">
                <?php foreach ($pasos as $i => $paso):
                    $active = $i === 0;
                ?>
                    <button type="button"
                        class="jlb-proceso__tab<?php echo $active ? ' is-active' : ''; ?>"
                        id="<?php echo esc_attr($uid . '-tab-' . $i); ?>"
                        role="tab"
                        aria-selected="<?php echo $active ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr($uid . '-panel-' . $i); ?>"
                        tabindex="<?php echo $active ? '0' : '-1'; ?>"
                        data-jlb-tab="<?php echo (int) $i; ?>">
                        <?php echo esc_html($paso['etiqueta']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="jlb-proceso__panels">
                <?php foreach ($pasos as $i => $paso):
                    $active = $i === 0;
                ?>
                    <div class="jlb-proceso__panel<?php echo $active ? ' is-active' : ''; ?>"
                        id="<?php echo esc_attr($uid . '-panel-' . $i); ?>"
                        role="tabpanel"
                        aria-labelledby="<?php echo esc_attr($uid . '-tab-' . $i); ?>"
                        data-jlb-panel="<?php echo (int) $i; ?>"
                        <?php echo $active ? '' : 'hidden'; ?>>
                        <?php if ($paso['intro']): ?>
                            <div class="jlb-proceso__intro"><?php echo wp_kses_post(wpautop($paso['intro'])); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($paso['requisitos'])): ?>
                            <ul class="jlb-proceso__list">
                                <?php foreach ($paso['requisitos'] as $req): ?>
                                    <li><?php echo esc_html($req); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
