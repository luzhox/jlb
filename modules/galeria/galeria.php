<?php
/**
 * Módulo: Galería — Fase 3 / shadcn × Kresna
 *
 * Grid de imágenes cuadradas con lightbox vía Colorbox (solo singular).
 * Mantiene clases BEM (.galeria__link, data-rel="galeria-{uid}") porque
 * jquery.colorbox engancha selector `.galeria__link[data-rel]`.
 *
 * - Aspect 1:1 con object-cover
 * - Hover sutil scale(1.04) + overlay zoom-in icon
 * - Caption opcional debajo
 * - Stagger zoom-in con GSAP
 *
 * Sin migración ACF — refactor visual puro. Las clases nuevas Tailwind
 * conviven con BEM legacy: utilities ganan en cascade order (utilities >
 * legacy en @layer order definido en src/main.css).
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$columnas  = get_sub_field('columnas') ?: '3';
$imagenes  = get_sub_field('imagenes');

if (!$imagenes) return;

$col_classes = array(
    '2' => 'grid-cols-2 md:grid-cols-2',
    '3' => 'grid-cols-2 md:grid-cols-3',
    '4' => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
);
$col_clean = isset($col_classes[$columnas]) ? $columnas : '3';

$uid = uniqid('galeria-');
?>
<section class="galeria py-16 lg:py-24">
    <div class="container">
        <?php if ($titulo || $subtitulo): ?>
            <header class="mb-10 lg:mb-14 max-w-2xl">
                <?php if ($titulo): ?>
                    <h2 class="galeria__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($subtitulo): ?>
                    <p class="galeria__subtitulo mt-3 text-lg text-muted-foreground">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <div class="galeria__grid galeria__grid--col-<?php echo esc_attr($col_clean); ?> grid <?php echo esc_attr($col_classes[$col_clean]); ?> gap-3 md:gap-4 lg:gap-5">
            <?php
            $i = 0;
            foreach ($imagenes as $imagen):
                $delay = number_format($i * 0.06, 2, '.', '');
                $url_full = $imagen['url'];
                $url_med  = $imagen['sizes']['medium_large'] ?? $imagen['url'];
                $w_med    = $imagen['sizes']['medium_large-width']  ?? ($imagen['width']  ?? 800);
                $h_med    = $imagen['sizes']['medium_large-height'] ?? ($imagen['height'] ?? 800);
                $alt_text = $imagen['alt'] ?: ($imagen['title'] ?? '');
                $caption  = $imagen['caption'] ?? '';
            ?>
                <figure
                    class="galeria__item group flex flex-col"
                    data-gsap="zoom-in"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <a
                        href="<?php echo esc_url($url_full); ?>"
                        class="galeria__link relative block overflow-hidden rounded-xl bg-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        data-rel="<?php echo esc_attr($uid); ?>"
                        aria-label="<?php echo esc_attr($alt_text ?: __('Ver imagen ampliada', 'boilerplate')); ?>"
                        style="aspect-ratio: 1/1;"
                    >
                        <img
                            class="galeria__img absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                            src="<?php echo esc_url($url_med); ?>"
                            alt="<?php echo esc_attr($alt_text); ?>"
                            width="<?php echo esc_attr($w_med); ?>"
                            height="<?php echo esc_attr($h_med); ?>"
                            loading="lazy"
                            decoding="async"
                        >
                        <span class="galeria__overlay absolute inset-0 bg-foreground/0 group-hover:bg-foreground/20 transition-colors duration-300 flex items-center justify-center" aria-hidden="true">
                            <span class="galeria__zoom-icon opacity-0 group-hover:opacity-100 transition-opacity duration-300 w-10 h-10 rounded-full bg-white/95 text-foreground flex items-center justify-center">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                    <line x1="11" y1="8" x2="11" y2="14"></line>
                                    <line x1="8" y1="11" x2="14" y2="11"></line>
                                </svg>
                            </span>
                        </span>
                    </a>
                    <?php if ($caption !== ''): ?>
                        <figcaption class="galeria__caption text-sm text-muted-foreground mt-2 leading-relaxed">
                            <?php echo esc_html($caption); ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
                <?php $i++; endforeach; ?>
        </div>
    </div>
</section>
