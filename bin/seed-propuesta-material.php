<?php
/**
 * Reemplaza las portadas de los items "Propuesta educativa" del módulo
 * jlb_experience del home con las fotos REALES del Drive
 * ("Contenidos/Feed/03 Propuesta educativa (foto portada)").
 *
 * Los archivos staged se llaman EXACTAMENTE como el título del item
 * (p.ej. "Aprendizaje vivencial.jpg" → item titulo "Aprendizaje vivencial");
 * el match es por título normalizado. Solo se toca el sub-campo `imagen`
 * de cada item — títulos y video_url quedan como están.
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-propuesta-material.php "<dir-staged>"
 */

if (!defined('ABSPATH')) {
    exit('Run via wp eval-file.');
}
if (!function_exists('update_field')) {
    WP_CLI::error('ACF Pro no activo.');
}

$staged = isset($args[0]) ? rtrim($args[0], '/') : '';
if ($staged === '' || !is_dir($staged)) {
    WP_CLI::error("Directorio staged no encontrado: {$staged}");
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$front_id = (int) get_option('page_on_front');
if (!$front_id) {
    WP_CLI::error('No hay página frontal configurada.');
}

function jlb_prop_norm($s) {
    return mb_strtolower(trim($s));
}

// Staged: "<Título del item>.{jpg,png}" → título normalizado => ruta
$files = array();
foreach (glob("{$staged}/*.{jpg,png}", GLOB_BRACE) as $path) {
    $files[jlb_prop_norm(pathinfo($path, PATHINFO_FILENAME))] = $path;
}
if (!$files) {
    WP_CLI::error('Sin archivos staged.');
}

function jlb_prop_upload($path, $title) {
    $prev = get_posts(array(
        'post_type' => 'attachment', 'numberposts' => 1, 'fields' => 'ids',
        'title' => $title,
    ));
    if ($prev) {
        return (int) $prev[0];
    }
    $tmp = wp_tempnam(basename($path));
    copy($path, $tmp);
    $id = media_handle_sideload(array('name' => basename($path), 'tmp_name' => $tmp), 0, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);
        WP_CLI::warning('Fallo subiendo ' . basename($path) . ': ' . $id->get_error_message());
        return 0;
    }
    return (int) $id;
}

$rows = get_field('modules', $front_id);
if (!$rows) {
    WP_CLI::error('El home no tiene módulos.');
}

$touched = array();
$missing = array_keys($files);
foreach ($rows as $i => $row) {
    if ($row['acf_fc_layout'] !== 'jlb_experience' || empty($row['items'])) {
        continue;
    }
    foreach ($row['items'] as $j => $item) {
        $key = jlb_prop_norm($item['titulo'] ?? '');
        if (!isset($files[$key])) {
            continue;
        }
        $att = jlb_prop_upload($files[$key], 'Propuesta educativa — ' . $item['titulo']);
        if ($att) {
            $rows[$i]['items'][$j]['imagen'] = $att;
            $touched[] = "{$item['titulo']}={$att}";
            $missing = array_diff($missing, array($key));
        }
    }
}

if (!$touched) {
    WP_CLI::error('Ningún item de jlb_experience matcheó los archivos staged.');
}
update_field('modules', $rows, $front_id);

WP_CLI::log("OK home {$front_id}: " . implode(' · ', $touched));
if ($missing) {
    WP_CLI::warning('Sin item para: ' . implode(', ', $missing));
}
WP_CLI::success('Portadas de Propuesta educativa reemplazadas.');
