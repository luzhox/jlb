<?php
/**
 * Registro programático de módulos ACF (Flexible Content).
 *
 * Estos campos se registran via PHP — no requieren JSON exportado
 * ni configuración en el admin de WordPress.
 *
 * Para exportar a acf-json: WP Admin → Custom Fields → Field Groups
 * → seleccionar → Export Field Groups.
 *
 * IMPORTANTE: No mezclar este archivo con un grupo igual en acf-json/
 * o en la BD (p. ej. group_5504bb5d9b343). Dos grupos con el mismo
 * campo `modules` en páginas/entradas corrompen el guardado: parece
 * que guarda y al refrescar el admin los campos vuelven vacíos.
 */

// No cargar exports JSON del tema: el flexible `modules` ya viene solo de PHP.
add_filter('acf/settings/load_json', function ($paths) {
    if (!is_array($paths)) {
        return $paths;
    }
    $strip = array();
    foreach (array(get_template_directory(), get_stylesheet_directory()) as $base) {
        $dir = wp_normalize_path(untrailingslashit($base . '/acf-json'));
        if ($dir !== '' && !in_array($dir, $strip, true)) {
            $strip[] = $dir;
        }
    }
    return array_values(array_filter($paths, function ($path) use ($strip) {
        $n = wp_normalize_path(untrailingslashit((string) $path));
        return !in_array($n, $strip, true);
    }));
}, 20);

/**
 * Garantizar una sola fuente de verdad para el flexible `modules`.
 *
 * Cualquier otro grupo ACF (registrado en la BD por instalaciones
 * previas, o por plugins) que contenga un campo con name="modules"
 * provoca un conflicto de field_key en `_modules` al guardar y rompe
 * el Flexible Content: parece que graba y al refrescar se vacía.
 *
 * Aquí ocultamos esos grupos huérfanos del editor sin tocar la BD.
 * Si necesitas migrar contenido viejo, hazlo antes de activar este filtro.
 */
