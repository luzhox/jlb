<?php
/**
 * Módulo: Post — Fase 3 / shadcn × Kresna
 *
 * Componente de "post embed" usable de dos formas:
 *
 *   1) Como módulo ACF Flexible Content (legacy, no registrado en
 *      inc/acf-modules.php — sigue funcionando si se llama vía
 *      the_module('post')). Campos legacy:
 *        - module_post (post object)
 *        - show_post_header (bool)
 *        - show_full_content (bool)
 *
 *   2) Como template-part dentro de single.php (sin sub_fields), donde
 *      lee directamente del global $post.
 *
 * Layout: <article> centrado max-w-3xl con tipografía prose neutral,
 * featured image arriba (aspect 16/9 + radius), badge categoría brand,
 * meta (autor + fecha) en muted-foreground.
 *
 * Sin migración ACF — es refactor visual + cleanup defensivo (sigue
 * tolerando que get_sub_field() no exista cuando se llama fuera de loop).
 */

if (!function_exists('get_sub_field')) {
    return;
}

// ── Datos del post a renderizar ────────────────────────────────────────────
$module_post     = get_sub_field('module_post');
$show_header     = get_sub_field('show_post_header');
$show_full       = get_sub_field('show_full_content');
$has_module_post = $module_post instanceof WP_Post;

if ($has_module_post) {
    global $post;
    $post = $module_post;
    setup_postdata($post);
}

// Si no hay sub_field ni post global válido, salida temprana.
if (!$has_module_post && !get_post()) {
    return;
}

$render_header = !$has_module_post || $show_header;
$render_full   = !$has_module_post || $show_full;

$cats   = get_the_category();
$cat    = !empty($cats) ? $cats[0] : null;
$thumb  = get_the_post_thumbnail_url(get_the_ID(), 'large');
$author = get_the_author();
?>
<article class="post-modulo py-12 lg:py-16">
    <div class="container max-w-3xl">

        <?php if ($thumb): ?>
            <div
                class="post-modulo__featured w-full overflow-hidden rounded-xl bg-muted mb-8 lg:mb-10"
                style="aspect-ratio: 16/9;"
                data-gsap="fade-up"
            >
                <img
                    src="<?php echo esc_url($thumb); ?>"
                    alt="<?php echo esc_attr(get_the_title()); ?>"
                    class="w-full h-full object-cover"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        <?php endif; ?>

        <?php if ($render_header): ?>
            <header class="post-modulo__header mb-6 lg:mb-8" data-gsap="fade-up" data-gsap-delay="0.05">
                <?php if ($cat): ?>
                    <a
                        href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                        class="post-modulo__cat inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold uppercase tracking-wide mb-4 hover:bg-brand-100 transition-colors"
                    >
                        <?php echo esc_html($cat->name); ?>
                    </a>
                <?php endif; ?>

                <h1 class="post-modulo__titulo text-3xl lg:text-5xl font-display font-semibold text-foreground tracking-tight leading-tight">
                    <?php if ($has_module_post): ?>
                        <a class="hover:text-brand-600 transition-colors" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <?php else: ?>
                        <?php the_title(); ?>
                    <?php endif; ?>
                </h1>

                <p class="post-modulo__meta mt-4 text-sm text-muted-foreground flex flex-wrap items-center gap-x-3 gap-y-1">
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo esc_html(get_the_date()); ?>
                    </time>
                    <?php if ($author): ?>
                        <span aria-hidden="true">·</span>
                        <span><?php echo esc_html(sprintf(__('Por %s', 'boilerplate'), $author)); ?></span>
                    <?php endif; ?>
                </p>
            </header>
        <?php endif; ?>

        <div class="post-modulo__contenido prose prose-neutral max-w-none text-base lg:text-lg leading-relaxed text-foreground" data-gsap="fade-up" data-gsap-delay="0.10">
            <?php
            if ($render_full) {
                the_content();
            } else {
                the_excerpt();
            }
            ?>
        </div>

    </div>
</article>

<?php
if ($has_module_post) {
    wp_reset_postdata();
}

// Tipografía prose para el WYSIWYG. Print-once por request.
if (empty($GLOBALS['__bp_post_modulo_styled'])):
    $GLOBALS['__bp_post_modulo_styled'] = true;
?>
<style>
.post-modulo__contenido h1,
.post-modulo__contenido h2,
.post-modulo__contenido h3,
.post-modulo__contenido h4 {
    font-family: var(--font-display);
    font-weight: 600;
    letter-spacing: -0.02em;
    color: var(--color-foreground);
    line-height: 1.2;
}
.post-modulo__contenido h2 { font-size: clamp(1.5rem, 1.3rem + 1vw, 2rem);   margin: 2rem 0 1rem; }
.post-modulo__contenido h3 { font-size: clamp(1.25rem, 1.1rem + 0.6vw, 1.5rem); margin: 1.5rem 0 0.75rem; }
.post-modulo__contenido p  { margin: 0 0 1.25rem; }
.post-modulo__contenido p:last-child { margin-bottom: 0; }
.post-modulo__contenido a  { color: var(--color-brand-600); text-decoration: underline; text-underline-offset: 3px; }
.post-modulo__contenido a:hover { color: var(--color-brand-700); }
.post-modulo__contenido ul,
.post-modulo__contenido ol { margin: 0 0 1.25rem; padding-left: 1.5rem; }
.post-modulo__contenido li { margin: 0.25rem 0; }
.post-modulo__contenido strong { color: var(--color-foreground); font-weight: 600; }
.post-modulo__contenido blockquote {
    margin: 1.5rem 0;
    padding: 1rem 1.25rem;
    border-left: 3px solid var(--color-brand-500);
    background: var(--color-muted);
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    font-style: italic;
    color: var(--color-foreground);
}
.post-modulo__contenido img {
    border-radius: var(--radius-lg);
    margin: 1.5rem 0;
    max-width: 100%;
    height: auto;
}
.post-modulo__contenido code {
    background: var(--color-muted);
    padding: 0.125rem 0.375rem;
    border-radius: var(--radius-sm);
    font-size: 0.9em;
}
.post-modulo__contenido pre {
    background: var(--color-card-soft);
    padding: 1rem;
    border-radius: var(--radius-md);
    overflow-x: auto;
    margin: 1.5rem 0;
}
.post-modulo__contenido pre code {
    background: transparent;
    padding: 0;
}
</style>
<?php endif; ?>
