<?php
/**
 * single.php — Layout para single post (shadcn × Kresna)
 *
 * Refactorizado al patrón shadcn × Kresna (Fase 3 follow-up):
 *   - <article> semántico con max-width prose (~720px) + sidebar
 *   - Header: badge categoría brand + h1 display + meta (autor + fecha)
 *   - Featured image arriba con aspect-ratio 16/9 + radius
 *   - Contenido WYSIWYG con prose styling consistente con módulo `texto`
 *   - Sidebar "Artículos recientes" con cards hairline (mismo patrón que blog)
 *   - GSAP fade-up reemplaza data-aos legacy
 *   - Schema.org Article microdata
 *   - Reduce-motion respetado vía main.css
 *
 * Compat: el módulo `modules/post/post.php` (Fase 3) cubre el caso de "post
 * dentro de Flexible Content". single.php usa markup propio para tener
 * control sobre el sidebar + grid layout, sin depender del módulo.
 */

get_header();
?>

<main id="contenido" class="bg-background">
    <?php while (have_posts()): the_post();
        $categories  = get_the_category();
        $primary_cat = !empty($categories) ? $categories[0] : null;
        $author      = get_the_author();
    ?>
        <article class="single-post pt-12 lg:pt-16 pb-16 lg:pb-24" itemscope itemtype="https://schema.org/Article">
            <div class="container mx-auto px-4 lg:px-6">

                <!-- Layout: contenido principal + sidebar -->
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,720px)_minmax(0,1fr)] gap-10 lg:gap-16 max-w-6xl mx-auto">

                    <!-- ── CONTENIDO PRINCIPAL ────────────────────────────────── -->
                    <div class="single-post__main min-w-0">
                        <header class="single-post__header mb-8 lg:mb-10" data-gsap="fade-up">
                            <?php if ($primary_cat): ?>
                                <a
                                    href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold uppercase tracking-wide mb-4 hover:bg-brand-100 transition-colors no-underline"
                                >
                                    <?php echo esc_html($primary_cat->name); ?>
                                </a>
                            <?php endif; ?>

                            <h1 class="single-post__title text-3xl md:text-4xl lg:text-5xl font-display font-semibold text-foreground tracking-tight leading-[1.1] mb-5" itemprop="headline">
                                <?php the_title(); ?>
                            </h1>

                            <div class="single-post__meta flex items-center flex-wrap gap-x-3 gap-y-1 text-sm text-muted-foreground">
                                <?php if ($author): ?>
                                    <span itemprop="author" itemscope itemtype="https://schema.org/Person">
                                        <?php esc_html_e('Por', 'boilerplate'); ?>
                                        <span class="font-medium text-foreground" itemprop="name"><?php echo esc_html($author); ?></span>
                                    </span>
                                    <span aria-hidden="true">·</span>
                                <?php endif; ?>

                                <time
                                    datetime="<?php echo esc_attr(get_the_date('c')); ?>"
                                    itemprop="datePublished"
                                >
                                    <?php echo esc_html(get_the_date()); ?>
                                </time>
                            </div>
                        </header>

                        <?php if (has_post_thumbnail()): ?>
                            <figure class="single-post__featured w-full overflow-hidden rounded-xl bg-muted mb-8 lg:mb-10" style="aspect-ratio:16/9;" data-gsap="fade-up" data-gsap-delay="0.1">
                                <?php the_post_thumbnail('large', array(
                                    'class'         => 'w-full h-full object-cover',
                                    'fetchpriority' => 'high',
                                    'decoding'      => 'async',
                                    'itemprop'      => 'image',
                                    'alt'           => esc_attr(get_the_title()),
                                )); ?>
                            </figure>
                        <?php endif; ?>

                        <div class="single-post__content single-prose" itemprop="articleBody" data-gsap="fade-up" data-gsap-delay="0.15">
                            <?php the_content(); ?>
                        </div>

                        <?php
                        // ── Compartir en redes sociales ───────────────────────
                        // URLs share oficiales de cada red. URL del post +
                        // título encoded para evitar problemas con &/áéíóú.
                        $share_url   = rawurlencode(get_permalink());
                        $share_title = rawurlencode(get_the_title());
                        $share_links = array(
                            'linkedin' => array(
                                'label' => __('LinkedIn', 'boilerplate'),
                                'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url,
                                'svg'   => '<path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z"/>',
                            ),
                            'x' => array(
                                'label' => __('X', 'boilerplate'),
                                'url'   => 'https://twitter.com/intent/tweet?text=' . $share_title . '&url=' . $share_url,
                                'svg'   => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117l11.966 15.644Z"/>',
                            ),
                            'facebook' => array(
                                'label' => __('Facebook', 'boilerplate'),
                                'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url,
                                'svg'   => '<path d="M9.198 21.5h4v-8.01h3.604l.396-3.98h-4V7.5a1 1 0 0 1 1-1h3v-4h-3a5 5 0 0 0-5 5v2.51h-2l-.396 3.98h2.396v8.01Z"/>',
                            ),
                        );
                        ?>
                        <div class="single-post__share mt-10 pt-6 border-t border-border" data-gsap="fade-up">
                            <p class="text-sm font-semibold text-foreground mb-3">
                                <?php esc_html_e('Compartir este artículo', 'boilerplate'); ?>
                            </p>
                            <ul class="flex flex-wrap gap-2 list-none m-0 p-0">
                                <?php foreach ($share_links as $key => $share): ?>
                                    <li>
                                        <a
                                            href="<?php echo esc_url($share['url']); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="share-btn share-btn--<?php echo esc_attr($key); ?> inline-flex items-center gap-2 px-4 py-2 rounded-md border border-border bg-card text-foreground text-sm font-medium hover:bg-foreground hover:text-background hover:border-foreground transition-colors no-underline"
                                            aria-label="<?php echo esc_attr(sprintf(__('Compartir en %s (abre en nueva pestaña)', 'boilerplate'), $share['label'])); ?>"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                                                <?php echo $share['svg']; // SVG path estático ?>
                                            </svg>
                                            <span><?php echo esc_html($share['label']); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>

                                <li>
                                    <button
                                        type="button"
                                        class="share-btn share-btn--copy js-share-copy inline-flex items-center gap-2 px-4 py-2 rounded-md border border-border bg-card text-foreground text-sm font-medium hover:bg-muted transition-colors"
                                        data-share-url="<?php echo esc_attr(get_permalink()); ?>"
                                        aria-label="<?php esc_attr_e('Copiar enlace al portapapeles', 'boilerplate'); ?>"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                        </svg>
                                        <span class="js-share-copy-label"><?php esc_html_e('Copiar enlace', 'boilerplate'); ?></span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <?php
                        $tags = get_the_tags();
                        if ($tags) : ?>
                            <footer class="single-post__tags mt-10 pt-6 border-t border-border flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-muted-foreground mr-2"><?php esc_html_e('Tags:', 'boilerplate'); ?></span>
                                <?php foreach ($tags as $tag) : ?>
                                    <a
                                        href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                        class="inline-flex items-center px-3 py-1 rounded-full bg-muted text-foreground text-xs font-medium hover:bg-muted/70 transition-colors no-underline"
                                    >
                                        #<?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </footer>
                        <?php endif; ?>
                    </div>

                    <!-- ── SIDEBAR: ARTÍCULOS RECIENTES ──────────────────────── -->
                    <aside class="single-post__aside lg:sticky lg:top-24 lg:self-start" aria-label="<?php esc_attr_e('Artículos recientes', 'boilerplate'); ?>">
                        <h2 class="text-sm font-semibold text-muted-foreground uppercase tracking-wide mb-5">
                            <?php esc_html_e('Artículos recientes', 'boilerplate'); ?>
                        </h2>
                        <?php
                        $recent = new WP_Query(array(
                            'post_type'      => 'post',
                            'posts_per_page' => 4,
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                            'post__not_in'   => array(get_the_ID()),
                            'no_found_rows'  => true,
                        ));
                        if ($recent->have_posts()) : ?>
                            <ul class="flex flex-col gap-4 list-none m-0 p-0">
                                <?php $i = 0; while ($recent->have_posts()) : $recent->the_post(); $delay = number_format($i * 0.06, 2, '.', ''); ?>
                                    <li>
                                        <article
                                            class="single-post__recent group relative flex gap-3 bg-card border border-border rounded-lg p-3 transition-colors duration-200 hover:border-foreground/20"
                                            data-gsap="fade-up"
                                            data-gsap-delay="<?php echo esc_attr($delay); ?>"
                                        >
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="flex-shrink-0 w-20 h-20 overflow-hidden rounded-md bg-muted" style="aspect-ratio:1/1;">
                                                    <?php the_post_thumbnail('thumbnail', array(
                                                        'class'   => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
                                                        'loading' => 'lazy',
                                                        'alt'     => esc_attr(get_the_title()),
                                                    )); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex flex-col justify-center min-w-0 flex-1">
                                                <h3 class="text-sm font-semibold text-foreground leading-snug line-clamp-2 mb-1">
                                                    <a
                                                        href="<?php the_permalink(); ?>"
                                                        class="single-post__recent-link before:content-[''] before:absolute before:inset-0 hover:text-brand-700 transition-colors no-underline"
                                                    >
                                                        <?php the_title(); ?>
                                                    </a>
                                                </h3>
                                                <time
                                                    datetime="<?php echo esc_attr(get_the_date('c')); ?>"
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    <?php echo esc_html(get_the_date()); ?>
                                                </time>
                                            </div>
                                        </article>
                                    </li>
                                <?php $i++; endwhile; ?>
                            </ul>
                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <p class="text-sm text-muted-foreground"><?php esc_html_e('Sin más artículos por ahora.', 'boilerplate'); ?></p>
                        <?php endif; ?>
                    </aside>

                </div>
            </div>
        </article>
    <?php endwhile; ?>
