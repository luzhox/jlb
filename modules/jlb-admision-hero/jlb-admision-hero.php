<?php
/**
 * Módulo: jlb_admision_hero — Hero de la página de Admisión (Figma 4128:316).
 *
 * Gradiente de marca full-width, texto a la izquierda (título KG 52px + subtítulo
 * + botones-link tipo píldora blanca con icono ↗) e imagen a la derecha con
 * esquina redondeada.
 *
 * Datos (dual-mode flex/$args):
 *   titulo      string (h1)
 *   subtitulo   string
 *   botones     repeater de { texto, url, target }   (máx 2)
 *   imagen      array{url,alt,width,height}
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$eyebrow    = $get('eyebrow');
$titulo     = $get('titulo');
$titulo_img = $get('titulo_imagen', null);
$subtitulo  = $get('subtitulo');
$imagen    = $get('imagen', null);
$video     = (string) $get('video_url');
$video_cap = (string) $get('video_caption');

// Botones (dual-mode repeater).
$botones = array();
if ($in_flex) {
    if (have_rows('botones')) {
        while (have_rows('botones')) {
            the_row();
            $url = (string) get_sub_field('url');
            if (!$url) continue;
            $botones[] = array(
                'texto'  => (string) get_sub_field('texto'),
                'url'    => $url,
                'target' => get_sub_field('target') ?: '_self',
            );
        }
    }
} else {
    foreach ((array) ($args['botones'] ?? array()) as $b) {
        if (!empty($b['url'])) {
            $botones[] = array(
                'texto'  => (string) ($b['texto'] ?? ''),
                'url'    => (string) $b['url'],
                'target' => (string) ($b['target'] ?? '_self'),
            );
        }
    }
}

if (!$titulo && empty($imagen)) {
    return;
}

// Icono ↗ (enlace externo) — SVG propio.
$ext_icon = '<svg class="jlb-link-external__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">'
    . '<path d="M8 16L16 8M16 8H9.5M16 8V14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="jlb-adm-hero" aria-labelledby="jlb-adm-hero-title">
    <div class="jlb-adm-hero__content">
        <?php if ($eyebrow): ?>
            <p class="jlb-adm-hero__eyebrow" data-gsap="fade-up"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if (!empty($titulo_img['url'])): ?>
            <h1 class="jlb-adm-hero__title jlb-adm-hero__title--img" id="jlb-adm-hero-title" data-gsap="fade-up" data-gsap-delay="0.1">
                <img src="<?php echo esc_url($titulo_img['url']); ?>"
                    alt="<?php echo esc_attr($titulo ?: $titulo_img['alt'] ?? ''); ?>"
                    <?php if (!empty($titulo_img['width'])): ?>width="<?php echo esc_attr($titulo_img['width']); ?>"<?php endif; ?>
                    <?php if (!empty($titulo_img['height'])): ?>height="<?php echo esc_attr($titulo_img['height']); ?>"<?php endif; ?>
                    fetchpriority="high" decoding="async">
            </h1>
        <?php elseif ($titulo): ?>
            <h1 class="jlb-adm-hero__title" id="jlb-adm-hero-title" data-gsap="fade-up" data-gsap-delay="0.1">
                <?php echo esc_html($titulo); ?>
            </h1>
        <?php endif; ?>

        <?php if ($subtitulo): ?>
            <p class="jlb-adm-hero__subtitle" data-gsap="fade-up" data-gsap-delay="0.2"><?php echo esc_html($subtitulo); ?></p>
        <?php endif; ?>

        <?php if (!empty($botones)): ?>
            <div class="jlb-adm-hero__actions" data-gsap="fade-up" data-gsap-delay="0.3">
                <?php foreach ($botones as $bi => $b):
                    $blank = $b['target'] === '_blank';
                    // 1º botón: píldora blanca rellena (texto teal). 2º+: outline blanco. (Figma)
                    $btn_cls = 'jlb-link-external' . ($bi > 0 ? ' jlb-link-external--outline' : '');
                ?>
                    <a class="<?php echo esc_attr($btn_cls); ?>"
                        href="<?php echo esc_url($b['url']); ?>"
                        target="<?php echo esc_attr($b['target']); ?>"
                        <?php echo $blank ? 'rel="noopener noreferrer"' : ''; ?>>
                        <span><?php echo esc_html($b['texto']); ?></span>
                        <?php echo $ext_icon; ?>
                        <?php if ($blank): ?><span class="sr-text"><?php esc_html_e('(se abre en una ventana nueva)', 'boilerplate'); ?></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($imagen['url'])):
        // Si hay video, la imagen es un enlace que abre el lightbox (data-jlb-video).
        $media_tag = $video !== '' ? 'a' : 'figure';
        $play_svg  = get_template_directory_uri() . '/assets/figma-home/blog/play.svg';
    ?>
        <<?php echo $media_tag; ?> class="jlb-adm-hero__media<?php echo $video !== '' ? ' jlb-adm-hero__media--video' : ''; ?>"
            data-gsap="zoom-in" data-gsap-delay="0.15" data-gsap-duration="1.1"
            <?php if ($video !== ''): ?>
                href="<?php echo esc_url($video); ?>"
                data-jlb-video="<?php echo esc_url($video); ?>"
                aria-label="<?php echo esc_attr($video_cap ?: __('Reproducir video', 'boilerplate')); ?>"
            <?php endif; ?>>
            <img src="<?php echo esc_url($imagen['url']); ?>"
                alt="<?php echo esc_attr($imagen['alt'] ?? ''); ?>"
                <?php if (!empty($imagen['width'])): ?>width="<?php echo esc_attr($imagen['width']); ?>"<?php endif; ?>
                <?php if (!empty($imagen['height'])): ?>height="<?php echo esc_attr($imagen['height']); ?>"<?php endif; ?>
                fetchpriority="high"
                decoding="async">
            <?php if ($video !== ''): ?>
                <span class="jlb-adm-hero__play" aria-hidden="true">
                    <img src="<?php echo esc_url($play_svg); ?>" alt="" width="96" height="96">
                    <?php if ($video_cap): ?><span class="jlb-adm-hero__play-cap"><?php echo esc_html($video_cap); ?></span><?php endif; ?>
                </span>
            <?php endif; ?>
        </<?php echo $media_tag; ?>>
    <?php endif; ?>

    <?php // Wave blanco de transición al pie del hero (Figma 4128:324). ?>
    <svg class="jlb-adm-hero__wave" viewBox="0 0 4883 229" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M0 157.253L135.639 131.012C271.278 105.163 542.556 52.0901 813.833 62.9013C1085.11 73.7124 1356.39 146.442 1627.67 157.253C1898.94 168.064 2170.22 114.991 2441.5 89.1429C2712.78 62.9013 2984.06 62.9013 3255.33 68.1103C3526.61 73.7124 3797.89 83.5408 4069.17 73.4176C4340.44 62.9013 4611.72 31.4506 4747.36 15.7253L4883 0V229H0L0 157.253Z" fill="#fff"/>
    </svg>
</section>
