<?php
/**
 * Módulo: jlb_admision_form — Formulario de solicitud de admisión.
 *
 * Envía al endpoint REST `jlb/v1/admision` (proxy server-side: nonce +
 * honeypot + sanitización; captura en CPT `jlb_solicitud` + email + hook
 * `jlb_admision_lead`). Ver inc/admision-form.php.
 *
 * Campos: nombre, apellido, correo, telefono, grado (select de niveles), mensaje
 * + consentimiento de datos. reCAPTCHA opcional (reusa la config de Integraciones).
 *
 * Datos (dual-mode flex/$args):
 *   titulo     string (opcional, encabezado)
 *   subtitulo  string (opcional)
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$titulo    = (string) $get('titulo');
$subtitulo = (string) $get('subtitulo');

$uid          = wp_unique_id('jlb-adm-');
$nonce        = wp_create_nonce('wp_rest');
$endpoint     = esc_url_raw(rest_url('jlb/v1/admision'));
$grados       = function_exists('jlb_admision_grados') ? jlb_admision_grados() : array('Inicial', 'Primaria', 'Secundaria', 'Bachiller');
// Site key reCAPTCHA: reusa la config de la integración (opcional).
$recaptcha_kp = function_exists('jlb_hubspot_cfg') ? jlb_hubspot_cfg('recaptcha_site') : '';
?>
<section class="jlb-admform" id="admision-form" aria-labelledby="<?php echo esc_attr($uid); ?>-h">
    <div class="jlb-admform__container">
        <?php if ($titulo): ?>
            <h2 class="jlb-admform__title" id="<?php echo esc_attr($uid); ?>-h"><?php echo esc_html($titulo); ?></h2>
        <?php else: ?>
            <h2 class="sr-text" id="<?php echo esc_attr($uid); ?>-h"><?php esc_html_e('Solicitud de admisión', 'boilerplate'); ?></h2>
        <?php endif; ?>
        <?php if ($subtitulo): ?>
            <p class="jlb-admform__subtitle"><?php echo esc_html($subtitulo); ?></p>
        <?php endif; ?>

        <form class="jlb-admform__form" data-jlb-admform
            data-endpoint="<?php echo esc_attr($endpoint); ?>"
            data-nonce="<?php echo esc_attr($nonce); ?>"
            method="post" novalidate>

            <?php // Honeypot anti-spam: invisible para humanos. ?>
            <div class="jlb-admform__hp" aria-hidden="true">
                <label>Déjalo vacío <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="jlb-admform__grid">
                <div class="jlb-admform__field">
                    <label for="<?php echo esc_attr($uid); ?>-nombre"><?php esc_html_e('Nombre', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="text" id="<?php echo esc_attr($uid); ?>-nombre" name="nombre" required autocomplete="given-name">
                </div>
                <div class="jlb-admform__field">
                    <label for="<?php echo esc_attr($uid); ?>-apellido"><?php esc_html_e('Apellido', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="text" id="<?php echo esc_attr($uid); ?>-apellido" name="apellido" required autocomplete="family-name">
                </div>

                <div class="jlb-admform__field">
                    <label for="<?php echo esc_attr($uid); ?>-correo"><?php esc_html_e('Correo', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="email" id="<?php echo esc_attr($uid); ?>-correo" name="correo" required autocomplete="email">
                </div>
                <div class="jlb-admform__field">
                    <label for="<?php echo esc_attr($uid); ?>-telefono"><?php esc_html_e('Teléfono', 'boilerplate'); ?> <span class="req">*</span></label>
                    <input type="tel" id="<?php echo esc_attr($uid); ?>-telefono" name="telefono" required autocomplete="tel" inputmode="tel">
                </div>

                <div class="jlb-admform__field">
                    <label for="<?php echo esc_attr($uid); ?>-grado"><?php esc_html_e('Grado al que postula', 'boilerplate'); ?> <span class="req">*</span></label>
                    <select id="<?php echo esc_attr($uid); ?>-grado" name="grado" required>
                        <option value=""><?php esc_html_e('Selecciona un nivel', 'boilerplate'); ?></option>
                        <?php foreach ($grados as $g): ?>
                            <option value="<?php echo esc_attr($g); ?>"><?php echo esc_html($g); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="jlb-admform__field jlb-admform__field--full">
                    <label for="<?php echo esc_attr($uid); ?>-mensaje"><?php esc_html_e('Mensaje', 'boilerplate'); ?></label>
                    <textarea id="<?php echo esc_attr($uid); ?>-mensaje" name="mensaje" rows="4"></textarea>
                </div>
            </div>

            <div class="jlb-admform__consents">
                <label class="jlb-admform__check">
                    <input type="checkbox" name="consent_datos" value="1" required>
                    <span><?php esc_html_e('Acepto que el Colegio Jean Le Boulch almacene y procese mis datos personales para gestionar mi solicitud.', 'boilerplate'); ?> <span class="req">*</span></span>
                </label>
            </div>

            <?php if ($recaptcha_kp): ?>
                <div class="jlb-admform__recaptcha g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_kp); ?>"></div>
            <?php endif; ?>

            <div class="jlb-admform__actions">
                <button type="submit" class="jlb-admform__submit"><?php esc_html_e('Enviar solicitud', 'boilerplate'); ?></button>
            </div>

            <p class="jlb-admform__status" data-jlb-admform-status role="status" aria-live="polite" hidden></p>
        </form>
    </div>
</section>
<?php if ($recaptcha_kp): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
