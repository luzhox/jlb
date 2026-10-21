<?php
/**
 * Parchea SOLO las filas jlb_proceso y jlb_cuota de la página Admisión con los
 * datos de bin/inc-admision.php, sin tocar hero/galería (material real).
 * Conserva los PDFs de descargas subidos en wp-admin y enlaza "Ver condiciones"
 * de la cuota al PDF de información económica.
 *
 * Uso: wp eval-file bin/seed-admision-textos.php  (vía Open site shell de Local)
 */
if (!defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Solo WP-CLI.\n"); return; }
if (!function_exists('update_row')) { WP_CLI::error('ACF Pro no activo.'); }

require_once __DIR__ . '/inc-admision.php';

$page = get_page_by_path('admision', OBJECT, 'page');
if (!$page) { WP_CLI::error('No existe la página admision.'); }
$pid = (int) $page->ID;

$rows = (array) get_field('modules', $pid);

// PDFs ya cargados en la fila de proceso (por texto de botón).
$archivos = array();
foreach ($rows as $row) {
    if (($row['acf_fc_layout'] ?? '') !== 'jlb_proceso') { continue; }
    foreach ((array) ($row['descargas'] ?? array()) as $d) {
        if (!empty($d['archivo']['ID'])) { $archivos[$d['texto']] = $d['archivo']; }
    }
}

foreach ($rows as $i => $row) {
    $layout = $row['acf_fc_layout'] ?? '';
    if ($layout === 'jlb_proceso') {
        $new = jlb_adm_proceso();
        foreach ($new['descargas'] as &$d) {
            if (isset($archivos[$d['texto']])) { $d['archivo'] = (int) $archivos[$d['texto']]['ID']; }
        }
        unset($d);
    } elseif ($layout === 'jlb_cuota') {
        $pdf = $archivos['Descargar información económica']['url'] ?? '#condiciones';
        $new = jlb_adm_cuota($pdf);
    } else {
        continue;
    }
    $ok = update_row('modules', $i + 1, $new, $pid);
    WP_CLI::log("$layout (fila " . ($i + 1) . ') => ' . var_export($ok, true));
}
clean_post_cache($pid);
WP_CLI::success('Admisión actualizada.');
