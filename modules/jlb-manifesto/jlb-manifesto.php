<?php
/**
 * Módulo: jlb_manifesto — Bloque manifesto de un solo párrafo.
 *
 * Datos:
 *   anchor  string (opcional, para anclajes desde nav)
 *   texto   WYSIWYG (admite <strong> para palabras destacadas con gradient)
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$anchor = $get('anchor', 'colegio');
$texto  = $get('texto');

if (!$texto) {
    return;
}
?>
<section class="jlb-manifesto"
    <?php echo $anchor ? 'id="' . esc_attr($anchor) . '"' : ''; ?>
    aria-label="<?php esc_attr_e('Propuesta del colegio', 'boilerplate'); ?>">
    <div class="jlb-container" data-gsap="fade-up" data-gsap-duration="1.1">
        <?php echo wp_kses_post($texto); ?>
    </div>
</section>
