<?php
/**
 * Módulo: Acordeón — Fase 3 / shadcn × Kresna
 *
 * Cards hairline shadcn agrupadas en stack. Cada item su propia card con
 * border 1px y radius xl. Chevron SVG que rota 180° cuando aria-expanded=true,
 * en lugar del ± legacy con pseudo-elementos.
 *
 * Clases BEM legacy preservadas porque src/acordeon.js las lee:
 *   - .acordeon__pregunta  ← el JS engancha el click
 *   - aria-controls + ID   ← el JS toggle el atributo `hidden` en el panel
 *
 * El SCSS legacy en _modules.scss sigue cargando pero como las clases BEM
 * coexisten con utilities Tailwind, las utilities ganan en cascade order
 * (utilities > legacy en @layer order).
 *
 * Sin migración ACF (refactor visual puro).
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$items     = get_sub_field('items');

if (!$items) return;

$uid = uniqid('acordeon-');
?>
<section class="acordeon py-16 lg:py-24">
    <div class="container">
        <?php if ($titulo || $subtitulo): ?>
            <header class="mb-10 lg:mb-14 max-w-2xl">
                <?php if ($titulo): ?>
                    <h2 class="acordeon__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($subtitulo): ?>
                    <p class="acordeon__subtitulo mt-3 text-lg text-muted-foreground">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <dl class="acordeon__lista flex flex-col gap-3 max-w-3xl">
            <?php foreach ($items as $i => $item):
                $item_id = $uid . '-' . $i;
                $delay   = number_format($i * 0.04, 2, '.', '');
            ?>
                <div
                    class="acordeon__item bg-card border border-border rounded-xl overflow-hidden transition-colors duration-200"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <dt>
                        <button
                            class="acordeon__pregunta w-full flex items-center justify-between gap-4 px-5 lg:px-6 py-4 lg:py-5 text-left text-base lg:text-lg font-semibold text-foreground hover:bg-muted/50 transition-colors duration-200"
                            aria-expanded="false"
                            aria-controls="<?php echo esc_attr($item_id); ?>"
                            type="button"
                        >
                            <span><?php echo esc_html($item['pregunta']); ?></span>
                            <span class="acordeon__icono flex-shrink-0 text-muted-foreground transition-[transform,color] duration-300" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd
                        class="acordeon__respuesta"
                        id="<?php echo esc_attr($item_id); ?>"
                        hidden
                    >
                        <div class="acordeon__contenido px-5 lg:px-6 pb-5 lg:pb-6 pt-1 text-base text-muted-foreground leading-relaxed border-t border-border/50">
                            <?php echo wp_kses_post($item['respuesta']); ?>
                        </div>
                    </dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>

<?php
// Estilos del acordeón que no encajan bien con utilities Tailwind v4:
//   - :has() para reaccionar a aria-expanded del button hijo
//   - rotación + cambio de color del chevron sin necesidad de class extra en JS
// Patrón embebido (igual que lucky-cube, footer-watermark) con flag global
// para imprimir el CSS UNA sola vez por request aunque el módulo se repita.
if (empty($GLOBALS['__bp_acordeon_styled'])):
    $GLOBALS['__bp_acordeon_styled'] = true;
?>
<style>
.acordeon__item:has(button[aria-expanded="true"]) {
    border-color: color-mix(in srgb, var(--color-foreground) 20%, transparent);
}
.acordeon__pregunta[aria-expanded="true"] .acordeon__icono {
    transform: rotate(180deg);
    color: var(--color-brand-600);
}
@media (prefers-reduced-motion: reduce) {
    .acordeon__icono { transition: none; }
}
</style>
<?php endif; ?>
