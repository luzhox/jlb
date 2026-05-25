<?php
/**
 * Módulo: jlb_galeria — Dúo de imágenes (Figma: 744×418 + 372×418).
 *
 * Datos (dual-mode flex/$args):
 *   imagen_ancha    array{url,alt}
 *   imagen_angosta  array{url,alt}
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$ancha   = $get('imagen_ancha', null);
$angosta = $get('imagen_angosta', null);

if (empty($ancha['url']) && empty($angosta['url'])) {
    return;
}

// Grupo para el lightbox: permite navegar prev/next entre las imágenes (src/jlbImageLightbox.js).
$group = wp_unique_id('jlb-galeria-');

// Trigger de zoom: <a href> = progressive enhancement (sin JS abre la imagen).
$zoom = function ($img) use ($group) {
    if (empty($img['url'])) {
        return;
    }
    $alt  = $img['alt'] ?? '';
    $full = !empty($img['sizes']['large']) ? $img['sizes']['large'] : $img['url']; // miniatura visible
    ?>
    <a class="jlb-galeria__zoom"
        href="<?php echo esc_url($img['url']); ?>"
        data-jlb-zoom="<?php echo esc_url($img['url']); ?>"
        data-jlb-zoom-group="<?php echo esc_attr($group); ?>"
        data-jlb-zoom-alt="<?php echo esc_attr($alt); ?>"
        aria-label="<?php echo esc_attr(sprintf(__('Ampliar imagen: %s', 'boilerplate'), $alt ?: __('galería', 'boilerplate'))); ?>">
        <img src="<?php echo esc_url($full); ?>"
            alt="<?php echo esc_attr($alt); ?>"
            loading="lazy" decoding="async">
        <span class="jlb-galeria__zoom-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M16.5 16.5L21 21M11 8v6M8 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </span>
    </a>
    <?php
};
?>
<section class="jlb-galeria" aria-label="<?php echo esc_attr__('Galería', 'boilerplate'); ?>">
    <div class="jlb-container jlb-galeria__grid">
        <?php if (!empty($ancha['url'])): ?>
            <figure class="jlb-galeria__item jlb-galeria__item--wide" data-gsap="fade-up">
                <?php $zoom($ancha); ?>
            </figure>
        <?php endif; ?>
        <?php if (!empty($angosta['url'])): ?>
            <figure class="jlb-galeria__item jlb-galeria__item--narrow" data-gsap="fade-up" data-gsap-delay="0.12">
                <?php $zoom($angosta); ?>
            </figure>
        <?php endif; ?>
    </div>
</section>
