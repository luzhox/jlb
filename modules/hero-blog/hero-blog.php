<?php
/**
 * Módulo: Hero Blog — Fase 3 / shadcn × Kresna
 *
 * Hero secundario (no full-screen) usado para landing/blog/categoría/etc.
 * Layout:
 *   - min-height clamp(360px, 50vh, 560px)
 *   - Background imagen full-bleed con overlay (color picker o gradient
 *     fade por defecto)
 *   - Texto centrado, h1 display 4xl-6xl blanco
 *   - CTA opcional con button atom variant kresna-dark
 *
 * fetchpriority="high" en la imagen porque suele ser LCP de la página.
 *
 * Sin migración ACF — refactor visual puro. El campo `text` es WYSIWYG y
 * el cliente puede meter h1+p; aplicamos prose-on-dark para tipografía.
 */

$bg      = get_sub_field('bg');           // URL string
$overlay = trim((string) get_sub_field('overlay'));
$text    = get_sub_field('text');
$button  = get_sub_field('button');

if (!$bg && !$text) return;
?>
<section
    class="hero-blog relative overflow-hidden flex items-center justify-center"
    style="min-height: clamp(360px, 50vh, 560px);"
>
    <?php if ($bg): ?>
        <img
            class="hero-blog__bg absolute inset-0 w-full h-full object-cover"
            src="<?php echo esc_url($bg); ?>"
            alt=""
            aria-hidden="true"
            fetchpriority="high"
            decoding="async"
        >
    <?php endif; ?>

    <?php
    // Overlay: si el cliente puso un color personalizado, lo usamos como
    // background-color sólido. Si no, gradient fade del foreground (negro
    // adaptable a dark mode) para asegurar contraste WCAG AA del título.
    if ($overlay !== ''):
    ?>
        <div class="hero-blog__overlay absolute inset-0" style="background-color:<?php echo esc_attr($overlay); ?>;" aria-hidden="true"></div>
    <?php else: ?>
        <div class="hero-blog__overlay absolute inset-0" style="background: linear-gradient(180deg, rgba(10,10,11,0.55) 0%, rgba(10,10,11,0.75) 100%);" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container relative z-10 py-16 lg:py-24">
        <div class="hero-blog__inner max-w-3xl mx-auto text-center flex flex-col items-center gap-5">
            <?php if ($text): ?>
                <div
                    class="hero-blog__text text-white"
                    data-gsap="fade-up"
                >
                    <?php echo wp_kses_post($text); ?>
                </div>
            <?php endif; ?>

            <?php if ($button && !empty($button['url'])): ?>
                <div
                    class="hero-blog__button"
                    data-gsap="fade-up"
                    data-gsap-delay="0.15"
                >
                    <?php
                    get_template_part('template-parts/atoms/button', null, array(
                        'label'   => $button['title'] ?? __('Saber más', 'boilerplate'),
                        'url'     => $button['url']   ?? '#',
                        'target'  => $button['target'] ?: '_self',
                        'variant' => 'kresna-dark',
                        'size'    => 'lg',
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Tipografía display + sizing para el WYSIWYG dentro del hero. Solo se
// imprime una vez por request aunque el módulo se repita.
if (empty($GLOBALS['__bp_hero_blog_styled'])):
    $GLOBALS['__bp_hero_blog_styled'] = true;
?>
<style>
.hero-blog__text h1,
.hero-blog__text h2 {
    font-family: var(--font-display);
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1.05;
    margin: 0;
    color: #fff;
    font-size: clamp(2.25rem, 1.5rem + 3vw, 3.75rem);
}
.hero-blog__text p {
    margin: 0.75rem 0 0;
    font-size: clamp(1rem, 0.95rem + 0.3vw, 1.125rem);
    line-height: 1.6;
    color: rgb(255 255 255 / 0.85);
}
</style>
<?php endif; ?>
