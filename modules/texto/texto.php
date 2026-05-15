<?php
/**
 * Módulo: Texto — Fase 3 / shadcn × Kresna
 *
 * Bloque WYSIWYG largo. Dos layouts segun el campo `imagen`:
 *   - Sin imagen → container narrow (~700px), tipografía DM Sans body,
 *     h2 opcional con color picker.
 *   - Con imagen → grid 1fr 1fr en lg+, imagen izquierda + texto derecha.
 *     En mobile se apila (imagen primero por defecto del flow).
 *
 * El campo `color` actúa solo sobre el primer h2 generado por el WYSIWYG
 * (selector :first-child). Si no se rellena se usa text-foreground.
 *
 * Sin migración ACF — refactor visual puro. Mantiene .container .texto
 * como wrapper para que SCSS legacy de prose siga aplicando si existe.
 */

$imagen = get_sub_field('imagen');
$color  = trim((string) get_sub_field('color'));
$texto  = get_sub_field('texto');

if (!$texto && empty($imagen)) return;

$has_imagen = !empty($imagen) && !empty($imagen['url']);
$uid        = uniqid('texto-');
?>
<section class="texto py-16 lg:py-24" data-gsap="fade-up">
    <div class="<?php echo $has_imagen ? 'container' : 'container max-w-3xl'; ?>">
        <?php if ($has_imagen): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
                <figure class="texto__media order-first">
                    <img
                        class="w-full h-auto rounded-xl object-cover"
                        src="<?php echo esc_url($imagen['url']); ?>"
                        alt="<?php echo esc_attr($imagen['alt']); ?>"
                        <?php if (!empty($imagen['width'])): ?>width="<?php echo esc_attr($imagen['width']); ?>"<?php endif; ?>
                        <?php if (!empty($imagen['height'])): ?>height="<?php echo esc_attr($imagen['height']); ?>"<?php endif; ?>
                        loading="lazy"
                        decoding="async"
                    >
                </figure>

                <div
                    class="texto__contenido <?php echo esc_attr($uid); ?> prose prose-neutral max-w-none text-base lg:text-lg leading-relaxed text-foreground"
                    data-gsap="fade-up"
                    data-gsap-delay="0.10"
                >
                    <?php echo wp_kses_post($texto); ?>
                </div>
            </div>
        <?php else: ?>
            <div
                class="texto__contenido <?php echo esc_attr($uid); ?> prose prose-neutral max-w-none text-base lg:text-lg leading-relaxed text-foreground"
            >
                <?php echo wp_kses_post($texto); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Estilos embebidos: solo cuando se rellena el color picker. Aplica al primer
// heading del WYSIWYG (h2 o h3) y a la tipografía display de los headings
// del bloque. UID por instancia para evitar colisiones entre varios bloques
// texto en la misma página.
if ($color !== '' || true):
    // Print global text styles ONCE per request (font-display + spacing reset)
    if (empty($GLOBALS['__bp_texto_global_styled'])):
        $GLOBALS['__bp_texto_global_styled'] = true;
    ?>
    <style>
    .texto__contenido h1,
    .texto__contenido h2,
    .texto__contenido h3,
    .texto__contenido h4 {
        font-family: var(--font-display);
        font-weight: 600;
        letter-spacing: -0.02em;
        color: var(--color-foreground);
        line-height: 1.2;
    }
    .texto__contenido h2 { font-size: clamp(1.75rem, 1.4rem + 1.5vw, 2.25rem); margin: 0 0 1rem; }
    .texto__contenido h3 { font-size: clamp(1.375rem, 1.2rem + 0.8vw, 1.625rem); margin: 1.5rem 0 0.75rem; }
    .texto__contenido p  { margin: 0 0 1.25rem; color: var(--color-foreground); }
    .texto__contenido p:last-child { margin-bottom: 0; }
    .texto__contenido a  { color: var(--color-brand-600); text-decoration: underline; text-underline-offset: 3px; }
    .texto__contenido a:hover { color: var(--color-brand-700); }
    .texto__contenido ul,
    .texto__contenido ol { margin: 0 0 1.25rem; padding-left: 1.5rem; color: var(--color-foreground); }
    .texto__contenido li { margin: 0.25rem 0; }
    .texto__contenido strong { color: var(--color-foreground); font-weight: 600; }
    </style>
    <?php endif; ?>
    <?php if ($color !== ''): ?>
    <style>
    .<?php echo esc_attr($uid); ?> > h1:first-child,
    .<?php echo esc_attr($uid); ?> > h2:first-child,
    .<?php echo esc_attr($uid); ?> > h3:first-child {
        color: <?php echo esc_attr($color); ?>;
    }
    </style>
    <?php endif; ?>
<?php endif; ?>
