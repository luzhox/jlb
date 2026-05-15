<?php
/**
 * Atom · Lucky Cube
 *
 * Cubo decorativo (gradiente azul Kresna + radius 22 + sombra dramática + rotación).
 * Uso autorizado: footer (definido), CTA principal (Fase 2), página 404.
 *
 * Args (todos opcionales):
 *   - size:     'sm' | 'md' | 'lg'   (72 / 96 / 120 px). Default 'md'.
 *   - mark:     string. Marca interior centrada. Default 'K'.
 *   - rotation: number (deg). Default -10.
 *   - variant:  'light' | 'dark'.    Reservado para futuro. Default 'light'.
 *   - class:    string adicional aplicada al wrapper.
 *   - aria_hidden: bool. Default true (es decorativo).
 *
 * Uso:
 *   get_template_part('template-parts/atoms/lucky-cube', null, [
 *       'size' => 'md', 'mark' => 'K', 'rotation' => -10
 *   ]);
 */

$args         = $args ?? [];
$size         = $args['size']        ?? 'md';
$mark         = $args['mark']        ?? 'K';
$rotation     = isset($args['rotation']) ? (float) $args['rotation'] : -10;
$variant      = $args['variant']     ?? 'light';
$extra_class  = $args['class']       ?? '';
$aria_hidden  = $args['aria_hidden'] ?? true;

$size_map = array(
    'sm' => array('px' => 72,  'inner' => 18, 'mark' => 22),
    'md' => array('px' => 96,  'inner' => 22, 'mark' => 30),
    'lg' => array('px' => 120, 'inner' => 28, 'mark' => 38),
);
$dim = $size_map[$size] ?? $size_map['md'];

$style = sprintf(
    'width:%1$dpx;height:%1$dpx;border-radius:%2$dpx;transform:rotate(%3$sdeg);',
    $dim['px'],
    $dim['inner'],
    rtrim(rtrim(number_format($rotation, 2, '.', ''), '0'), '.')
);

$classes = trim('lucky-cube lucky-cube--' . $size . ' ' . $extra_class);
?>
<div
    class="<?php echo esc_attr($classes); ?>"
    style="<?php echo esc_attr($style); ?>"
    <?php if ($aria_hidden): ?>aria-hidden="true"<?php endif; ?>
    data-lucky-cube
>
    <?php if ($mark !== ''): ?>
        <span
            class="lucky-cube__mark"
            style="font-size:<?php echo esc_attr($dim['mark']); ?>px;"
        ><?php echo esc_html($mark); ?></span>
    <?php endif; ?>
</div>

<?php
// Estilos del cube — declarados una sola vez por render mediante un flag estático.
// Vive aquí para que el atom sea autosuficiente (no obliga a editar SASS o main.css).
if (!isset($GLOBALS['__bp_lucky_cube_styled'])) {
    $GLOBALS['__bp_lucky_cube_styled'] = true;
    ?>
    <style>
        .lucky-cube {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-cube);
            box-shadow: var(--shadow-cube);
            color: rgb(255 255 255 / 0.85);
            position: relative;
            isolation: isolate;
        }
        .lucky-cube__mark {
            font-family: var(--font-display);
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.04em;
            user-select: none;
        }
        @media (prefers-reduced-motion: reduce) {
            .lucky-cube { transition: none !important; }
        }
    </style>
    <?php
}
