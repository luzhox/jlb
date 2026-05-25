<?php get_header('jlb'); ?>
<?php // El chrome JLB (header-jlb.php) ya abre <main id="contenido">. ?>
<div class="main-404">
	<div class="container main-404__content">
		<img src="<?php echo esc_url(get_theme_mod('brand_img')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
		<h1>La página que estás buscando no existe</h1>
		<a class="btn__primary" href="<?php echo esc_url(home_url('/')); ?>">Regresar a home</a>
	</div>
</div>
<?php get_footer('jlb'); ?>
