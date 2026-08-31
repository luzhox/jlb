<?php
/**
 * Módulo: jlb_hero — Hero "Admisión" del template Jean Le Boulch (CARRUSEL).
 *
 * Doble fuente:
 *   · Dentro de the_modules_loop()  → lee el repeater `slides` con have_rows().
 *   · Vía get_template_part() con $args → cae al fallback estático
 *     (lo usa front-page.php cuando la página no tiene ACF poblado).
 *
 * Forma de los datos (compatible con ACF):
 *   slides  repeater de slides; cada slide:
 *     eyebrow             string
 *     titulo              string (h1 en el 1er slide, h2 en el resto)
 *     texto               string (párrafo)
 *     boton_principal     array{title,url,target}
 *     boton_secundario    array{title,url,target}
 *     imagen              array{url,alt,width,height}
 *
 * Retrocompat $args: acepta `slides` (nuevo) o los campos planos de un solo
 * slide (eyebrow, titulo, texto, boton_principal, boton_secundario, imagen) y
 * los envuelve en 1 slide.
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();

// ── Normaliza a un array de slides (misma forma en ambos modos) ──────────────
$slides = array();

if ($in_flex && have_rows('slides')) {
    while (have_rows('slides')) {
        the_row();
        $slides[] = array(
            'eyebrow'          => (string) get_sub_field('eyebrow'),
            'titulo'           => (string) get_sub_field('titulo'),
            'texto'            => (string) get_sub_field('texto'),
            'boton_principal'  => get_sub_field('boton_principal'),
            'boton_secundario' => get_sub_field('boton_secundario'),
            'imagen'           => get_sub_field('imagen'),
        );
    }
} else {
    // Fallback $args: lista de slides o, por retrocompat, campos planos = 1 slide.
    $raw = (!empty($args['slides']) && is_array($args['slides']))
        ? $args['slides']
        : array($args);
    foreach ($raw as $s) {
        if (!is_array($s)) {
            continue;
        }
        $slides[] = array(
            'eyebrow'          => (string) ($s['eyebrow'] ?? ''),
            'titulo'           => (string) ($s['titulo'] ?? ''),
            'texto'            => (string) ($s['texto'] ?? ''),
            'boton_principal'  => $s['boton_principal'] ?? null,
            'boton_secundario' => $s['boton_secundario'] ?? null,
            'imagen'           => $s['imagen'] ?? null,
        );
    }
}

// Descarta slides vacíos (sin título, texto ni imagen).
$slides = array_values(array_filter($slides, function ($s) {
    return $s['titulo'] !== '' || $s['texto'] !== '' || !empty($s['imagen']['url']);
}));

if (empty($slides)) {
    return;
}

$total = count($slides);
$multi = $total > 1;

// Icono ↗ (enlace externo) a la derecha del texto, como en Figma.
$btn_icon = '<svg class="jlb-btn__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">'
    . '<path d="M5 11L11 5M11 5H6.5M11 5V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="jlb-hero" aria-labelledby="jlb-hero-title" aria-roledescription="<?php esc_attr_e('carrusel', 'boilerplate'); ?>">
    <div class="jlb-hero__carousel swiper">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $i => $s):
                $is_first = $i === 0;
                $btn_pri  = $s['boton_principal'];
                $btn_sec  = $s['boton_secundario'];
                $imagen   = $s['imagen'];
            ?>
                <div class="swiper-slide jlb-hero__slide" role="group"
                    aria-roledescription="<?php esc_attr_e('diapositiva', 'boilerplate'); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('%1$d de %2$d', 'boilerplate'), $i + 1, $total)); ?>">
                    <div class="jlb-hero__content">
                        <?php if ($s['titulo']): ?>
                            <?php if ($is_first): ?>
                                <h1 class="jlb-hero__title" id="jlb-hero-title"><?php echo esc_html($s['titulo']); ?></h1>
                            <?php else: ?>
                                <h2 class="jlb-hero__title"><?php echo esc_html($s['titulo']); ?></h2>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($s['eyebrow']): ?>
                            <?php // Píldora blanca DEBAJO del título (como Figma). ?>
                            <p class="jlb-hero__eyebrow"><span><?php echo esc_html($s['eyebrow']); ?></span></p>
                        <?php endif; ?>

                        <?php if ($s['texto']): ?>
                            <p class="jlb-hero__text"><?php echo esc_html($s['texto']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($btn_pri['url']) || !empty($btn_sec['url'])): ?>
                            <div class="jlb-hero__actions">
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
                        <figure class="jlb-hero__media">
                            <img src="<?php echo esc_url($imagen['url']); ?>"
                                alt="<?php echo esc_attr($imagen['alt'] ?? ''); ?>"
                                <?php if (!empty($imagen['width'])): ?>width="<?php echo esc_attr($imagen['width']); ?>"<?php endif; ?>
                                <?php if (!empty($imagen['height'])): ?>height="<?php echo esc_attr($imagen['height']); ?>"<?php endif; ?>
                                <?php if ($is_first): ?>fetchpriority="high"<?php else: ?>loading="lazy"<?php endif; ?>
                                decoding="async">
                        </figure>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($multi):
            // Chevrons SVG propios (caps redondeados, estilo del icono ↗). El color
            // lo da el `color` del botón (morado de marca) vía currentColor.
            $chev_prev = '<svg class="jlb-hero__chev" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M14.5 5L8 12L14.5 19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $chev_next = '<svg class="jlb-hero__chev" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M9.5 5L16 12L9.5 19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        ?>
            <div class="jlb-hero__dots swiper-pagination"></div>
            <button type="button" class="jlb-hero__nav jlb-hero__nav--prev swiper-button-prev"
                aria-label="<?php esc_attr_e('Slide anterior', 'boilerplate'); ?>"><?php echo $chev_prev; ?></button>
            <button type="button" class="jlb-hero__nav jlb-hero__nav--next swiper-button-next"
                aria-label="<?php esc_attr_e('Slide siguiente', 'boilerplate'); ?>"><?php echo $chev_next; ?></button>
        <?php endif; ?>

        <?php // Wave blanco de transición al pie del hero. Va DENTRO del .swiper
        // para compartir su stacking-context: queda sobre las imágenes (wrapper)
        // y bajo los controles (z-index:10). Mismo path que .jlb-adm-hero__wave. ?>
        <svg class="jlb-hero__wave" viewBox="0 0 4883 229" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <path d="M0 157.253L135.639 131.012C271.278 105.163 542.556 52.0901 813.833 62.9013C1085.11 73.7124 1356.39 146.442 1627.67 157.253C1898.94 168.064 2170.22 114.991 2441.5 89.1429C2712.78 62.9013 2984.06 62.9013 3255.33 68.1103C3526.61 73.7124 3797.89 83.5408 4069.17 73.4176C4340.44 62.9013 4611.72 31.4506 4747.36 15.7253L4883 0V229H0L0 157.253Z" fill="#fff"/>
        </svg>
    </div>
</section>
