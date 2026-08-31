<?php
/**
 * Formulario de Admisión — captura de solicitudes.
 *
 * Endpoint REST `POST /wp-json/jlb/v1/admision` (proxy server-side, mismo patrón
 * de seguridad que Open Day/HubSpot, ver inc/hubspot.php): valida nonce +
 * honeypot, sanitiza, valida y (opcional) reCAPTCHA. Luego captura el lead:
 *   1) Crea un CPT `jlb_solicitud` (revisable en wp-admin → Solicitudes).
 *   2) Envía un aviso por email (wp_mail) a la dirección de admisiones.
 *   3) Dispara la acción `jlb_admision_lead` para integraciones externas
 *      (HubSpot/CRM) sin tocar este archivo.
 *
 * Email destino (prioridad): constante JLB_ADMISION_EMAIL → Ajustes del sitio ›
 * Integraciones (pestaña Admisión) → get_option('admin_email').
 *
 * Campos: nombre, apellido, correo, telefono, grado (select de niveles), mensaje.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Niveles válidos para el campo "grado al que postula".
 * Filtrable por si la institución agrega niveles.
 *
 * @return string[]
 */
function jlb_admision_grados() {
    return apply_filters('jlb_admision_grados', array('Inicial', 'Primaria', 'Secundaria', 'Bachiller'));
}

// ── CPT para almacenar las solicitudes (privado, solo wp-admin) ──────────────
add_action('init', function () {
    register_post_type('jlb_solicitud', array(
        'labels' => array(
            'name'          => __('Solicitudes de admisión', 'boilerplate'),
            'singular_name' => __('Solicitud de admisión', 'boilerplate'),
            'menu_name'     => __('Solicitudes', 'boilerplate'),
            'all_items'     => __('Solicitudes de admisión', 'boilerplate'),
            'edit_item'     => __('Ver solicitud', 'boilerplate'),
        ),
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => false, // PII: no exponer por REST
        'menu_icon'           => 'dashicons-welcome-learn-more',
        'menu_position'       => 26,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'supports'            => array('title'),
        'has_archive'         => false,
        'rewrite'             => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
    ));
});

/**
 * Dirección de email que recibe los avisos de solicitud.
 *
 * @return string
 */
function jlb_admision_email() {
    if (defined('JLB_ADMISION_EMAIL') && constant('JLB_ADMISION_EMAIL') !== '') {
        return (string) constant('JLB_ADMISION_EMAIL');
    }
    if (function_exists('get_field')) {
        $v = get_field('jlb_admision_email', 'option');
        if (is_string($v) && is_email($v)) {
            return $v;
        }
    }
    return (string) get_option('admin_email');
}

// Campo administrable (email de avisos) en "Ajustes del sitio › Integraciones".
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    acf_add_local_field_group(array(
        'key'    => 'group_jlb_admision',
        'title'  => __('Integraciones — Admisión', 'boilerplate'),
        'fields' => array(
            array(
                'key'       => 'field_jlb_adm_tab',
                'label'     => __('Admisión', 'boilerplate'),
                'name'      => '',
                'type'      => 'tab',
                'placement' => 'top',
            ),
            array(
                'key'          => 'field_jlb_admision_email',
                'label'        => __('Email de avisos de admisión', 'boilerplate'),
                'name'         => 'jlb_admision_email',
                'type'         => 'email',
                'instructions' => __('Dirección que recibe el aviso de cada solicitud. Si defines JLB_ADMISION_EMAIL en wp-config tiene prioridad. Por defecto, el email del administrador. Las solicitudes siempre quedan guardadas en wp-admin → Solicitudes.', 'boilerplate'),
            ),
        ),
        'location' => array(
            array(
                array('param' => 'options_page', 'operator' => '==', 'value' => 'jlb-site-settings'),
            ),
        ),
        'menu_order'      => 6,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));
});

// ── Endpoint REST ────────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('jlb/v1', '/admision', array(
        'methods'             => 'POST',
        'callback'            => 'jlb_admision_submit',
        'permission_callback' => '__return_true', // form público; CSRF vía nonce
    ));
});

