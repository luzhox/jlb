<?php
/**
 * Módulo: Formulario — Fase 2 / shadcn × Kresna
 *
 * Layout shadcn:
 *   - Sin imagen: container narrow (700px), card hairline.
 *   - Con imagen: container content (1150px), grid 1fr 1fr en desktop
 *     (form izquierda, imagen derecha), stacked en mobile.
 *
 * Headings: title text-3xl + subtitle text-lg + description text-base muted.
 * El shortcode CF7 hereda los estilos shadcn vía overrides en src/main.css
 * (selectores `.formulario .wpcf7 input`, `.wpcf7 textarea`, etc.).
 *
 * Sin migración ACF — refactor PHP + CSS.
 */

$titulo      = get_sub_field('titulo');
$subtitulo   = get_sub_field('subtitulo');
$descripcion = get_sub_field('descripcion');
$shortcode   = get_sub_field('shortcode_cf7');
$imagen      = get_sub_field('imagen_lateral');

if (!$shortcode) return;

$has_imagen = !empty($imagen) && !empty($imagen['url']);
?>
<section class="formulario py-16 lg:py-24 <?php echo $has_imagen ? 'formulario--con-imagen' : ''; ?>">
    <div class="<?php echo $has_imagen ? 'container' : 'container container-narrow'; ?>">
        <div class="<?php echo $has_imagen ? 'grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-start' : ''; ?>">
            <div class="formulario__contenido">
                <header class="mb-6 lg:mb-8">
                    <?php if ($titulo): ?>
                        <h2 class="formulario__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                            <?php echo esc_html($titulo); ?>
                        </h2>
                    <?php endif; ?>

                    <?php if ($subtitulo): ?>
                        <p class="formulario__subtitulo mt-3 text-lg text-foreground">
                            <?php echo esc_html($subtitulo); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($descripcion): ?>
                        <p class="formulario__desc mt-2 text-base text-muted-foreground">
                            <?php echo esc_html($descripcion); ?>
                        </p>
                    <?php endif; ?>
                </header>

                <div class="formulario__form bg-card border border-border rounded-xl p-6 md:p-8" data-gsap="fade-up">
                    <?php echo do_shortcode(wp_kses_post($shortcode)); ?>
                </div>
            </div>

            <?php if ($has_imagen): ?>
                <div class="formulario__lateral order-first lg:order-last">
                    <img
                        class="w-full h-auto rounded-xl object-cover"
                        src="<?php echo esc_url($imagen['url']); ?>"
                        alt="<?php echo esc_attr($imagen['alt']); ?>"
                        <?php if (!empty($imagen['width'])): ?>width="<?php echo esc_attr($imagen['width']); ?>"<?php endif; ?>
                        <?php if (!empty($imagen['height'])): ?>height="<?php echo esc_attr($imagen['height']); ?>"<?php endif; ?>
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
