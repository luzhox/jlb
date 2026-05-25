<?php
/**
 * Módulo: jlb_hero — Hero "Admisión" del template Jean Le Boulch.
 *
 * Doble fuente:
 *   · Dentro de the_modules_loop()  → lee con get_sub_field().
 *   · Vía get_template_part() con $args → cae al fallback estático
 *     (lo usa front-page.php cuando la página no tiene ACF poblado).
 *
 * Forma de los datos (compatible con ACF):
 *   eyebrow             string
 *   titulo              string (h1)
 *   texto               string (párrafo)
 *   boton_principal     array{title,url,target}
 *   boton_secundario    array{title,url,target}
 *   imagen              array{url,alt,width,height}
 */

$args     = isset($args) && is_array($args) ? $args : array();
$in_flex  = function_exists('get_row_layout') && get_row_layout();
$get      = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$eyebrow = $get('eyebrow');
$titulo  = $get('titulo');
$texto   = $get('texto');
$btn_pri = $get('boton_principal', null);
$btn_sec = $get('boton_secundario', null);
$imagen  = $get('imagen', null);

if (!$titulo && !$texto && empty($imagen)) {
    return;
}
?>
<section class="jlb-hero" aria-labelledby="jlb-hero-title">
    <div class="jlb-hero__content">
        <?php if ($titulo): ?>
            <h1 id="jlb-hero-title" data-gsap="fade-up" data-gsap-delay="0.1"><?php echo esc_html($titulo); ?></h1>
        <?php endif; ?>

        <?php if ($eyebrow): ?>
            <?php // Píldora blanca DEBAJO del título (como Figma). ?>
            <p class="jlb-hero__eyebrow" data-gsap="fade-up" data-gsap-delay="0.2"><span><?php echo esc_html($eyebrow); ?></span></p>
        <?php endif; ?>

        <?php if ($texto): ?>
            <p data-gsap="fade-up" data-gsap-delay="0.28"><?php echo esc_html($texto); ?></p>
        <?php endif; ?>

        <?php
        // Icono ↗ (enlace externo) a la derecha del texto, como en Figma.
        $btn_icon = '<svg class="jlb-btn__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">'
            . '<path d="M5 11L11 5M11 5H6.5M11 5V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        ?>
        <?php if (!empty($btn_pri['url']) || !empty($btn_sec['url'])): ?>
            <div class="jlb-hero__actions" data-gsap="fade-up" data-gsap-delay="0.42">
                <?php if (!empty($btn_pri['url'])): ?>
                    <a class="jlb-btn jlb-btn--light"
                        href="<?php echo esc_url($btn_pri['url']); ?>"
                        target="<?php echo esc_attr($btn_pri['target'] ?? '_self'); ?>"
                        <?php echo ($btn_pri['target'] ?? '') === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                        <span><?php echo esc_html($btn_pri['title'] ?? ''); ?></span>
                        <?php echo $btn_icon; ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($btn_sec['url'])): ?>
                    <a class="jlb-btn jlb-btn--outline"
                        href="<?php echo esc_url($btn_sec['url']); ?>"
                        target="<?php echo esc_attr($btn_sec['target'] ?? '_self'); ?>"
                        <?php echo ($btn_sec['target'] ?? '') === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                        <span><?php echo esc_html($btn_sec['title'] ?? ''); ?></span>
                        <?php echo $btn_icon; ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($imagen['url'])): ?>
        <figure class="jlb-hero__media" data-gsap="zoom-in" data-gsap-delay="0.1" data-gsap-duration="1.1">
            <img src="<?php echo esc_url($imagen['url']); ?>"
                alt="<?php echo esc_attr($imagen['alt'] ?? ''); ?>"
                <?php if (!empty($imagen['width'])): ?>width="<?php echo esc_attr($imagen['width']); ?>"<?php endif; ?>
                <?php if (!empty($imagen['height'])): ?>height="<?php echo esc_attr($imagen['height']); ?>"<?php endif; ?>
                fetchpriority="high"
                decoding="async">
        </figure>
    <?php endif; ?>
</section>
