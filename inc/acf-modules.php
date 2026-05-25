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

                    // ─────────────────────────────────────────────────────────
                    // Módulos Jean Le Boulch (template Figma home)
                    // Layout slug snake_case ↔ folder kebab-case (jlb_hero → jlb-hero).
                    // ─────────────────────────────────────────────────────────

                    // ── JLB Hero (Admisión) ───────────────────────────────────
                    'jlb_hero' => array(
                        'key' => 'layout_bp_jlb_hero',
                        'name' => 'jlb_hero',
                        'label' => 'JLB · Hero (Admisión)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_hero_eyebrow', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text', 'placeholder' => '2025 - 2026'),
                            array('key' => 'field_bp_jlb_hero_titulo', 'label' => 'Título (H1)', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_jlb_hero_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'textarea', 'rows' => 3),
                            array('key' => 'field_bp_jlb_hero_btn_pri', 'label' => 'Botón principal', 'name' => 'boton_principal', 'type' => 'link'),
                            array('key' => 'field_bp_jlb_hero_btn_sec', 'label' => 'Botón secundario', 'name' => 'boton_secundario', 'type' => 'link'),
                            array('key' => 'field_bp_jlb_hero_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Imagen del lado derecho del hero (LCP).'),
                        ),
                    ),

                    // ── JLB Niveles ───────────────────────────────────────────
                    'jlb_niveles' => array(
                        'key' => 'layout_bp_jlb_niveles',
                        'name' => 'jlb_niveles',
                        'label' => 'JLB · Niveles educativos',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_niv_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'default_value' => 'Nuestros niveles educativos'),
                            array(
                                'key' => 'field_bp_jlb_niv_items',
                                'label' => 'Niveles',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar nivel',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_niv_item_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_niv_item_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_jlb_niv_item_link', 'label' => 'Enlace', 'name' => 'link', 'type' => 'link'),
                                    array('key' => 'field_bp_jlb_niv_item_wide', 'label' => 'Card ancha (toda la fila)', 'name' => 'wide', 'type' => 'true_false', 'default_value' => 0, 'instructions' => 'Si activo, la card ocupa toda la fila (modifier `--wide`).'),
                                ),
                            ),
                        ),
                    ),

                    // ── JLB Manifesto ─────────────────────────────────────────
                    'jlb_manifesto' => array(
                        'key' => 'layout_bp_jlb_manifesto',
                        'name' => 'jlb_manifesto',
                        'label' => 'JLB · Manifesto',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_man_anchor', 'label' => 'Anchor ID', 'name' => 'anchor', 'type' => 'text', 'default_value' => 'colegio', 'instructions' => 'ID para anclajes desde la navegación.'),
                            array('key' => 'field_bp_jlb_man_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'instructions' => 'Las palabras envueltas en <strong> se pintan con el gradient JLB.'),
                        ),
                    ),

                    // ── JLB Experiencia ───────────────────────────────────────
                    'jlb_experience' => array(
                        'key' => 'layout_bp_jlb_experience',
                        'name' => 'jlb_experience',
                        'label' => 'JLB · Experiencia + Propuesta',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_exp_hero_img', 'label' => 'Imagen hero video', 'name' => 'hero_imagen', 'type' => 'image', 'return_format' => 'array'),
                            array('key' => 'field_bp_jlb_exp_hero_video', 'label' => 'URL de video (hero)', 'name' => 'hero_video_url', 'type' => 'url', 'instructions' => 'YouTube, Vimeo o MP4. Si se llena, el botón play abre el video en un lightbox. Si está vacío, el play queda decorativo.'),
                            array('key' => 'field_bp_jlb_exp_hero_titulo', 'label' => 'Título hero', 'name' => 'hero_titulo', 'type' => 'text', 'default_value' => 'Conoce la experiencia JLB'),
                            array('key' => 'field_bp_jlb_exp_pro_titulo', 'label' => 'Título propuesta', 'name' => 'propuesta_titulo', 'type' => 'text', 'default_value' => 'Propuesta educativa'),
                            array('key' => 'field_bp_jlb_exp_pro_texto', 'label' => 'Texto propuesta', 'name' => 'propuesta_texto', 'type' => 'textarea', 'rows' => 4),
                            array(
                                'key' => 'field_bp_jlb_exp_items',
                                'label' => 'Experiencias (videos)',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar experiencia',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_exp_item_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_exp_item_imagen', 'label' => 'Imagen / poster', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_jlb_exp_item_video', 'label' => 'URL de video', 'name' => 'video_url', 'type' => 'url', 'instructions' => 'YouTube, Vimeo o MP4. Si se llena, el play abre el video en un lightbox. Si está vacío, el play queda decorativo.'),
                                ),
                            ),
                        ),
                    ),

                    // ── JLB Testimonio padres ─────────────────────────────────
                    'jlb_testimonio_padres' => array(
                        'key' => 'layout_bp_jlb_testimonio_padres',
                        'name' => 'jlb_testimonio_padres',
                        'label' => 'JLB · Testimonio padres',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_tes_kicker', 'label' => 'Kicker', 'name' => 'kicker', 'type' => 'text', 'default_value' => 'Lo que dicen'),
                            array('key' => 'field_bp_jlb_tes_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'default_value' => 'los padres'),
                            array(
                                'key' => 'field_bp_jlb_tes_citas',
                                'label' => 'Testimonios (carrusel)',
                                'name' => 'citas',
                                'type' => 'repeater',
                                'button_label' => 'Agregar testimonio',
                                'min' => 1,
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_tes_cita', 'label' => 'Cita', 'name' => 'cita', 'type' => 'textarea', 'rows' => 4),
                                    array('key' => 'field_bp_jlb_tes_autor', 'label' => 'Autor de la cita', 'name' => 'autor', 'type' => 'text', 'placeholder' => 'Papá de Ex Alumna'),
                                ),
                            ),
                        ),
                    ),

                    // ── JLB Testimoniales (slider) ────────────────────────────
                    'jlb_testimoniales' => array(
                        'key' => 'layout_bp_jlb_testimoniales',
                        'name' => 'jlb_testimoniales',
                        'label' => 'JLB · Testimoniales (slider)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_tlist_kicker', 'label' => 'Kicker', 'name' => 'kicker', 'type' => 'text', 'default_value' => 'Testimoniales'),
                            array('key' => 'field_bp_jlb_tlist_arco', 'label' => '¿Mostrar arco decorativo?', 'name' => 'mostrar_arco_decorativo', 'type' => 'true_false', 'default_value' => 1, 'instructions' => 'Arco rojo multicapa en la esquina superior derecha (decorativo, oculto en mobile).'),
                            array(
                                'key' => 'field_bp_jlb_tlist_items',
                                'label' => 'Testimoniales',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar testimonial',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_tlist_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_jlb_tlist_video', 'label' => 'URL de video (opcional)', 'name' => 'video_url', 'type' => 'url', 'instructions' => 'Si se llena, se muestra el botón "play" sobre la imagen. Si está vacío, el play se oculta.'),
                                    array('key' => 'field_bp_jlb_tlist_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'placeholder' => 'Me motivaron a conseguir mis objetivos'),
                                    array('key' => 'field_bp_jlb_tlist_cita', 'label' => 'Cita', 'name' => 'cita', 'type' => 'textarea', 'rows' => 4),
                                    array('key' => 'field_bp_jlb_tlist_autor_nombre', 'label' => 'Autor — nombre', 'name' => 'autor_nombre', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_tlist_autor_rol', 'label' => 'Autor — rol', 'name' => 'autor_rol', 'type' => 'text', 'placeholder' => 'Ex Alumna'),
                                ),
                            ),
                        ),
                    ),

                    // ── JLB Noticias ──────────────────────────────────────────
                    'jlb_noticias' => array(
                        'key' => 'layout_bp_jlb_noticias',
                        'name' => 'jlb_noticias',
                        'label' => 'JLB · Noticias',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_not_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'default_value' => 'Noticias'),
                            array(
                                'key' => 'field_bp_jlb_not_items',
                                'label' => 'Noticias',
                                'name' => 'items',
                                'type' => 'repeater',
                                'button_label' => 'Agregar noticia',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_not_item_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_not_item_fecha', 'label' => 'Fecha', 'name' => 'fecha', 'type' => 'text', 'placeholder' => '21/01/2026'),
                                    array('key' => 'field_bp_jlb_not_item_etiqueta', 'label' => 'Etiqueta', 'name' => 'etiqueta', 'type' => 'text', 'default_value' => 'Noticias'),
                                    array('key' => 'field_bp_jlb_not_item_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array'),
                                    array('key' => 'field_bp_jlb_not_item_link', 'label' => 'Enlace', 'name' => 'link', 'type' => 'link'),
                                ),
                            ),
                        ),
                    ),

                    // ── Página Admisión: Hero ─────────────────────────────────
                    'jlb_admision_hero' => array(
                        'key' => 'layout_bp_jlb_adm_hero',
                        'name' => 'jlb_admision_hero',
                        'label' => 'JLB · Admisión — Hero',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_adm_hero_eyebrow', 'label' => 'Eyebrow (texto pequeño sobre el título)', 'name' => 'eyebrow', 'type' => 'text'),
                            array('key' => 'field_bp_jlb_adm_hero_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'default_value' => 'Educación integral para tu hijo'),
                            array('key' => 'field_bp_jlb_adm_hero_titulo_img', 'label' => 'Título como imagen/logo (opcional; reemplaza el texto del título)', 'name' => 'titulo_imagen', 'type' => 'image', 'return_format' => 'array'),
                            array('key' => 'field_bp_jlb_adm_hero_sub', 'label' => 'Subtítulo', 'name' => 'subtitulo', 'type' => 'textarea', 'rows' => 2),
                            array('key' => 'field_bp_jlb_adm_hero_img', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array'),
                            array('key' => 'field_bp_jlb_adm_hero_video', 'label' => 'URL de video (play sobre la imagen; opcional)', 'name' => 'video_url', 'type' => 'url'),
                            array('key' => 'field_bp_jlb_adm_hero_video_cap', 'label' => 'Texto bajo el play (ej. "Ver recorrido virtual")', 'name' => 'video_caption', 'type' => 'text'),
                            array(
                                'key' => 'field_bp_jlb_adm_hero_botones', 'label' => 'Botones', 'name' => 'botones', 'type' => 'repeater', 'max' => 2, 'button_label' => 'Agregar botón',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_adm_hero_btn_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_adm_hero_btn_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url'),
                                    array('key' => 'field_bp_jlb_adm_hero_btn_target', 'label' => 'Target', 'name' => 'target', 'type' => 'select', 'choices' => array('_self' => 'Misma pestaña', '_blank' => 'Nueva pestaña'), 'default_value' => '_self'),
                                ),
                            ),
                        ),
                    ),

                    // ── Página Admisión: Proceso (stepper) ────────────────────
                    'jlb_proceso' => array(
                        'key' => 'layout_bp_jlb_proceso',
                        'name' => 'jlb_proceso',
                        'label' => 'JLB · Admisión — Proceso (pasos)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_proc_eyebrow', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text', 'default_value' => 'Proceso de admisión'),
                            array('key' => 'field_bp_jlb_proc_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array(
                                'key' => 'field_bp_jlb_proc_pasos', 'label' => 'Pasos', 'name' => 'pasos', 'type' => 'repeater', 'button_label' => 'Agregar paso',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_proc_etq', 'label' => 'Etiqueta (tab)', 'name' => 'etiqueta', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_proc_intro', 'label' => 'Intro', 'name' => 'intro', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0),
                                    array(
                                        'key' => 'field_bp_jlb_proc_reqs', 'label' => 'Requisitos', 'name' => 'requisitos', 'type' => 'repeater', 'button_label' => 'Agregar requisito',
                                        'sub_fields' => array(
                                            array('key' => 'field_bp_jlb_proc_req', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text'),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),

                    // ── Página Admisión: Cuota de ingreso (calculadora) ───────
                    'jlb_cuota' => array(
                        'key' => 'layout_bp_jlb_cuota',
                        'name' => 'jlb_cuota',
                        'label' => 'JLB · Admisión — Cuota de ingreso',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_cuota_eyebrow', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text', 'default_value' => 'Cuota de ingreso'),
                            array('key' => 'field_bp_jlb_cuota_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array('key' => 'field_bp_jlb_cuota_ver', 'label' => 'Ver condiciones (link)', 'name' => 'ver_condiciones', 'type' => 'link'),
                            array(
                                'key' => 'field_bp_jlb_cuota_niveles', 'label' => 'Niveles', 'name' => 'niveles', 'type' => 'repeater', 'button_label' => 'Agregar nivel',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_cuota_p_nombre', 'label' => 'Nivel', 'name' => 'nombre', 'type' => 'text', 'placeholder' => 'Inicial'),
                                    array('key' => 'field_bp_jlb_cuota_p_contado', 'label' => 'Cuota al contado', 'name' => 'cuota_contado', 'type' => 'text', 'placeholder' => 'US$3500'),
                                    array('key' => 'field_bp_jlb_cuota_p_cuotas', 'label' => 'Cuota en cuotas', 'name' => 'cuota_cuotas', 'type' => 'text', 'placeholder' => 'US$5000'),
                                    array('key' => 'field_bp_jlb_cuota_p_ahorro', 'label' => 'Ahorro (al contado)', 'name' => 'ahorro', 'type' => 'text', 'placeholder' => '$1500'),
                                ),
                            ),
                        ),
                    ),

                    // ── Página Admisión: Galería dúo ──────────────────────────
                    'jlb_galeria' => array(
                        'key' => 'layout_bp_jlb_galeria',
                        'name' => 'jlb_galeria',
                        'label' => 'JLB · Galería (2 imágenes)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_gal_ancha', 'label' => 'Imagen ancha', 'name' => 'imagen_ancha', 'type' => 'image', 'return_format' => 'array'),
                            array('key' => 'field_bp_jlb_gal_angosta', 'label' => 'Imagen angosta', 'name' => 'imagen_angosta', 'type' => 'image', 'return_format' => 'array'),
                        ),
                    ),

                    // ── Página Admisión: Preguntas frecuentes ─────────────────
                    'jlb_faq' => array(
                        'key' => 'layout_bp_jlb_faq',
                        'name' => 'jlb_faq',
                        'label' => 'JLB · Preguntas frecuentes',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_faq_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'default_value' => 'Preguntas frecuentes'),
                            array(
                                'key' => 'field_bp_jlb_faq_preguntas', 'label' => 'Preguntas', 'name' => 'preguntas', 'type' => 'repeater', 'button_label' => 'Agregar pregunta',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_faq_q', 'label' => 'Pregunta', 'name' => 'pregunta', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_faq_a', 'label' => 'Respuesta', 'name' => 'respuesta', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0),
                                ),
                            ),
                        ),
                    ),

                    // ── Página Experiencias innovadoras: lista de experiencias ─
                    'jlb_experiencias' => array(
                        'key' => 'layout_bp_jlb_experiencias',
                        'name' => 'jlb_experiencias',
                        'label' => 'JLB · Experiencias (filas media/texto)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_bp_jlb_exps_items', 'label' => 'Experiencias', 'name' => 'experiencias', 'type' => 'repeater', 'button_label' => 'Agregar experiencia',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_exps_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_exps_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'textarea', 'rows' => 4, 'new_lines' => ''),
                                    array('key' => 'field_bp_jlb_exps_video', 'label' => 'URL del video', 'name' => 'video_url', 'type' => 'url', 'instructions' => 'YouTube / Vimeo / MP4. Vacío = sin play.'),
                                    array('key' => 'field_bp_jlb_exps_img', 'label' => 'Imagen (poster)', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'),
                                    array('key' => 'field_bp_jlb_exps_boton', 'label' => 'Botón', 'name' => 'boton', 'type' => 'link', 'return_format' => 'array'),
                                ),
                            ),
                        ),
                    ),

                    // ── Página Open Day: formulario de registro (→ HubSpot) ────
                    'jlb_open_day_form' => array(
                        'key' => 'layout_bp_jlb_open_day_form',
                        'name' => 'jlb_open_day_form',
                        'label' => 'JLB · Open Day — Formulario (HubSpot)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_od_titulo', 'label' => 'Título (opcional, oculto si vacío)', 'name' => 'titulo', 'type' => 'text'),
                        ),
                    ),

                    // ── Página Nosotros: línea de tiempo / historia ────────────
                    'jlb_timeline' => array(
                        'key' => 'layout_bp_jlb_timeline',
                        'name' => 'jlb_timeline',
                        'label' => 'JLB · Línea de tiempo (historia)',
                        'display' => 'block',
                        'sub_fields' => array(
                            array('key' => 'field_bp_jlb_tl_eyebrow', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text', 'default_value' => 'Nuestra historia'),
                            array('key' => 'field_bp_jlb_tl_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text'),
                            array(
                                'key' => 'field_bp_jlb_tl_hitos', 'label' => 'Hitos', 'name' => 'hitos', 'type' => 'repeater', 'button_label' => 'Agregar hito',
                                'sub_fields' => array(
                                    array('key' => 'field_bp_jlb_tl_anio', 'label' => 'Año', 'name' => 'anio', 'type' => 'text', 'placeholder' => '1983'),
                                    array('key' => 'field_bp_jlb_tl_h_titulo', 'label' => 'Título del hito', 'name' => 'titulo', 'type' => 'text'),
                                    array('key' => 'field_bp_jlb_tl_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''),
                                    array('key' => 'field_bp_jlb_tl_img', 'label' => 'Imagen (opcional)', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array'),
                                ),
                            ),
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
