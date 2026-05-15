
	<header id="masthead" class="site-header" role="banner">
			<div class="container">
				<div class="site-header-brand">
					<a class="site-header-brand__item" href="<?php echo esc_url(home_url('/')); ?>">
						<img
							id="brand"
							data-brand="<?php echo esc_url(get_theme_mod('brand_img')); ?>"
							data-brandtwo="<?php echo esc_url(get_theme_mod('brand_img-revert')); ?>"
							src="<?php echo esc_url(get_theme_mod('brand_img')); ?>"
							alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
							width="160"
							height="50"
						>
					</a>
				</div>
				<button
					class="site-header-sandwich"
					type="button"
					aria-label="<?php esc_attr_e('Abrir menú de navegación', 'boilerplate'); ?>"
					aria-expanded="false"
					aria-controls="site-navegation"
				>
					<span class="menu menu-1" aria-hidden="true"></span>
					<span class="menu menu-2" aria-hidden="true"></span>
					<span class="menu menu-3" aria-hidden="true"></span>
				</button>
				<div class="site-header-nav">
					<nav id="site-navegation" class="main-navegation" role="navigation">
						<?php wp_nav_menu(array('theme_location' => 'menu_principal')); ?>
					</nav>
				</div>
			</div>
		</header>
