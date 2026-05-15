<?php
/**
 * Atom · Input (shadcn)
 *
 * Input estándar con label arriba, focus ring shadcn, helper / error opcionales.
 * Lo usan: footer subscribe (Fase 1), módulo `formulario` (Fase 2).
 *
 * Args:
 *   - name:          string (REQUERIDO si type≠search; usado para id por defecto)
 *   - id:            string. Default: derivado de name.
 *   - type:          'text' | 'email' | 'tel' | 'url' | 'search' | 'password'.
 *                    Default 'text'.
 *   - label:         string visible. Si vacío + aria_label setado → label SR-only.
 *   - aria_label:    string. Fallback accesible si no hay label visible.
 *   - placeholder:   string.
 *   - value:         string.
 *   - required:      bool. Default false.
 *   - autocomplete:  string. Default ''.
 *   - helper:        string. Texto de ayuda debajo del input.
 *   - error:         string. Si se pasa, sustituye al helper y marca aria-invalid.
 *   - class:         clases extra al input.
 *   - wrapper_class: clases extra al wrapper <div class="form-field">.
 *   - inline_button: bool. Si true, el input no incluye el botón pero puede
 *                    convivir con un sibling adyacente (caso footer subscribe).
 *
 * Uso:
 *   get_template_part('template-parts/atoms/input', null, [
 *       'name'        => 'email',
 *       'type'        => 'email',
 *       'label'       => 'Email',
 *       'placeholder' => 'tu@email.com',
 *       'required'    => true,
 *       'helper'      => 'Te enviaremos un código de verificación.',
 *   ]);
 */

$args = $args ?? [];

$name         = $args['name']         ?? '';
$id           = $args['id']           ?? ($name ? 'bp-input-' . sanitize_html_class($name) : 'bp-input-' . wp_unique_id());
$type         = $args['type']         ?? 'text';
$label        = $args['label']        ?? '';
$aria_label   = $args['aria_label']   ?? '';
$placeholder  = $args['placeholder']  ?? '';
$value        = $args['value']        ?? '';
$required     = !empty($args['required']);
$autocomplete = $args['autocomplete'] ?? '';
$helper       = $args['helper']       ?? '';
$error        = $args['error']        ?? '';
$extra        = $args['class']        ?? '';
$wrapper      = $args['wrapper_class']?? '';

$has_error    = $error !== '';
$describedby  = array();
$helper_id    = $id . '-helper';
$error_id     = $id . '-error';

if ($has_error)        { $describedby[] = $error_id; }
elseif ($helper !== '') { $describedby[] = $helper_id; }

$input_classes  = trim('input-shadcn ' . $extra);
$wrapper_classes = trim('form-field ' . $wrapper);
?>
<div class="<?php echo esc_attr($wrapper_classes); ?>">
    <?php if ($label !== ''): ?>
        <label for="<?php echo esc_attr($id); ?>" class="form-label">
            <?php echo esc_html($label); ?>
            <?php if ($required): ?>
                <span class="form-label__required" aria-hidden="true" style="color:var(--color-destructive);">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php
    // Inferir inputmode automáticamente para teclados móviles más útiles.
    $inputmode_map = array(
        'email'    => 'email',
        'tel'      => 'tel',
        'url'      => 'url',
        'search'   => 'search',
        'number'   => 'numeric',
    );
    $inputmode = $inputmode_map[$type] ?? '';
    ?>
    <input
        type="<?php echo esc_attr($type); ?>"
        id="<?php echo esc_attr($id); ?>"
        <?php if ($name): ?>name="<?php echo esc_attr($name); ?>"<?php endif; ?>
        class="<?php echo esc_attr($input_classes); ?>"
        <?php if ($placeholder): ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php endif; ?>
        <?php if ($value !== ''): ?>value="<?php echo esc_attr($value); ?>"<?php endif; ?>
        <?php if ($required): ?>required aria-required="true"<?php endif; ?>
        <?php if ($autocomplete): ?>autocomplete="<?php echo esc_attr($autocomplete); ?>"<?php endif; ?>
        <?php if ($inputmode): ?>inputmode="<?php echo esc_attr($inputmode); ?>"<?php endif; ?>
        <?php if ($label === '' && $aria_label !== ''): ?>aria-label="<?php echo esc_attr($aria_label); ?>"<?php endif; ?>
        <?php if ($describedby): ?>aria-describedby="<?php echo esc_attr(implode(' ', $describedby)); ?>"<?php endif; ?>
        <?php if ($has_error): ?>aria-invalid="true"<?php endif; ?>
    >

    <?php if ($has_error): ?>
        <p id="<?php echo esc_attr($error_id); ?>" class="form-error" role="alert">
            <?php echo esc_html($error); ?>
        </p>
    <?php elseif ($helper !== ''): ?>
        <p id="<?php echo esc_attr($helper_id); ?>" class="form-helper">
            <?php echo esc_html($helper); ?>
        </p>
    <?php endif; ?>
</div>
