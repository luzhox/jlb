<?php
/**
 * ACF Options Page · Ajustes del sitio (Footer + identidad)
 *
 * Centraliza datos globales editables desde wp-admin:
 *   · Identidad: logo header, logo footer
 *   · Footer: dirección, teléfonos, redes, email, legal, copy
 *
 * Lectura desde plantillas:
 *   get_field('jlb_footer_address', 'option')
 *   get_field('jlb_footer_telefonos', 'option')  // repeater (have_rows + get_sub_field)
 *
 * Si ACF Pro no está activo (no existe acf_add_options_page), este archivo
 * no hace nada — las plantillas deben tener fallback hardcodeado.
 */

if (!defined('ABSPATH')) exit;

// ── Options Page ────────────────────────────────────────────────────────────
add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page(array(
        'page_title'  => __('Ajustes del sitio', 'boilerplate'),
        'menu_title'  => __('Ajustes del sitio', 'boilerplate'),
        'menu_slug'   => 'jlb-site-settings',
        'capability'  => 'edit_theme_options',
        'redirect'    => false,
        'icon_url'    => 'dashicons-admin-customizer',
        'position'    => 60,
    ));
});

// ── Field group: Identidad + Footer ─────────────────────────────────────────
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key'    => 'group_jlb_site_settings',
        'title'  => __('Ajustes del sitio', 'boilerplate'),
        'fields' => array(

            // ── Tab: Identidad ──────────────────────────────────────────────
            array(
                'key'         => 'field_jlb_tab_identidad',
                'label'       => __('Identidad', 'boilerplate'),
                'name'        => '',
                'type'        => 'tab',
                'placement'   => 'top',
            ),
            array(
                'key'           => 'field_jlb_logo_header',
                'label'         => __('Logo (header)', 'boilerplate'),
                'name'          => 'logo_header',
                'type'          => 'image',
                'return_format' => 'id',
                'instructions'  => __('Logo del header. Si está vacío, se usa el SVG de assets/figma-home/logo.svg.', 'boilerplate'),
            ),
            array(
                'key'           => 'field_jlb_logo_footer',
                'label'         => __('Logo (footer)', 'boilerplate'),
                'name'          => 'jlb_logo_footer',
                'type'          => 'image',
                'return_format' => 'id',
                'instructions'  => __('Si está vacío, reutiliza el logo del header.', 'boilerplate'),
            ),

            // ── Tab: Footer ─────────────────────────────────────────────────
            array(
                'key'       => 'field_jlb_tab_footer',
                'label'     => __('Footer', 'boilerplate'),
                'name'      => '',
                'type'      => 'tab',
                'placement' => 'top',
            ),
            array(
                'key'   => 'field_jlb_footer_address_title',
                'label' => __('Título dirección', 'boilerplate'),
                'name'  => 'jlb_footer_address_title',
                'type'  => 'text',
                'default_value' => __('Visítanos en:', 'boilerplate'),
            ),
            array(
                'key'   => 'field_jlb_footer_address',
                'label' => __('Dirección', 'boilerplate'),
                'name'  => 'jlb_footer_address',
                'type'  => 'textarea',
                'rows'  => 2,
                'new_lines' => '',
            ),
            array(
                'key'   => 'field_jlb_footer_phones_title',
                'label' => __('Título teléfonos', 'boilerplate'),
                'name'  => 'jlb_footer_phones_title',
                'type'  => 'text',
                'default_value' => __('Llámanos:', 'boilerplate'),
            ),
            array(
                'key'          => 'field_jlb_footer_phones',
                'label'        => __('Teléfonos', 'boilerplate'),
                'name'         => 'jlb_footer_phones',
                'type'         => 'repeater',
                'button_label' => __('Agregar teléfono', 'boilerplate'),
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_jlb_footer_phone_label',
                        'label'       => __('Rótulo', 'boilerplate'),
                        'name'        => 'label',
                        'type'        => 'text',
                        'placeholder' => __('Admisión', 'boilerplate'),
                    ),
                    array(
                        'key'         => 'field_jlb_footer_phone_number',
                        'label'       => __('Número', 'boilerplate'),
                        'name'        => 'number',
                        'type'        => 'text',
                        'placeholder' => '976-369-407',
                    ),
                    array(
                        'key'          => 'field_jlb_footer_phone_whatsapp',
                        'label'        => __('Número WhatsApp (E.164 sin +)', 'boilerplate'),
                        'name'         => 'whatsapp',
                        'type'         => 'text',
                        'placeholder'  => '51976369407',
                        'instructions' => __('Si se rellena, el número enlaza a wa.me/<este>.', 'boilerplate'),
                    ),
                ),
            ),
            array(
                'key'   => 'field_jlb_footer_socials_title',
                'label' => __('Título redes', 'boilerplate'),
                'name'  => 'jlb_footer_socials_title',
                'type'  => 'text',
                'default_value' => __('Síguenos en:', 'boilerplate'),
            ),
            array(
                'key'          => 'field_jlb_footer_socials',
                'label'        => __('Redes sociales', 'boilerplate'),
                'name'         => 'jlb_footer_socials',
                'type'         => 'repeater',
                'button_label' => __('Agregar red', 'boilerplate'),
                'sub_fields'   => array(
                    array(
                        'key'     => 'field_jlb_footer_social_name',
                        'label'   => __('Red', 'boilerplate'),
                        'name'    => 'name',
                        'type'    => 'select',
                        'choices' => array(
                            'instagram' => 'Instagram',
                            'facebook'  => 'Facebook',
                            'tiktok'    => 'TikTok',
                            'youtube'   => 'YouTube',
                            'linkedin'  => 'LinkedIn',
                            'x'         => 'X (Twitter)',
                        ),
                    ),
                    array(
                        'key'   => 'field_jlb_footer_social_label',
                        'label' => __('Etiqueta visible', 'boilerplate'),
                        'name'  => 'label',
                        'type'  => 'text',
                        'instructions' => __('Texto corto (ej. "IG", "FB"). Si vacío, se usa el nombre de la red.', 'boilerplate'),
                    ),
                    array(
                        'key'   => 'field_jlb_footer_social_url',
                        'label' => __('URL', 'boilerplate'),
                        'name'  => 'url',
                        'type'  => 'url',
                    ),
                ),
            ),
            array(
                'key'   => 'field_jlb_footer_email_title',
                'label' => __('Título email', 'boilerplate'),
                'name'  => 'jlb_footer_email_title',
                'type'  => 'text',
                'default_value' => __('Escríbenos:', 'boilerplate'),
            ),
            array(
                'key'   => 'field_jlb_footer_email',
                'label' => __('Email de contacto', 'boilerplate'),
                'name'  => 'jlb_footer_email',
                'type'  => 'email',
            ),

            // ── Tab: Bottom bar ─────────────────────────────────────────────
            array(
                'key'       => 'field_jlb_tab_bottom',
                'label'     => __('Barra inferior', 'boilerplate'),
                'name'      => '',
                'type'      => 'tab',
                'placement' => 'top',
            ),
            array(
                'key'          => 'field_jlb_footer_copy',
                'label'        => __('Texto copyright', 'boilerplate'),
                'name'         => 'jlb_footer_copy',
                'type'         => 'text',
                'instructions' => __('Usa {year} para insertar el año actual.', 'boilerplate'),
                'default_value' => __('{year} Todos los derechos reservados - Colegio Jean Le Boulch La Molina', 'boilerplate'),
            ),
            array(
                'key'          => 'field_jlb_footer_legal',
                'label'        => __('Enlaces legales', 'boilerplate'),
                'name'         => 'jlb_footer_legal',
                'type'         => 'repeater',
                'button_label' => __('Agregar enlace', 'boilerplate'),
                'sub_fields'   => array(
                    array(
                        'key'   => 'field_jlb_footer_legal_label',
                        'label' => __('Etiqueta', 'boilerplate'),
                        'name'  => 'label',
                        'type'  => 'text',
                    ),
                    array(
                        'key'   => 'field_jlb_footer_legal_url',
                        'label' => __('URL', 'boilerplate'),
                        'name'  => 'url',
                        'type'  => 'text',
                        'instructions' => __('Puede ser un anchor (#privacidad) o una URL completa.', 'boilerplate'),
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'jlb-site-settings',
                ),
            ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));
});

// ── Helpers de lectura con fallback ─────────────────────────────────────────

if (!function_exists('jlb_footer_get')) {
    /**
     * Lee un campo de las opciones del footer con fallback.
     *
     * @param string $key     Nombre del campo ACF (sin prefijo 'jlb_footer_').
     * @param string $default Valor por defecto si el campo está vacío o ACF no está activo.
     * @return string
     */
    function jlb_footer_get($key, $default = '') {
        if (!function_exists('get_field')) {
            return $default;
        }
        $value = get_field($key, 'option');
        return is_string($value) && $value !== '' ? $value : $default;
    }
}

if (!function_exists('jlb_footer_copy')) {
    /**
     * Resuelve el texto del copyright reemplazando {year} por el año actual.
     *
     * @param string $default
     * @return string
     */
    function jlb_footer_copy($default = '') {
        $raw = jlb_footer_get('jlb_footer_copy', $default);
        return str_replace('{year}', date_i18n('Y'), $raw);
    }
}
