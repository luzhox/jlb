<?php

function my_customize_register($wp_customize)
{

  $wp_customize->add_panel('brand', array(
    'title' => __('Brand', 'textdomain'),
    'priority' => 50,
    'capability' => 'edit_theme_options',
  ));

  // Section para Google Analytics
  $wp_customize->add_section('brand_section', array(
    'title' => __('Logo de Menu', 'textdomain'),
    'panel' => 'brand',
    'priority' => 1,
    'capability' => 'edit_theme_options',
  ));

  //Google Analytics
  $wp_customize->add_setting('brand_img', array(
    'default' => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw'
  ));

  $wp_customize->add_control(new WP_Customize_Image_Control(
    $wp_customize,
    'brand_img',
    array(
      'label' => __('Logo principal del menu'),
      'description' => esc_html__('Aca se pondrá el logo principal del menu de navegación'),
      'section' => 'brand_section',
      'button_labels' => array( // Optional.
        'select' => __('Elige una imagen'),
        'change' => __('Cambiar imagen'),
        'remove' => __('Borrar'),
        'default' => __('Default'),
        'placeholder' => __('No hay una imagen elegida'),
        'frame_title' => __('Elegir imagen'),
        'frame_button' => __('Escoger'),
      )
    )
  ));

  $wp_customize->add_setting('brand_img-revert', array(
    'default' => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw'
  ));

  $wp_customize->add_control(new WP_Customize_Image_Control(
    $wp_customize,
    'brand_img-revert',
    array(
      'label' => __('Logo alternativo del menu'),
      'description' => esc_html__('Aca se pondrá el logo alternativo del menu de navegación'),
      'section' => 'brand_section',
      'button_labels' => array( // Optional.
        'select' => __('Elige una imagen'),
        'change' => __('Cambiar imagen'),
        'remove' => __('Borrar'),
        'default' => __('Default'),
        'placeholder' => __('No hay una imagen elegida'),
        'frame_title' => __('Elegir imagen'),
        'frame_button' => __('Escoger'),
      )
    )
  ));

}
add_action('customize_register', 'my_customize_register');


// Google Analytics — inyectar via wp_head con escape adecuado
add_action('wp_head', function () {
  $ga_code = get_option('my_google_analytics');
  if ($ga_code) {
    echo wp_kses($ga_code, array(
      'script' => array(
        'async'  => true,
        'src'    => true,
        'type'   => true,
      ),
    ));
  }
});