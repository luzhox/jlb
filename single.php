<?php
/**
 * single.php — Artículo del blog JLB (Figma 4177:961).
 *
 * Tag de categoría + título KG + fecha (icono calendario) + imagen destacada +
 * línea de autor ("Palabras de:") + divisor + contenido (the_content, estilado
 * como .jlb-article__content) + "Ver más artículos relacionados" (2 tarjetas).
 * El cuerpo (párrafos, imágenes, video) es el contenido Gutenberg del post.
 * Header/footer vienen del chrome JLB.
 */
get_header('jlb');

$cal_icon = '<svg class="jlb-article__cal" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M3 9h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
$quote_icon = '<svg class="jlb-article__quote" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 7H5a2 2 0 0 0-2 2v6h6V9H6.5L10 7zm11 0h-5a2 2 0 0 0-2 2v6h6V9h-2.5L21 7z"/></svg>';
$arrow_circle = '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true"><circle cx="16" cy="16" r="16" fill="url(#jlb-rc-grad)"/><path d="M13 11l5 5-5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><defs><linearGradient id="jlb-rc-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse"><stop stop-color="#614794"/><stop offset="0.57" stop-color="#993356"/><stop offset="1" stop-color="#c92323"/></linearGradient></defs></svg>';
$check_icon = '<svg class="jlb-rcard__tag-ico" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3.5 8.5l3 3 6-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$deco_url = get_template_directory_uri() . '/assets/figma-home/blog/scribble.svg';

while (have_posts()): the_post();
    $cats        = get_the_category();
    $primary_cat = !empty($cats) ? $cats[0] : null;
    $feat        = get_the_post_thumbnail_url(get_the_ID(), 'large');

    // Autor: meta editable (_jlb_autor / _jlb_autor_rol) con fallback al autor WP.
    $author_name = get_post_meta(get_the_ID(), '_jlb_autor', true) ?: get_the_author();
    $author_role = get_post_meta(get_the_ID(), '_jlb_autor_rol', true);
    $author_line = trim($author_name . ($author_role ? ', ' . $author_role : ''));
?>
    <article class="jlb-article" itemscope itemtype="https://schema.org/Article">
        <div class="jlb-article__container">
            <?php if ($primary_cat): ?>
                <a class="jlb-tag" href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>"><?php echo esc_html($primary_cat->name); ?></a>
            <?php endif; ?>

            <h1 class="jlb-article__title" itemprop="headline"><?php the_title(); ?></h1>

            <p class="jlb-article__date">
                <?php echo $cal_icon; ?>
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished"><?php echo esc_html(jlb_fecha_larga((int) get_the_time('U'))); ?></time>
            </p>

            <?php if ($feat): ?>
                <figure class="jlb-article__featured">
                    <img class="jlb-article__cover" src="<?php echo esc_url($feat); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" fetchpriority="high" decoding="async" itemprop="image">
                    <img class="jlb-article__deco" src="<?php echo esc_url($deco_url); ?>" alt="" aria-hidden="true" width="100" height="100">
                </figure>
            <?php endif; ?>

            <?php if ($author_line): ?>
                <p class="jlb-article__author">
                    <?php echo $quote_icon; ?>
                    <span><strong><?php esc_html_e('Palabras de:', 'boilerplate'); ?></strong> <?php echo esc_html($author_line); ?></span>
                </p>
            <?php endif; ?>

            <hr class="jlb-article__divider">

            <div class="jlb-article__content" itemprop="articleBody">
                <?php the_content(); ?>
            </div>
        </div>

        <?php
        // ── Artículos relacionados: misma categoría primero; si hay <2, se
        //    completa con las entradas más recientes (excluyendo la actual). ──
        $current_id = get_the_ID();
        $rel_ids    = array();
        if ($primary_cat) {
            $rel_ids = get_posts(array(
                'post_type' => 'post', 'posts_per_page' => 2, 'fields' => 'ids',
                'post__not_in' => array($current_id), 'category__in' => array($primary_cat->term_id),
                'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true,
            ));
        }
        if (count($rel_ids) < 2) {
            $fill = get_posts(array(
                'post_type' => 'post', 'posts_per_page' => 2 - count($rel_ids), 'fields' => 'ids',
                'post__not_in' => array_merge(array($current_id), $rel_ids),
                'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true,
            ));
            $rel_ids = array_merge($rel_ids, $fill);
        }
        $related = new WP_Query(array(
            'post_type' => 'post', 'post__in' => $rel_ids ?: array(0),
            'orderby' => 'post__in', 'posts_per_page' => 2, 'ignore_sticky_posts' => true,
        ));
        if ($related->have_posts()):
        ?>
            <section class="jlb-related" aria-label="<?php echo esc_attr__('Artículos relacionados', 'boilerplate'); ?>">
                <div class="jlb-related__container">
                    <h2 class="jlb-related__title"><?php esc_html_e('Ver más artículos relacionados', 'boilerplate'); ?></h2>
                    <div class="jlb-related__grid">
                        <?php while ($related->have_posts()): $related->the_post();
                            $r_cats = get_the_category();
                            $r_cat  = !empty($r_cats) ? $r_cats[0]->name : '';
                            $r_img  = get_the_post_thumbnail_url(get_the_ID(), 'large');
                        ?>
                            <a class="jlb-rcard" href="<?php echo esc_url(get_permalink()); ?>">
                                <span class="jlb-rcard__image">
                                    <?php if ($r_img): ?>
                                        <img src="<?php echo esc_url($r_img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                                    <?php endif; ?>
                                    <?php if ($r_cat): ?>
                                        <span class="jlb-rcard__tag"><?php echo $check_icon; ?><?php echo esc_html($r_cat); ?></span>
                                    <?php endif; ?>
                                    <span class="jlb-rcard__arrow"><?php echo $arrow_circle; ?></span>
                                </span>
                                <span class="jlb-rcard__title"><?php echo esc_html(get_the_title()); ?></span>
                                <span class="jlb-rcard__cta"><?php esc_html_e('Leer artículo', 'boilerplate'); ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
        <?php
        endif;
        wp_reset_postdata();
        ?>
    </article>
<?php
endwhile;

get_footer('jlb');
