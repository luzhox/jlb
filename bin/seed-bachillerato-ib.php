<?php
/**
 * Carga el bloque "Programa del Diploma Bachillerato" (IB) en el nivel
 * Bachillerato — campos `destacado_*` del CPT nivel (ver inc/cpt-nivel.php).
 * Contenido del flyer institucional (2026-08).
 *
 * Uso:
 *   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" \
 *     eval-file wp-content/themes/jlb/bin/seed-bachillerato-ib.php
 */

if (!defined('ABSPATH')) {
    exit('Run via wp eval-file.');
}
if (!function_exists('update_field')) {
    WP_CLI::error('ACF Pro no activo.');
}

$pid = 0;
foreach (get_posts(array('post_type' => 'nivel', 'numberposts' => -1)) as $p) {
    if (mb_stripos($p->post_title, 'Bachiller') !== false) {
        $pid = (int) $p->ID;
        break;
    }
}
if (!$pid) {
    WP_CLI::error('Nivel Bachillerato no encontrado.');
}

update_field('destacado_eyebrow', 'IB para secundaria', $pid);
update_field('destacado_titulo', 'Programa del Diploma Bachillerato', $pid);
update_field(
    'destacado_texto',
    'A partir del 2028, con la finalidad de seguir fortaleciendo una propuesta educativa coherente, hemos iniciado las gestiones para implementar el Programa del Diploma IB, proyectado para estudiantes de 10.° y 11.° grado a partir del 2028.',
    $pid
);
update_field('destacado_lista_titulo', 'A nivel universitario, el programa ofrece los siguientes beneficios:', $pid);
update_field('destacado_puntos', array(
    array('texto' => 'Mejor acceso a universidades del Perú y extranjero.'),
    array('texto' => 'Mayor competitividad en postulaciones internacionales.'),
    array('texto' => 'Posibilidad de convalidación de cursos o créditos universitarios, según la universidad y la carrera.'),
), $pid);

// Imagen: la foto de laboratorio de Bachillerato ya subida (card del home);
// si no existe, se deja vacía y la sección sale solo con texto.
$img = get_posts(array(
    'post_type' => 'attachment', 'numberposts' => 1, 'fields' => 'ids',
    'title' => 'Niveles home — Bachiller',
));
if ($img) {
    update_field('destacado_imagen', (int) $img[0], $pid);
}

WP_CLI::success("Bloque IB cargado en nivel {$pid}" . ($img ? " (imagen {$img[0]})" : ' (sin imagen)'));
