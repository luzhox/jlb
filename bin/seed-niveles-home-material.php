<?php
/**
 * Reemplaza las portadas de las cards del módulo jlb_niveles del home con las
 * fotos REALES del Drive ("Contenidos/Feed/01 Nuestros niveles educativos").
 *
 * Los archivos staged se llaman como el nivel ("Inicial.jpg", "Bachillerato.jpg");
 * el match con el título del item es por contención normalizada en ambos
 * sentidos ("Bachiller" ⊂ "Bachillerato"). Solo se toca el sub-campo `imagen`.
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-niveles-home-material.php "<dir-staged>"
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

function jlb_nivh_norm($s) {
    return mb_strtolower(trim($s));
}

$files = array();
foreach (glob("{$staged}/*.{jpg,png}", GLOB_BRACE) as $path) {
    $files[jlb_nivh_norm(pathinfo($path, PATHINFO_FILENAME))] = $path;
}
if (!$files) {
    WP_CLI::error('Sin archivos staged.');
}

function jlb_nivh_upload($path, $title) {
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
foreach ($rows as $i => $row) {
    if ($row['acf_fc_layout'] !== 'jlb_niveles' || empty($row['items'])) {
        continue;
    }
    foreach ($row['items'] as $j => $item) {
        $titulo = jlb_nivh_norm($item['titulo'] ?? '');
        if ($titulo === '') {
            continue;
        }
        $match = '';
        foreach ($files as $key => $path) {
            if (mb_strpos($key, $titulo) !== false || mb_strpos($titulo, $key) !== false) {
                $match = $key;
                break;
            }
        }
        if ($match === '') {
            WP_CLI::warning("Sin archivo para item '{$item['titulo']}'.");
            continue;
        }
        $att = jlb_nivh_upload($files[$match], 'Niveles home — ' . $item['titulo']);
        if ($att) {
            $rows[$i]['items'][$j]['imagen'] = $att;
            $touched[] = "{$item['titulo']}={$att}";
        }
    }
}

if (!$touched) {
    WP_CLI::error('Ningún item de jlb_niveles matcheó los archivos staged.');
}
update_field('modules', $rows, $front_id);

WP_CLI::log("OK home {$front_id}: " . implode(' · ', $touched));
WP_CLI::success('Portadas de niveles del home reemplazadas.');