/**
 * Maneja el envío del formulario de admisión.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function jlb_admision_submit(WP_REST_Request $request) {
    // 1) CSRF: nonce wp_rest (cabecera X-WP-Nonce o campo _wpnonce).
    $nonce = $request->get_header('x_wp_nonce');
    if (!$nonce) {
        $nonce = (string) $request->get_param('_wpnonce');
    }
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response(array('ok' => false, 'message' => __('Tu sesión expiró. Recarga la página e inténtalo de nuevo.', 'boilerplate')), 403);
    }

    // 2) Honeypot: si el bot rellenó "website", fingimos éxito y descartamos.
    if (trim((string) $request->get_param('website')) !== '') {
        return new WP_REST_Response(array('ok' => true, 'spam' => true), 200);
    }

    // 3) Sanitización.
    $data = array(
        'nombre'        => sanitize_text_field((string) $request->get_param('nombre')),
        'apellido'      => sanitize_text_field((string) $request->get_param('apellido')),
        'correo'        => sanitize_email((string) $request->get_param('correo')),
        'telefono'      => sanitize_text_field((string) $request->get_param('telefono')),
        'grado'         => sanitize_text_field((string) $request->get_param('grado')),
        'mensaje'       => sanitize_textarea_field((string) $request->get_param('mensaje')),
        'consent_datos' => (bool) $request->get_param('consent_datos'),
    );

    // 4) Validación.
    $errors = array();
    foreach (array('nombre', 'apellido', 'correo', 'telefono', 'grado') as $req) {
        if ($data[$req] === '') {
            $errors[$req] = __('Campo obligatorio.', 'boilerplate');
        }
    }
    if ($data['correo'] !== '' && !is_email($data['correo'])) {
        $errors['correo'] = __('Correo no válido.', 'boilerplate');
    }
    if ($data['grado'] !== '' && !in_array($data['grado'], jlb_admision_grados(), true)) {
        $errors['grado'] = __('Selecciona un grado válido.', 'boilerplate');
    }
    if (!$data['consent_datos']) {
        $errors['consent_datos'] = __('Debes aceptar el tratamiento de datos.', 'boilerplate');
    }
    if ($errors) {
        return new WP_REST_Response(array('ok' => false, 'message' => __('Revisa los campos marcados.', 'boilerplate'), 'errors' => $errors), 422);
    }

    // 5) reCAPTCHA (reutiliza la config de la integración si está presente).
    if (function_exists('jlb_hubspot_cfg') && jlb_hubspot_cfg('recaptcha_secret') !== '' && function_exists('jlb_verify_recaptcha')) {
        $token = (string) $request->get_param('g-recaptcha-response');
        if (!$token || !jlb_verify_recaptcha($token)) {
            return new WP_REST_Response(array('ok' => false, 'message' => __('Verificación reCAPTCHA fallida. Inténtalo de nuevo.', 'boilerplate')), 422);
        }
    }

    // 6) Captura: CPT + email + hook.
    $post_id = wp_insert_post(array(
        'post_type'   => 'jlb_solicitud',
        'post_status' => 'publish',
        'post_title'  => wp_strip_all_tags(sprintf('%s %s — %s', $data['nombre'], $data['apellido'], $data['grado'])),
    ), true);

    if (!is_wp_error($post_id)) {
        foreach (array('nombre', 'apellido', 'correo', 'telefono', 'grado', 'mensaje') as $k) {
            update_post_meta($post_id, '_jlb_adm_' . $k, $data[$k]);
        }
        update_post_meta($post_id, '_jlb_adm_ip', jlb_admision_client_ip());
        update_post_meta($post_id, '_jlb_adm_fecha', current_time('mysql'));
    }

    jlb_admision_notify($data);

    /**
     * Lead de admisión capturado (para HubSpot/CRM/etc. sin tocar este archivo).
     *
     * @param array           $data
     * @param WP_REST_Request $request
     */
    do_action('jlb_admision_lead', $data, $request);

    return new WP_REST_Response(array(
        'ok'      => true,
        'message' => __('¡Gracias! Recibimos tu solicitud de admisión. Te contactaremos pronto.', 'boilerplate'),
    ), 200);
}

