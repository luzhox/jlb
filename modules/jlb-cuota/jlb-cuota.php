<?php
/**
 * Módulo: jlb_cuota — Cuota de ingreso con calculadora (Figma 4130:*).
 *
 * Izquierda: eyebrow + título (rojo) + link "Ver condiciones".
 * Derecha: icono decorativo + tarjeta lila con 2 selects (Nivel de postulante,
 * Modo de pago) → al Calcular muestra el Resultado + ahorro ("Ahorras $X" si es
 * al contado). Mapeo nivel×modo, sin aritmética.
 *
 * Datos (dual-mode flex/$args):
 *   eyebrow, titulo            string
 *   ver_condiciones            array{url,title,target}
 *   niveles                    repeater de { nombre, cuota_contado, cuota_cuotas, ahorro }
 */

$args = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$eyebrow = $get('eyebrow');
$titulo = $get('titulo');
$ver = $get('ver_condiciones', null);

$niveles = array();
if ($in_flex) {
    if (have_rows('niveles')) {
        while (have_rows('niveles')) {
            the_row();
            $n = (string) get_sub_field('nombre');
            if ($n === '')
                continue;
            $niveles[] = array(
                'nombre' => $n,
                'cuota_contado' => (string) get_sub_field('cuota_contado'),
                'cuota_cuotas' => (string) get_sub_field('cuota_cuotas'),
                'ahorro' => (string) get_sub_field('ahorro'),
            );
        }
    }
} else {
    foreach ((array) ($args['niveles'] ?? array()) as $p) {
        if (!empty($p['nombre'])) {
            $niveles[] = array(
                'nombre' => (string) $p['nombre'],
                'cuota_contado' => (string) ($p['cuota_contado'] ?? ''),
                'cuota_cuotas' => (string) ($p['cuota_cuotas'] ?? ''),
                'ahorro' => (string) ($p['ahorro'] ?? ''),
            );
        }
    }
}

if (!$titulo && empty($niveles)) {
    return;
}

$uid = wp_unique_id('jlb-cuota-');
$first = $niveles[0] ?? array('cuota_contado' => '', 'ahorro' => '');
$icono = get_template_directory_uri() . '/assets/figma-home/admision/cuota-icono.svg';

$ext_icon = '<svg class="jlb-link-external__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">'
    . '<path d="M8 16L16 8M16 8H9.5M16 8V14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$chevron = '<svg class="jlb-cuota__chevron" width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true"><path d="M6 8.5l5 5 5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="jlb-cuota" id="cuota" aria-labelledby="<?php echo esc_attr($uid); ?>-title">
    <div class="jlb-container jlb-cuota__inner">
        <div class="jlb-cuota__intro" data-gsap="fade-right">
            <?php if ($eyebrow): ?>
                <p class="jlb-eyebrow-red jlb-eyebrow-red--left"><span><?php echo esc_html($eyebrow); ?></span></p>
            <?php endif; ?>
            <?php if ($titulo): ?>
                <h2 class="jlb-cuota__title" id="<?php echo esc_attr($uid); ?>-title"><?php echo esc_html($titulo); ?></h2>
            <?php endif; ?>
            <?php if (!empty($ver['url'])):
                $blank = ($ver['target'] ?? '') === '_blank';
                ?>
                <a class="jlb-link-external jlb-link-external--ghost" href="<?php echo esc_url($ver['url']); ?>"
                    target="<?php echo esc_attr($ver['target'] ?? '_self'); ?>" <?php echo $blank ? 'rel="noopener noreferrer"' : ''; ?>>
                    <span><?php echo esc_html($ver['title'] ?: __('Ver condiciones', 'boilerplate')); ?></span>
                    <?php echo $ext_icon; ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($niveles)): ?>
            <div class="jlb-cuota__calc" data-gsap="fade-left" data-gsap-delay="0.12">
                <img class="jlb-cuota__icono" src="<?php echo esc_url($icono); ?>" alt="" aria-hidden="true" width="111"
                    height="111">
                <form class="jlb-cuota__card" data-jlb-cuota>
                    <div class="jlb-cuota__field">
                        <label
                            for="<?php echo esc_attr($uid); ?>-nivel"><?php esc_html_e('Nivel de postulante', 'boilerplate'); ?></label>
                        <div class="jlb-cuota__select">
                            <select id="<?php echo esc_attr($uid); ?>-nivel" data-jlb-cuota-nivel>
                                <?php foreach ($niveles as $i => $p): ?>
                                    <option value="<?php echo (int) $i; ?>"
                                        data-contado="<?php echo esc_attr($p['cuota_contado']); ?>"
                                        data-cuotas="<?php echo esc_attr($p['cuota_cuotas']); ?>"
                                        data-ahorro="<?php echo esc_attr($p['ahorro']); ?>">
                                        <?php echo esc_html($p['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo $chevron; ?>
                        </div>
                    </div>

                    <div class="jlb-cuota__field">
                        <label
                            for="<?php echo esc_attr($uid); ?>-modo"><?php esc_html_e('Modo de pago', 'boilerplate'); ?></label>
                        <div class="jlb-cuota__select">
                            <select id="<?php echo esc_attr($uid); ?>-modo" data-jlb-cuota-modo>
                                <option value="contado"><?php esc_html_e('Al contado', 'boilerplate'); ?></option>
                                <option value="cuotas"><?php esc_html_e('En cuotas', 'boilerplate'); ?></option>
                            </select>
                            <?php echo $chevron; ?>
                        </div>
                    </div>

                    <div class="jlb-cuota__result">
                        <span class="jlb-cuota__result-label"><?php esc_html_e('Resultado', 'boilerplate'); ?></span>
                        <span class="jlb-cuota__result-box" aria-live="polite">
                            <span class="jlb-cuota__result-value"
                                data-jlb-cuota-resultado><?php echo esc_html($first['cuota_contado']); ?></span>
                            <?php if (!empty($first['ahorro'])): ?>
                                <span class="jlb-cuota__result-ahorro"
                                    data-jlb-cuota-ahorro><?php echo esc_html(sprintf(__('Ahorras %s', 'boilerplate'), $first['ahorro'])); ?></span>
                            <?php else: ?>
                                <span class="jlb-cuota__result-ahorro" data-jlb-cuota-ahorro hidden></span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <button type="button" class="jlb-cuota__btn"
                        data-jlb-cuota-calc><?php esc_html_e('Calcular', 'boilerplate'); ?></button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>