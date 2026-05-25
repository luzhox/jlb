<?php
/**
 * Seeder para QA visual del módulo jlb_testimoniales.
 *
 * Crea (o actualiza) tres páginas:
 *   - "QA Testimoniales · single"        → 1 ítem sin video (flechas ocultas, sin play)
 *   - "QA Testimoniales · multi"         → 3 ítems sin video (flechas visibles)
 *   - "QA Testimoniales · con video"     → 1 ítem con video_url (botón play visible)
 *
 * Uso:
 *   php /Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar \
 *     --path="/Users/luismorales/Local Sites/jlb-school/app/public" \
 *     eval-file "/Users/luismorales/Local Sites/jlb-school/app/public/wp-content/themes/jlb/bin/seed-testimoniales-qa.php"
 */

if (!defined('ABSPATH')) {
    exit('Run via wp eval-file.');
}

if (!function_exists('update_field')) {
    fwrite(STDERR, "ACF no está activo. Aborto.\n");
    return;
}

/**
 * Sube (o reutiliza) un attachment a partir de un path local.
 */
function jlb_qa_attach_image(string $path, string $alt = ''): int {
    if (!file_exists($path)) {
        fwrite(STDERR, "Imagen no encontrada: $path\n");
        return 0;
    }
    $basename = basename($path);
    // Reutilizar si ya existe attachment con el mismo nombre original
    $existing = get_posts(array(
        'post_type'  => 'attachment',
        'meta_key'   => '_wp_attached_file',
        'meta_query' => array(
            array('key' => '_wp_attached_file', 'value' => $basename, 'compare' => 'LIKE'),
        ),
        'posts_per_page' => 1,
        'fields' => 'ids',
    ));
    if (!empty($existing)) {
        $id = (int) $existing[0];
        if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt);
        return $id;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $upload = wp_upload_dir();
    $target = trailingslashit($upload['path']) . $basename;
    if (!copy($path, $target)) {
        fwrite(STDERR, "Fallo al copiar $path → $target\n");
        return 0;
    }
    $filetype = wp_check_filetype($basename, null);
    $attachment = array(
        'guid'           => trailingslashit($upload['url']) . $basename,
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name(pathinfo($basename, PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );
    $id = wp_insert_attachment($attachment, $target);
    if (is_wp_error($id) || !$id) {
        fwrite(STDERR, "wp_insert_attachment falló.\n");
        return 0;
    }
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $target));
    if ($alt) update_post_meta($id, '_wp_attachment_image_alt', $alt);
    return (int) $id;
}

/**
 * Crea o actualiza una página por título y aplica modules ACF.
 */
function jlb_qa_upsert_page(string $title, array $modules): int {
    $found = get_page_by_title($title, OBJECT, 'page');
    if ($found) {
        $page_id = (int) $found->ID;
        wp_update_post(array('ID' => $page_id, 'post_status' => 'publish'));
    } else {
        $page_id = wp_insert_post(array(
            'post_title'  => $title,
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_content'=> '',
        ));
        if (is_wp_error($page_id)) {
            fwrite(STDERR, "wp_insert_post falló para $title\n");
            return 0;
        }
    }
    update_field('modules', $modules, $page_id);
    return (int) $page_id;
}

$theme_dir = dirname(__DIR__);
$img_path  = $theme_dir . '/assets/figma-home/testimonial-nicole.png';
$att_id    = jlb_qa_attach_image($img_path, 'Retrato de Nicole, Ex Alumna del colegio Jean Le Boulch');

if (!$att_id) {
    fwrite(STDERR, "No se pudo crear el attachment de Nicole. Aborto.\n");
    return;
}

WP_CLI::log("Attachment Nicole ID: $att_id");

// ─── Item base reutilizable ───
$item_nicole = array(
    'imagen'       => $att_id,
    'video_url'    => '',
    'titulo'       => 'Me motivaron a conseguir mis objetivos',
    'cita'         => 'En Jean Le Boulch las herramientas base que me dieron me ayudaron a seguir un crecimiento profesional y proyectarme solidamente.',
    'autor_nombre' => 'Nicole',
    'autor_rol'    => 'Ex Alumna',
);

$item_carla = array_merge($item_nicole, array(
    'titulo'       => 'Encontré mi vocación aquí',
    'cita'         => 'Los profesores confiaron en mí desde el primer día. Eso marcó la diferencia y me dio la confianza para elegir mi camino.',
    'autor_nombre' => 'Carla',
    'autor_rol'    => 'Ex Alumna · Promoción 2018',
));

$item_diego = array_merge($item_nicole, array(
    'titulo'       => 'Aprendí a pensar por mí mismo',
    'cita'         => 'La metodología de Jean Le Boulch me enseñó a cuestionar, debatir y construir argumentos. Llegué a la universidad con esa ventaja.',
    'autor_nombre' => 'Diego',
    'autor_rol'    => 'Ex Alumno',
));

// ─── Caso A: single, sin video ───
$id_single = jlb_qa_upsert_page('QA Testimoniales · single', array(
    array(
        'acf_fc_layout'           => 'jlb_testimoniales',
        'kicker'                  => 'Testimoniales',
        'mostrar_arco_decorativo' => true,
        'items'                   => array($item_nicole),
    ),
));
WP_CLI::log("Página single ID: $id_single — " . get_permalink($id_single));

// ─── Caso B: multi (3 ítems) ───
$id_multi = jlb_qa_upsert_page('QA Testimoniales · multi', array(
    array(
        'acf_fc_layout'           => 'jlb_testimoniales',
        'kicker'                  => 'Testimoniales',
        'mostrar_arco_decorativo' => true,
        'items'                   => array($item_nicole, $item_carla, $item_diego),
    ),
));
WP_CLI::log("Página multi ID: $id_multi — " . get_permalink($id_multi));

// ─── Caso C: con video ───
$item_nicole_video = array_merge($item_nicole, array(
    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
));
$id_video = jlb_qa_upsert_page('QA Testimoniales · con video', array(
    array(
        'acf_fc_layout'           => 'jlb_testimoniales',
        'kicker'                  => 'Testimoniales',
        'mostrar_arco_decorativo' => true,
        'items'                   => array($item_nicole_video),
    ),
));
WP_CLI::log("Página con video ID: $id_video — " . get_permalink($id_video));

WP_CLI::success("Seeder completado. URLs:");
WP_CLI::log("  single → " . get_permalink($id_single));
WP_CLI::log("  multi  → " . get_permalink($id_multi));
WP_CLI::log("  video  → " . get_permalink($id_video));
