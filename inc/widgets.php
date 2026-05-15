<?php
/**
 * Register widget areas.
 */
function theme_widgets() {
  register_sidebar(array(
    'name'          => __('Footer · Información', 'boilerplate'),
    'id'            => 'location',
    'description'   => __('Bloque opcional para dirección, horarios u otra información institucional del footer.', 'boilerplate'),
    'before_widget' => '<div id="%1$s" class="site-footer__widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3 class="site-footer__title">',
    'after_title'   => '</h3>',
  ));

  register_sidebar(array(
    'name'          => __('Footer · Contenido adicional', 'boilerplate'),
    'id'            => 'newWidget',
    'description'   => __('Bloque opcional para textos, formularios cortos o enlaces adicionales.', 'boilerplate'),
    'before_widget' => '<div id="%1$s" class="site-footer__widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3 class="site-footer__title">',
    'after_title'   => '</h3>',
  ));
}

add_action('widgets_init', 'theme_widgets');
