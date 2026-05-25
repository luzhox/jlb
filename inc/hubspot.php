<?php
/**
 * Integración HubSpot — Formulario Open Day.
 *
 * Endpoint REST `POST /wp-json/jlb/v1/open-day` que actúa de proxy server-side:
 * valida nonce + honeypot, sanitiza, (opcional) verifica reCAPTCHA y reenvía a
 * la Forms Submission API de HubSpot con wp_remote_post.
 *
 * Configuración (dos vías; la CONSTANTE en wp-config tiene prioridad sobre el panel):
 *
 *   A) Administrable desde wp-admin → "Ajustes del sitio › Integraciones"
 *      (solo datos NO secretos: Portal ID, Form GUID, reCAPTCHA site key).
 *
 *   B) Constantes en wp-config.php (recomendado para SECRETOS; override de A):
 *        define('JLB_HUBSPOT_PORTAL_ID', '12345678');
 *        define('JLB_HUBSPOT_FORM_GUID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
 *        define('JLB_HUBSPOT_TOKEN', 'pat-xx-...');     // SECRETO → endpoint /secure
 *        define('JLB_RECAPTCHA_SITE_KEY', '...');        // público (front)
 *        define('JLB_RECAPTCHA_SECRET', '...');          // SECRETO → verificación
 *
 *   El token y el reCAPTCHA secret son SECRETOS: solo se leen de constante
 *   (nunca se exponen como opción editable en la BD).
 *
 * Mapeo campo→propiedad HubSpot: filtro `jlb_hubspot_field_map`.
 * Consentimiento legal (GDPR): filtro `jlb_hubspot_legal`.
 * Si no hay portal/form configurados, se dispara la acción `jlb_openday_lead`
 * (para captura alternativa) y se responde ok sin enviar a HubSpot.
 *
 * Docs: docs/hubspot-open-day.md
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Devuelve un valor de configuración de la integración.
 * Prioridad: constante en wp-config (si está definida y no vacía) → opción ACF.
 * Los secretos (token, recaptcha_secret) NUNCA se leen de la BD: solo constante.
 *
 * @param string $key portal_id | form_guid | token | recaptcha_site | recaptcha_secret
 * @return string
 */
function jlb_hubspot_cfg($key) {
    static $const_map = array(
        'portal_id'        => 'JLB_HUBSPOT_PORTAL_ID',
        'form_guid'        => 'JLB_HUBSPOT_FORM_GUID',
        'token'            => 'JLB_HUBSPOT_TOKEN',
        'recaptcha_site'   => 'JLB_RECAPTCHA_SITE_KEY',
        'recaptcha_secret' => 'JLB_RECAPTCHA_SECRET',
    );
    static $secret_only = array('token', 'recaptcha_secret');

    if (isset($const_map[$key]) && defined($const_map[$key]) && constant($const_map[$key]) !== '') {
        return (string) constant($const_map[$key]);
    }
    if (!in_array($key, $secret_only, true) && function_exists('get_field')) {
        $val = get_field('jlb_hs_' . $key, 'option');
        if (is_string($val) && $val !== '') {
            return trim($val);
        }
    }
    return '';
}

