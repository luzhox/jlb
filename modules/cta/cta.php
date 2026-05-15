<?php
/**
 * Módulo: CTA — Fase 2 / shadcn × Kresna
 *
 * Layout horizontal "momento Kresna": heading grande + subtexto + CTA,
 * opcional lucky cube flotante a la derecha.
 *
 * Variantes (campo nuevo `variante`):
 *   - brand   → fondo brand-500 (azul Kresna), texto blanco, botón kresna-dark
 *   - surface → fondo card-soft (#f0f1f5 / espejo dark), texto neutro, botón shadcn-primary
 *   - dark    → fondo neutral-900, texto blanco, sin cube por defecto, botón kresna-dark
 *
 * Modificadores opcionales (campos nuevos):
 *   - cube_visible (true/false)
 *   - video_fondo  (file mp4/webm) — si se rellena, se renderiza vídeo bg
 *     con poster (la imagen_fondo) + reduce-motion compatible.
 *
 * Compatibilidad legacy:
 *   - Lee primero `variante`. Si está vacío, mapea desde `fondo` legacy
 *     (primary/dark/light → brand/dark/surface).
 *   - El campo `imagen_fondo` ahora actúa como poster del vídeo si hay
 *     video_fondo. Si no hay video_fondo se ignora (la composición Kresna
 *     no usa imagen full-bleed en CTA).
 */

$titulo      = get_sub_field('titulo');
$subtitulo   = get_sub_field('subtitulo');
$btn_main    = get_sub_field('boton_principal');
$btn_sec     = get_sub_field('boton_secundario');
$alineacion  = get_sub_field('alineacion') ?: 'left';
$cube_visible = (bool) get_sub_field('cube_visible');
$video_fondo = get_sub_field('video_fondo');
$poster      = get_sub_field('imagen_fondo');

// ── Resolver variante (nuevo) con fallback al legacy `fondo` ─────────────────
$variante = get_sub_field('variante');
if (!$variante) {
    $fondo_legacy = get_sub_field('fondo');
    $map_legacy = array(
        'primary' => 'brand',
        'dark'    => 'dark',
        'light'   => 'surface',
        ''        => 'surface',
    );
    $variante = $map_legacy[(string) $fondo_legacy] ?? 'surface';
}
$variante = in_array($variante, array('brand', 'surface', 'dark'), true) ? $variante : 'surface';

if (!$titulo && !$btn_main) return;

// ── Estilos por variante ────────────────────────────────────────────────────
$variant_classes = array(
    'brand'   => 'bg-brand-500 text-white',
    'surface' => 'bg-[var(--color-card-soft)] text-foreground',
    'dark'    => 'bg-foreground text-background',
);
$btn_main_variant = ($variante === 'surface') ? 'shadcn-primary' : 'kresna-dark';
$btn_sec_variant  = ($variante === 'surface') ? 'shadcn-outline' : 'shadcn-ghost';
$align_class      = $alineacion === 'center' ? 'text-center items-center'
                  : ($alineacion === 'right' ? 'text-right items-end' : 'text-left items-start');

$has_video = !empty($video_fondo) && !empty($video_fondo['url']);
$video_url = $has_video ? $video_fondo['url'] : '';
$video_mime = $has_video ? ($video_fondo['mime_type'] ?? 'video/mp4') : '';
$poster_url = (!empty($poster) && !empty($poster['url'])) ? $poster['url'] : '';

$show_cube = $cube_visible && in_array($variante, array('brand', 'dark'), true);
?>
<section
    class="cta cta--<?php echo esc_attr($variante); ?> relative overflow-hidden rounded-2xl <?php echo esc_attr($variant_classes[$variante]); ?> my-12 lg:my-16"
    data-gsap="fade-up"
>
    <?php if ($has_video): ?>
        <video
            class="cta__bg absolute inset-0 w-full h-full object-cover"
            autoplay muted loop playsinline
            preload="metadata"
            <?php if ($poster_url): ?>poster="<?php echo esc_url($poster_url); ?>"<?php endif; ?>
            aria-hidden="true"
            data-decorative
        >
            <source src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr($video_mime); ?>">
        </video>
        <div class="cta__overlay absolute inset-0" style="background: var(--gradient-card-fade);" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container relative z-10 py-12 lg:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-8 lg:gap-12 items-center">
            <div class="cta__content flex flex-col gap-4 lg:gap-5 <?php echo esc_attr($align_class); ?>">
                <?php if ($titulo): ?>
                    <h2 class="cta__titulo text-3xl lg:text-4xl font-display font-semibold tracking-tight leading-tight max-w-2xl">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>

                <?php if ($subtitulo): ?>
                    <p class="cta__subtitulo text-lg leading-relaxed opacity-90 max-w-xl">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>

                <?php if ($btn_main || $btn_sec): ?>
                    <div class="cta__botones flex flex-wrap gap-3 mt-2 <?php echo $alineacion === 'center' ? 'justify-center' : ($alineacion === 'right' ? 'justify-end' : 'justify-start'); ?>">
                        <?php if ($btn_main): ?>
                            <?php
                            get_template_part('template-parts/atoms/button', null, array(
                                'label'   => $btn_main['title'] ?? __('Empezar', 'boilerplate'),
                                'url'     => $btn_main['url']   ?? '#',
                                'target'  => $btn_main['target'] ?: '_self',
                                'variant' => $btn_main_variant,
                                'size'    => 'lg',
                            ));
                            ?>
                        <?php endif; ?>

                        <?php if ($btn_sec): ?>
                            <?php
                            get_template_part('template-parts/atoms/button', null, array(
                                'label'   => $btn_sec['title'] ?? __('Saber más', 'boilerplate'),
                                'url'     => $btn_sec['url']   ?? '#',
                                'target'  => $btn_sec['target'] ?: '_self',
                                'variant' => $btn_sec_variant,
                                'size'    => 'lg',
                            ));
                            ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($show_cube): ?>
                <div class="cta__cube hidden lg:flex justify-end items-center pr-2">
                    <?php
                    get_template_part('template-parts/atoms/lucky-cube', null, array(
                        'size'     => 'md',
                        'mark'     => 'K',
                        'rotation' => -10,
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
