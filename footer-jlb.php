<?php
/**
 * Footer JLB — invocado vía get_footer('jlb').
 *
 * Lee datos desde la Options Page "Ajustes del sitio" (ACF Pro) con fallback
 * al contenido del diseño Figma. Si ACF Pro no está activo o las opciones
 * están vacías, se renderiza el contenido por defecto para no romper el QA visual.
 *
 * Helpers usados: jlb_footer_get(), jlb_footer_copy() (inc/footer-options.php).
 */

if (!defined('ABSPATH')) exit;

/**
 * Devuelve el glifo SVG (blanco, currentColor) de una red social por nombre.
 * Markup propio y confiable → se imprime crudo. Si no hay match, '' (el caller
 * cae al label de texto como fallback).
 */
if (!function_exists('jlb_footer_social_icon')) {
    function jlb_footer_social_icon($name) {
        switch (strtolower(trim((string) $name))) {
            case 'instagram':
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true" focusable="false">'
                    . '<rect x="3" y="3" width="18" height="18" rx="5.2" stroke="currentColor" stroke-width="1.9"/>'
                    . '<circle cx="12" cy="12" r="4.1" stroke="currentColor" stroke-width="1.9"/>'
                    . '<circle cx="17.4" cy="6.6" r="1.25" fill="currentColor"/></svg>';
            case 'facebook':
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false">'
                    . '<path d="M13.7 21v-7.3h2.45l.37-2.85H13.7V8.97c0-.82.23-1.38 1.41-1.38h1.5V5.04c-.26-.03-1.15-.11-2.18-.11-2.16 0-3.64 1.32-3.64 3.74v2.09H8.33v2.85h2.46V21z"/></svg>';
            case 'youtube':
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false">'
                    . '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.76-1.77C18.27 5 12 5 12 5s-6.27 0-7.84.43A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.76 1.77C5.73 19 12 19 12 19s6.27 0 7.84-.43a2.5 2.5 0 0 0 1.76-1.77A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8ZM10 15V9l5.2 3z"/></svg>';
            case 'tiktok':
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false">'
                    . '<path d="M16.5 3c.3 2 1.5 3.4 3.5 3.6v2.5c-1.2.1-2.4-.2-3.5-.8v5.9c0 3-2.2 5.3-5.1 5.3A5.1 5.1 0 0 1 6.3 14c0-2.9 2.6-5.2 5.6-4.7v2.6a2.5 2.5 0 0 0-2 .6 2.4 2.4 0 0 0 1.6 4.2c1.3 0 2.3-1 2.3-2.4V3z"/></svg>';
            default:
                return '';
        }
    }
}

$asset_base = get_template_directory_uri() . '/assets/figma-home/';

// Logo del footer: prioriza ACF, cae al logo del header, finalmente al SVG estático.
$logo_id   = function_exists('get_field') ? (int) get_field('jlb_logo_footer', 'option') : 0;
if (!$logo_id && function_exists('get_field')) {
    $logo_id = (int) get_field('logo_header', 'option');
}
$logo_url  = $logo_id ? wp_get_attachment_url($logo_id) : ($asset_base . 'logo.svg');

// Textos con fallback al diseño.
$address_title = jlb_footer_get('jlb_footer_address_title', __('Visítanos en:', 'boilerplate'));
$address       = jlb_footer_get('jlb_footer_address', 'Jr. Rodrigo de Triana 150-154, Santa Patricia 3ra Etapa, La Molina');
$phones_title  = jlb_footer_get('jlb_footer_phones_title', __('Llámanos:', 'boilerplate'));
$socials_title = jlb_footer_get('jlb_footer_socials_title', __('Síguenos en:', 'boilerplate'));
$email_title   = jlb_footer_get('jlb_footer_email_title', __('Escríbenos:', 'boilerplate'));
$email         = jlb_footer_get('jlb_footer_email', 'informes@jlb.edu.pe');

// Teléfonos: lee repeater ACF, sino fallback Figma.
$phones = array();
if (function_exists('have_rows') && have_rows('jlb_footer_phones', 'option')) {
    while (have_rows('jlb_footer_phones', 'option')) {
        the_row();
        $label    = get_sub_field('label');
        $number   = get_sub_field('number');
        $whatsapp = get_sub_field('whatsapp');
        if (!$number) continue;
        $phones[] = array(
            'label'    => (string) $label,
            'number'   => (string) $number,
            'whatsapp' => (string) $whatsapp,
        );
    }
}
if (empty($phones)) {
    $phones = array(
        array('label' => __('Admisión', 'boilerplate'),  'number' => '976-369-407', 'whatsapp' => '51976369407'),
        array('label' => __('Talleres', 'boilerplate'),  'number' => '976-369-417', 'whatsapp' => '51976369417'),
        array('label' => __('Consultas', 'boilerplate'), 'number' => '976-369-496', 'whatsapp' => '51976369496'),
    );
}

