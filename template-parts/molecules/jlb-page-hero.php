<?php
/**
 * Molécula: jlb-page-hero — Banner secundario con gradiente de marca.
 *
 * Título KG centrado + subtítulo + wave blanco inferior. Usado por el listado
 * de blog (home.php) y reutilizable en cualquier archive/página.
 *
 * Uso:
 *   get_template_part('template-parts/molecules/jlb-page-hero', null, [
 *       'title'    => 'Blog JLB',
 *       'subtitle' => 'Descubre las últimas novedades y noticias',
 *   ]);
 */

$args     = isset($args) && is_array($args) ? $args : array();
$title    = $args['title'] ?? '';
$subtitle = $args['subtitle'] ?? '';

if ($title === '' && $subtitle === '') {
    return;
}
?>
<section class="jlb-page-hero" role="banner">
    <div class="jlb-page-hero__inner">
        <?php if ($title): ?>
            <h1 class="jlb-page-hero__title"><?php echo esc_html($title); ?></h1>
        <?php endif; ?>
        <?php if ($subtitle): ?>
            <p class="jlb-page-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
    </div>
    <svg class="jlb-page-hero__wave" viewBox="0 0 1440 64" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M0 64h1440V20.5c-180 28-420 28-720 0S180-7.5 0 20.5V64z" fill="#fff"/>
    </svg>
</section>
