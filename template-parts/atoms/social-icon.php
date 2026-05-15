<?php
/**
 * Atom · Social Icon
 *
 * Renderiza un anchor con un SVG inline para los 4 canales sociales del footer
 * Kresna: discord | x | linkedin | github.
 *
 * Args:
 *   - network: 'discord' | 'x' | 'linkedin' | 'github'  (REQUERIDO)
 *   - url:     string. Si vacío → no renderiza nada.
 *   - label:   string accesible (aria-label). Si vacío usa el nombre de la red.
 *   - class:   string adicional aplicada al anchor.
 *   - size:    int (px). Default 20.
 *
 * Uso:
 *   get_template_part('template-parts/atoms/social-icon', null, [
 *       'network' => 'discord',
 *       'url'     => 'https://discord.gg/kresna',
 *   ]);
 */

$args     = $args ?? [];
$network  = $args['network'] ?? '';
$url      = trim((string) ($args['url'] ?? ''));
$label    = $args['label']   ?? '';
$extra    = $args['class']   ?? '';
$size     = isset($args['size']) ? (int) $args['size'] : 20;

if ($url === '' || $network === '') return;

// SVG paths oficiales (simpleicons / brand guidelines). currentColor para hereditar.
// Networks soportados: discord, x, twitter (alias x), linkedin, github,
// facebook, instagram, youtube, tiktok, whatsapp.
$icons = array(
    'discord'  => array(
        'label' => 'Discord',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M19.27 5.33C17.94 4.71 16.5 4.26 15 4a.09.09 0 0 0-.07.03c-.18.33-.39.76-.53 1.09a16.09 16.09 0 0 0-4.8 0c-.14-.34-.35-.76-.54-1.09c-.01-.02-.04-.03-.07-.03c-1.5.26-2.93.71-4.27 1.33c-.01 0-.02.01-.03.02c-2.72 4.07-3.47 8.03-3.1 11.95c0 .02.01.04.03.05c1.8 1.32 3.53 2.12 5.24 2.65c.03.01.06 0 .07-.02c.4-.55.76-1.13 1.07-1.74c.02-.04 0-.08-.04-.09c-.57-.22-1.11-.48-1.64-.78c-.04-.02-.04-.08-.01-.11c.11-.08.22-.17.33-.25c.02-.02.05-.02.07-.01c3.44 1.57 7.15 1.57 10.55 0c.02-.01.05-.01.07.01c.11.09.22.17.33.26c.04.03.04.09-.01.11c-.52.31-1.07.56-1.64.78c-.04.01-.05.06-.04.09c.32.61.68 1.19 1.07 1.74c.03.01.06.02.09.01c1.72-.53 3.45-1.33 5.25-2.65c.02-.01.03-.03.03-.05c.44-4.53-.73-8.46-3.1-11.95c-.01-.01-.02-.02-.04-.02zM8.52 14.91c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.84 2.12-1.89 2.12zm6.97 0c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.83 2.12-1.89 2.12z"/>',
    ),
    'x'        => array(
        'label' => 'X (Twitter)',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117l11.966 15.644Z"/>',
    ),
    'twitter'  => array( // alias clásico de X (algunos admin todavía lo nombran así)
        'label' => 'Twitter',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117l11.966 15.644Z"/>',
    ),
    'linkedin' => array(
        'label' => 'LinkedIn',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z"/>',
    ),
    'github'   => array(
        'label' => 'GitHub',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>',
    ),
    'facebook' => array(
        'label' => 'Facebook',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M9.198 21.5h4v-8.01h3.604l.396-3.98h-4V7.5a1 1 0 0 1 1-1h3v-4h-3a5 5 0 0 0-5 5v2.51h-2l-.396 3.98h2.396v8.01Z"/>',
    ),
    'instagram'=> array(
        'label' => 'Instagram',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>',
    ),
    'youtube'  => array(
        'label' => 'YouTube',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
    ),
    'tiktok'   => array(
        'label' => 'TikTok',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>',
    ),
    'whatsapp' => array(
        'label' => 'WhatsApp',
        'view'  => '0 0 24 24',
        'path'  => '<path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.413c-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>',
    ),
);

// Detección automática de network desde URL (útil cuando el callsite tiene
// solo la URL del menú WP — ver footer.php). Permite que el admin gestione
// las redes desde Apariencia → Menús sin tocar Customizer.
if ($network === 'auto' || $network === '') {
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $map  = array(
        'discord.com'   => 'discord',
        'discord.gg'    => 'discord',
        'x.com'         => 'x',
        'twitter.com'   => 'twitter',
        'linkedin.com'  => 'linkedin',
        'github.com'    => 'github',
        'facebook.com'  => 'facebook',
        'fb.com'        => 'facebook',
        'instagram.com' => 'instagram',
        'youtube.com'   => 'youtube',
        'youtu.be'      => 'youtube',
        'tiktok.com'    => 'tiktok',
        'wa.me'         => 'whatsapp',
        'whatsapp.com'  => 'whatsapp',
    );
    foreach ($map as $needle => $slug) {
        if (strpos($host, $needle) !== false) { $network = $slug; break; }
    }
    if ($network === 'auto' || $network === '') return;
}

if (!isset($icons[$network])) return;

$icon         = $icons[$network];
$display_name = $icon['label'];
$aria         = $label !== '' ? $label : sprintf(__('%s (opens in new tab)', 'boilerplate'), $display_name);
$classes      = trim('social-icon social-icon--' . $network . ' ' . $extra);
?>
<a
    href="<?php echo esc_url($url); ?>"
    class="<?php echo esc_attr($classes); ?>"
    aria-label="<?php echo esc_attr($aria); ?>"
    rel="noopener noreferrer me"
    target="_blank"
>
    <svg
        width="<?php echo esc_attr($size); ?>"
        height="<?php echo esc_attr($size); ?>"
        viewBox="<?php echo esc_attr($icon['view']); ?>"
        fill="currentColor"
        aria-hidden="true"
        focusable="false"
    >
        <?php
        // SVG paths estáticos definidos arriba — seguros para output literal.
        echo $icon['path']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    </svg>
</a>

<?php
if (!isset($GLOBALS['__bp_social_icon_styled'])) {
    $GLOBALS['__bp_social_icon_styled'] = true;
    ?>
    <style>
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-full);
            background: rgb(255 255 255 / 0.10);
            color: rgb(255 255 255 / 0.95);
            transition: background-color var(--duration-fast) var(--ease-out),
                        transform        var(--duration-fast) var(--ease-out);
        }
        .social-icon:hover { background: rgb(255 255 255 / 0.20); transform: translateY(-1px); }
        .social-icon:focus-visible { outline: 2px solid rgb(255 255 255 / 0.85); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) {
            .social-icon { transition: none; }
            .social-icon:hover { transform: none; }
        }
    </style>
    <?php
}
