<?php get_header(); ?>
<?php // include('menu.php') retirado: header.php trae el masthead Kresna nativo. ?>
<?php while (have_posts()): the_post(); ?>
    <?php if (function_exists('get_field') && get_field('modules') !== null): ?>
        <?php the_modules_loop(); ?>
    <?php else: ?>
        <main class="container" style="padding:80px 0;">
            <article>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        </main>
    <?php endif; ?>
<?php endwhile; ?>
<?php get_footer(); ?>
