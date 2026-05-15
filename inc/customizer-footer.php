<?php
/**
 * Customizer · Footer
 *
 * Textos globales para un footer tradicional. Los links se administran desde
 * Apariencia > Menus y el contenido libre desde Apariencia > Widgets.
 */

if (!function_exists('bp_footer_defaults')) {
  /**
   * Defaults del footer.
   *
   * Incluye los del footer legacy (footer_*) Y los del rediseño Kresna
   * (kresna_*) para que el footer Kresna se vea completo sin necesidad
   * de rellenar nada en Customizer. El admin puede sobrescribir cualquiera
   * desde Apariencia → Customizer → Footer.
   *
   * @return array<string,string>
   */
  function bp_footer_defaults() {
    return array(
      // Legacy
      'footer_description'   => get_bloginfo('description'),
      'footer_contact_title' => __('Contacto', 'boilerplate'),
      'footer_address'       => '',
      'footer_phone'         => '',
      'footer_email'         => '',
      'footer_copyright'     => sprintf(
        __('© %1$s %2$s. Todos los derechos reservados.', 'boilerplate'),
        gmdate('Y'),
        get_bloginfo('name')
      ),

      // Kresna — copys
      'kresna_tagline'                => __('Smarter sales automation, powered by AI.', 'boilerplate'),
      'kresna_tagline_accent'         => __('powered by AI.', 'boilerplate'),
      'kresna_social_label'           => __('Stay in touch!', 'boilerplate'),
      'kresna_lucky_text'             => __('Feeling lucky?', 'boilerplate'),
      'kresna_cta_heading'            => __("AI moves fast.\nStay ahead with Kresna.", 'boilerplate'),
      'kresna_cta_strong'             => __('Stay ahead with Kresna.', 'boilerplate'),
      'kresna_subscribe_placeholder'  => __('Enter email address', 'boilerplate'),
      'kresna_subscribe_button_label' => __('Subscribe', 'boilerplate'),
      'kresna_copyright'              => sprintf(
        __('© %1$s %2$s. All rights reserved.', 'boilerplate'),
        gmdate('Y'),
        get_bloginfo('name')
      ),

      // Kresna — URLs sociales (placeholders apuntando a homepages oficiales).
      // El admin debería sustituirlas por las cuentas reales en Customizer.
      // Mientras estén con estos defaults, el footer renderiza los 4 iconos
      // clickables hacia las homepages — útil para QA visual del rediseño.
      'kresna_social_discord_url'   => 'https://discord.com/',
      'kresna_social_x_url'         => 'https://x.com/',
      'kresna_social_linkedin_url'  => 'https://www.linkedin.com/',
      'kresna_social_github_url'    => 'https://github.com/',
    );
  }
}

if (!function_exists('bp_footer_get')) {
  /**
   * Lee un theme_mod del footer con fallback centralizado.
   *
   * @param string $key
   * @param string $default
   * @return string
   */
  function bp_footer_get($key, $default = '') {
    $defaults = bp_footer_defaults();
    $fallback = $default !== '' ? $default : ($defaults[$key] ?? '');
    $value    = get_theme_mod($key, $fallback);

    return is_string($value) ? $value : $fallback;
  }
}

/*
 * Compatibilidad temporal: algunos archivos/docs del rediseño anterior
 * referencian bp_kresna_get(). El nuevo footer ya no lo usa, pero mantener
 * estos wrappers evita fatals si alguien incluye una molécula antigua.
 */
if (!function_exists('bp_kresna_defaults')) {
  function bp_kresna_defaults() {
    return bp_footer_defaults();
  }
}

if (!function_exists('bp_kresna_get')) {
  function bp_kresna_get($key, $default = '') {
    return bp_footer_get($key, $default);
  }
}

add_action('customize_register', function ($wp_customize) {
  $defaults = bp_footer_defaults();

  $wp_customize->add_section('site_footer', array(
    'title'       => __('Footer', 'boilerplate'),
    'priority'    => 60,
    'capability'  => 'edit_theme_options',
    'description' => __('Datos globales del footer. Los enlaces se editan en Apariencia > Menus y los bloques libres en Apariencia > Widgets.', 'boilerplate'),
  ));

  $fields = array(
    array(
      'id'    => 'footer_description',
      'label' => __('Descripción breve', 'boilerplate'),
      'type'  => 'textarea',
    ),
    array(
      'id'    => 'footer_contact_title',
      'label' => __('Título de contacto', 'boilerplate'),
      'type'  => 'text',
    ),
    array(
      'id'    => 'footer_address',
      'label' => __('Dirección', 'boilerplate'),
      'type'  => 'textarea',
    ),
    array(
      'id'    => 'footer_phone',
      'label' => __('Teléfono', 'boilerplate'),
      'type'  => 'text',
    ),
    array(
      'id'    => 'footer_email',
      'label' => __('Email', 'boilerplate'),
      'type'  => 'email',
    ),
    array(
      'id'    => 'footer_copyright',
      'label' => __('Copyright', 'boilerplate'),
      'type'  => 'text',
    ),
  );

  $priority = 10;
  foreach ($fields as $field) {
    $id      = $field['id'];
    $type    = $field['type'];
    $default = $defaults[$id] ?? '';

    switch ($type) {
      case 'email':
        $sanitize     = 'sanitize_email';
        $control_type = 'email';
        break;
      case 'textarea':
        $sanitize     = 'sanitize_textarea_field';
        $control_type = 'textarea';
        break;
      case 'text':
      default:
        $sanitize     = 'sanitize_text_field';
        $control_type = 'text';
        break;
    }

    $wp_customize->add_setting($id, array(
      'default'           => $default,
      'transport'         => 'refresh',
      'sanitize_callback' => $sanitize,
    ));

    $wp_customize->add_control($id, array(
      'label'    => $field['label'],
      'section'  => 'site_footer',
      'type'     => $control_type,
      'priority' => $priority,
    ));

    $priority += 5;
  }
});