/**
 * Envía el aviso por email (texto plano, escapado) a admisiones.
 *
 * @param array $data
 * @return void
 */
function jlb_admision_notify(array $data) {
    $to = jlb_admision_email();
    if (!$to || !is_email($to)) {
        return;
    }

    $subject = sprintf(
        /* translators: %s: nombre completo del postulante */
        __('Nueva solicitud de admisión — %s', 'boilerplate'),
        trim($data['nombre'] . ' ' . $data['apellido'])
    );

    $lines = array(
        __('Nueva solicitud de admisión recibida:', 'boilerplate'),
        '',
        __('Nombre:', 'boilerplate') . ' ' . $data['nombre'],
        __('Apellido:', 'boilerplate') . ' ' . $data['apellido'],
        __('Correo:', 'boilerplate') . ' ' . $data['correo'],
        __('Teléfono:', 'boilerplate') . ' ' . $data['telefono'],
        __('Grado al que postula:', 'boilerplate') . ' ' . $data['grado'],
        __('Mensaje:', 'boilerplate') . ' ' . ($data['mensaje'] !== '' ? $data['mensaje'] : '—'),
    );
    $body = implode("\n", $lines);

    $headers = array('Content-Type: text/plain; charset=UTF-8');
    // Reply-To al correo del solicitante (ya validado con is_email → sin saltos de línea).
    if (is_email($data['correo'])) {
        $headers[] = 'Reply-To: ' . $data['correo'];
    }

    wp_mail($to, $subject, $body, $headers);
}

/**
 * IP del cliente (best-effort). No depende de inc/hubspot.php.
 *
 * @return string
 */
function jlb_admision_client_ip() {
    if (function_exists('jlb_client_ip')) {
        return jlb_client_ip();
    }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

// ── Columnas del listado del CPT (revisión rápida en wp-admin) ───────────────
add_filter('manage_jlb_solicitud_posts_columns', function ($cols) {
    $new = array();
    if (isset($cols['cb'])) {
        $new['cb'] = $cols['cb'];
    }
    $new['title']        = __('Postulante', 'boilerplate');
    $new['jlb_correo']   = __('Correo', 'boilerplate');
    $new['jlb_telefono'] = __('Teléfono', 'boilerplate');
    $new['jlb_grado']    = __('Grado', 'boilerplate');
    $new['date']         = isset($cols['date']) ? $cols['date'] : __('Fecha', 'boilerplate');
    return $new;
});

add_action('manage_jlb_solicitud_posts_custom_column', function ($col, $post_id) {
    $map = array(
        'jlb_correo'   => '_jlb_adm_correo',
        'jlb_telefono' => '_jlb_adm_telefono',
        'jlb_grado'    => '_jlb_adm_grado',
    );
    if (isset($map[$col])) {
        echo esc_html((string) get_post_meta($post_id, $map[$col], true));
    }
}, 10, 2);

// ── Metabox con el detalle (incluye el mensaje) en la pantalla de edición ────
add_action('add_meta_boxes', function () {
    add_meta_box('jlb_solicitud_detalle', __('Detalle de la solicitud', 'boilerplate'), 'jlb_solicitud_metabox', 'jlb_solicitud', 'normal', 'high');
});

/**
 * @param WP_Post $post
 * @return void
 */
function jlb_solicitud_metabox($post) {
    $fields = array(
        'nombre'   => __('Nombre', 'boilerplate'),
        'apellido' => __('Apellido', 'boilerplate'),
        'correo'   => __('Correo', 'boilerplate'),
        'telefono' => __('Teléfono', 'boilerplate'),
        'grado'    => __('Grado al que postula', 'boilerplate'),
        'mensaje'  => __('Mensaje', 'boilerplate'),
        'fecha'    => __('Fecha', 'boilerplate'),
        'ip'       => __('IP', 'boilerplate'),
    );
    echo '<table class="widefat striped"><tbody>';
    foreach ($fields as $k => $label) {
        $val = (string) get_post_meta($post->ID, '_jlb_adm_' . $k, true);
        echo '<tr><th style="width:200px">' . esc_html($label) . '</th><td>' . nl2br(esc_html($val)) . '</td></tr>';
    }
    echo '</tbody></table>';
}