/**
 * Campos administrables (no secretos) en "Ajustes del sitio › Integraciones".
 * Se montan como un field group adicional sobre la options page existente
 * `jlb-site-settings` (registrada en inc/footer-options.php).
 */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    acf_add_local_field_group(array(
        'key'    => 'group_jlb_hubspot',
        'title'  => __('Integraciones — Open Day / HubSpot', 'boilerplate'),
        'fields' => array(
            array(
                'key'   => 'field_jlb_hs_tab',
                'label' => __('HubSpot', 'boilerplate'),
                'name'  => '',
                'type'  => 'tab',
                'placement' => 'top',
            ),
            array(
                'key'          => 'field_jlb_hs_intro',
                'label'        => '',
                'name'         => '',
                'type'         => 'message',
                'message'      => __('Conecta el formulario de Open Day con HubSpot. El **Portal ID** y el **Form GUID** los encuentras en HubSpot › Marketing › Formularios (no son secretos). El **token de private app** y el **reCAPTCHA secret** son secretos y se definen en wp-config.php (`JLB_HUBSPOT_TOKEN`, `JLB_RECAPTCHA_SECRET`). Si una constante existe en wp-config, tiene prioridad sobre estos campos.', 'boilerplate'),
                'new_lines'    => 'wpautop',
                'esc_html'     => 0,
            ),
            array(
                'key'          => 'field_jlb_hs_portal_id',
                'label'        => __('HubSpot Portal ID (Hub ID)', 'boilerplate'),
                'name'         => 'jlb_hs_portal_id',
                'type'         => 'text',
                'placeholder'  => '12345678',
                'instructions' => __('ID numérico de tu cuenta de HubSpot.', 'boilerplate'),
            ),
            array(
                'key'          => 'field_jlb_hs_form_guid',
                'label'        => __('HubSpot Form GUID', 'boilerplate'),
                'name'         => 'jlb_hs_form_guid',
                'type'         => 'text',
                'placeholder'  => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'instructions' => __('GUID del formulario destino en HubSpot.', 'boilerplate'),
            ),
            array(
                'key'          => 'field_jlb_hs_recaptcha_site',
                'label'        => __('reCAPTCHA v2 — Site key (público)', 'boilerplate'),
                'name'         => 'jlb_hs_recaptcha_site',
                'type'         => 'text',
                'instructions' => __('Opcional. Si se rellena (y el secret está en wp-config), el formulario muestra y valida reCAPTCHA.', 'boilerplate'),
            ),
        ),
        'location' => array(
            array(
                array('param' => 'options_page', 'operator' => '==', 'value' => 'jlb-site-settings'),
            ),
        ),
        'menu_order'      => 5,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('jlb/v1', '/open-day', array(
        'methods'             => 'POST',
        'callback'            => 'jlb_open_day_submit',
        'permission_callback' => '__return_true', // form público; CSRF vía nonce
    ));
});

/**
 * Maneja el envío del formulario Open Day.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function jlb_open_day_submit(WP_REST_Request $request) {
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
        'responsable'   => sanitize_text_field((string) $request->get_param('responsable')),
        'postulante'    => sanitize_text_field((string) $request->get_param('postulante')),
        'correo'        => sanitize_email((string) $request->get_param('correo')),
        'celular'       => sanitize_text_field((string) $request->get_param('celular')),
        'grado'         => sanitize_text_field((string) $request->get_param('grado')),
        'anio_admision' => sanitize_text_field((string) $request->get_param('anio_admision')),
        'hora_visita'   => sanitize_text_field((string) $request->get_param('hora_visita')),
        'consent_info'  => (bool) $request->get_param('consent_info'),
        'consent_datos' => (bool) $request->get_param('consent_datos'),
    );

    // 4) Validación.
    $errors = array();
    foreach (array('responsable', 'postulante', 'correo', 'celular', 'grado', 'anio_admision', 'hora_visita') as $req) {
        if ($data[$req] === '') {
            $errors[$req] = __('Campo obligatorio.', 'boilerplate');
        }
    }
    if ($data['correo'] !== '' && !is_email($data['correo'])) {
        $errors['correo'] = __('Correo no válido.', 'boilerplate');
    }
    if (!$data['consent_datos']) {
        $errors['consent_datos'] = __('Debes aceptar el tratamiento de datos.', 'boilerplate');
    }
    if (!$data['consent_info']) {
        $errors['consent_info'] = __('Debes aceptar este punto.', 'boilerplate');
    }
    if ($errors) {
        return new WP_REST_Response(array('ok' => false, 'message' => __('Revisa los campos marcados.', 'boilerplate'), 'errors' => $errors), 422);
    }

    // 5) reCAPTCHA (si hay secret configurado en wp-config).
    if (jlb_hubspot_cfg('recaptcha_secret') !== '') {
        $token = (string) $request->get_param('g-recaptcha-response');
        if (!$token || !jlb_verify_recaptcha($token)) {
            return new WP_REST_Response(array('ok' => false, 'message' => __('Verificación reCAPTCHA fallida. Inténtalo de nuevo.', 'boilerplate')), 422);
        }
    }

    // 6) Envío a HubSpot.
    $result = jlb_hubspot_send($data, $request);

    if (is_wp_error($result)) {
        return new WP_REST_Response(array('ok' => false, 'message' => __('No pudimos enviar tu registro. Inténtalo más tarde.', 'boilerplate')), 502);
    }

    return new WP_REST_Response(array(
        'ok'      => true,
        'message' => __('¡Gracias! Tu registro al Open Day fue recibido.', 'boilerplate'),
        'hubspot' => $result,
    ), 200);
}

/**
 * Verifica un token reCAPTCHA contra Google.
 *
 * @param string $token
 * @return bool
 */
