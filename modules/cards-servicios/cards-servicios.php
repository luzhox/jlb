<?php
/**
 * Módulo: Cards de servicios — Fase 3 / shadcn × Kresna
 *
 * Grid de cards hairline shadcn (border 1px, no shadow). Cada card:
 *   - Icono ACF (PNG/SVG) dentro de un wrapper redondeado bg-brand-50
 *   - Título h3 semibold
 *   - Descripción line-clamp-3 muted
 *   - Link con flecha que se separa en hover (gap-1 → gap-2)
 *
 * Toda la card es clicable mediante .stretched-link (before:absolute).
 *
 * Hover: -translate-y-0.5 + border-color shift (foreground/20).
 *
 * Sin migración ACF — refactor visual puro.
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$columnas  = get_sub_field('columnas') ?: '3';
$items     = get_sub_field('items');

if (!$items) return;

$col_classes = array(
    '2' => 'grid-cols-1 md:grid-cols-2',
    '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    '4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
);
$col_clean = isset($col_classes[$columnas]) ? $columnas : '3';
?>
<section class="cards-servicios py-16 lg:py-24">
    <div class="container">
        <?php if ($titulo || $subtitulo): ?>
            <header class="mb-10 lg:mb-14 max-w-2xl">
                <?php if ($titulo): ?>
                    <h2 class="cards-servicios__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($subtitulo): ?>
                    <p class="cards-servicios__subtitulo mt-3 text-lg text-muted-foreground">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <div class="cards-servicios__grid cards-servicios__grid--col-<?php echo esc_attr($col_clean); ?> grid <?php echo esc_attr($col_classes[$col_clean]); ?> gap-6 md:gap-8">
            <?php
            $i = 0;
            foreach ($items as $item):
                $delay      = number_format($i * 0.08, 2, '.', '');
                $titulo_card = isset($item['titulo_card']) ? (string) $item['titulo_card'] : '';
                $descripcion = isset($item['descripcion']) ? (string) $item['descripcion'] : '';
                $icono       = $item['icono'] ?? null;
                $boton       = $item['boton'] ?? null;
                $has_icono   = !empty($icono) && !empty($icono['url']);
                $has_boton   = !empty($boton) && !empty($boton['url']);
            ?>
                <article
                    class="cards-servicios__card group relative flex flex-col bg-card border border-border rounded-xl p-6 lg:p-7 transition-all duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-card"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <?php if ($has_icono): ?>
                        <div class="cards-servicios__icono w-12 h-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
                            <img
                                src="<?php echo esc_url($icono['url']); ?>"
                                alt=""
                                aria-hidden="true"
                                width="28" height="28"
                                loading="lazy"
                                decoding="async"
                                class="w-7 h-7 object-contain"
                            >
                        </div>
                    <?php endif; ?>

                    <?php if ($titulo_card): ?>
                        <h3 class="cards-servicios__nombre text-xl font-semibold text-foreground mb-2 leading-snug">
                            <?php if ($has_boton): ?>
                                <a
                                    href="<?php echo esc_url($boton['url']); ?>"
                                    class="cards-servicios__title-link before:content-[''] before:absolute before:inset-0 before:z-10"
                                    target="<?php echo esc_attr($boton['target'] ?: '_self'); ?>"
                                    aria-label="<?php echo esc_attr($titulo_card); ?>"
                                ><?php echo esc_html($titulo_card); ?></a>
                            <?php else: ?>
                                <?php echo esc_html($titulo_card); ?>
                            <?php endif; ?>
                        </h3>
                    <?php endif; ?>

                    <?php if ($descripcion): ?>
                        <p class="cards-servicios__desc text-sm text-muted-foreground line-clamp-3 mb-5 leading-relaxed">
                            <?php echo esc_html($descripcion); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($has_boton): ?>
                        <span
                            class="cards-servicios__link relative z-20 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 group-hover:gap-2 group-hover:text-brand-700 transition-all duration-200 mt-auto pointer-events-none"
                            aria-hidden="true"
                        >
                            <?php echo esc_html($boton['title'] ?: __('Ver más', 'boilerplate')); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    <?php endif; ?>
                </article>
                <?php $i++; endforeach; ?>
        </div>
    </div>
</section>
