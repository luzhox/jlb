<?php
/**
 * Atom · Dark mode toggle
 *
 * Botón con SVG sol/luna inline. La lógica corre en src/darkMode.js, que:
 *   - lee localStorage('theme'): 'light' | 'dark' | 'system'
 *   - aplica data-theme="dark" en <html> cuando corresponde
 *   - escucha clicks en [data-bp-dark-toggle]
 *
 * Args (opcionales):
 *   - class: clases extra para el botón
 *   - size:  tamaño del icono en px (default 18)
 */

$args  = $args ?? [];
$extra = $args['class'] ?? '';
$size  = isset($args['size']) ? (int) $args['size'] : 18;

$classes = trim('dark-toggle ' . $extra);
?>
<button
    type="button"
    class="<?php echo esc_attr($classes); ?>"
    data-bp-dark-toggle
    aria-label="<?php echo esc_attr__('Toggle dark mode', 'boilerplate'); ?>"
    aria-pressed="false"
    title="<?php echo esc_attr__('Toggle dark mode', 'boilerplate'); ?>"
>
    <svg
        class="dark-toggle__icon dark-toggle__icon--sun"
        width="<?php echo esc_attr($size); ?>"
        height="<?php echo esc_attr($size); ?>"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v2"></path>
        <path d="M12 20v2"></path>
        <path d="m4.93 4.93 1.41 1.41"></path>
        <path d="m17.66 17.66 1.41 1.41"></path>
        <path d="M2 12h2"></path>
        <path d="M20 12h2"></path>
        <path d="m6.34 17.66-1.41 1.41"></path>
        <path d="m19.07 4.93-1.41 1.41"></path>
    </svg>
    <svg
        class="dark-toggle__icon dark-toggle__icon--moon"
        width="<?php echo esc_attr($size); ?>"
        height="<?php echo esc_attr($size); ?>"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
    <span class="sr-text"><?php echo esc_html__('Toggle dark mode', 'boilerplate'); ?></span>
</button>

<?php
if (!isset($GLOBALS['__bp_dark_toggle_styled'])) {
    $GLOBALS['__bp_dark_toggle_styled'] = true;
    ?>
    <style>
        .dark-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: transparent;
            color: var(--color-foreground);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: background-color var(--duration-fast) var(--ease-out),
                        color            var(--duration-fast) var(--ease-out);
        }
        .dark-toggle:hover { background: var(--color-muted); }
        .dark-toggle:focus-visible {
            outline: 2px solid var(--color-ring);
            outline-offset: 2px;
        }
        .dark-toggle__icon { display: none; }
        .dark-toggle__icon--sun  { display: block; }
        :root[data-theme="dark"] .dark-toggle__icon--sun  { display: none; }
        :root[data-theme="dark"] .dark-toggle__icon--moon { display: block; }
        .sr-text {
            position: absolute;
            width: 1px; height: 1px;
            padding: 0; margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }
    </style>
    <?php
}
