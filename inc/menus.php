<?php
/**
 * Register navigation menus for the theme.
 */
function menus()
{
  register_nav_menus(array(
    'menu_principal'  => __('Menu Principal', 'boilerplate'),
    'footer'          => __('Footer', 'boilerplate'),
    'redes'           => __('Redes Sociales', 'boilerplate'),
    'menu_secundario' => __('Menu Secundario', 'boilerplate'),
  ));
}
add_action('init', 'menus');
