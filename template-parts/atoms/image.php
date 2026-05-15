<?php
/**
 * Átomo: Image
 *
 * Uso:
 *   get_template_part('template-parts/atoms/image', null, [
 *       'image'    => get_sub_field('imagen'),  // array ACF o array manual
 *       'size'     => 'large',                  // tamaño WP (thumbnail|medium|large|full)
 *       'class'    => 'hero__img',
 *       'lazy'     => true,                     // loading="lazy" (false en LCP)
 *       'priority' => false,                    // fetchpriority="high" (true en LCP)
 *       'cover'    => false,                    // object-fit: cover inline
 *   ]);
 *
 * $args['image'] puede ser:
 *   - Array ACF: ['url'=>'...','alt'=>'...','width'=>800,'height'=>600]
 *   - Post thumbnail ID: pasar como ['id' => $post_id]
 *   - URL directa: ['url' => '...', 'alt' => '...']
 */

$args     = $args ?? [];
$image    = $args['image']    ?? null;
$size     = $args['size']     ?? 'large';
$class    = $args['class']    ?? '';
$lazy     = $args['lazy']     ?? true;
$priority = $args['priority'] ?? false;
$cover    = $args['cover']    ?? false;

// Resolver imagen desde post ID
if (isset($args['id']) && !$image) {
    $post_id = $args['id'];
    $image = [
        'url'    => get_the_post_thumbnail_url($post_id, $size),
        'alt'    => get_post_meta($post_id, '_wp_attachment_image_alt', true)
                    ?: get_the_title($post_id),
        'width'  => '',
        'height' => '',
    ];
}

if (empty($image) || empty($image['url'])) return;

$url    = $image['url']    ?? '';
$alt    = $image['alt']    ?? '';
$width  = $image['width']  ?? '';
$height = $image['height'] ?? '';

$loading  = $lazy && !$priority ? 'lazy' : 'eager';
$fetch    = $priority ? 'fetchpriority="high"' : '';
$style    = $cover ? 'style="width:100%;height:100%;object-fit:cover;"' : '';
?>
<img
    src="<?php echo esc_url($url); ?>"
    alt="<?php echo esc_attr($alt); ?>"
    <?php if ($width):  ?>width="<?php  echo esc_attr($width);  ?>"<?php endif; ?>
    <?php if ($height): ?>height="<?php echo esc_attr($height); ?>"<?php endif; ?>
    loading="<?php echo esc_attr($loading); ?>"
    <?php echo $fetch; ?>
    <?php if ($class): ?>class="<?php echo esc_attr($class); ?>"<?php endif; ?>
    <?php echo $style; ?>
>