</main>

<?php
// Estilos prose para single post: spacing y tipografía consistentes con
// el módulo `texto`. Print-once vía flag global (mismo patrón que lucky-cube).
if (empty($GLOBALS['__bp_single_prose_styled'])):
    $GLOBALS['__bp_single_prose_styled'] = true;
?>
<style>
.single-prose { color: var(--color-foreground); font-family: var(--font-sans); font-size: 1.0625rem; line-height: 1.7; }
.single-prose > * + * { margin-top: 1.25rem; }
.single-prose h2 { font-family: var(--font-display); font-size: clamp(1.5rem, 1.2rem + 1.4vw, 2rem); font-weight: 600; line-height: 1.25; letter-spacing: -0.01em; margin-top: 2.25rem; margin-bottom: 0.75rem; color: var(--color-foreground); }
.single-prose h3 { font-family: var(--font-display); font-size: clamp(1.25rem, 1.05rem + 1vw, 1.5rem); font-weight: 600; line-height: 1.3; margin-top: 2rem; margin-bottom: 0.5rem; color: var(--color-foreground); }
.single-prose h4 { font-family: var(--font-display); font-size: 1.125rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.25rem; color: var(--color-foreground); }
.single-prose p { margin: 0; }
.single-prose a { color: var(--color-brand-700); text-decoration: underline; text-underline-offset: 3px; text-decoration-thickness: 1px; transition: color var(--duration-fast) var(--ease-out); }
.single-prose a:hover { color: var(--color-brand-600); text-decoration-thickness: 2px; }
.single-prose strong { font-weight: 600; color: var(--color-foreground); }
.single-prose em { font-style: italic; }
.single-prose ul, .single-prose ol { padding-left: 1.5rem; }
.single-prose ul { list-style: disc; }
.single-prose ol { list-style: decimal; }
.single-prose li { margin-bottom: 0.5rem; }
.single-prose blockquote {
    border-left: 3px solid var(--color-brand-500);
    background: var(--color-muted);
    padding: 1rem 1.25rem;
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    color: var(--color-foreground);
    font-style: italic;
    margin: 1.75rem 0;
}
.single-prose img { max-width: 100%; height: auto; border-radius: var(--radius-lg); margin: 1.5rem 0; }
.single-prose figure { margin: 1.75rem 0; }
.single-prose figure figcaption { font-size: 0.875rem; color: var(--color-muted-foreground); text-align: center; margin-top: 0.5rem; }
.single-prose code {
    background: var(--color-muted);
    color: var(--color-foreground);
    font-family: var(--font-mono);
    font-size: 0.875em;
    padding: 0.125rem 0.375rem;
    border-radius: var(--radius-xs);
}
.single-prose pre {
    background: var(--color-foreground);
    color: var(--color-background);
    padding: 1rem 1.25rem;
    border-radius: var(--radius-lg);
    overflow-x: auto;
    font-family: var(--font-mono);
    font-size: 0.875rem;
    line-height: 1.6;
}
.single-prose pre code { background: transparent; color: inherit; padding: 0; }
.single-prose hr { border: 0; border-top: 1px solid var(--color-border); margin: 2.5rem 0; }
</style>
<?php endif; ?>

<?php
// Botón "Copiar enlace": pequeño JS vanilla embebido (no merece crear un
// archivo aparte). Print-once vía flag global. Soporta navigator.clipboard
// (estándar moderno) con fallback a textarea + execCommand para browsers
// antiguos. Feedback visual cambiando el label a "✓ Copiado" 2s.
if (empty($GLOBALS['__bp_share_copy_js'])):
    $GLOBALS['__bp_share_copy_js'] = true;
?>
<script>
(function () {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-share-copy');
        if (!btn) return;
        const url = btn.dataset.shareUrl;
        if (!url) return;
        const label = btn.querySelector('.js-share-copy-label');
        const original = label ? label.textContent : '';
        const done = function () {
            if (label) label.textContent = '✓ Copiado';
            btn.setAttribute('aria-live', 'polite');
            setTimeout(function () { if (label) label.textContent = original; }, 2000);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(done).catch(function () { fallback(url, done); });
        } else {
            fallback(url, done);
        }
    });
    function fallback(text, done) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(ta);
    }
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
