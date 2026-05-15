<?php
/**
 * Módulo: Hero — Fase 2 / shadcn × Kresna
 *
 * Mantiene compatibilidad con el repeater `sliderhero` legacy.
 *
 * Campos nuevos (Fase 2):
 *   - tipo_fondo (select: swiper|video|imagen|color, default 'swiper')
 *   - overline_manuscrito (text) → Caveat overline opcional sobre H1
 *   - video_fondo (file mp4/webm, solo si tipo_fondo='video')
 *
 * Comportamiento:
 *   - swiper: render legacy con repeater (compat exacta).
 *   - video : <video> bg con poster (primer slide imagen_de_escritorio
 *             como poster + LCP). Reduce-motion → no autoplay.
 *   - imagen: imagen única bg full-bleed (primer slide imagen).
 *   - color : solo color de fondo (overlay del primer slide o brand).
 *
 * El campo `overline_manuscrito` es el 3er sitio autorizado de Caveat
 * (§3.3 design-system). Si no se rellena, hero queda 100% shadcn.
 */

$tipo_fondo = get_sub_field('tipo_fondo') ?: 'swiper';
$overline   = get_sub_field('overline_manuscrito');
$video      = get_sub_field('video_fondo');
$ver_mas    = get_sub_field('ver_mas');

// ── Swiper (legacy) ─────────────────────────────────────────────────────────
if ($tipo_fondo === 'swiper'):
    if (!have_rows('sliderhero')) return;
    ?>
    <section class="hero">
        <div class="swiper hero-container">
            <div class="swiper-wrapper">
                <?php while (have_rows('sliderhero')): the_row(); ?>
                    <?php
                    // Resolución de imagen responsive con fallback:
                    //   - Si hay imagen_de_mobile, se usa solo en <601px (`hide-on-med-and-up`).
                    //   - Si NO hay imagen_de_mobile, la `imagen_de_escritorio` se muestra
                    //     SIEMPRE (también en mobile) en lugar de quedar oculta por
                    //     `hide-on-small-only`. Antes el slide quedaba VACÍO en mobile.
                    $img_desktop = get_sub_field('imagen_de_escritorio');
                    $img_mobile  = get_sub_field('imagen_de_mobile');
                    $overlay_col = trim((string) get_sub_field('overlay'));
                    $desktop_class = !empty($img_mobile) ? 'hide-on-small-only' : '';
                    ?>
                    <div class="swiper-slide hero-item">
                        <?php // Overlay solo si hay color real (evita div transparente que tape touch). ?>
                        <?php if ($overlay_col !== ''): ?>
                            <div class="overlay" style="background-color:<?php echo esc_attr($overlay_col); ?>"></div>
                        <?php endif; ?>

                        <?php if (!empty($img_desktop)): ?>
                            <img class="<?php echo esc_attr($desktop_class); ?>"
                                src="<?php echo esc_url($img_desktop['url']); ?>"
                                alt="<?php echo esc_attr($img_desktop['alt']); ?>"
                                width="<?php echo esc_attr($img_desktop['width']); ?>"
                                height="<?php echo esc_attr($img_desktop['height']); ?>" />
                        <?php endif; ?>

                        <?php if (!empty($img_mobile)): ?>
                            <img class="hide-on-med-and-up"
                                src="<?php echo esc_url($img_mobile['url']); ?>"
                                alt="<?php echo esc_attr($img_mobile['alt']); ?>"
                                width="<?php echo esc_attr($img_mobile['width']); ?>"
                                height="<?php echo esc_attr($img_mobile['height']); ?>" />
                        <?php endif; ?>

                        <div class="hero-item__content container">
                            <?php if ($overline): ?>
                                <span class="hero-item__overline font-handwritten text-2xl text-white/90 block mb-2">
                                    <?php echo esc_html($overline); ?>
                                </span>
                            <?php endif; ?>
                            <div>
                                <?php echo wp_kses_post(get_sub_field('texto')); ?>
                            </div>
                            <?php
                            // Sin data-gsap aquí: GSAP/ScrollTrigger puede no dispararse a
                            // tiempo si el hero está en viewport desde el primer paint, dejando
                            // texto/CTA en opacity:0 hasta scroll. En el hero modo swiper no
                            // hace falta animación de entrada (Swiper ya da movimiento entre
                            // slides). Para los modos video/imagen/color el data-gsap se
                            // mantiene más abajo porque el contenido sí entra desde quieto.
                            $link     = get_sub_field('boton');
                            $hayboton = get_sub_field('hay_boton');
                            if ($hayboton && $link):
                                $link_url    = $link['url'];
                                $link_title  = $link['title'];
                                $link_target = $link['target'] ? $link['target'] : '_self';
                                ?>
                                <a
                                    href="<?php echo esc_url($link_url); ?>"
                                    target="<?php echo esc_attr($link_target); ?>"
                                    <?php echo $link_target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                                ><?php echo esc_html($link_title); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

        <?php if ($ver_mas): ?>
            <div class="vermas">
                <?php
                $link_url    = $ver_mas['url'];
                $link_title  = $ver_mas['title'];
                $link_target = $ver_mas['target'] ? $ver_mas['target'] : '_self';
                ?>
                <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"
                    <?php echo $link_target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                    <span></span><?php echo esc_html($link_title); ?>
                </a>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return;
