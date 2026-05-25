<?php
/**
 * home.php — Índice del blog JLB (Figma 4175:533).
 *
 * Banner "Blog JLB" + grid de 3 columnas de tarjetas de entrada + paginación.
 * Se usa cuando hay una página estática asignada como "Página de entradas".
 * Header/footer vienen del chrome JLB (get_header/get_footer 'jlb').
 */
get_header('jlb');

get_template_part('template-parts/molecules/jlb-page-hero', null, array(
    'title'    => 'Blog JLB',
    'subtitle' => __('Descubre las últimas novedades y noticias', 'boilerplate'),
));
?>

<section class="jlb-blog" aria-label="<?php echo esc_attr__('Entradas del blog', 'boilerplate'); ?>">
    <div class="jlb-blog__container">
        <?php if (have_posts()): ?>
            <div class="jlb-blog__grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/molecules/jlb-post-card'); ?>
                <?php endwhile; ?>
            </div>

            <?php
            the_posts_pagination(array(
                'mid_size'           => 1,
                'prev_text'          => __('Anteriores', 'boilerplate'),
                'next_text'          => __('Siguientes', 'boilerplate'),
                'screen_reader_text' => __('Navegación de entradas', 'boilerplate'),
                'class'              => 'jlb-blog__pagination',
            ));
            ?>
        <?php else: ?>
            <p class="jlb-blog__empty"><?php esc_html_e('Aún no hay entradas publicadas.', 'boilerplate'); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer('jlb'); ?>
