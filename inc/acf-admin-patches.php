<?php
/**
 * Parches JS defensivos para ACF Pro en wp-admin.
 *
 * Actualmente: parche para el bug de renderLayout en Flexible Content
 * (ACF Pro 6.8.1 + WP 6.9.4). Ver assets/admin/acf-collapse-patch.js para detalle.
 *
 * Eliminar este archivo y el require en functions.php cuando ACF Pro publique fix.
 */

if (!defined('ABSPATH')) exit;

add_action('admin_enqueue_scripts', function () {
    // Solo en pantallas de edición que potencialmente usen ACF Flexible Content.
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) return;

    $relevant_bases = array('post', 'page', 'toplevel_page_jlb-site-settings', 'acf_page_jlb-site-settings');
    $is_relevant = in_array($screen->base, $relevant_bases, true) || strpos((string) $screen->id, 'jlb-site-settings') !== false;
    if (!$is_relevant) return;

    $patch_js_path  = get_template_directory() . '/assets/admin/acf-collapse-patch.js';
    $patch_css_path = get_template_directory() . '/assets/admin/acf-collapse-patch.css';
    $patch_js_ver   = file_exists($patch_js_path)  ? (string) filemtime($patch_js_path)  : (string) time();
    $patch_css_ver  = file_exists($patch_css_path) ? (string) filemtime($patch_css_path) : (string) time();

    wp_enqueue_script(
        'jlb-acf-collapse-patch',
        get_template_directory_uri() . '/assets/admin/acf-collapse-patch.js',
        array('acf-input'),
        $patch_js_ver,
        true
    );

    wp_enqueue_style(
        'jlb-acf-collapse-patch',
        get_template_directory_uri() . '/assets/admin/acf-collapse-patch.css',
        array('acf-input'),
        $patch_css_ver
    );
});
