<?php
/**
 * Módulo: Testimonios — Fase 2 / shadcn × Kresna
 *
 * Grid de quote-cards (no más Swiper).
 * Cards hairline shadcn (border 1px, no shadow). Layout 1/2/3 responsive.
 * Avatar 56px circular con border background. Quote en tipografía display
 * con comilla decorativa grande en brand-300 (sutil). Stars amber.
 *
 * Campo nuevo (Fase 2):
 *   - destacado (true/false, default false): card ocupa columna doble en
 *     md+ y fondo brand-50 (light) / brand-900 (dark). Recomendado 1 por
 *     sección, máximo 2.
 *
 * Animación GSAP: data-gsap="fade-up" con stagger por orden DOM.
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$items     = get_sub_field('items');

if (!$items) return;
?>
<section class="testimonios py-16 lg:py-24">
    <div class="container">
        <?php if ($titulo || $subtitulo): ?>
            <header class="mb-10 lg:mb-14 max-w-2xl">
                <?php if ($titulo): ?>
                    <h2 class="text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($subtitulo): ?>
                    <p class="mt-3 text-lg text-muted-foreground">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 auto-rows-fr">
            <?php
            $i = 0;
            foreach ($items as $item):
                $delay      = number_format($i * 0.08, 2, '.', '');
                $destacado  = !empty($item['destacado']);
                $nombre     = isset($item['nombre']) ? (string) $item['nombre'] : '';
                $cargo      = isset($item['cargo']) ? (string) $item['cargo'] : '';
                $empresa    = isset($item['empresa']) ? (string) $item['empresa'] : '';
                $foto       = $item['foto'] ?? null;
                $testimonio = isset($item['testimonio']) ? (string) $item['testimonio'] : '';
                $califica   = isset($item['calificacion']) ? (int) $item['calificacion'] : 0;

                $base_class = 'testimonios__item relative flex flex-col p-6 md:p-8 rounded-xl border border-border';
                $bg_class   = $destacado
                    ? 'bg-brand-50 dark:bg-brand-900 md:col-span-2'
                    : 'bg-card';
                ?>
                <article
                    class="<?php echo esc_attr(trim($base_class . ' ' . $bg_class)); ?>"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <span aria-hidden="true" class="absolute top-4 left-6 font-display text-5xl leading-none text-brand-300 select-none pointer-events-none">
                        &ldquo;
                    </span>

                    <blockquote class="testimonios__cita relative z-10 text-base lg:text-lg text-foreground leading-relaxed mt-4 mb-5">
                        <?php echo wp_kses_post($testimonio); ?>
                    </blockquote>

                    <?php if ($califica > 0): ?>
                        <div
                            class="testimonios__stars flex gap-0.5 mb-5"
                            aria-label="<?php echo esc_attr(sprintf(__('Calificación: %d de 5', 'boilerplate'), $califica)); ?>"
                        >
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <span
                                    class="text-base leading-none <?php echo $s <= $califica ? 'text-warning' : 'text-border'; ?>"
                                    aria-hidden="true"
                                >★</span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                    <footer class="testimonios__autor flex items-center gap-3 mt-auto">
                        <?php if (!empty($foto) && !empty($foto['url'])): ?>
                            <img
                                class="testimonios__foto w-14 h-14 rounded-full object-cover border-2 border-background flex-shrink-0"
                                src="<?php echo esc_url($foto['url']); ?>"
                                alt="<?php echo esc_attr($nombre ?: ''); ?>"
                                width="56" height="56"
                                loading="lazy"
                            >
                        <?php endif; ?>
                        <div class="testimonios__info flex flex-col">
                            <?php if ($nombre): ?>
                                <strong class="testimonios__nombre text-sm font-semibold text-foreground">
                                    <?php echo esc_html($nombre); ?>
                                </strong>
                            <?php endif; ?>
                            <?php if ($cargo || $empresa): ?>
                                <span class="testimonios__cargo text-xs text-muted-foreground">
                                    <?php echo esc_html($cargo); ?>
                                    <?php if ($cargo && $empresa): ?> &mdash; <?php endif; ?>
                                    <?php echo esc_html($empresa); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </footer>
                </article>
                <?php $i++; endforeach; ?>
        </div>
    </div>
</section>
