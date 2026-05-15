<?php
/**
 * Molécula: Card de Post
 *
 * Uso dentro de un loop WordPress:
 *   while (have_posts()): the_post();
 *       get_template_part('template-parts/molecules/card');
 *   endwhile;
 *
 * Uso con datos externos:
 *   get_template_part('template-parts/molecules/card', null, [
 *       'title'     => 'Título',
 *       'url'       => 'https://...',
 *       'excerpt'   => 'Extracto...',
 *       'image_url' => 'https://...',
 *       'image_alt' => 'Alt',
 *       'date'      => '15 enero 2026',
 *       'category'  => 'Noticias',
 *       'cta_label' => 'Leer más',
 *       'variant'   => '',         // '' | featured | horizontal
 *   ]);
 */

$args = $args ?? [];

// Si estamos dentro de un loop WP, tomar datos del post global
$in_loop = !$args && have_posts() === false; // heurística básica

$title    = $args['title']     ?? get_the_title();
$url      = $args['url']       ?? get_permalink();
$excerpt  = $args['excerpt']   ?? get_the_excerpt();
$date     = $args['date']      ?? get_the_date();
$category = $args['category']  ?? '';
$cta      = $args['cta_label'] ?? 'Leer más';
$variant  = $args['variant']   ?? '';

// Imagen
$image_url = $args['image_url'] ?? get_the_post_thumbnail_url(null, 'medium_large');
$image_alt = $args['image_alt'] ?? esc_attr($title);

$card_class = 'card' . ($variant ? ' card--' . esc_attr($variant) : '');

if (!isset($args['category']) && function_exists('get_the_category')) {
    $cats = get_the_category();
    if ($cats) $category = $cats[0]->name;
}
?>
<article class="<?php echo esc_attr($card_class); ?>">

    <?php if ($image_url): ?>
        <div class="card__image">
            <img
                src="<?php echo esc_url($image_url); ?>"
                alt="<?php echo esc_attr($image_alt); ?>"
                loading="lazy"
            >
        </div>
    <?php endif; ?>

    <div class="card__body">

        <p class="card__meta">
            <?php if ($category): ?>
                <span><?php echo esc_html($category); ?></span>
            <?php endif; ?>
            <time><?php echo esc_html($date); ?></time>
        </p>

        <h2 class="card__title">
            <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
        </h2>

        <?php if ($excerpt): ?>
            <p class="card__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>

        <a href="<?php echo esc_url($url); ?>" class="card__cta">
            <?php echo esc_html($cta); ?>
        </a>

    </div>

    <?php // Link de cobertura accesible — cubre toda la tarjeta ?>
    <a href="<?php echo esc_url($url); ?>" class="card__link-overlay" aria-hidden="true" tabindex="-1"></a>

</article>
