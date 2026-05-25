<?php
/**
 * CPT "taller" + campos ACF — formato de Taller (Figma 4131:452).
 *
 * Cada taller es una entrada del CPT `taller` (slug público /talleres/<slug>/)
 * que se renderiza con single-taller.php siguiendo el formato del diseño:
 * hero + plan de estudios + video + objetivos + galería + testimonial.
 *
 * El hero y la galería reutilizan los módulos jlb_admision_hero / jlb_galeria
 * vía get_template_part($args). Las demás secciones se estilan en
 * styles/sass/organisms/_jlb-taller.scss.
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── Registro del CPT ─────────────────────────────────────────────────────────
add_action('init', function () {
    register_post_type('taller', array(
        'labels' => array(
            'name'          => __('Talleres', 'boilerplate'),
            'singular_name' => __('Taller', 'boilerplate'),
            'add_new_item'  => __('Agregar taller', 'boilerplate'),
            'edit_item'     => __('Editar taller', 'boilerplate'),
            'menu_name'     => __('Talleres', 'boilerplate'),
        ),
        'public'       => true,
        'has_archive'  => 'talleres',
        'menu_icon'    => 'dashicons-welcome-learn-more',
        'menu_position' => 22,
        'supports'     => array('title', 'thumbnail', 'excerpt'),
        'rewrite'      => array('slug' => 'talleres', 'with_front' => false),
        'show_in_rest' => true,
    ));
});

// ── Field group del taller ───────────────────────────────────────────────────
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key'    => 'group_jlb_taller',
        'title'  => __('Taller — Contenido', 'boilerplate'),
        'fields' => array(

            // ── Hero ──────────────────────────────────────────────────────
            array('key' => 'field_jlb_t_tab_hero', 'label' => __('Hero', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_t_hero_sub', 'label' => __('Subtítulo', 'boilerplate'), 'name' => 'hero_subtitulo', 'type' => 'textarea', 'rows' => 2),
            array('key' => 'field_jlb_t_hero_img', 'label' => __('Imagen del hero', 'boilerplate'), 'name' => 'hero_imagen', 'type' => 'image', 'return_format' => 'array'),
            array(
                'key' => 'field_jlb_t_hero_botones', 'label' => __('Botones', 'boilerplate'), 'name' => 'hero_botones', 'type' => 'repeater', 'max' => 2, 'button_label' => __('Agregar botón', 'boilerplate'),
                'sub_fields' => array(
                    array('key' => 'field_jlb_t_hb_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text'),
                    array('key' => 'field_jlb_t_hb_url', 'label' => 'URL', 'name' => 'url', 'type' => 'text'),
                ),
            ),

            // ── Plan de estudios ──────────────────────────────────────────
            array('key' => 'field_jlb_t_tab_plan', 'label' => __('Plan de estudios', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_t_plan_eyebrow', 'label' => 'Eyebrow', 'name' => 'plan_eyebrow', 'type' => 'text', 'default_value' => 'Plan de estudios'),
            array('key' => 'field_jlb_t_plan_titulo', 'label' => 'Título', 'name' => 'plan_titulo', 'type' => 'text', 'default_value' => 'Creamos las bases de un aprendizaje integral'),
            array('key' => 'field_jlb_t_plan_etiqueta', 'label' => 'Etiqueta lateral (banda vertical)', 'name' => 'plan_etiqueta', 'type' => 'text', 'default_value' => 'Sílabo del taller'),
            array(
                'key' => 'field_jlb_t_cursos', 'label' => 'Cursos', 'name' => 'cursos', 'type' => 'repeater', 'button_label' => __('Agregar curso', 'boilerplate'),
                'sub_fields' => array(
                    array('key' => 'field_jlb_t_curso_nombre', 'label' => 'Nombre', 'name' => 'nombre', 'type' => 'text'),
                ),
            ),

            // ── Video ─────────────────────────────────────────────────────
            array('key' => 'field_jlb_t_tab_video', 'label' => __('Video', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_t_video_url', 'label' => 'URL del video', 'name' => 'video_url', 'type' => 'url', 'instructions' => 'YouTube / Vimeo / MP4. Vacío = sin play.'),
            array('key' => 'field_jlb_t_video_poster', 'label' => 'Poster', 'name' => 'video_poster', 'type' => 'image', 'return_format' => 'array'),

            // ── Objetivos ─────────────────────────────────────────────────
            array('key' => 'field_jlb_t_tab_obj', 'label' => __('Objetivos', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_t_obj_eyebrow', 'label' => 'Eyebrow', 'name' => 'obj_eyebrow', 'type' => 'text', 'default_value' => 'Objetivos'),
            array('key' => 'field_jlb_t_obj_titulo', 'label' => 'Título', 'name' => 'obj_titulo', 'type' => 'text'),
            array(
                'key' => 'field_jlb_t_obj_puntos', 'label' => 'Puntos (lista numerada)', 'name' => 'obj_puntos', 'type' => 'repeater', 'button_label' => __('Agregar punto', 'boilerplate'),
                'sub_fields' => array(
                    array('key' => 'field_jlb_t_obj_punto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text'),
                ),
            ),
            array('key' => 'field_jlb_t_obj_card_img', 'label' => 'Imagen de la tarjeta', 'name' => 'obj_card_imagen', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_t_obj_card_texto', 'label' => 'Texto de la tarjeta CTA', 'name' => 'obj_card_texto', 'type' => 'text', 'default_value' => 'Aplica hoy y potencia el aprendizaje de tu engreído'),
            array('key' => 'field_jlb_t_obj_card_boton', 'label' => 'Botón de la tarjeta', 'name' => 'obj_card_boton', 'type' => 'link', 'return_format' => 'array'),

            // ── Galería ───────────────────────────────────────────────────
            array('key' => 'field_jlb_t_tab_gal', 'label' => __('Galería', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_t_gal_ancha', 'label' => 'Imagen ancha', 'name' => 'gal_ancha', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_t_gal_angosta', 'label' => 'Imagen angosta', 'name' => 'gal_angosta', 'type' => 'image', 'return_format' => 'array'),

            // ── Testimonial ───────────────────────────────────────────────
            array('key' => 'field_jlb_t_tab_testi', 'label' => __('Testimonial', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_t_testi_quote', 'label' => 'Frase destacada', 'name' => 'testi_quote', 'type' => 'text'),
            array('key' => 'field_jlb_t_testi_texto', 'label' => 'Texto', 'name' => 'testi_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''),
            array('key' => 'field_jlb_t_testi_autor', 'label' => 'Autor', 'name' => 'testi_autor', 'type' => 'text'),
            array('key' => 'field_jlb_t_testi_rol', 'label' => 'Rol', 'name' => 'testi_rol', 'type' => 'text', 'placeholder' => 'Ex alumno'),
            array('key' => 'field_jlb_t_testi_img', 'label' => 'Imagen', 'name' => 'testi_imagen', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_t_testi_video', 'label' => 'URL del video (opcional, play en la imagen)', 'name' => 'testi_video_url', 'type' => 'url'),
        ),
        'location' => array(
            array(
                array('param' => 'post_type', 'operator' => '==', 'value' => 'taller'),
            ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));
});