// Redes: ACF repeater, sino fallback.
$socials = array();
if (function_exists('have_rows') && have_rows('jlb_footer_socials', 'option')) {
    while (have_rows('jlb_footer_socials', 'option')) {
        the_row();
        $name  = (string) get_sub_field('name');
        $label = (string) get_sub_field('label');
        $url   = (string) get_sub_field('url');
        if (!$url) continue;
        $socials[] = array(
            'name'  => $name,
            'label' => $label !== '' ? $label : strtoupper(substr($name, 0, 2)),
            'url'   => $url,
        );
    }
}
if (empty($socials)) {
    $socials = array(
        array('name' => 'instagram', 'label' => 'IG', 'url' => 'https://www.instagram.com/'),
        array('name' => 'facebook',  'label' => 'FB', 'url' => 'https://www.facebook.com/'),
    );
}

// Enlaces legales: ACF repeater, sino fallback.
$legal = array();
if (function_exists('have_rows') && have_rows('jlb_footer_legal', 'option')) {
    while (have_rows('jlb_footer_legal', 'option')) {
        the_row();
        $label = (string) get_sub_field('label');
        $url   = (string) get_sub_field('url');
        if (!$label) continue;
        $legal[] = array('label' => $label, 'url' => $url);
    }
}
if (empty($legal)) {
    $legal = array(
        array('label' => __('Política de privacidad', 'boilerplate'), 'url' => '#privacidad'),
        array('label' => __('Política de cookies', 'boilerplate'),    'url' => '#cookies'),
        array('label' => __('Términos y condiciones', 'boilerplate'), 'url' => '#terminos'),
    );
}

// Copy del bottom (resuelve {year}).
$copy = jlb_footer_copy(sprintf(
    '%s %s',
    date_i18n('Y'),
    __('Todos los derechos reservados - Colegio Jean Le Boulch La Molina', 'boilerplate')
));
?>
    </main><?php // /#contenido abierto en header-jlb.php ?>

    <footer class="jlb-footer" id="contacto">
        <div class="jlb-footer__inner">
            <!-- Columna 1: logo -->
            <img class="jlb-footer__logo"
                src="<?php echo esc_url($logo_url); ?>"
                alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                width="311" height="61">

            <!-- Columna 2 (centro): Llámanos + Síguenos + Escríbenos -->
            <div class="jlb-footer__center">
                <?php if (!empty($phones)): ?>
                    <div class="jlb-footer__block">
                        <h2><?php echo esc_html($phones_title); ?></h2>
                        <?php foreach ($phones as $phone): ?>
                            <p class="jlb-footer__phone">
                                <?php if ($phone['label']): ?>
                                    <strong><?php echo esc_html($phone['label']); ?>:</strong>
                                <?php endif; ?>
                                <?php if ($phone['whatsapp']): ?>
                                    <a href="<?php echo esc_url('https://api.whatsapp.com/send/?phone=' . preg_replace('/\D/', '', $phone['whatsapp'])); ?>"
                                        target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($phone['number']); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($phone['number']); ?>
                                <?php endif; ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($socials)): ?>
                    <div class="jlb-footer__inline">
                        <span class="jlb-footer__label"><?php echo esc_html($socials_title); ?></span>
                        <div class="jlb-footer__socials">
                            <?php foreach ($socials as $social):
                                $icon = jlb_footer_social_icon($social['name']);
                            ?>
                                <a href="<?php echo esc_url($social['url']); ?>"
                                    target="_blank" rel="noopener noreferrer"
                                    aria-label="<?php echo esc_attr(ucfirst($social['name'])); ?>">
                                    <?php
                                    // SVG propio (confiable) o fallback a texto del label.
                                    echo $icon ? $icon : esc_html($social['label']);
                                    ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($email): ?>
                    <div class="jlb-footer__inline">
                        <span class="jlb-footer__label"><?php echo esc_html($email_title); ?></span>
                        <a class="jlb-footer__email" href="<?php echo esc_url('mailto:' . $email); ?>">
                            <?php echo esc_html($email); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Columna 3 (derecha): Visítanos -->
            <?php if ($address): ?>
                <div class="jlb-footer__visit">
                    <h2><?php echo esc_html($address_title); ?></h2>
                    <p><?php echo esc_html($address); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="jlb-footer__bottom">
            <?php // Onda orgánica: cresta más alta a la izquierda, como en Figma. ?>
            <svg class="jlb-footer__wave" viewBox="0 0 1440 44" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="jlb-footer-wave-grad" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#614794"/>
                        <stop offset="0.52" stop-color="#993356"/>
                        <stop offset="1" stop-color="#c92323"/>
                    </linearGradient>
                </defs>
                <path d="M0,9 C 300,1 520,37 880,35 C 1160,33.5 1320,21 1440,25 L1440,44 L0,44 Z" fill="url(#jlb-footer-wave-grad)"/>
            </svg>

            <div class="jlb-footer__bottom-inner">
                <p><?php echo esc_html($copy); ?></p>
                <nav class="jlb-footer__legal" aria-label="<?php esc_attr_e('Enlaces legales', 'boilerplate'); ?>">
                    <?php foreach ($legal as $link): ?>
                        <a href="<?php echo esc_url($link['url']); ?>">
                            <?php echo esc_html($link['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>

</html>
