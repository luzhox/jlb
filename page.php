<?php get_header('jlb'); ?>
<?php // El chrome JLB (header-jlb.php) ya abre <main id="contenido">. ?>
<?php while (have_posts()): the_post(); ?>
    <?php if (function_exists('get_field') && get_field('modules') !== null): ?>
        <?php the_modules_loop(); ?>
    <?php else: ?>
        <div class="container" style="padding:80px 0;">
            <article>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        </div>
    <?php endif; ?>
<?php endwhile; ?>
<?php get_footer('jlb'); ?>
