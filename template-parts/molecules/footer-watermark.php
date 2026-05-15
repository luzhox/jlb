<?php
/**
 * Molecule · Footer watermark "Kresna"
 *
 * SVG con el texto "Kresna" gigante semitransparente. Lo mide y reposiciona
 * src/footerWatermark.js usando getBBox() para que ocupe siempre el ancho del
 * contenedor sin overflow lateral, esperando a document.fonts.ready.
 *
 * Args:
 *   - text: string. Default 'Kresna'.
 */

$args = $args ?? [];
$text = isset($args['text']) ? (string) $args['text'] : 'Kresna';
?>
<div class="footer-watermark" aria-hidden="true" role="presentation" data-bp-watermark>
    <svg
        class="footer-watermark__svg"
        viewBox="0 0 100 20"
        preserveAspectRatio="xMidYMax meet"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
        role="presentation"
        focusable="false"
    >
        <text
            class="footer-watermark__text"
            x="50"
            y="18"
            text-anchor="middle"
            data-bp-watermark-text
            aria-hidden="true"
        ><?php echo esc_html($text); ?></text>
    </svg>
</div>

<?php
if (!isset($GLOBALS['__bp_footer_watermark_styled'])) {
    $GLOBALS['__bp_footer_watermark_styled'] = true;
    ?>
    <style>
        .footer-watermark {
            position: relative;
            width: 100%;
            line-height: 0;
            user-select: none;
            pointer-events: none;
            margin-top: 2rem;
            overflow: hidden;
        }
        .footer-watermark__svg {
            display: block;
            width: 100%;
            height: auto;
            overflow: visible;
        }
        .footer-watermark__text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 18px;            /* JS lo recalcula */
            letter-spacing: -0.06em;
            fill: var(--color-watermark);
            dominant-baseline: alphabetic;
        }
    </style>
    <?php
}
