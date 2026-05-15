<?php
/**
 * Módulo: Estadísticas — Fase 3 / shadcn × Kresna
 *
 * Grid de números grandes con counter GSAP. Tres variantes según el campo
 * `fondo`:
 *   - light    → bg muted, números brand-600
 *   - primary  → bg brand-500, texto blanco, números white
 *   - dark     → bg foreground, texto background, números brand-300
 *
 * Counter: el atributo `data-gsap-counter` lo dispara la animación en
 * src/animations/onScroll.js (no se rompe si está fuera de viewport).
 * Las clases BEM (.estadisticas__valor, .estadisticas__item) se mantienen
 * porque el JS legacy puede engancharlas.
 *
 * Sin migración ACF — refactor visual puro.
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$fondo     = get_sub_field('fondo') ?: 'light';
$items     = get_sub_field('items');

if (!$items) return;

// Variantes shadcn × Kresna ──────────────────────────────────────────────────
$variant_classes = array(
    'light'   => 'bg-muted/50 text-foreground',
    'primary' => 'bg-brand-500 text-white',
    'dark'    => 'bg-foreground text-background',
);
$valor_classes = array(
    'light'   => 'text-brand-600',
    'primary' => 'text-white',
    'dark'    => 'text-brand-300',
);
$sufijo_classes = array(
    'light'   => 'text-muted-foreground',
    'primary' => 'text-white/80',
    'dark'    => 'text-background/70',
);
$etiqueta_classes = array(
    'light'   => 'text-foreground',
    'primary' => 'text-white',
    'dark'    => 'text-background',
);
$desc_classes = array(
    'light'   => 'text-muted-foreground',
    'primary' => 'text-white/85',
    'dark'    => 'text-background/75',
);
$subtitulo_classes = array(
    'light'   => 'text-muted-foreground',
    'primary' => 'text-white/85',
    'dark'    => 'text-background/75',
);

$fondo_clean = isset($variant_classes[$fondo]) ? $fondo : 'light';
$container_outer = $variant_classes[$fondo_clean];
?>
<section
    class="estadisticas estadisticas--<?php echo esc_attr($fondo_clean); ?> py-16 lg:py-24 <?php echo esc_attr($container_outer); ?>"
>
    <div class="container">
        <?php if ($titulo || $subtitulo): ?>
            <header class="mb-10 lg:mb-14 max-w-2xl">
                <?php if ($titulo): ?>
                    <h2 class="estadisticas__titulo text-3xl lg:text-4xl font-display font-semibold tracking-tight">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($subtitulo): ?>
                    <p class="estadisticas__subtitulo mt-3 text-lg <?php echo esc_attr($subtitulo_classes[$fondo_clean]); ?>">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <div class="estadisticas__grid grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8" data-gsap-batch=".estadisticas__item">
            <?php
            $i = 0;
            foreach ($items as $item):
                $delay   = number_format($i * 0.08, 2, '.', '');
                $numero  = isset($item['numero']) ? (int) $item['numero'] : 0;
                $sufijo  = isset($item['sufijo']) ? (string) $item['sufijo'] : '';
                $etiqueta = isset($item['etiqueta']) ? (string) $item['etiqueta'] : '';
                $desc    = isset($item['descripcion']) ? (string) $item['descripcion'] : '';
            ?>
                <div
                    class="estadisticas__item flex flex-col gap-1.5"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <div class="estadisticas__numero flex items-baseline gap-1 leading-none">
                        <span
                            class="estadisticas__valor text-4xl lg:text-5xl xl:text-6xl font-display font-bold tracking-tight <?php echo esc_attr($valor_classes[$fondo_clean]); ?>"
                            data-gsap-counter
                        ><?php echo absint($numero); ?></span>
                        <?php if ($sufijo !== ''): ?>
                            <span class="estadisticas__sufijo text-2xl lg:text-3xl font-display font-semibold <?php echo esc_attr($sufijo_classes[$fondo_clean]); ?>">
                                <?php echo esc_html($sufijo); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($etiqueta): ?>
                        <p class="estadisticas__etiqueta text-base font-semibold mt-2 <?php echo esc_attr($etiqueta_classes[$fondo_clean]); ?>">
                            <?php echo esc_html($etiqueta); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($desc): ?>
                        <p class="estadisticas__desc text-sm leading-relaxed <?php echo esc_attr($desc_classes[$fondo_clean]); ?>">
                            <?php echo esc_html($desc); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php $i++; endforeach; ?>
        </div>
    </div>
</section>
