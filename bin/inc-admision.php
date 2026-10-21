<?php
/**
 * Filas jlb_proceso y jlb_cuota de Admisión — textos y montos del cliente
 * (docx "textos corregidos 8-10-26" + "Proceso de admisión y económica 2027-2028").
 * Compartido por bin/seed-admision.php y bin/seed-admision-textos.php.
 */
if (!defined('WP_CLI') || !WP_CLI) { return; }

if (!function_exists('jlb_adm_proceso')) {
function jlb_adm_proceso() {
    return array(
        'acf_fc_layout' => 'jlb_proceso',
        'eyebrow' => 'Proceso de admisión 2027 y 2028',
        'titulo'  => 'Conoce cómo postular en tres pasos.',
        'pasos'   => array(
            array(
                'etiqueta' => '1. Solicitud',
                'intro'    => 'Presenta la solicitud de vacante junto con los siguientes documentos:',
                'requisitos' => array(
                    array('texto' => 'DNI del postulante (copia simple)'),
                    array('texto' => 'DNI del papá, mamá o apoderado (copia simple)'),
                    array('texto' => 'Partida de nacimiento del postulante'),
                    array('texto' => 'Informe académico (libreta de notas) del año anterior'),
                ),
            ),
            array(
                'etiqueta' => '2. Entrevista y evaluación',
                'intro'    => '<p>Coordinamos una entrevista individual con cada familia, previa cita, con nuestro equipo psicopedagógico. A partir de 2.° de Primaria, el postulante también participa en una evaluación según su grado.</p><p>Costo del proceso de admisión:</p>',
                'requisitos' => array(
                    array('texto' => 'Inicial y 1.° de Primaria: S/ 200.00'),
                    array('texto' => 'Desde 2.° de Primaria: S/ 300.00'),
                ),
            ),
            array(
                'etiqueta' => '3. Resultados',
                'intro'    => '<p>Comunicamos los resultados por correo electrónico a partir del tercer día hábil después de la evaluación. Si el postulante es admitido, la familia deberá pagar la cuota de ingreso dentro de los 10 días siguientes para reservar la vacante y continuar con la matrícula.</p><p>El proceso de admisión es confidencial: comunicamos únicamente el resultado y no entregamos informes verbales ni escritos sobre la evaluación o la entrevista.</p>',
                'requisitos' => array(),
            ),
        ),
        // Los PDFs se suben desde wp-admin (sin archivo = botón oculto).
        'descargas' => array(
            array('texto' => 'Descargar solicitud de vacante', 'archivo' => 0),
            array('texto' => 'Descargar información económica', 'archivo' => 0),
        ),
    );
}

// Cuota de ingreso 2027-2028. Al contado = monto con descuento (+ ahorro);
// en cuotas = fraccionamiento anual por grado (tabla del PDF económico).
function jlb_adm_cuota($condiciones_url = '#condiciones') {
    $n = function ($nombre, $contado, $ahorro, $anios, $cuota) {
        return array(
            'nombre'        => $nombre,
            'cuota_contado' => $contado,
            'cuota_cuotas'  => sprintf(_n('%1$d cuota anual de %2$s', '%1$d cuotas anuales de %2$s', $anios, 'boilerplate'), $anios, $cuota),
            'ahorro'        => $ahorro,
        );
    };
    return array(
        'acf_fc_layout'   => 'jlb_cuota',
        'eyebrow'         => 'Cuota de ingreso 2027 - 2028',
        'titulo'          => 'Conoce cuál sería la cuota de ingreso',
        'ver_condiciones' => array('title' => 'Ver condiciones', 'url' => $condiciones_url, 'target' => $condiciones_url[0] === '#' ? '_self' : '_blank'),
        'niveles'         => array(
            $n('Inicial',                  'US$2,500', 'US$2,500', 11, 'US$227'),
            $n('Primaria — 1.° grado',     'US$3,500', 'US$1,500', 11, 'US$318'),
            $n('Primaria — 2.° grado',     'US$3,500', 'US$1,500', 10, 'US$350'),
            $n('Primaria — 3.° grado',     'US$2,500', 'US$2,500', 9,  'US$278'),
            $n('Primaria — 4.° grado',     'US$2,500', 'US$2,500', 8,  'US$312.50'),
            $n('Primaria — 5.° grado',     'US$2,500', 'US$2,500', 7,  'US$357'),
            $n('Primaria — 6.° grado',     'US$2,500', 'US$2,500', 6,  'US$417'),
            $n('Secundaria — 7.° grado',   'US$1,250', 'US$1,250', 5,  'US$250'),
            $n('Secundaria — 8.° grado',   'US$1,250', 'US$1,250', 4,  'US$312.50'),
            $n('Secundaria — 9.° grado',   'US$1,250', 'US$1,250', 3,  'US$417'),
            $n('Secundaria — 10.° grado',  'US$1,250', 'US$1,250', 2,  'US$625'),
            $n('Secundaria — 11.° grado',  'US$1,250', 'US$1,250', 1,  'US$1,250'),
        ),
    );
}
}
