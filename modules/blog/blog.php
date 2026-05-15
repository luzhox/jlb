<?php
/**
 * Módulo: Blog (listado de posts) — Fase 2 / shadcn × Kresna
 *
 * Cards hairline shadcn (border 1px, no shadow), toda la card clicable
 * mediante .stretched-link, aspect-ratio 16/10, badge categoría brand,
 * hover sutil con border-color shift + translate. Grid 1/2/3 responsive.
 *
 * Animación GSAP: data-gsap="fade-up" con stagger por orden DOM
 * (gsapDelay incremental). Respeta prefers-reduced-motion vía src/main.css.
 *
 * Sin migración ACF — refactor 100% visual + estructural.
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$cantidad  = get_sub_field('cantidad') ?: 3;
$categoria = get_sub_field('categoria');

$args = array(
    'post_type'      => 'post',
    'posts_per_page' => absint($cantidad),
    'orderby'        => 'date',
    'order'          => 'DESC',
    'no_found_rows'  => true,
);

if ($categoria) {
    $args['cat'] = absint($categoria);
}

$blog_query = new WP_Query($args);

if (!$blog_query->have_posts()) return;
?>
<section class="modulo-blog py-16 lg:py-24">
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php
            $i = 0;
            while ($blog_query->have_posts()):
                $blog_query->the_post();
                $delay = number_format($i * 0.08, 2, '.', '');
                $cats  = get_the_category();
                $cat   = !empty($cats) ? $cats[0] : null;
                ?>
                <article
                    class="modulo-blog__card group relative bg-card border border-border rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-card"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <?php if (has_post_thumbnail()): ?>
                        <div class="modulo-blog__card-img relative w-full overflow-hidden bg-muted" style="aspect-ratio:16/10;">
                            <?php the_post_thumbnail('medium_large', array(
                                'loading' => 'lazy',
                                'alt'     => esc_attr(get_the_title()),
                                'class'   => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]',
                            )); ?>
                        </div>
                    <?php endif; ?>

                    <div class="modulo-blog__card-body p-6">
                        <?php if ($cat): ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold uppercase tracking-wide mb-3">
                                <?php echo esc_html($cat->name); ?>
                            </span>
                        <?php endif; ?>

                        <h3 class="text-xl font-semibold text-foreground leading-snug mb-2 line-clamp-2">
                            <a
                                href="<?php the_permalink(); ?>"
                                class="modulo-blog__card-link before:content-[''] before:absolute before:inset-0 before:z-10"
                                aria-label="<?php echo esc_attr(get_the_title()); ?>"
                            >
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <p class="text-sm text-muted-foreground line-clamp-3 mb-4">
                            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 22, '…')); ?>
                        </p>

                        <p class="text-xs text-muted-foreground mt-auto">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date()); ?>
                            </time>
                        </p>
                    </div>
                </article>
                <?php
                $i++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
