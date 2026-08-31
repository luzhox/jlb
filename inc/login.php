<?php
/**
 * Login de wp-admin con el diseño JLB.
 *
 * Reemplaza el login del boilerplate (slideshow Vegas + jQuery, en /login/):
 * ahora es CSS puro (assets/admin/login-jlb.css) + DM Sans, sin JS.
 */

/** Estilos del login: DM Sans (Google Fonts) + hoja JLB. */
function jlb_login_styles() {
  wp_enqueue_style(
    'jlb-login-font',
    'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap',
    array(),
    null
  );
  wp_enqueue_style(
    'jlb-login',
    get_template_directory_uri() . '/assets/admin/login-jlb.css',
    array(),
    filemtime(get_template_directory() . '/assets/admin/login-jlb.css')
  );
}
add_action('login_enqueue_scripts', 'jlb_login_styles');

/**
 * Fotos para el fondo del login: heroes reales de niveles y talleres (ACF).
 * Se actualizan solas si el cliente cambia las portadas. Filtrable vía
 * `jlb_login_bg_images` (array de URLs). Si no hay fotos, queda el gradiente.
 */
function jlb_login_bg_images() {
  $urls = array();
  if (function_exists('get_field')) {
    $ids = get_posts(array(
      'post_type'   => array('nivel', 'taller'),
      'numberposts' => 8,
      'fields'      => 'ids',
      'orderby'     => 'ID',
      'order'       => 'ASC',
    ));
    foreach ($ids as $pid) {
      $img = get_field('hero_imagen', $pid);
      $att = is_array($img) ? ($img['ID'] ?? 0) : (int) $img;
      $url = $att ? wp_get_attachment_image_url($att, '2048x2048') : '';
      if ($url && !in_array($url, $urls, true)) {
        $urls[] = $url;
      }
      if (count($urls) >= 6) {
        break;
      }
    }
  }
  return apply_filters('jlb_login_bg_images', $urls);
}

/** Slides del fondo + crossfade (vanilla, respeta prefers-reduced-motion). */
function jlb_login_bg_markup() {
  $urls = jlb_login_bg_images();
  if (empty($urls)) {
    return;
  }
  echo '<div class="jlb-login-bg" aria-hidden="true">';
  foreach ($urls as $i => $url) {
    printf(
      '<div class="jlb-login-bg__slide%s" style="background-image:url(%s)"></div>',
      $i === 0 ? ' is-active' : '',
      esc_url($url)
    );
  }
  echo '</div>';
  ?>
  <script>
  (function () {
    var slides = document.querySelectorAll('.jlb-login-bg__slide');
    if (slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var i = 0;
    setInterval(function () {
      slides[i].classList.remove('is-active');
      i = (i + 1) % slides.length;
      slides[i].classList.add('is-active');
    }, 6000);
  })();
  </script>
  <?php
}
add_action('login_footer', 'jlb_login_bg_markup');

/** El logo enlaza al sitio, no a wordpress.org. */
function jlb_login_headerurl() {
  return home_url('/');
}
add_filter('login_headerurl', 'jlb_login_headerurl');

/** Texto accesible del logo. */
function jlb_login_headertext() {
  return get_bloginfo('name');
}
add_filter('login_headertext', 'jlb_login_headertext');