endif;

// ── Modos no-slider: video / imagen / color ────────────────────────────────
// Tomamos la información del primer slide como contenido principal.
$slides = get_sub_field('sliderhero');
$first  = (is_array($slides) && !empty($slides)) ? $slides[0] : null;

$texto    = $first['texto']                  ?? '';
$boton    = $first['boton']                  ?? null;
$hayboton = !empty($first['hay_boton']);
$img_d    = $first['imagen_de_escritorio']   ?? null;
$overlay  = $first['overlay']                ?? '';

$has_text = !empty($texto) || !empty($overline);
if (!$has_text && !$img_d) return;

$poster_url = (!empty($img_d) && !empty($img_d['url'])) ? $img_d['url'] : '';
$poster_alt = (!empty($img_d) && !empty($img_d['alt'])) ? $img_d['alt'] : '';
$poster_w   = (!empty($img_d) && !empty($img_d['width'])) ? $img_d['width'] : '';
$poster_h   = (!empty($img_d) && !empty($img_d['height'])) ? $img_d['height'] : '';

$has_video = $tipo_fondo === 'video' && !empty($video) && !empty($video['url']);
$video_url = $has_video ? $video['url'] : '';
$video_mime = $has_video ? ($video['mime_type'] ?? 'video/mp4') : '';

$inline_bg_style = ($tipo_fondo === 'color' && $overlay) ? 'background-color:' . esc_attr($overlay) . ';' : '';
?>
<section
    class="hero hero--<?php echo esc_attr($tipo_fondo); ?> relative overflow-hidden flex items-center"
    style="min-height: clamp(480px, 70vh, 720px); <?php echo $inline_bg_style; ?>"
>
    <?php if ($has_video): ?>
        <video
            class="hero__bg absolute inset-0 w-full h-full object-cover"
            autoplay muted loop playsinline
            preload="metadata"
            <?php if ($poster_url): ?>poster="<?php echo esc_url($poster_url); ?>"<?php endif; ?>
            aria-hidden="true"
            data-decorative
        >
            <source src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr($video_mime); ?>">
        </video>
        <div class="hero__overlay absolute inset-0" style="background: var(--gradient-card-fade);" aria-hidden="true"></div>
    <?php elseif ($tipo_fondo === 'imagen' && $poster_url): ?>
        <img
            class="hero__bg absolute inset-0 w-full h-full object-cover"
            src="<?php echo esc_url($poster_url); ?>"
            alt="<?php echo esc_attr($poster_alt); ?>"
            <?php if ($poster_w): ?>width="<?php echo esc_attr($poster_w); ?>"<?php endif; ?>
            <?php if ($poster_h): ?>height="<?php echo esc_attr($poster_h); ?>"<?php endif; ?>
            fetchpriority="high"
            decoding="async"
        >
        <?php if ($overlay): ?>
            <div class="hero__overlay absolute inset-0" style="background-color:<?php echo esc_attr($overlay); ?>" aria-hidden="true"></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="container relative z-10 py-16 lg:py-24">
        <div class="max-w-3xl flex flex-col gap-4">
            <?php if ($overline): ?>
                <span
                    class="hero__overline font-handwritten text-2xl lg:text-3xl <?php echo $tipo_fondo === 'video' || $tipo_fondo === 'imagen' ? 'text-white/95' : 'text-foreground'; ?>"
                    data-gsap="fade-up"
                >
                    <?php echo esc_html($overline); ?>
                </span>
            <?php endif; ?>

            <?php if ($texto): ?>
                <div
                    class="hero__texto <?php echo $tipo_fondo === 'video' || $tipo_fondo === 'imagen' ? 'text-white' : 'text-foreground'; ?>"
                    data-gsap="fade-up"
                    data-gsap-delay="0.15"
                >
                    <?php echo wp_kses_post($texto); ?>
                </div>
            <?php endif; ?>

            <?php if ($hayboton && $boton): ?>
                <div
                    class="hero__cta mt-2"
                    data-gsap="fade-up"
                    data-gsap-delay="0.3"
                >
                    <?php
                    get_template_part('template-parts/atoms/button', null, array(
                        'label'   => $boton['title'] ?? __('Saber más', 'boilerplate'),
                        'url'     => $boton['url']   ?? '#',
                        'target'  => $boton['target'] ?: '_self',
                        'variant' => 'kresna-dark',
                        'size'    => 'lg',
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($ver_mas): ?>
        <div class="vermas absolute bottom-6 left-1/2 -translate-x-1/2 z-20">
            <?php
            $link_url    = $ver_mas['url'];
            $link_title  = $ver_mas['title'];
            $link_target = $ver_mas['target'] ? $ver_mas['target'] : '_self';
            ?>
            <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"
                <?php echo $link_target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                <span></span><?php echo esc_html($link_title); ?>
            </a>
        </div>
    <?php endif; ?>
</section>