function jlb_verify_recaptcha($token) {
    $resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
        'timeout' => 8,
        'body'    => array(
            'secret'   => jlb_hubspot_cfg('recaptcha_secret'),
            'response' => $token,
            'remoteip' => jlb_client_ip(),
        ),
    ));
    if (is_wp_error($resp)) {
        return false;
    }
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    return !empty($body['success']);
}

/**
 * Construye y envía el payload a la Forms Submission API de HubSpot.
 *
 * @param array           $data
 * @param WP_REST_Request $request
 * @return array|WP_Error  ['status'=>'sent'|'skipped'] o WP_Error
 */
function jlb_hubspot_send(array $data, WP_REST_Request $request) {
    $portal = jlb_hubspot_cfg('portal_id');
    $guid   = jlb_hubspot_cfg('form_guid');

    // Hook para captura alternativa (CRM propio, email, CPT…) — siempre se dispara.
    do_action('jlb_openday_lead', $data, $request);

    // Sin configuración: no es error; el lead queda disponible vía el hook.
    if (!$portal || !$guid) {
        return array('status' => 'skipped', 'reason' => 'not_configured');
    }

    // Mapeo campo interno → nombre de propiedad en el form de HubSpot.
    $map = apply_filters('jlb_hubspot_field_map', array(
        'correo'        => 'email',
        'responsable'   => 'firstname',
        'celular'       => 'phone',
        'postulante'    => 'nombre_postulante',
        'grado'         => 'grado_postula',
        'anio_admision' => 'anio_admision',
        'hora_visita'   => 'hora_visita',
    ), $data);

    $fields = array();
    foreach ($map as $local => $hs_name) {
        if (!$hs_name || !isset($data[$local]) || $data[$local] === '') {
            continue;
        }
        $fields[] = array(
            'objectTypeId' => '0-1', // contacto
            'name'         => $hs_name,
            'value'        => $data[$local],
        );
    }

    $payload = array(
        'submittedAt' => (string) (time() * 1000),
        'fields'      => $fields,
        'context'     => array(
            'pageUri'  => esc_url_raw((string) $request->get_header('referer')),
            'pageName' => 'Open Day — Colegio Jean Le Boulch',
            'ipAddress' => jlb_client_ip(),
        ),
    );

    // hubspotutk cookie → atribución del contacto (si existe).
    if (!empty($_COOKIE['hubspotutk'])) {
        $payload['context']['hutk'] = sanitize_text_field(wp_unslash($_COOKIE['hubspotutk']));
    }

    // Consentimiento legal (GDPR) — filtrable; estructura depende del portal.
    $legal = apply_filters('jlb_hubspot_legal', array(
        'consent' => array(
            'consentToProcess' => $data['consent_datos'],
            'text'             => 'Acepto que el Colegio Jean Le Boulch almacene y procese mis datos personales.',
        ),
    ), $data);
    if (!empty($legal)) {
        $payload['legalConsentOptions'] = $legal;
    }

    // Endpoint: /secure con token de private app, o público.
    $token = jlb_hubspot_cfg('token');
    $base  = $token
        ? "https://api.hsforms.com/submissions/v3/integration/secure/submit/{$portal}/{$guid}"
        : "https://api.hsforms.com/submissions/v3/integration/submit/{$portal}/{$guid}";

    $headers = array('Content-Type' => 'application/json');
    if ($token) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    $resp = wp_remote_post($base, array(
        'timeout' => 10,
        'headers' => $headers,
        'body'    => wp_json_encode($payload),
    ));

    if (is_wp_error($resp)) {
        return $resp;
    }
    $code = (int) wp_remote_retrieve_response_code($resp);
    if ($code < 200 || $code >= 300) {
        return new WP_Error('hubspot_http', 'HubSpot respondió ' . $code, array('body' => wp_remote_retrieve_body($resp)));
    }

    return array('status' => 'sent', 'code' => $code);
}

/**
 * IP del cliente (best-effort, respetando proxies de confianza mínimos).
 *
 * @return string
 */
function jlb_client_ip() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}