add_filter('acf/load_field_groups', function ($groups) {
    if (!is_array($groups)) {
        return $groups;
    }

    $own_group_key = 'group_bp_componentes';
    $reserved_field_name = 'modules';

    return array_values(array_filter($groups, function ($group) use ($own_group_key, $reserved_field_name) {
        // Conservamos siempre nuestro grupo canónico.
        if (!is_array($group) || (isset($group['key']) && $group['key'] === $own_group_key)) {
            return true;
        }

        // Si el grupo declara un campo con name="modules", lo descartamos
        // para que no colisione con nuestro flexible.
        if (function_exists('acf_get_fields')) {
            $fields = acf_get_fields($group);
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field['name']) && $field['name'] === $reserved_field_name) {
                        return false;
                    }
                }
            }
        }

        return true;
    }));
}, 20);

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_bp_componentes',
        'title' => 'Componentes de Página',
        'fields' => array(
            array(
                'key' => 'field_bp_modules',
                'label' => 'Módulos',
                'name' => 'modules',
                'type' => 'flexible_content',
                'button_label' => 'Agregar módulo',
                'layouts' => array(

                    // ── Hero ──────────────────────────────────────────────────
                    'hero' => array(
                        'key' => 'layout_bp_hero',
                        'name' => 'hero',
                        'label' => 'Hero (slider)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_bp_hero_slider',
                                'label' => 'Slides',
                                'name' => 'sliderhero',
                                'type' => 'repeater',
                                'button_label' => 'Agregar slide',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_hero_overlay', 'label' => 'Color overlay', 'name' => 'overlay', 'type' => 'color_picker'),
                                    array('key' => 'field_bp_hero_img_desk', 'label' => 'Imagen desktop', 'name' => 'imagen_de_escritorio', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_hero_img_mob', 'label' => 'Imagen mobile', 'name' => 'imagen_de_mobile', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_hero_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'wysiwyg'),
                                    array('key' => 'field_bp_hero_hay_boton', 'label' => '¿Tiene botón?', 'name' => 'hay_boton', 'type' => 'true_false', 'default_value' => 1),
                                    array('key' => 'field_bp_hero_boton', 'label' => 'Botón', 'name' => 'boton', 'type' => 'link'),
                                )
                            ),
                            array('key' => 'field_bp_hero_overline', 'label' => 'Overline manuscrito', 'name' => 'overline_manuscrito', 'type' => 'text', 'instructions' => 'Texto opcional en Caveat sobre el H1 (ej. "Hi there 👋"). Sitio #3 autorizado de Caveat — máx 1 vez por página.'),
                            array('key' => 'field_bp_hero_tipo_fondo', 'label' => 'Tipo de fondo', 'name' => 'tipo_fondo', 'type' => 'select', 'choices' => array('swiper' => 'Slider (legacy)', 'video' => 'Vídeo', 'imagen' => 'Imagen', 'color' => 'Color sólido'), 'default_value' => 'swiper', 'instructions' => 'Mantener "swiper" preserva el carrusel actual.'),
                            array('key' => 'field_bp_hero_video', 'label' => 'Vídeo de fondo', 'name' => 'video_fondo', 'type' => 'file', 'mime_types' => 'mp4,webm', 'instructions' => 'Solo se aplica si Tipo de fondo = Vídeo. La imagen del primer slide actúa como poster (LCP).', 'conditional_logic' => array(array(array('field' => 'field_bp_hero_tipo_fondo', 'operator' => '==', 'value' => 'video')))),
                            array('key' => 'field_bp_hero_ver_mas', 'label' => 'Link "ver más"', 'name' => 'ver_mas', 'type' => 'link'),
                        ),
                    ),

                    // ── CTA ───────────────────────────────────────────────────
                    'cta' => array(
                        'key' => 'layout_bp_cta',
                        'name' => 'cta',
                        'label' => 'CTA (llamada a la acción)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_cta_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_cta_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'textarea', 'rows' => 2),
                            array('key' => 'field_bp_cta_btn_main', 'label' => 'Botón principal', 'name' => 'boton_principal', 'type' => 'link'),
                            array('key' => 'field_bp_cta_btn_sec', 'label' => 'Botón secundario', 'name' => 'boton_secundario', 'type' => 'link'),
                            array('key' => 'field_bp_cta_imagen', 'label' => 'Imagen de fondo / poster del vídeo', 'name' => 'imagen_fondo', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Si rellenas también "Vídeo de fondo" más abajo, esta imagen actúa como poster (LCP). Si solo hay imagen sin vídeo, NO se renderiza como bg full-bleed (la composición Kresna no la usa). Para imagen full-bleed considera el módulo Hero con tipo_fondo=imagen.'),
                            array('key' => 'field_bp_cta_overlay', 'label' => 'Color overlay (deprecado)', 'name' => 'overlay_color', 'type' => 'color_picker', 'default_value' => 'rgba(6,90,152,0.85)', 'instructions' => '⚠ DEPRECADO. El CTA rediseñado no usa este campo (las variantes brand/surface/dark ya definen el fondo). Se mantiene solo por compatibilidad con datos existentes y será retirado en una futura versión.', 'wrapper' => array('class' => 'acf-deprecated')),
                            array('key' => 'field_bp_cta_alineacion', 'label' => 'Alineación', 'name' => 'alineacion', 'type' => 'select', 'choices' => array('center' => 'Centro', 'left' => 'Izquierda', 'right' => 'Derecha'), 'default_value' => 'center'),
                            array('key' => 'field_bp_cta_fondo', 'label' => 'Fondo (legacy)', 'name' => 'fondo', 'type' => 'select', 'choices' => array('primary' => 'Primario', 'dark' => 'Oscuro', 'light' => 'Claro'), 'default_value' => 'primary', 'instructions' => 'Campo legacy. Conviene usar "Variante" (brand/surface/dark). Se mantiene para compatibilidad con datos existentes.'),
                            array('key' => 'field_bp_cta_variante', 'label' => 'Variante', 'name' => 'variante', 'type' => 'select', 'choices' => array('brand' => 'Brand (azul Kresna)', 'surface' => 'Surface (claro)', 'dark' => 'Dark (negro)'), 'default_value' => 'brand', 'instructions' => 'Variante visual de la sección. Si está vacío, mapea desde "Fondo" legacy.'),
                            array('key' => 'field_bp_cta_cube_visible', 'label' => '¿Mostrar lucky cube?', 'name' => 'cube_visible', 'type' => 'true_false', 'default_value' => 0, 'instructions' => 'Cubo decorativo flotante. Solo aplica en variantes brand y dark.'),
                            array('key' => 'field_bp_cta_video', 'label' => 'Vídeo de fondo (opcional)', 'name' => 'video_fondo', 'type' => 'file', 'mime_types' => 'mp4,webm', 'instructions' => 'Si se rellena, se renderiza vídeo de fondo (autoplay muted loop). La imagen de fondo actúa como poster (LCP).'),
                        ),
                    ),

                    // ── Testimonios ───────────────────────────────────────────
                    'testimonios' => array(
                        'key' => 'layout_bp_testimonios',
                        'name' => 'testimonios',
                        'label' => 'Testimonios',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_test_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_test_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array(
                                'key' => 'field_bp_test_items',
                                'label' => 'Testimonios',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar testimonio',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_test_nombre', 'label' => 'Nombre', 'name' => 'nombre', 'type' => 'text'),
                                    array('key' => 'field_bp_test_cargo', 'label' => 'Cargo', 'name' => 'cargo', 'type' => 'text'),
                                    array('key' => 'field_bp_test_empresa', 'label' => 'Empresa', 'name' => 'empresa', 'type' => 'text'),
                                    array('key' => 'field_bp_test_foto', 'label' => 'Foto', 'name' => 'foto', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_test_testimonio', 'label' => 'Testimonio', 'name' => 'testimonio', 'type' => 'textarea'),
                                    array('key' => 'field_bp_test_calif', 'label' => 'Calificación (1-5)', 'name' => 'calificacion', 'type' => 'number', 'min' => 1, 'max' => 5, 'default_value' => 5),
                                    array('key' => 'field_bp_test_destacado', 'label' => 'Destacado', 'name' => 'destacado', 'type' => 'true_false', 'default_value' => 0, 'instructions' => 'Si está activo, el testimonio ocupa columna doble y usa fondo brand-50.'),
                                )
                            ),
                        ),
                    ),

                    // ── Cards de servicios ────────────────────────────────────
                    'cards_servicios' => array(
                        'key' => 'layout_bp_cards_servicios',
                        'name' => 'cards_servicios',
                        'label' => 'Cards de Servicios',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_cs_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_cs_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array('key' => 'field_bp_cs_columnas', 'label' => 'Columnas', 'name' => 'columnas', 'type' => 'select', 'choices' => array('2' => '2', '3' => '3', '4' => '4'), 'default_value' => '3'),
                            array(
                                'key' => 'field_bp_cs_items',
                                'label' => 'Cards',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar card',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_cs_icono', 'label' => 'Ícono', 'name' => 'icono', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_cs_titulo_card', 'label' => 'Título', 'name' => 'titulo_card', 'type' => 'text'),
                                    array('key' => 'field_bp_cs_desc', 'label' => 'Descripción', 'name' => 'descripcion', 'type' => 'textarea', 'rows' => 3),
                                    array('key' => 'field_bp_cs_boton', 'label' => 'Botón', 'name' => 'boton', 'type' => 'link'),
                                )
                            ),
                        ),
                    ),

                    // ── Acordeón / FAQ ────────────────────────────────────────
                    'acordeon' => array(
                        'key' => 'layout_bp_acordeon',
                        'name' => 'acordeon',
                        'label' => 'Acordeón / FAQ',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_acord_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_acord_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array(
                                'key' => 'field_bp_acord_items',
                                'label' => 'Preguntas',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar pregunta',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_acord_pregunta', 'label' => 'Pregunta', 'name' => 'pregunta', 'type' => 'text'),
                                    array('key' => 'field_bp_acord_respuesta', 'label' => 'Respuesta', 'name' => 'respuesta', 'type' => 'wysiwyg', 'toolbar' => 'basic'),
                                )
                            ),
                        ),
                    ),

                    // ── Estadísticas ──────────────────────────────────────────
                    'estadisticas' => array(
                        'key' => 'layout_bp_estadisticas',
                        'name' => 'estadisticas',
                        'label' => 'Estadísticas',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_est_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_est_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array('key' => 'field_bp_est_fondo', 'label' => 'Fondo', 'name' => 'fondo', 'type' => 'select', 'choices' => array('light' => 'Claro', 'primary' => 'Primario', 'dark' => 'Oscuro'), 'default_value' => 'light'),
                            array(
                                'key' => 'field_bp_est_items',
                                'label' => 'Estadísticas',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar estadística',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_est_numero', 'label' => 'Número', 'name' => 'numero', 'type' => 'number'),
                                    array('key' => 'field_bp_est_sufijo', 'label' => 'Sufijo (+, %, k...)', 'name' => 'sufijo', 'type' => 'text'),
                                    array('key' => 'field_bp_est_etiqueta', 'label' => 'Etiqueta', 'name' => 'etiqueta', 'type' => 'text'),
                                    array('key' => 'field_bp_est_desc', 'label' => 'Descripción', 'name' => 'descripcion', 'type' => 'text'),
                                )
                            ),
                        ),
                    ),

                    // ── Equipo ────────────────────────────────────────────────
                    'equipo' => array(
                        'key' => 'layout_bp_equipo',
                        'name' => 'equipo',
                        'label' => 'Equipo',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_eq_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_eq_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array(
                                'key' => 'field_bp_eq_items',
                                'label' => 'Miembros',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar miembro',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_eq_nombre', 'label' => 'Nombre', 'name' => 'nombre', 'type' => 'text'),
                                    array('key' => 'field_bp_eq_cargo', 'label' => 'Cargo', 'name' => 'cargo', 'type' => 'text'),
                                    array('key' => 'field_bp_eq_foto', 'label' => 'Foto', 'name' => 'foto', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_eq_bio', 'label' => 'Bio', 'name' => 'bio', 'type' => 'textarea', 'rows' => 2),
                                    array(
                                        'key' => 'field_bp_eq_redes',
                                        'label' => 'Redes sociales',
                                        'name' => 'redes_sociales',
                                        'type' => 'group',
                                        'sub_fields' => array(
                                            array('key' => 'field_bp_eq_linkedin', 'label' => 'LinkedIn', 'name' => 'linkedin', 'type' => 'url'),
                                            array('key' => 'field_bp_eq_twitter', 'label' => 'Twitter/X', 'name' => 'twitter', 'type' => 'url'),
                                            array('key' => 'field_bp_eq_instagram', 'label' => 'Instagram', 'name' => 'instagram', 'type' => 'url'),
                                        )
                                    ),
                                )
                            ),
                        ),
                    ),

                    // ── Galería ───────────────────────────────────────────────
                    'galeria' => array(
                        'key' => 'layout_bp_galeria',
                        'name' => 'galeria',
                        'label' => 'Galería',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_gal_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_gal_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array('key' => 'field_bp_gal_columnas', 'label' => 'Columnas', 'name' => 'columnas', 'type' => 'select', 'choices' => array('2' => '2', '3' => '3', '4' => '4'), 'default_value' => '3'),
                            array('key' => 'field_bp_gal_imagenes', 'label' => 'Imágenes', 'name' => 'imagenes', 'type' => 'gallery', 'return_format' => 'array'),
                        ),
                    ),

                    // ── Formulario ────────────────────────────────────────────
                    'formulario' => array(
                        'key' => 'layout_bp_formulario',
                        'name' => 'formulario',
                        'label' => 'Formulario de contacto',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_form_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_form_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array('key' => 'field_bp_form_desc', 'label' => 'Descripción', 'name' => 'descripcion', 'type' => 'textarea', 'rows' => 3),
                            array('key' => 'field_bp_form_sc', 'label' => 'Shortcode (Contact Form 7 u otro)', 'name' => 'shortcode_cf7', 'type' => 'text', 'placeholder' => '[contact-form-7 id="123"]'),
                            array('key' => 'field_bp_form_imagen', 'label' => 'Imagen lateral (opcional)', 'name' => 'imagen_lateral', 'type' => 'image', 'return_format' => 'array'),
                        ),
                    ),

                    // ── Hero Blog ─────────────────────────────────────────────
                    'hero_blog' => array(
                        'key' => 'layout_bp_hero_blog',
                        'name' => 'hero_blog',
                        'label' => 'Hero Blog',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_hb_bg', 'label' => 'Imagen de fondo', 'name' => 'bg', 'type' => 'image', 'return_format' => 'url'),
                            array('key' => 'field_bp_hb_overlay', 'label' => 'Color overlay', 'name' => 'overlay', 'type' => 'color_picker'),
                            array('key' => 'field_bp_hb_text', 'label' => 'Texto', 'name' => 'text', 'type' => 'wysiwyg'),
                            array('key' => 'field_bp_hb_button', 'label' => 'Botón', 'name' => 'button', 'type' => 'link'),
                        ),
                    ),

                    // ── Texto ─────────────────────────────────────────────────
                    'texto' => array(
                        'key' => 'layout_bp_texto',
                        'name' => 'texto',
                        'label' => 'Bloque de texto',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_txt_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Si se rellena, layout 2-col en desktop (imagen izquierda, texto derecha). Sin imagen, container narrow centrado.'),
                            array('key' => 'field_bp_txt_color', 'label' => 'Color del título', 'name' => 'color', 'type' => 'color_picker', 'instructions' => 'Aplica solo al PRIMER heading (h1/h2/h3) del WYSIWYG. Útil para acentos puntuales. Si el texto empieza con un párrafo, este color no se ve.'),
                            array('key' => 'field_bp_txt_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'wysiwyg'),
                        ),
                    ),

                    // ── Blog (listado de posts) ───────────────────────────────
                    'blog' => array(
                        'key' => 'layout_bp_blog',
                        'name' => 'blog',
                        'label' => 'Listado de posts',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_blog_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_blog_subtitulo', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'text'),
                            array('key' => 'field_bp_blog_cantidad', 'label' => 'Cantidad de posts', 'name' => 'cantidad', 'type' => 'number', 'default_value' => 3, 'min' => 1, 'max' => 12),
                            array('key' => 'field_bp_blog_cat', 'label' => 'Categoría (opcional)', 'name' => 'categoria', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'select', 'return_format' => 'id', 'allow_null' => 1),
                        ),
                    ),

                ),
            ),
        ),
        'location' => array(
            array(
                array('param' => 'post_type', 'operator' => '==', 'value' => 'page'),
            ),
            array(
                array('param' => 'post_type', 'operator' => '==', 'value' => 'post'),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
    ));
});
