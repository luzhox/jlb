<?php
/**
 * Módulo: jlb_open_day_form — Formulario de registro al Open Day (Figma 4172:416).
 *
 * Envía a HubSpot vía la Forms Submission API a través del endpoint REST
 * `jlb/v1/open-day` (proxy server-side: nonce + sanitización + wp_remote_post).
 * Ver inc/hubspot.php y docs/hubspot-open-day.md.
 *
 * Protecciones: nonce (wp_rest), honeypot, y reCAPTCHA opcional (si se define
 * JLB_RECAPTCHA_SITE_KEY). El detalle de campos ↔ propiedades HubSpot es
 * filtrable en el endpoint.
 *
 * Datos (dual-mode flex/$args):
 *   titulo  string (opcional, encabezado del bloque)
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$titulo = (string) $get('titulo');

$uid          = wp_unique_id('jlb-od-');
$nonce        = wp_create_nonce('wp_rest');
$endpoint     = esc_url_raw(rest_url('jlb/v1/open-day'));
// Site key: constante (wp-config) u opción administrable (Ajustes del sitio › Integraciones).
$recaptcha_kp = function_exists('jlb_hubspot_cfg') ? jlb_hubspot_cfg('recaptcha_site') : '';

$anios = array('2026', '2027', '2028');
$horas = array(
    '9 a 10am'  => '9 a 10am',
    '10 a 11am' => '10 a 11am',
    '11 a 12am' => '11 a 12am',
    '12 a 1pm'  => '12 a 1pm',
    '1 a 2pm'   => '1 a 2pm',
);
?>
<section class="jlb-openday" id="open-day-form" aria-labelledby="<?php echo esc_attr($uid); ?>-h">
    <div class="jlb-openday__container">
        <h2 class="sr-text" id="<?php echo esc_attr($uid); ?>-h"><?php echo esc_html($titulo ?: __('Regístrate al Open Day', 'boilerplate')); ?></h2>

        <form class="jlb-openday__form" data-jlb-openday
            data-endpoint="<?php echo esc_attr($endpoint); ?>"
            data-nonce="<?php echo esc_attr($nonce); ?>"
            method="post" novalidate>

            <?php // Honeypot anti-spam: invisible para humanos. ?>
            <div class="jlb-openday__hp" aria-hidden="true">
                <label>Déjalo vacío <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="jlb-openday__grid">
                <div class="jlb-openday__field">
                    <label for="<?php echo esc_attr($uid); ?>-resp"><?php esc_html_e('Nombre de papá / mamá / responsable', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="text" id="<?php echo esc_attr($uid); ?>-resp" name="responsable" required autocomplete="name">
                </div>
                <div class="jlb-openday__field">
                    <label for="<?php echo esc_attr($uid); ?>-post"><?php esc_html_e('Nombre de postulante', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="text" id="<?php echo esc_attr($uid); ?>-post" name="postulante" required>
                </div>

                <div class="jlb-openday__field jlb-openday__field--full">
                    <label for="<?php echo esc_attr($uid); ?>-mail"><?php esc_html_e('Correo', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="email" id="<?php echo esc_attr($uid); ?>-mail" name="correo" required autocomplete="email">
                </div>

                <div class="jlb-openday__field">
                    <label for="<?php echo esc_attr($uid); ?>-cel"><?php esc_html_e('Número de celular', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="tel" id="<?php echo esc_attr($uid); ?>-cel" name="celular" required autocomplete="tel" inputmode="tel">
                </div>
                <div class="jlb-openday__field">
                    <label for="<?php echo esc_attr($uid); ?>-grado"><?php esc_html_e('Grado o año al que postula', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="text" id="<?php echo esc_attr($uid); ?>-grado" name="grado" required>
                </div>

                <fieldset class="jlb-openday__field jlb-openday__radios">
                    <legend><?php esc_html_e('Año de admisión', 'boilerplate'); ?> <span class="req">*</span></legend>
                    <?php foreach ($anios as $i => $a): ?>
                        <label class="jlb-openday__radio">
                            <input type="radio" name="anio_admision" value="<?php echo esc_attr($a); ?>" <?php echo $i === 0 ? 'required' : ''; ?>>
                            <span><?php echo esc_html($a); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset class="jlb-openday__field jlb-openday__radios">
                    <legend><?php esc_html_e('Hora de visita', 'boilerplate'); ?> <span class="req">*</span></legend>
                    <?php foreach ($horas as $val => $label): ?>
                        <label class="jlb-openday__radio">
                            <input type="radio" name="hora_visita" value="<?php echo esc_attr($val); ?>" required>
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            </div>

            <div class="jlb-openday__consents">
                <label class="jlb-openday__check">
                    <input type="checkbox" name="consent_info" value="1" required>
                    <span><?php esc_html_e('Acepto recibir información del Colegio Jean Le Boulch.', 'boilerplate'); ?> <span class="req">*</span></span>
                </label>
                <label class="jlb-openday__check">
                    <input type="checkbox" name="consent_datos" value="1" required>
                    <span><?php esc_html_e('Acepto permitir que Colegio Jean Le Boulch almacene y procese mis datos personales.', 'boilerplate'); ?> <span class="req">*</span></span>
                </label>
            </div>

            <?php if ($recaptcha_kp): ?>
                <div class="jlb-openday__recaptcha g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_kp); ?>"></div>
            <?php endif; ?>

            <div class="jlb-openday__actions">
                <button type="submit" class="jlb-openday__submit"><?php esc_html_e('Enviar registro', 'boilerplate'); ?></button>
            </div>

            <p class="jlb-openday__status" data-jlb-openday-status role="status" aria-live="polite" hidden></p>
        </form>
    </div>
</section>
<?php if ($recaptcha_kp): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
