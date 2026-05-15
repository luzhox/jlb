<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" href="<?php echo esc_url(site_icon_url()); ?>">
	<?php
	// Aplica el tema antes del paint para evitar flash (FOUC) entre light y dark.
	// Estados de localStorage('theme'):  'light' (default) | 'dark' | 'system'
	//
	// Política: dark mode es OPT-IN explícito. NO seguimos `prefers-color-scheme`
	// del sistema operativo a menos que el usuario haya elegido 'system' en el
	// toggle. Razón: un visitante con macOS/Windows en dark mode no espera que
	// un sitio marketing salga directamente en dark — puede ver el rediseño
	// "raro" si el body queda en su color default y los componentes en dark.
	?>
	<script>
	(function () {
		try {
			var t = localStorage.getItem('theme');
			var sysDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			var isDark = t === 'dark' || (t === 'system' && sysDark);
			if (isDark) document.documentElement.setAttribute('data-theme', 'dark');
		} catch (e) { /* noop */ }
	})();
	</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<a class="skip-link sr-text" href="#contenido"><?php esc_html_e('Skip to content', 'boilerplate'); ?></a>

	<div id="page">
		<?php
		// ─── Masthead Kresna nativo ───────────────────────────────────────────
		// Reemplaza al menu.php legacy (que ya no se incluye desde page.php /
		// index.php / 404.php / page-demo.php). Razón: el legacy era un header
		// negro full-bleed con BEM SASS que chocaba visualmente con el rediseño
		// shadcn × Kresna y dejaba el dark-toggle suelto encima del contenido.
		//
		// Clases legacy preservadas (porque algunos JS las leen):
		//   .site-header              ← scrollHeader.js (clase 'actived')
		//   #brand + data-brand[two]  ← scrollHeader.js cambia el logo en scroll
		//   .site-header-sandwich     ← menuMobile.js abre/cierra el drawer
		//   #site-navegation          ← typo legacy preservado por compat
		//
		// Estilos visuales (backdrop-blur, hairline, padding) vienen de Tailwind
		// utilities que ganan al SCSS legacy (envuelto en @layer legacy).
		?>
		<header
			id="masthead"
			class="site-header fixed inset-x-0 top-0 z-[100] backdrop-blur-md bg-[color:var(--color-background)]/85 border-b border-[color:var(--color-border)] transition-[background-color,box-shadow,padding] duration-200"
			role="banner"
		>
			<div class="site-header__inner container mx-auto flex items-center justify-between gap-4 px-4 lg:px-6 py-3">

				<!-- Marca -->
				<a class="site-header-brand__item flex items-center" href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
					<?php
					$brand_main = get_theme_mod('brand_img');
					$brand_alt  = get_theme_mod('brand_img-revert');
					if ($brand_main):
					?>
						<img
							id="brand"
							data-brand="<?php echo esc_url($brand_main); ?>"
							data-brandtwo="<?php echo esc_url($brand_alt ?: $brand_main); ?>"
							src="<?php echo esc_url($brand_main); ?>"
							alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
							width="160"
							height="40"
							class="block h-9 lg:h-10 w-auto"
						>
					<?php else: ?>
						<span class="font-display text-lg font-semibold text-foreground tracking-tight">
							<?php bloginfo('name'); ?>
						</span>
					<?php endif; ?>
				</a>

				<!-- Sandwich mobile (solo <lg) — controla #site-navegation-mobile -->
				<button
					class="site-header-sandwich lg:hidden inline-flex flex-col items-end justify-center gap-1.5 w-10 h-10 -mr-2 text-foreground"
					type="button"
					aria-label="<?php esc_attr_e('Abrir menú de navegación', 'boilerplate'); ?>"
					aria-expanded="false"
					aria-controls="site-navegation-mobile"
				>
					<span class="menu menu-1 block w-5 h-0.5 bg-current transition-transform" aria-hidden="true"></span>
					<span class="menu menu-2 block w-4 h-0.5 bg-current transition-opacity" aria-hidden="true"></span>
					<span class="menu menu-3 block w-5 h-0.5 bg-current transition-transform" aria-hidden="true"></span>
				</button>

				<!-- Nav + dark toggle (lg+) -->
				<div class="site-header-nav hidden lg:flex items-center gap-6 lg:gap-8">
					<?php if (has_nav_menu('menu_principal')): ?>
						<nav
							id="site-navegation"
							class="main-navegation"
							role="navigation"
							aria-label="<?php esc_attr_e('Menú principal', 'boilerplate'); ?>"
						>
							<?php
							wp_nav_menu(array(
								'theme_location' => 'menu_principal',
								'container'      => false,
								'menu_class'     => 'flex items-center gap-6 list-none m-0 p-0 text-sm font-medium',
								'fallback_cb'    => false,
								'depth'          => 2,
							));
							?>
						</nav>
					<?php endif; ?>

					<?php
					// Toggle dark integrado dentro del masthead (en lugar de
					// flotando suelto en .site-utilities como en Phase 1).
					get_template_part('template-parts/atoms/dark-toggle');
					?>
				</div>

			</div>

			<?php
			// Filter inline: clases utility a cada <a> del mobile menu.
			// Se identifica por menu_id='mobile-menu' (set abajo en wp_nav_menu).
			// El filter se queda registrado para todo el request — efecto solo
			// dentro del wp_nav_menu con ese id, así no contamina el menú desktop.
			add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
				if (isset($args->menu_id) && $args->menu_id === 'mobile-menu') {
					$base = 'block w-full px-5 py-4 text-base font-semibold text-foreground hover:bg-muted active:bg-muted transition-colors';
					$atts['class'] = isset($atts['class'])
						? $atts['class'] . ' ' . $base
						: $base;
				}
				return $atts;
			}, 10, 3);
			?>

			<!-- Drawer mobile: position absolute (no afecta height del header fixed).
			     Se despliega como panel full-width debajo del masthead. -->
			<div
				id="site-navegation-mobile"
				class="site-header-mobile-drawer hidden lg:hidden absolute top-full inset-x-0 bg-card border-b border-border shadow-lg max-h-[calc(100vh-4rem)] overflow-y-auto"
				role="dialog"
				aria-label="<?php esc_attr_e('Menú principal', 'boilerplate'); ?>"
			>
				<?php if (has_nav_menu('menu_principal')): ?>
					<nav aria-label="<?php esc_attr_e('Navegación móvil', 'boilerplate'); ?>">
						<?php
						wp_nav_menu(array(
							'theme_location' => 'menu_principal',
							'container'      => false,
							'menu_id'        => 'mobile-menu',
							'menu_class'     => 'flex flex-col list-none m-0 p-0 divide-y divide-border',
							'fallback_cb'    => false,
							'depth'          => 1,
						));
						?>
					</nav>
				<?php endif; ?>

				<div class="flex items-center justify-between gap-3 px-5 py-4 border-t border-border bg-muted/30">
					<span class="text-sm font-medium text-muted-foreground">
						<?php esc_html_e('Tema', 'boilerplate'); ?>
					</span>
					<?php get_template_part('template-parts/atoms/dark-toggle'); ?>
				</div>
			</div>
		</header>

		<?php
		// Overlay backdrop del drawer mobile.
		// - Reactivo a body.drawer-open (que añade/quita src/menuMobile.js).
		// - Solo visible en <lg (en desktop el drawer mismo no existe).
		// - z-index 90: debajo del masthead (z-100) para que el header siga
		//   visible y clickable, encima del contenido para oscurecerlo.
		// - Click captura por menuMobile.js (listener click fuera del header)
		//   y cierra el drawer.
		?>
		<div class="site-overlay" aria-hidden="true"></div>

		<?php
		// CSS específico del drawer mobile + overlay.
		// Patrón inline (mismo que lucky-cube/footer-watermark) con flag global
		// para imprimirlo UNA sola vez por request.
		if (empty($GLOBALS['__bp_mobile_drawer_styled'])):
			$GLOBALS['__bp_mobile_drawer_styled'] = true;
		?>
		<style>
		/* ── Items activos del menú mobile (página actual) ───────────────── */
		.site-header-mobile-drawer .current-menu-item > a,
		.site-header-mobile-drawer .current_page_item > a {
			color: var(--color-brand-600);
			background-color: var(--color-brand-50);
		}
		.site-header-mobile-drawer a:focus-visible {
			outline: 2px solid var(--color-ring);
			outline-offset: -2px;
		}
		.site-header-mobile-drawer:not(.hidden) {
			animation: drawer-fade-in 180ms ease-out;
		}
		@keyframes drawer-fade-in {
			from { opacity: 0; transform: translateY(-4px); }
			to   { opacity: 1; transform: translateY(0); }
		}

		/* ── Overlay backdrop ────────────────────────────────────────────── */
		.site-overlay {
			position: fixed;
			inset: 0;
			z-index: 90; /* < masthead 100, > contenido */
			background-color: rgb(0 0 0 / 0.5);
			opacity: 0;
			pointer-events: none;
			transition: opacity 200ms ease-out;
		}
		body.drawer-open .site-overlay {
			opacity: 1;
			pointer-events: auto;
		}
		body.drawer-open { overflow: hidden; }

		@media (min-width: 1024px) {
			.site-overlay { display: none !important; }
		}
		@media (prefers-reduced-motion: reduce) {
			.site-header-mobile-drawer:not(.hidden) { animation: none; }
			.site-overlay { transition: none; }
		}
		</style>
		<?php endif; ?>

		<div id="contenido">
