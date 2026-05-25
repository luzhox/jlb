<?php
/**
 * CPT "nivel" + campos ACF — formato de Nivel (Figma 4110:116).
 *
 * Cada nivel (Inicial, Primaria, Secundaria…) es una entrada del CPT `nivel`
 * (slug público /niveles/<slug>/) que se renderiza con single-nivel.php usando
 * el formato de detalle COMPARTIDO con Talleres
 * (template-parts/jlb-formato-detalle.php).
 *
 * Diferencias de datos vs Taller: el plan usa `plan_grupos` (varias bandas:
 * "Áreas Curriculares", "Talleres") en vez de cursos planos, y hay una imagen
 * de galería full-width (`gal_full`). No tiene sección de video.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    register_post_type('nivel', array(
        'labels' => array(
            'name'          => __('Niveles', 'boilerplate'),
            'singular_name' => __('Nivel', 'boilerplate'),
            'add_new_item'  => __('Agregar nivel', 'boilerplate'),
            'edit_item'     => __('Editar nivel', 'boilerplate'),
            'menu_name'     => __('Niveles', 'boilerplate'),
        ),
        'public'        => true,
        'has_archive'   => 'niveles',
        'menu_icon'     => 'dashicons-groups',
        'menu_position' => 21,
        'supports'      => array('title', 'thumbnail', 'excerpt'),
        'rewrite'       => array('slug' => 'niveles', 'with_front' => false),
        'show_in_rest'  => true,
    ));
});

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key'    => 'group_jlb_nivel',
        'title'  => __('Nivel — Contenido', 'boilerplate'),
        'fields' => array(

            // ── Hero ──
            array('key' => 'field_jlb_n_tab_hero', 'label' => __('Hero', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_n_hero_sub', 'label' => __('Subtítulo', 'boilerplate'), 'name' => 'hero_subtitulo', 'type' => 'textarea', 'rows' => 2),
            array('key' => 'field_jlb_n_hero_img', 'label' => __('Imagen del hero', 'boilerplate'), 'name' => 'hero_imagen', 'type' => 'image', 'return_format' => 'array'),
            array(
                'key' => 'field_jlb_n_hero_botones', 'label' => __('Botones', 'boilerplate'), 'name' => 'hero_botones', 'type' => 'repeater', 'max' => 2, 'button_label' => __('Agregar botón', 'boilerplate'),
                'sub_fields' => array(
                    array('key' => 'field_jlb_n_hb_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text'),
                    array('key' => 'field_jlb_n_hb_url', 'label' => 'URL', 'name' => 'url', 'type' => 'text'),
                ),
            ),

            // ── Plan de estudios (grupos) ──
            array('key' => 'field_jlb_n_tab_plan', 'label' => __('Plan de estudios', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_n_plan_eyebrow', 'label' => 'Eyebrow', 'name' => 'plan_eyebrow', 'type' => 'text', 'default_value' => 'Plan de estudios'),
            array('key' => 'field_jlb_n_plan_titulo', 'label' => 'Título', 'name' => 'plan_titulo', 'type' => 'text', 'default_value' => 'Creamos las bases de un aprendizaje integral'),
            array(
                'key' => 'field_jlb_n_plan_grupos', 'label' => 'Grupos del plan', 'name' => 'plan_grupos', 'type' => 'repeater', 'button_label' => __('Agregar grupo', 'boilerplate'),
                'sub_fields' => array(
                    array('key' => 'field_jlb_n_grupo_etiqueta', 'label' => 'Etiqueta (banda vertical)', 'name' => 'etiqueta', 'type' => 'text', 'placeholder' => 'Áreas Curriculares'),
                    array(
                        'key' => 'field_jlb_n_grupo_cursos', 'label' => 'Cursos', 'name' => 'cursos', 'type' => 'repeater', 'button_label' => __('Agregar curso', 'boilerplate'),
                        'sub_fields' => array(
                            array('key' => 'field_jlb_n_curso_nombre', 'label' => 'Nombre', 'name' => 'nombre', 'type' => 'text'),
                        ),
                    ),
                ),
            ),

            // ── Objetivos ──
            array('key' => 'field_jlb_n_tab_obj', 'label' => __('Objetivos', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_n_obj_eyebrow', 'label' => 'Eyebrow', 'name' => 'obj_eyebrow', 'type' => 'text', 'default_value' => 'Objetivos'),
            array('key' => 'field_jlb_n_obj_titulo', 'label' => 'Título', 'name' => 'obj_titulo', 'type' => 'text'),
            array(
                'key' => 'field_jlb_n_obj_puntos', 'label' => 'Puntos (lista numerada)', 'name' => 'obj_puntos', 'type' => 'repeater', 'button_label' => __('Agregar punto', 'boilerplate'),
                'sub_fields' => array(
                    array('key' => 'field_jlb_n_obj_punto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text'),
                ),
            ),
            array('key' => 'field_jlb_n_obj_card_img', 'label' => 'Imagen de la tarjeta', 'name' => 'obj_card_imagen', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_n_obj_card_texto', 'label' => 'Texto de la tarjeta CTA', 'name' => 'obj_card_texto', 'type' => 'text', 'default_value' => 'Aplica hoy y potencia el aprendizaje de tu engreído'),
            array('key' => 'field_jlb_n_obj_card_boton', 'label' => 'Botón de la tarjeta', 'name' => 'obj_card_boton', 'type' => 'link', 'return_format' => 'array'),

            // ── Galería ──
            array('key' => 'field_jlb_n_tab_gal', 'label' => __('Galería', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_n_gal_ancha', 'label' => 'Imagen ancha (fila superior)', 'name' => 'gal_ancha', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_n_gal_angosta', 'label' => 'Imagen angosta (fila superior)', 'name' => 'gal_angosta', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_n_gal_full', 'label' => 'Imagen full-width (fila inferior)', 'name' => 'gal_full', 'type' => 'image', 'return_format' => 'array'),

            // ── Testimonial ──
            array('key' => 'field_jlb_n_tab_testi', 'label' => __('Testimonial', 'boilerplate'), 'name' => '', 'type' => 'tab'),
            array('key' => 'field_jlb_n_testi_quote', 'label' => 'Frase destacada', 'name' => 'testi_quote', 'type' => 'text'),
            array('key' => 'field_jlb_n_testi_texto', 'label' => 'Texto', 'name' => 'testi_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''),
            array('key' => 'field_jlb_n_testi_autor', 'label' => 'Autor', 'name' => 'testi_autor', 'type' => 'text'),
            array('key' => 'field_jlb_n_testi_rol', 'label' => 'Rol', 'name' => 'testi_rol', 'type' => 'text', 'placeholder' => 'Padres'),
            array('key' => 'field_jlb_n_testi_img', 'label' => 'Imagen', 'name' => 'testi_imagen', 'type' => 'image', 'return_format' => 'array'),
            array('key' => 'field_jlb_n_testi_video', 'label' => 'URL del video (opcional)', 'name' => 'testi_video_url', 'type' => 'url'),
        ),
        'location' => array(
            array(
                array('param' => 'post_type', 'operator' => '==', 'value' => 'nivel'),
            ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));
});
