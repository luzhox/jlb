<?php
/**
 * Importa las fotos REALES de la página Admisión (Drive "Contenidos/Admisión")
 * parcheando las filas del Flexible Content `modules` de la página /admision/.
 *
 * Mapeo:
 *   Foto portada → jlb_admision_hero.imagen
 *   Foto 1       → jlb_galeria.imagen_ancha
 *   Foto 2       → jlb_galeria.imagen_angosta
 *
 * Espera archivos staged admision-{hero,gal-ancha,gal-angosta}.{png,jpg};
 * el directorio staged se pasa como 1er argumento del eval-file.
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-admision-material.php "<dir-staged>"
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

$page = get_page_by_path('admision');
if (!$page) {
    WP_CLI::error('Página /admision/ no encontrada.');
}
$pid = (int) $page->ID;

/** Sube un archivo staged (png o jpg) a la mediateca. Reejecutable sin duplicar. */
function jlb_adm_upload($staged, $base, $title) {
    $path = '';
    foreach (array('png', 'jpg') as $ext) {
        if (file_exists("{$staged}/{$base}.{$ext}")) {
            $path = "{$staged}/{$base}.{$ext}";
            break;
        }
    }
    if ($path === '') {
        WP_CLI::warning("No existe staged: {$base} — omitido.");
        return 0;
    }
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
        WP_CLI::warning("Fallo subiendo {$base}: " . $id->get_error_message());
        return 0;
    }
    return (int) $id;
}

$hero    = jlb_adm_upload($staged, 'admision-hero', 'Admisión — hero');
$ancha   = jlb_adm_upload($staged, 'admision-gal-ancha', 'Admisión — galería ancha');
$angosta = jlb_adm_upload($staged, 'admision-gal-angosta', 'Admisión — galería angosta');

// Parchear filas del flexible: get_field devuelve arrays de imagen; para
// re-guardar basta el attachment ID en el sub-campo.
$rows = get_field('modules', $pid);
if (!$rows) {
    WP_CLI::error('La página no tiene módulos.');
}
$touched = array();
foreach ($rows as $i => $row) {
    if ($row['acf_fc_layout'] === 'jlb_admision_hero' && $hero) {
        $rows[$i]['imagen'] = $hero;
        $touched[] = 'jlb_admision_hero.imagen';
    }
    if ($row['acf_fc_layout'] === 'jlb_galeria') {
        if ($ancha)   { $rows[$i]['imagen_ancha'] = $ancha;     $touched[] = 'jlb_galeria.imagen_ancha'; }
        if ($angosta) { $rows[$i]['imagen_angosta'] = $angosta; $touched[] = 'jlb_galeria.imagen_angosta'; }
    }
}
update_field('modules', $rows, $pid);

WP_CLI::log("OK página {$pid}: " . implode(' ', $touched) . " (hero={$hero} ancha={$ancha} angosta={$angosta})");
WP_CLI::success('Fotos de Admisión importadas.');
