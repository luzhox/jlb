<?php
/**
 * Módulo: Equipo — Fase 3 / shadcn × Kresna
 *
 * Grid de personas. Cada miembro:
 *   - Foto rounded-2xl (28px), aspect 4/5, object-cover
 *   - Nombre + cargo + bio (line-clamp-3)
 *   - Redes vía atom social-icon con auto-detect por URL
 *
 * Layout: 1 col mobile → 2 col sm → 3 col md → 4 col lg.
 *
 * Sin migración ACF (mantenemos los campos linkedin/twitter/instagram del
 * grupo `redes_sociales`). El atom social-icon tiene auto-detect por URL,
 * con lo cual si en el futuro se pasa a un repeater de URLs sueltas, el
 * código aquí no necesita cambiar (solo el bucle de redes).
 */

$titulo    = get_sub_field('titulo');
$subtitulo = get_sub_field('subtitulo');
$items     = get_sub_field('items');

if (!$items) return;
?>
<section class="equipo py-16 lg:py-24">
    <div class="container">
        <?php if ($titulo || $subtitulo): ?>
            <header class="mb-10 lg:mb-14 max-w-2xl">
                <?php if ($titulo): ?>
                    <h2 class="equipo__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                        <?php echo esc_html($titulo); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($subtitulo): ?>
                    <p class="equipo__subtitulo mt-3 text-lg text-muted-foreground">
                        <?php echo esc_html($subtitulo); ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <div class="equipo__grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8" data-gsap-batch=".equipo__miembro">
            <?php
            $i = 0;
            foreach ($items as $item):
                $delay  = number_format($i * 0.08, 2, '.', '');
                $nombre = isset($item['nombre']) ? (string) $item['nombre'] : '';
                $cargo  = isset($item['cargo']) ? (string) $item['cargo'] : '';
                $bio    = isset($item['bio']) ? (string) $item['bio'] : '';
                $foto   = $item['foto'] ?? null;
                $redes  = isset($item['redes_sociales']) && is_array($item['redes_sociales']) ? $item['redes_sociales'] : array();

                $foto_w = (!empty($foto['width']))  ? (int) $foto['width']  : 480;
                $foto_h = (!empty($foto['height'])) ? (int) $foto['height'] : 600;

                $redes_links = array_filter(array(
                    'linkedin'  => $redes['linkedin']  ?? '',
                    'twitter'   => $redes['twitter']   ?? '',
                    'instagram' => $redes['instagram'] ?? '',
                ));
            ?>
                <article
                    class="equipo__miembro group flex flex-col"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <?php if (!empty($foto) && !empty($foto['url'])): ?>
                        <div class="equipo__foto-wrap relative overflow-hidden rounded-2xl bg-muted mb-4" style="aspect-ratio: 4/5;">
                            <img
                                class="equipo__foto absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                src="<?php echo esc_url($foto['url']); ?>"
                                alt="<?php echo esc_attr($nombre); ?>"
                                width="<?php echo esc_attr($foto_w); ?>"
                                height="<?php echo esc_attr($foto_h); ?>"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    <?php endif; ?>

                    <div class="equipo__info flex flex-col gap-1">
                        <?php if ($nombre): ?>
                            <h3 class="equipo__nombre text-lg font-semibold text-foreground leading-snug">
                                <?php echo esc_html($nombre); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ($cargo): ?>
                            <p class="equipo__cargo text-sm text-brand-600 font-medium">
                                <?php echo esc_html($cargo); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($bio): ?>
                            <p class="equipo__bio text-sm text-muted-foreground line-clamp-3 mt-2 leading-relaxed">
                                <?php echo esc_html($bio); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($redes_links)): ?>
                            <ul class="equipo__redes flex items-center gap-2 mt-3 list-none p-0">
                                <?php foreach ($redes_links as $key => $url):
                                    // El atom acepta network explícito (linkedin|twitter|x|instagram|...)
                                    // pero también es robusto vía auto-detect por URL.
                                    ?>
                                    <li>
                                        <?php
                                        get_template_part('template-parts/atoms/social-icon', null, array(
                                            'network' => $key,
                                            'url'     => $url,
                                            'label'   => sprintf(__('%1$s en %2$s', 'boilerplate'), $nombre, ucfirst($key)),
                                            'class'   => 'equipo__red',
                                            'size'    => 16,
                                        ));
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </article>
                <?php $i++; endforeach; ?>
        </div>
    </div>
</section>

<?php
// Override social-icon para variante "equipo" — los iconos del social-icon
// vienen blancos sobre fondo blanco/10 (footer dark). En equipo (light) los
// queremos en muted-foreground sobre bg-muted.
if (empty($GLOBALS['__bp_equipo_redes_styled'])):
    $GLOBALS['__bp_equipo_redes_styled'] = true;
?>
<style>
.equipo__redes .social-icon {
    width: 32px;
    height: 32px;
    background: var(--color-muted);
    color: var(--color-muted-foreground);
}
.equipo__redes .social-icon:hover {
    background: var(--color-brand-50);
    color: var(--color-brand-600);
}
[data-theme="dark"] .equipo__redes .social-icon {
    background: rgb(255 255 255 / 0.06);
    color: var(--color-muted-foreground);
}
[data-theme="dark"] .equipo__redes .social-icon:hover {
    background: rgb(255 255 255 / 0.12);
    color: var(--color-brand-300);
}
</style>
<?php endif; ?>
