<?php get_header('jlb'); ?>
<?php // El chrome JLB (header-jlb.php) ya abre <main id="contenido">. ?>
		<?php $args = array(
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		); ?>
		<?php $family = new WP_Query($args); ?>
		<?php $url = wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>
		<?php while ($family->have_posts()): $family->the_post(); ?>
		<div class="articulo-principal">
			<?php if ($url): ?>
				<img
					class="articulo-principal__bg"
					src="<?php echo esc_url($url); ?>"
					alt="<?php echo esc_attr(get_the_title()); ?>"
					fetchpriority="high"
				>
			<?php endif; ?>
			<div class="overlay"></div>
			<div class="container">
				<div class="texto" data-aos="fade-right">
					<h1><?php the_title(); ?></h1>
					<p class="hide-on-small-only"><?php excerpt('25'); ?></p>
					<a href="<?php the_permalink(); ?>">Leer artículo <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/images/arrow-pointing-to-right.png" alt=""></a>
				</div>
			</div>
		</div>
		<?php endwhile; wp_reset_postdata(); ?>

	<section id="articulos">
		<div class="container">
			<div class="contenedor-articulos">
				<?php while (have_posts()): the_post(); ?>
					<?php
					global $post;
					$thumbID      = get_post_thumbnail_id($post->ID);
					$imgDestacada = wp_get_attachment_url($thumbID);
					?>
					<div class="articles row">
						<a href="<?php the_permalink(); ?>" style="position:absolute; display:block; width:100%; height:100%;"></a>
						<div class="imagen-article">
							<?php if ($imgDestacada): ?>
								<img
									class="imagen-article__bg"
									src="<?php echo esc_url($imgDestacada); ?>"
									alt="<?php echo esc_attr(get_the_title()); ?>"
									loading="lazy"
								>
							<?php endif; ?>
						</div>
						<div class="textos">
							<div class="detalles">
								<div class="date"><span><?php the_category(); ?> </span><?php echo get_the_date(); ?></div>
								<span class="icon-profile"></span>
							</div>
							<h2><?php the_title(); ?></h2>
							<p><?php excerpt('15'); ?></p>
							<a href="<?php the_permalink(); ?>" class="btn-seemore">Leer Más</a>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
<?php get_footer('jlb'); ?>
