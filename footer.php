</div>
<?php
$footer_logo    = get_theme_mod('brand_img');
$description    = bp_footer_get('footer_description');
$contact_title  = bp_footer_get('footer_contact_title');
$address        = bp_footer_get('footer_address');
$phone          = bp_footer_get('footer_phone');
$email          = bp_footer_get('footer_email');
$copyright      = bp_footer_get('footer_copyright');
$has_contact    = $address || $phone || $email;
$has_widgets    = is_active_sidebar('location') || is_active_sidebar('newWidget');
?>
<footer class="site-footer" role="contentinfo" aria-labelledby="site-footer-heading">
  <div class="site-footer__inner">
    <h2 id="site-footer-heading" class="sr-text"><?php esc_html_e('Footer', 'boilerplate'); ?></h2>

    <div class="site-footer__main">
      <section class="site-footer__brand" aria-label="<?php echo esc_attr__('Resumen del sitio', 'boilerplate'); ?>">
        <?php if ($footer_logo): ?>
          <a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
            <img
              src="<?php echo esc_url($footer_logo); ?>"
              alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
              width="180"
              height="56"
              loading="lazy"
              decoding="async"
            >
          </a>
        <?php else: ?>
          <a class="site-footer__site-name" href="<?php echo esc_url(home_url('/')); ?>">
            <?php echo esc_html(get_bloginfo('name')); ?>
          </a>
        <?php endif; ?>

        <?php if ($description): ?>
          <p class="site-footer__description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>

        <?php
        // Redes sociales del footer.
        // Fuente: menú WP asignado a 'redes' (Apariencia → Menús → asignar
        // a la posición "Redes sociales"). Cada item del menú se renderiza
        // con el atom social-icon que detecta automáticamente la red por la
        // URL (facebook.com → facebook, instagram.com → instagram, etc.).
        // Networks soportados: discord, x/twitter, linkedin, github,
        // facebook, instagram, youtube, tiktok, whatsapp.
        //
        // Si no hay menú asignado, fallback a las URLs del Customizer
        // (kresna_social_*_url, con defaults a homepages oficiales).
        $social_links = array();
        if (has_nav_menu('redes')) {
            $locations = get_nav_menu_locations();
            $menu_obj  = isset($locations['redes']) ? wp_get_nav_menu_object($locations['redes']) : false;
            if ($menu_obj) {
                $items = wp_get_nav_menu_items($menu_obj->term_id);
                if ($items) {
                    foreach ($items as $item) {
                        $social_links[] = $item->url;
                    }
                }
            }
        }
        if (empty($social_links)) {
            $social_links = array_filter(array(
                bp_kresna_get('kresna_social_discord_url'),
                bp_kresna_get('kresna_social_x_url'),
                bp_kresna_get('kresna_social_linkedin_url'),
                bp_kresna_get('kresna_social_github_url'),
            ), static function ($url) { return trim((string) $url) !== ''; });
        }
        if ($social_links): ?>
          <nav class="site-footer__socials" aria-label="<?php echo esc_attr__('Redes sociales', 'boilerplate'); ?>">
            <ul class="site-footer__social-list" role="list">
              <?php foreach ($social_links as $url): ?>
                <li>
                  <?php get_template_part('template-parts/atoms/social-icon', null, array(
                    'network' => 'auto',
                    'url'     => $url,
                  )); ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        <?php endif; ?>

        <?php
        // CSS específico para los social icons EN el footer tradicional.
        // Sobrescribe el styling del atom (pensado para fondo oscuro Kresna)
        // con tokens shadcn neutros que funcionan sobre cualquier fondo y
        // respetan light/dark mode. Flag global → solo se imprime una vez.
        if (!isset($GLOBALS['__bp_footer_social_styled'])):
            $GLOBALS['__bp_footer_social_styled'] = true;
        ?>
        <style>
        .site-footer__social-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            list-style: none;
            margin: 16px 0 0;
            padding: 0;
        }
        /* Override del atom: en footer tradicional, fondo neutro hairline.
           Reset CRÍTICO de padding/min-width: src/main.css trae una regla
           .site-footer__social-list a { padding: 0 1rem; min-height: 36px }
           pensada para items con texto del nav legacy. Sin reset, los 40px
           del círculo quedan tragados por padding 32px lateral → SVG se ve
           minúsculo (~8px) dentro del círculo. */
        .site-footer__social-list .social-icon {
            width: 40px;
            height: 40px;
            min-height: 0;
            min-width: 0;
            padding: 0;
            background: var(--color-card);
            color: var(--color-foreground);
            border: 1px solid var(--color-border);
            font-size: 0; /* anula el text-xs heredado para que no afecte al SVG */
        }
        .site-footer__social-list .social-icon svg {
            width: 50%;
            height: 50%;
            display: block;
        }
        .site-footer__social-list .social-icon:hover {
            background: var(--color-foreground);
            color: var(--color-background);
            border-color: var(--color-foreground);
            text-decoration: none; /* anula el underline heredado del .site-footer a:hover */
        }
        .site-footer__social-list .social-icon:focus-visible {
            outline: 2px solid var(--color-ring);
            outline-offset: 2px;
            text-decoration: none;
        }
        </style>
        <?php endif; ?>
      </section>

      <?php if (has_nav_menu('footer')): ?>
        <nav class="site-footer__nav" aria-label="<?php echo esc_attr__('Enlaces del footer', 'boilerplate'); ?>">
          <h3 class="site-footer__title"><?php esc_html_e('Enlaces', 'boilerplate'); ?></h3>
          <?php
          wp_nav_menu(array(
            'theme_location' => 'footer',
            'container'      => false,
            'menu_class'     => 'site-footer__menu',
            'depth'          => 1,
            'fallback_cb'    => false,
          ));
          ?>
        </nav>
      <?php endif; ?>

      <?php if (has_nav_menu('menu_secundario')): ?>
        <nav class="site-footer__nav" aria-label="<?php echo esc_attr__('Enlaces secundarios', 'boilerplate'); ?>">
          <h3 class="site-footer__title"><?php esc_html_e('Información', 'boilerplate'); ?></h3>
          <?php
          wp_nav_menu(array(
            'theme_location' => 'menu_secundario',
            'container'      => false,
            'menu_class'     => 'site-footer__menu',
            'depth'          => 1,
            'fallback_cb'    => false,
          ));
          ?>
        </nav>
      <?php endif; ?>

      <?php if ($has_contact): ?>
        <address class="site-footer__contact">
          <?php if ($contact_title): ?>
            <h3 class="site-footer__title"><?php echo esc_html($contact_title); ?></h3>
          <?php endif; ?>

          <?php if ($address): ?>
            <p><?php echo nl2br(esc_html($address)); ?></p>
          <?php endif; ?>

          <?php if ($phone): ?>
            <p>
              <a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9+]/', '', $phone)); ?>">
                <?php echo esc_html($phone); ?>
              </a>
            </p>
          <?php endif; ?>

          <?php if ($email): ?>
            <p>
              <a href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>">
                <?php echo esc_html(antispambot($email)); ?>
              </a>
            </p>
          <?php endif; ?>
        </address>
      <?php endif; ?>

      <?php if ($has_widgets): ?>
        <aside class="site-footer__widgets" aria-label="<?php echo esc_attr__('Contenido adicional del footer', 'boilerplate'); ?>">
          <?php if (is_active_sidebar('location')): ?>
            <?php dynamic_sidebar('location'); ?>
          <?php endif; ?>

          <?php if (is_active_sidebar('newWidget')): ?>
            <?php dynamic_sidebar('newWidget'); ?>
          <?php endif; ?>
        </aside>
      <?php endif; ?>
    </div>

    <div class="site-footer__bottom">
      <?php if ($copyright): ?>
        <p><?php echo esc_html($copyright); ?></p>
      <?php endif; ?>
    </div>
  </div>
</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
