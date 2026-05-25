<?php
/**
 * Molécula: jlb-post-card — Tarjeta de entrada para el grid del blog (Figma 4175:734).
 *
 * Imagen destacada + chip día/mes (KG) + label de categoría (píldora gradiente)
 * + título KG + extracto. Toda la tarjeta es clickeable (overlay accesible).
 * Se usa dentro de un loop WP: the_post() y luego get_template_part().
 */

$pid     = get_the_ID();
$url     = get_permalink();
$title   = get_the_title();
$excerpt = wp_trim_words(get_the_excerpt(), 26, '…');
$img     = get_the_post_thumbnail_url($pid, 'large');
$day     = get_the_date('d');
$month   = jlb_mes_abbr_es((int) get_the_time('U'));

$cats = get_the_category();
$cat  = !empty($cats) ? $cats[0]->name : '';
?>
<article class="jlb-pcard">
    <div class="jlb-pcard__image">
        <?php if ($img): ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async">
        <?php endif; ?>
        <span class="jlb-pcard__date" aria-hidden="true">
            <span class="jlb-pcard__date-day"><?php echo esc_html($day); ?></span>
            <span class="jlb-pcard__date-month"><?php echo esc_html($month); ?></span>
        </span>
        <?php if ($cat): ?>
            <span class="jlb-pcard__cat"><?php echo esc_html($cat); ?></span>
        <?php endif; ?>
    </div>
    <div class="jlb-pcard__body">
        <h2 class="jlb-pcard__title"><?php echo esc_html($title); ?></h2>
        <?php if ($excerpt): ?>
            <p class="jlb-pcard__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
    </div>
    <a class="jlb-pcard__link" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?>"></a>
</article>
