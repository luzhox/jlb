<?php
/**
 * Template Name: Demo — Todos los módulos
 * Template Post Type: page
 *
 * Página de demostración de los 13 módulos del boilerplate.
 * Seleccionable desde WP Admin → Página → Atributos de página → Plantilla.
 *
 * Estado de rediseño (shadcn × Kresna): COMPLETADO.
 *   ✓ Fase 2: Hero, CTA, Testimonios, Blog, Formulario.
 *   ✓ Fase 3: Acordeón, Texto, Estadísticas, Cards-servicios, Equipo,
 *             Galería, Hero-blog, Post.
 *
 * Todos los módulos usan HTML estático con datos de ejemplo;
 * no requieren configuración ACF para funcionar. Cada sección refleja
 * 1:1 el markup que producen los archivos modules/<x>/<x>.php con los
 * campos ACF rellenos.
 *
 * Rollback granular por módulo:  git tag -l 'pre-fase[23]-*'
 */

get_header();
// header.php ahora trae el masthead Kresna nativo (logo + nav + dark toggle
// + sandwich + backdrop-blur + respeta admin-bar). El menu.php legacy quedó
// archivado como menu.legacy.php (no se incluye desde ningún sitio).
?>

<!-- ══════════════════════════════════════════════════════════════════════════
     1. HERO — modo `imagen` + overline manuscrito (Fase 2 ✓)
     Módulo: modules/hero/hero.php
     ACF: sliderhero (legacy) + tipo_fondo + overline_manuscrito + video_fondo
     Mostrado: tipo_fondo=imagen, overline Caveat, CTA kresna-dark.
     ══════════════════════════════════════════════════════════════════════════ -->
<section
    class="hero hero--imagen relative overflow-hidden flex items-center"
    style="min-height: clamp(480px, 70vh, 720px);"
>
    <img
        class="hero__bg absolute inset-0 w-full h-full object-cover"
        src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=1920&h=1200&fit=crop"
        alt="Equipo trabajando en una oficina moderna con luz natural"
        width="1920" height="1200"
        fetchpriority="high"
        decoding="async"
    >
    <div class="hero__overlay absolute inset-0" style="background: linear-gradient(180deg, rgba(10,10,11,0.55) 0%, rgba(10,10,11,0.75) 100%);" aria-hidden="true"></div>

    <div class="container relative z-10 py-16 lg:py-24">
        <div class="max-w-3xl flex flex-col gap-4">
            <span
                class="hero__overline font-handwritten text-2xl lg:text-3xl text-white/95"
                data-gsap="fade-up"
            >
                Hello, builder.
            </span>

            <div
                class="hero__texto text-white"
                data-gsap="fade-up"
                data-gsap-delay="0.15"
            >
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-semibold tracking-tight leading-[1.05]">
                    Soluciones digitales <span class="text-brand-300">que transforman</span> tu negocio
                </h1>
                <p class="mt-4 text-lg text-white/85 max-w-2xl">
                    Diseñamos, construimos y optimizamos productos web modernos. Desde la primera línea de código hasta el lanzamiento.
                </p>
            </div>

            <div
                class="hero__cta mt-2"
                data-gsap="fade-up"
                data-gsap-delay="0.3"
            >
                <a href="#modulos" class="btn-tech inline-flex items-center gap-2 px-6 py-3 rounded-md bg-foreground text-background font-medium text-sm hover:opacity-90 transition" style="background:#111214;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,0.28),0 2px 8px rgba(0,0,0,0.15);">
                    Conoce nuestros servicios
                </a>
            </div>
        </div>
    </div>

    <div class="vermas absolute bottom-6 left-1/2 -translate-x-1/2 z-20">
        <a href="#modulos"><span></span>Ver módulos</a>
    </div>
</section>

<div id="modulos"></div>

<!-- ══════════════════════════════════════════════════════════════════════════
     2. CTA — 3 variantes (Fase 2 ✓)
     Módulo: modules/cta/cta.php
     ACF: titulo, subtitulo, boton_principal, boton_secundario, alineacion,
          variante (brand|surface|dark), cube_visible, video_fondo
     Mostradas las 3 variantes en sucesión para validación visual.
     ══════════════════════════════════════════════════════════════════════════ -->

<!-- Variante BRAND con lucky cube -->
<section class="container my-12 lg:my-16">
    <div class="cta cta--brand relative overflow-hidden rounded-2xl bg-brand-500 text-white" data-gsap="fade-up">
        <div class="container relative z-10 py-12 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-8 lg:gap-12 items-center">
                <div class="cta__content flex flex-col gap-4 lg:gap-5 text-left items-start">
                    <h2 class="cta__titulo text-3xl lg:text-4xl font-display font-semibold tracking-tight leading-tight max-w-2xl">
                        ¿Listo para llevar tu proyecto al siguiente nivel?
                    </h2>
                    <p class="cta__subtitulo text-lg leading-relaxed opacity-90 max-w-xl">
                        Trabajamos contigo desde la estrategia hasta el lanzamiento. Cuéntanos qué necesitas.
                    </p>
                    <div class="cta__botones flex flex-wrap gap-3 mt-2 justify-start">
                        <a href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-md font-medium text-sm" style="background:#111214;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,0.28),0 2px 8px rgba(0,0,0,0.15);">
                            Solicitar propuesta
                        </a>
                        <a href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-md font-medium text-sm text-white/90 hover:bg-white/10 transition">
                            Ver casos de éxito
                        </a>
                    </div>
                </div>

                <div class="cta__cube hidden lg:flex justify-end items-center pr-2" aria-hidden="true">
                    <div style="width:96px;height:96px;border-radius:22px;transform:rotate(-10deg);background:linear-gradient(135deg,#5b9ffb 0%,#1e5dd7 55%,#1448be 100%);box-shadow:inset 3px 3px 8px rgba(255,255,255,0.35),inset -3px -3px 12px rgba(0,0,0,0.18),8px 14px 28px rgba(20,72,200,0.35);display:flex;align-items:center;justify-content:center;">
                        <span style="font-family:'DM Sans',sans-serif;font-size:42px;font-weight:700;color:#fff;letter-spacing:-0.04em;transform:rotate(10deg);text-shadow:0 3px 6px rgba(0,0,0,0.25);line-height:1;">K</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Variante SURFACE (sin cube) -->
<section class="container my-12 lg:my-16">
    <div class="cta cta--surface relative overflow-hidden rounded-2xl text-foreground" style="background:var(--color-card-soft,#f0f1f5);" data-gsap="fade-up">
        <div class="container relative z-10 py-12 lg:py-20">
            <div class="cta__content flex flex-col gap-4 lg:gap-5 text-left items-start max-w-2xl">
                <h2 class="cta__titulo text-3xl lg:text-4xl font-display font-semibold tracking-tight leading-tight">
                    Plantilla, código y soporte. Todo en un solo lugar.
                </h2>
                <p class="cta__subtitulo text-lg leading-relaxed text-muted-foreground">
                    Una base sólida con ACF, Vite y Tailwind. Empezar nuevos proyectos lleva minutos, no días.
                </p>
                <div class="cta__botones flex flex-wrap gap-3 mt-2">
                    <a href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-md font-medium text-sm bg-foreground text-background hover:opacity-90 transition">
                        Empezar ahora
                    </a>
                    <a href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-md font-medium text-sm border border-border text-foreground hover:bg-card transition">
                        Ver documentación
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Variante DARK con cube -->
<section class="container my-12 lg:my-16">
    <div class="cta cta--dark relative overflow-hidden rounded-2xl bg-foreground text-background" data-gsap="fade-up">
        <div class="container relative z-10 py-12 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-8 lg:gap-12 items-center">
                <div class="cta__content flex flex-col gap-4 lg:gap-5 text-left items-start">
                    <h2 class="cta__titulo text-3xl lg:text-4xl font-display font-semibold tracking-tight leading-tight max-w-2xl">
                        AI moves fast. Stay ahead with us.
                    </h2>
                    <p class="cta__subtitulo text-lg leading-relaxed opacity-80 max-w-xl">
                        Equipos que diseñan, construyen y miden. Conviértete en el referente técnico de tu industria.
                    </p>
                    <div class="cta__botones flex flex-wrap gap-3 mt-2">
                        <a href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-md font-medium text-sm" style="background:#111214;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,0.4),0 2px 8px rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.08);">
                            Solicitar demo
                        </a>
                    </div>
                </div>

                <div class="cta__cube hidden lg:flex justify-end items-center pr-2" aria-hidden="true">
                    <div style="width:96px;height:96px;border-radius:22px;transform:rotate(-10deg);background:linear-gradient(135deg,#5b9ffb 0%,#1e5dd7 55%,#1448be 100%);box-shadow:inset 3px 3px 8px rgba(255,255,255,0.35),inset -3px -3px 12px rgba(0,0,0,0.18),8px 14px 28px rgba(20,72,200,0.45);display:flex;align-items:center;justify-content:center;">
                        <span style="font-family:'DM Sans',sans-serif;font-size:42px;font-weight:700;color:#fff;letter-spacing:-0.04em;transform:rotate(10deg);text-shadow:0 3px 6px rgba(0,0,0,0.25);line-height:1;">K</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     3. ESTADÍSTICAS — Contadores animados con GSAP (Fase 3 ✓)
     Módulo: modules/estadisticas/estadisticas.php
     ACF: titulo, subtitulo, fondo (light|primary|dark), items
     Tres variantes mostradas para validación visual.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="estadisticas estadisticas--light py-16 lg:py-24 bg-muted/50 text-foreground">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="estadisticas__titulo text-3xl lg:text-4xl font-display font-semibold tracking-tight">
                Resultados que hablan por sí solos
            </h2>
            <p class="estadisticas__subtitulo mt-3 text-lg text-muted-foreground">
                Más de una década construyendo soluciones digitales de alto impacto.
            </p>
        </header>

        <div class="estadisticas__grid grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8" data-gsap-batch=".estadisticas__item">
            <div class="estadisticas__item flex flex-col gap-1.5" data-gsap="fade-up" data-gsap-delay="0.00">
                <div class="estadisticas__numero flex items-baseline gap-1 leading-none">
                    <span class="estadisticas__valor text-4xl lg:text-5xl xl:text-6xl font-display font-bold tracking-tight text-brand-600" data-gsap-counter>250</span>
                    <span class="estadisticas__sufijo text-2xl lg:text-3xl font-display font-semibold text-muted-foreground">+</span>
                </div>
                <p class="estadisticas__etiqueta text-base font-semibold mt-2 text-foreground">Proyectos entregados</p>
                <p class="estadisticas__desc text-sm leading-relaxed text-muted-foreground">En 18 países de Latinoamérica</p>
            </div>
            <div class="estadisticas__item flex flex-col gap-1.5" data-gsap="fade-up" data-gsap-delay="0.08">
                <div class="estadisticas__numero flex items-baseline gap-1 leading-none">
                    <span class="estadisticas__valor text-4xl lg:text-5xl xl:text-6xl font-display font-bold tracking-tight text-brand-600" data-gsap-counter>12</span>
                    <span class="estadisticas__sufijo text-2xl lg:text-3xl font-display font-semibold text-muted-foreground">años</span>
                </div>
                <p class="estadisticas__etiqueta text-base font-semibold mt-2 text-foreground">De experiencia</p>
                <p class="estadisticas__desc text-sm leading-relaxed text-muted-foreground">Especializados en WordPress y tecnologías modernas</p>
            </div>
            <div class="estadisticas__item flex flex-col gap-1.5" data-gsap="fade-up" data-gsap-delay="0.16">
                <div class="estadisticas__numero flex items-baseline gap-1 leading-none">
                    <span class="estadisticas__valor text-4xl lg:text-5xl xl:text-6xl font-display font-bold tracking-tight text-brand-600" data-gsap-counter>98</span>
                    <span class="estadisticas__sufijo text-2xl lg:text-3xl font-display font-semibold text-muted-foreground">%</span>
                </div>
                <p class="estadisticas__etiqueta text-base font-semibold mt-2 text-foreground">Clientes satisfechos</p>
                <p class="estadisticas__desc text-sm leading-relaxed text-muted-foreground">Medido con encuestas post-entrega</p>
            </div>
            <div class="estadisticas__item flex flex-col gap-1.5" data-gsap="fade-up" data-gsap-delay="0.24">
                <div class="estadisticas__numero flex items-baseline gap-1 leading-none">
                    <span class="estadisticas__valor text-4xl lg:text-5xl xl:text-6xl font-display font-bold tracking-tight text-brand-600" data-gsap-counter>48</span>
                    <span class="estadisticas__sufijo text-2xl lg:text-3xl font-display font-semibold text-muted-foreground">h</span>
                </div>
                <p class="estadisticas__etiqueta text-base font-semibold mt-2 text-foreground">Tiempo de respuesta</p>
                <p class="estadisticas__desc text-sm leading-relaxed text-muted-foreground">Soporte garantizado para todos los clientes</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     4. CARDS DE SERVICIOS — Hairline shadcn (Fase 3 ✓)
     Módulo: modules/cards-servicios/cards-servicios.php
     ACF: titulo, subtitulo, columnas (2/3/4), items (icono, titulo_card, descripcion, boton)
     Card hairline + icono brand-50 wrapper + link con flecha animada (gap-1→gap-2).
     Toda la card clicable via .stretched-link.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="cards-servicios py-16 lg:py-24">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="cards-servicios__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                Nuestros servicios
            </h2>
            <p class="cards-servicios__subtitulo mt-3 text-lg text-muted-foreground">
                Soluciones completas para cada etapa de tu proyecto digital.
            </p>
        </header>

        <div class="cards-servicios__grid cards-servicios__grid--col-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">

            <article class="cards-servicios__card group relative flex flex-col bg-card border border-border rounded-xl p-6 lg:p-7 transition-all duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-card" data-gsap="fade-up" data-gsap-delay="0.00">
                <div class="cards-servicios__icono w-12 h-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 11l3 3 8-8"></path><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <h3 class="cards-servicios__nombre text-xl font-semibold text-foreground mb-2 leading-snug">
                    <a href="#" class="cards-servicios__title-link before:content-[''] before:absolute before:inset-0 before:z-10" aria-label="Desarrollo WordPress">Desarrollo WordPress</a>
                </h3>
                <p class="cards-servicios__desc text-sm text-muted-foreground line-clamp-3 mb-5 leading-relaxed">Temas a medida, plugins personalizados y optimización de rendimiento con las últimas tecnologías.</p>
                <span class="cards-servicios__link relative z-20 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 group-hover:gap-2 group-hover:text-brand-700 transition-all duration-200 mt-auto pointer-events-none" aria-hidden="true">
                    Ver más
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"></path></svg>
                </span>
            </article>

            <article class="cards-servicios__card group relative flex flex-col bg-card border border-border rounded-xl p-6 lg:p-7 transition-all duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-card" data-gsap="fade-up" data-gsap-delay="0.08">
                <div class="cards-servicios__icono w-12 h-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
                <h3 class="cards-servicios__nombre text-xl font-semibold text-foreground mb-2 leading-snug">
                    <a href="#" class="cards-servicios__title-link before:content-[''] before:absolute before:inset-0 before:z-10" aria-label="Diseño UI/UX">Diseño UI/UX</a>
                </h3>
                <p class="cards-servicios__desc text-sm text-muted-foreground line-clamp-3 mb-5 leading-relaxed">Interfaces intuitivas y experiencias de usuario que convierten visitas en clientes.</p>
                <span class="cards-servicios__link relative z-20 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 group-hover:gap-2 group-hover:text-brand-700 transition-all duration-200 mt-auto pointer-events-none" aria-hidden="true">
                    Ver más
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"></path></svg>
                </span>
            </article>

            <article class="cards-servicios__card group relative flex flex-col bg-card border border-border rounded-xl p-6 lg:p-7 transition-all duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-card" data-gsap="fade-up" data-gsap-delay="0.16">
                <div class="cards-servicios__icono w-12 h-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <h3 class="cards-servicios__nombre text-xl font-semibold text-foreground mb-2 leading-snug">
                    <a href="#" class="cards-servicios__title-link before:content-[''] before:absolute before:inset-0 before:z-10" aria-label="SEO y Performance">SEO &amp; Performance</a>
                </h3>
                <p class="cards-servicios__desc text-sm text-muted-foreground line-clamp-3 mb-5 leading-relaxed">Optimización técnica, Core Web Vitals y estrategias de posicionamiento orgánico a largo plazo.</p>
                <span class="cards-servicios__link relative z-20 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 group-hover:gap-2 group-hover:text-brand-700 transition-all duration-200 mt-auto pointer-events-none" aria-hidden="true">
                    Ver más
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"></path></svg>
                </span>
            </article>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     5. TESTIMONIOS — Grid hairline shadcn + 1 destacado (Fase 2 ✓)
     Módulo: modules/testimonios/testimonios.php
     ACF: titulo, subtitulo, items (nombre, cargo, empresa, foto, testimonio,
          calificacion, destacado [NEW])
     Sin Swiper. Grid 1/2/3 col, item destacado ocupa col-span-2 con bg brand-50.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="testimonios py-16 lg:py-24">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                Lo que dicen nuestros clientes
            </h2>
            <p class="mt-3 text-lg text-muted-foreground">
                Más de 250 empresas ya confían en nuestro equipo.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 auto-rows-fr">

            <!-- Card destacada (col-span-2) -->
            <article class="testimonios__item relative flex flex-col p-6 md:p-8 rounded-xl border border-border bg-brand-50 dark:bg-brand-900 md:col-span-2" data-gsap="fade-up" data-gsap-delay="0.00">
                <span aria-hidden="true" class="absolute top-4 left-6 font-display text-5xl leading-none text-brand-300 select-none pointer-events-none">&ldquo;</span>
                <blockquote class="testimonios__cita relative z-10 text-base lg:text-lg text-foreground leading-relaxed mt-4 mb-5">
                    El equipo superó todas nuestras expectativas. El sitio quedó perfecto, con animaciones increíbles, una velocidad de carga excelente y un acompañamiento técnico que de verdad sintió como parte de nuestro propio equipo.
                </blockquote>
                <div class="testimonios__stars flex gap-0.5 mb-5" aria-label="Calificación: 5 de 5">
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                </div>
                <footer class="testimonios__autor flex items-center gap-3 mt-auto">
                    <img class="testimonios__foto w-14 h-14 rounded-full object-cover border-2 border-background flex-shrink-0"
                        src="https://i.pravatar.cc/112?img=1" alt="Ana García"
                        width="56" height="56" loading="lazy">
                    <div class="testimonios__info flex flex-col">
                        <strong class="testimonios__nombre text-sm font-semibold text-foreground">Ana García</strong>
                        <span class="testimonios__cargo text-xs text-muted-foreground">Directora de Marketing &mdash; TechCorp</span>
                    </div>
                </footer>
            </article>

            <!-- Card normal -->
            <article class="testimonios__item relative flex flex-col p-6 md:p-8 rounded-xl border border-border bg-card" data-gsap="fade-up" data-gsap-delay="0.08">
                <span aria-hidden="true" class="absolute top-4 left-6 font-display text-5xl leading-none text-brand-300 select-none pointer-events-none">&ldquo;</span>
                <blockquote class="testimonios__cita relative z-10 text-base lg:text-lg text-foreground leading-relaxed mt-4 mb-5">
                    Trabajar con este equipo fue una experiencia brillante. Entregaron en tiempo y forma, y el resultado es exactamente lo que necesitábamos.
                </blockquote>
                <div class="testimonios__stars flex gap-0.5 mb-5" aria-label="Calificación: 5 de 5">
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                </div>
                <footer class="testimonios__autor flex items-center gap-3 mt-auto">
                    <img class="testimonios__foto w-14 h-14 rounded-full object-cover border-2 border-background flex-shrink-0"
                        src="https://i.pravatar.cc/112?img=3" alt="Carlos Mendoza"
                        width="56" height="56" loading="lazy">
                    <div class="testimonios__info flex flex-col">
                        <strong class="testimonios__nombre text-sm font-semibold text-foreground">Carlos Mendoza</strong>
                        <span class="testimonios__cargo text-xs text-muted-foreground">CEO &mdash; Innovatech</span>
                    </div>
                </footer>
            </article>

            <!-- Card normal -->
            <article class="testimonios__item relative flex flex-col p-6 md:p-8 rounded-xl border border-border bg-card" data-gsap="fade-up" data-gsap-delay="0.16">
                <span aria-hidden="true" class="absolute top-4 left-6 font-display text-5xl leading-none text-brand-300 select-none pointer-events-none">&ldquo;</span>
                <blockquote class="testimonios__cita relative z-10 text-base lg:text-lg text-foreground leading-relaxed mt-4 mb-5">
                    El nuevo sitio aumentó nuestras conversiones un 40%. La atención al detalle en el diseño y la performance son notables.
                </blockquote>
                <div class="testimonios__stars flex gap-0.5 mb-5" aria-label="Calificación: 4 de 5">
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-warning" aria-hidden="true">★</span>
                    <span class="text-base leading-none text-border" aria-hidden="true">★</span>
                </div>
                <footer class="testimonios__autor flex items-center gap-3 mt-auto">
                    <img class="testimonios__foto w-14 h-14 rounded-full object-cover border-2 border-background flex-shrink-0"
                        src="https://i.pravatar.cc/112?img=5" alt="Laura Torres"
                        width="56" height="56" loading="lazy">
                    <div class="testimonios__info flex flex-col">
                        <strong class="testimonios__nombre text-sm font-semibold text-foreground">Laura Torres</strong>
                        <span class="testimonios__cargo text-xs text-muted-foreground">Fundadora &mdash; Studio Creativo</span>
                    </div>
                </footer>
            </article>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     6. ACORDEÓN / FAQ — shadcn × Kresna (Fase 3 ✓)
     Módulo: modules/acordeon/acordeon.php
     ACF: titulo, subtitulo, items (pregunta, respuesta WYSIWYG)
     Cards hairline + chevron SVG animado. Mantiene clases BEM legacy
     (.acordeon__pregunta, aria-controls) para src/acordeon.js.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="acordeon py-16 lg:py-24">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="acordeon__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                Preguntas frecuentes
            </h2>
            <p class="acordeon__subtitulo mt-3 text-lg text-muted-foreground">
                Resolvemos tus dudas antes de empezar.
            </p>
        </header>

        <dl class="acordeon__lista flex flex-col gap-3 max-w-3xl">

            <?php
            $faqs = [
                ['¿Cuánto tiempo toma desarrollar un proyecto?', 'El tiempo varía según la complejidad. Un sitio corporativo estándar tarda entre 4 y 8 semanas desde el inicio del diseño hasta la entrega final. Proyectos más complejos con funcionalidades personalizadas pueden tomar de 3 a 6 meses.'],
                ['¿Qué incluye el servicio de mantenimiento?', 'El mantenimiento incluye actualizaciones de WordPress, plugins y tema; monitoreo de seguridad y rendimiento; backups diarios; soporte técnico con tiempo de respuesta garantizado de 48h; y revisiones de contenido menores hasta 2 horas al mes.'],
                ['¿Trabajan con clientes fuera de México?', 'Sí, trabajamos con clientes en toda Latinoamérica, España y Estados Unidos. Usamos herramientas de colaboración remota y adaptamos los horarios de reuniones a cada zona horaria.'],
                ['¿Puedo actualizar el contenido yo mismo después de la entrega?', 'Absolutamente. Todos nuestros proyectos se desarrollan con ACF Pro y una interfaz de administración simplificada, para que puedas actualizar textos, imágenes y módulos completos sin tocar una línea de código.'],
            ];
            foreach ($faqs as $i => [$pregunta, $respuesta]):
                $id    = 'faq-demo-' . $i;
                $delay = number_format($i * 0.04, 2, '.', '');
            ?>
            <div
                class="acordeon__item bg-card border border-border rounded-xl overflow-hidden transition-colors duration-200"
                data-gsap="fade-up"
                data-gsap-delay="<?php echo esc_attr($delay); ?>"
            >
                <dt>
                    <button
                        class="acordeon__pregunta w-full flex items-center justify-between gap-4 px-5 lg:px-6 py-4 lg:py-5 text-left text-base lg:text-lg font-semibold text-foreground hover:bg-muted/50 transition-colors duration-200"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($id); ?>"
                        type="button"
                    >
                        <span><?php echo esc_html($pregunta); ?></span>
                        <span class="acordeon__icono flex-shrink-0 text-muted-foreground transition-[transform,color] duration-300" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </button>
                </dt>
                <dd class="acordeon__respuesta" id="<?php echo esc_attr($id); ?>" hidden>
                    <div class="acordeon__contenido px-5 lg:px-6 pb-5 lg:pb-6 pt-1 text-base text-muted-foreground leading-relaxed border-t border-border/50">
                        <?php echo esc_html($respuesta); ?>
                    </div>
                </dd>
            </div>
            <?php endforeach; ?>

        </dl>
    </div>
</section>

<style>
.acordeon__item:has(button[aria-expanded="true"]) {
    border-color: color-mix(in srgb, var(--color-foreground) 20%, transparent);
}
.acordeon__pregunta[aria-expanded="true"] .acordeon__icono {
    transform: rotate(180deg);
    color: var(--color-brand-600);
}
@media (prefers-reduced-motion: reduce) {
    .acordeon__icono { transition: none; }
}
</style>

<!-- ══════════════════════════════════════════════════════════════════════════
     7. EQUIPO — Grid hairline shadcn (Fase 3 ✓)
     Módulo: modules/equipo/equipo.php
     ACF: titulo, subtitulo, items (nombre, cargo, foto, bio, redes_sociales)
     Foto rounded-2xl aspect 4/5. Redes via atom social-icon (auto-detect).
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="equipo py-16 lg:py-24">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="equipo__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                Conoce al equipo
            </h2>
            <p class="equipo__subtitulo mt-3 text-lg text-muted-foreground">
                Profesionales apasionados por crear experiencias digitales excepcionales.
            </p>
        </header>

        <div class="equipo__grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8" data-gsap-batch=".equipo__miembro">
            <?php
            $team = [
                ['Valentina Ríos',  'Frontend Lead',       'https://i.pravatar.cc/512?img=9',  'Especialista en Vite, React y animaciones GSAP con 8 años de experiencia.', 'https://linkedin.com/in/valentina', 'https://x.com/valentina'],
                ['Rodrigo Salas',   'Backend Developer',   'https://i.pravatar.cc/512?img=11', 'PHP y WordPress architect. Apasionado por el código limpio y la seguridad.', 'https://linkedin.com/in/rodrigo', 'https://github.com/rodrigo'],
                ['Camila Vargas',   'UI/UX Designer',      'https://i.pravatar.cc/512?img=7',  'Diseñadora centrada en el usuario con enfoque en accesibilidad y Design Thinking.', 'https://linkedin.com/in/camila', 'https://instagram.com/camila'],
                ['Mateo González',  'SEO & Performance',   'https://i.pravatar.cc/512?img=15', 'Experto en Core Web Vitals, SEO técnico y estrategia de contenido orgánico.', 'https://linkedin.com/in/mateo', ''],
            ];
            $i = 0;
            foreach ($team as [$nombre, $cargo, $foto, $bio, $linkedin, $second]):
                $delay = number_format($i * 0.08, 2, '.', '');
            ?>
            <article class="equipo__miembro group flex flex-col" data-gsap="fade-up" data-gsap-delay="<?php echo esc_attr($delay); ?>">
                <div class="equipo__foto-wrap relative overflow-hidden rounded-2xl bg-muted mb-4" style="aspect-ratio: 4/5;">
                    <img class="equipo__foto absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                        src="<?php echo esc_url($foto); ?>"
                        alt="<?php echo esc_attr($nombre); ?>"
                        width="480" height="600" loading="lazy" decoding="async">
                </div>
                <div class="equipo__info flex flex-col gap-1">
                    <h3 class="equipo__nombre text-lg font-semibold text-foreground leading-snug"><?php echo esc_html($nombre); ?></h3>
                    <p class="equipo__cargo text-sm text-brand-600 font-medium"><?php echo esc_html($cargo); ?></p>
                    <p class="equipo__bio text-sm text-muted-foreground line-clamp-3 mt-2 leading-relaxed"><?php echo esc_html($bio); ?></p>
                    <ul class="equipo__redes flex items-center gap-2 mt-3 list-none p-0">
                        <li>
                            <?php get_template_part('template-parts/atoms/social-icon', null, [
                                'network' => 'linkedin',
                                'url'     => $linkedin,
                                'label'   => $nombre . ' en LinkedIn',
                                'class'   => 'equipo__red',
                                'size'    => 16,
                            ]); ?>
                        </li>
                        <?php if ($second): ?>
                        <li>
                            <?php get_template_part('template-parts/atoms/social-icon', null, [
                                'network' => 'auto',
                                'url'     => $second,
                                'label'   => $nombre,
                                'class'   => 'equipo__red',
                                'size'    => 16,
                            ]); ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </article>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</section>

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

<!-- ══════════════════════════════════════════════════════════════════════════
     8. GALERÍA — Grid hairline + lightbox Colorbox (Fase 3 ✓)
     Módulo: modules/galeria/galeria.php
     ACF: titulo, subtitulo, columnas (2/3/4), imagenes (ACF Gallery)
     Aspect 1:1, hover scale + overlay zoom-in icon. Colorbox en is_singular().
     Las clases BEM (.galeria__link, data-rel) preservadas para Colorbox.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="galeria py-16 lg:py-24">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="galeria__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                Galería de proyectos
            </h2>
            <p class="galeria__subtitulo mt-3 text-lg text-muted-foreground">
                Una muestra de lo que construimos juntos.
            </p>
        </header>

        <div class="galeria__grid galeria__grid--col-3 grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 lg:gap-5">
            <?php
            $gallery_imgs = [
                ['https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=900&h=900&fit=crop', 'Plataforma e-commerce'],
                ['https://images.unsplash.com/photo-1551650975-87deedd944c3?w=900&h=900&fit=crop',  'App móvil fintech'],
                ['https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=900&h=900&fit=crop', 'Portal corporativo'],
                ['https://images.unsplash.com/photo-1547658719-da2b51169166?w=900&h=900&fit=crop',  'Dashboard analytics'],
                ['https://images.unsplash.com/photo-1522542550221-31fd19575a2d?w=900&h=900&fit=crop', 'Landing page SaaS'],
                ['https://images.unsplash.com/photo-1504639725590-34d0984388bd?w=900&h=900&fit=crop', 'Sistema de gestión'],
            ];
            foreach ($gallery_imgs as $i => [$src, $alt]):
                $delay = number_format($i * 0.06, 2, '.', '');
            ?>
            <figure class="galeria__item group flex flex-col" data-gsap="zoom-in" data-gsap-delay="<?php echo esc_attr($delay); ?>">
                <a href="<?php echo esc_url($src); ?>"
                   class="galeria__link relative block overflow-hidden rounded-xl bg-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                   data-rel="galeria-demo"
                   aria-label="<?php echo esc_attr($alt); ?>"
                   style="aspect-ratio: 1/1;">
                    <img class="galeria__img absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                        src="<?php echo esc_url($src); ?>"
                        alt="<?php echo esc_attr($alt); ?>"
                        width="900" height="900" loading="lazy" decoding="async">
                    <span class="galeria__overlay absolute inset-0 bg-foreground/0 group-hover:bg-foreground/20 transition-colors duration-300 flex items-center justify-center" aria-hidden="true">
                        <span class="galeria__zoom-icon opacity-0 group-hover:opacity-100 transition-opacity duration-300 w-10 h-10 rounded-full bg-white/95 text-foreground flex items-center justify-center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </span>
                    </span>
                </a>
                <figcaption class="galeria__caption text-sm text-muted-foreground mt-2 leading-relaxed"><?php echo esc_html($alt); ?></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     9. HERO BLOG — Hero secundario con overlay (Fase 3 ✓)
     Módulo: modules/hero-blog/hero-blog.php
     ACF: bg (imagen URL), overlay (color), text (WYSIWYG), button (link)
     min-height clamp(360,50vh,560), texto centrado, CTA kresna-dark.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="hero-blog relative overflow-hidden flex items-center justify-center" style="min-height: clamp(360px, 50vh, 560px);">
    <img class="hero-blog__bg absolute inset-0 w-full h-full object-cover"
        src="https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1920&h=900&fit=crop"
        alt="" aria-hidden="true" fetchpriority="high" decoding="async">
    <div class="hero-blog__overlay absolute inset-0" style="background: linear-gradient(180deg, rgba(10,10,11,0.55) 0%, rgba(10,10,11,0.75) 100%);" aria-hidden="true"></div>

    <div class="container relative z-10 py-16 lg:py-24">
        <div class="hero-blog__inner max-w-3xl mx-auto text-center flex flex-col items-center gap-5">
            <div class="hero-blog__text text-white" data-gsap="fade-up">
                <h1>Nuestro Blog</h1>
                <p>Consejos, tutoriales y tendencias del mundo del desarrollo web y WordPress.</p>
            </div>
            <div class="hero-blog__button" data-gsap="fade-up" data-gsap-delay="0.15">
                <a href="#" class="btn-base btn-kresna-dark btn-size-lg" target="_self">Ver todos los artículos</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-blog__text h1,
.hero-blog__text h2 {
    font-family: var(--font-display);
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1.05;
    margin: 0;
    color: #fff;
    font-size: clamp(2.25rem, 1.5rem + 3vw, 3.75rem);
}
.hero-blog__text p {
    margin: 0.75rem 0 0;
    font-size: clamp(1rem, 0.95rem + 0.3vw, 1.125rem);
    line-height: 1.6;
    color: rgb(255 255 255 / 0.85);
}
</style>

<!-- ══════════════════════════════════════════════════════════════════════════
     10. BLOG — Cards hairline shadcn (Fase 2 ✓)
     Módulo: modules/blog/blog.php
     ACF: titulo, subtitulo, cantidad, categoria
     Sin botón "Leer más"; toda la card clicable via .stretched-link.
     Aspect-ratio 16:10. Badge categoría brand-50 / brand-700.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="modulo-blog py-16 lg:py-24">
    <div class="container">
        <header class="mb-10 lg:mb-14 max-w-2xl">
            <h2 class="text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                Últimos artículos
            </h2>
            <p class="mt-3 text-lg text-muted-foreground">
                Conocimiento práctico para equipos de desarrollo y marketing digital.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php
            $demo_posts = [
                [
                    'titulo'    => 'Cómo mejorar el rendimiento de tu sitio WordPress en 2026',
                    'excerpt'   => 'Descubre las técnicas más efectivas para optimizar Core Web Vitals: lazy loading inteligente, fonts swap, y preconexiones DNS.',
                    'image'     => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=900&h=560&fit=crop',
                    'cat'       => 'WordPress',
                    'date_iso'  => '2026-01-15',
                    'date'      => '15 ene 2026',
                ],
                [
                    'titulo'    => 'GSAP ScrollTrigger: animaciones scroll-driven sin jank',
                    'excerpt'   => 'Patrones de pinning y scrub que escalan: cómo usar matchMedia, prefers-reduced-motion y batch para listas largas.',
                    'image'     => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=900&h=560&fit=crop',
                    'cat'       => 'Frontend',
                    'date_iso'  => '2026-02-08',
                    'date'      => '8 feb 2026',
                ],
                [
                    'titulo'    => 'ACF Pro + Vite v8: pipeline moderno para temas WordPress',
                    'excerpt'   => 'De Webpack a Vite con HMR de PHP, manifest hasheado y SASS legacy en convivencia. Lecciones de la migración.',
                    'image'     => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=900&h=560&fit=crop',
                    'cat'       => 'Tooling',
                    'date_iso'  => '2026-03-22',
                    'date'      => '22 mar 2026',
                ],
            ];
            foreach ($demo_posts as $i => $p):
                $delay = number_format($i * 0.08, 2, '.', '');
                ?>
                <article
                    class="modulo-blog__card group relative bg-card border border-border rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-card"
                    data-gsap="fade-up"
                    data-gsap-delay="<?php echo esc_attr($delay); ?>"
                >
                    <div class="modulo-blog__card-img relative w-full overflow-hidden bg-muted" style="aspect-ratio:16/10;">
                        <img
                            src="<?php echo esc_url($p['image']); ?>"
                            alt="<?php echo esc_attr($p['titulo']); ?>"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                            loading="lazy"
                        >
                    </div>

                    <div class="modulo-blog__card-body p-6">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold uppercase tracking-wide mb-3">
                            <?php echo esc_html($p['cat']); ?>
                        </span>

                        <h3 class="text-xl font-semibold text-foreground leading-snug mb-2 line-clamp-2">
                            <a
                                href="#"
                                class="modulo-blog__card-link before:content-[''] before:absolute before:inset-0 before:z-10"
                                aria-label="<?php echo esc_attr($p['titulo']); ?>"
                            >
                                <?php echo esc_html($p['titulo']); ?>
                            </a>
                        </h3>

                        <p class="text-sm text-muted-foreground line-clamp-3 mb-4">
                            <?php echo esc_html($p['excerpt']); ?>
                        </p>

                        <p class="text-xs text-muted-foreground mt-auto">
                            <time datetime="<?php echo esc_attr($p['date_iso']); ?>">
                                <?php echo esc_html($p['date']); ?>
                            </time>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     11. TEXTO — Bloque WYSIWYG con color personalizado (Fase 3 ✓)
     Módulo: modules/texto/texto.php
     ACF: imagen, color (picker), texto (WYSIWYG)
     Layout 2-col en lg+ con imagen lateral. Headings DM Sans, prose neutral.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="texto py-16 lg:py-24" data-gsap="fade-up">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <figure class="texto__media order-first">
                <img
                    class="w-full h-auto rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=900&h=700&fit=crop"
                    alt="Desarrollo web"
                    width="900" height="700"
                    loading="lazy"
                    decoding="async"
                >
            </figure>

            <div class="texto__contenido texto-demo prose prose-neutral max-w-none text-base lg:text-lg leading-relaxed text-foreground" data-gsap="fade-up" data-gsap-delay="0.10">
                <h2>Tecnología que conecta personas con oportunidades</h2>
                <p>En un mundo donde lo digital es el nuevo punto de contacto entre empresas y personas, cada línea de código importa. Construimos con precisión, pensamos en el usuario y medimos cada resultado.</p>
                <p>Cada proyecto es una oportunidad de hacerlo mejor que la última vez: con menos peso, más velocidad y una experiencia más cuidada de principio a fin.</p>
            </div>
        </div>
    </div>
</section>

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
.texto-demo > h2:first-child { color: #065A98; }
</style>

<!-- ══════════════════════════════════════════════════════════════════════════
     12. FORMULARIO — Layout shadcn con imagen lateral (Fase 2 ✓)
     Módulo: modules/formulario/formulario.php
     ACF: titulo, subtitulo, descripcion, shortcode_cf7, imagen_lateral
     Card hairline, inputs `.input-shadcn`, headings shadcn.
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="formulario py-16 lg:py-24 formulario--con-imagen">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-start">

            <div class="formulario__contenido">
                <header class="mb-6 lg:mb-8">
                    <h2 class="formulario__titulo text-3xl lg:text-4xl font-display font-semibold text-foreground tracking-tight">
                        Hablemos de tu proyecto
                    </h2>
                    <p class="formulario__subtitulo mt-3 text-lg text-foreground">
                        Respuesta garantizada en menos de 48h.
                    </p>
                    <p class="formulario__desc mt-2 text-base text-muted-foreground">
                        Cuéntanos qué tienes en mente. Analizamos tu caso sin costo y te damos una propuesta a medida.
                    </p>
                </header>

                <div class="formulario__form bg-card border border-border rounded-xl p-6 md:p-8" data-gsap="fade-up">
                    <?php if (function_exists('wpcf7_contact_form') && shortcode_exists('contact-form-7')): ?>
                        <?php echo do_shortcode('[contact-form-7 id="1" title="Demo"]'); ?>
                    <?php else: ?>
                        <form class="space-y-5" action="#" method="post" novalidate>
                            <div class="flex flex-col gap-1.5">
                                <label for="demo-nombre" class="form-label text-sm font-medium text-foreground">Nombre completo *</label>
                                <input
                                    type="text"
                                    id="demo-nombre"
                                    name="nombre"
                                    required
                                    autocomplete="name"
                                    placeholder="Ej. Juan Pérez"
                                    class="input-shadcn h-10 px-3 rounded-md border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition"
                                >
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="demo-email" class="form-label text-sm font-medium text-foreground">Correo electrónico *</label>
                                <input
                                    type="email"
                                    id="demo-email"
                                    name="email"
                                    required
                                    autocomplete="email"
                                    placeholder="juan@empresa.com"
                                    class="input-shadcn h-10 px-3 rounded-md border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition"
                                >
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="demo-mensaje" class="form-label text-sm font-medium text-foreground">Mensaje *</label>
                                <textarea
                                    id="demo-mensaje"
                                    name="mensaje"
                                    required
                                    rows="5"
                                    placeholder="Cuéntanos sobre tu proyecto…"
                                    class="input-shadcn px-3 py-2 rounded-md border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition resize-y"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-3 rounded-md bg-brand-500 hover:bg-brand-600 text-white font-medium text-sm transition"
                            >
                                Enviar mensaje
                            </button>

                            <p class="text-xs text-muted-foreground" aria-live="polite">
                                Respondemos en 1-2 días hábiles. Sin spam, solo trabajo real.
                            </p>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="formulario__lateral order-first lg:order-last">
                <img
                    class="w-full h-auto rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?w=900&h=1100&fit=crop"
                    alt="Persona escribiendo en una laptop sobre una mesa de madera"
                    width="900" height="1100"
                    loading="lazy"
                    decoding="async"
                >
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════════
     13. POST — Layout single-post-style (Fase 3 ✓)
     Módulo: modules/post/post.php
     ACF (legacy, opcional): module_post, show_post_header, show_full_content
     Si no se rellena el sub_field, el módulo lee del global $post (uso vía
     single.php). Aquí simulamos el layout con datos hardcoded.
     ══════════════════════════════════════════════════════════════════════════ -->
<article class="post-modulo py-12 lg:py-16">
    <div class="container max-w-3xl">
        <div class="post-modulo__featured w-full overflow-hidden rounded-xl bg-muted mb-8 lg:mb-10" style="aspect-ratio: 16/9;" data-gsap="fade-up">
            <img
                src="https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&h=675&fit=crop"
                alt="Cómo escalar un equipo remoto"
                class="w-full h-full object-cover"
                loading="lazy" decoding="async">
        </div>

        <header class="post-modulo__header mb-6 lg:mb-8" data-gsap="fade-up" data-gsap-delay="0.05">
            <span class="post-modulo__cat inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold uppercase tracking-wide mb-4">
                Producto
            </span>
            <h1 class="post-modulo__titulo text-3xl lg:text-5xl font-display font-semibold text-foreground tracking-tight leading-tight">
                Cómo escalar un equipo de producto remoto sin perder velocidad
            </h1>
            <p class="post-modulo__meta mt-4 text-sm text-muted-foreground flex flex-wrap items-center gap-x-3 gap-y-1">
                <time datetime="2026-04-12">12 abr 2026</time>
                <span aria-hidden="true">·</span>
                <span>Por Camila Vargas</span>
            </p>
        </header>

        <div class="post-modulo__contenido prose prose-neutral max-w-none text-base lg:text-lg leading-relaxed text-foreground" data-gsap="fade-up" data-gsap-delay="0.10">
            <p>El crecimiento rápido de un equipo distribuido suele venir acompañado de tres problemas predecibles: comunicación asíncrona desorganizada, decisiones que se atascan en threads infinitos, y una pérdida silenciosa del contexto compartido que tenía el equipo cuando eran cinco personas.</p>
            <h2>Define rituales antes de contratar</h2>
            <p>Antes de incorporar al sexto, séptimo u octavo miembro, dedica una semana entera a documentar los rituales que el equipo ya hace de forma implícita. <strong>Cualquier cosa que se haga "porque siempre lo hemos hecho así"</strong> es deuda cultural que el siguiente fichaje no va a heredar por ósmosis.</p>
            <blockquote>El equipo distribuido que no documenta sus rituales se queda atrapado en su propia memoria muscular.</blockquote>
            <h3>Tres rituales mínimos que todo equipo remoto necesita</h3>
            <ul>
                <li><strong>Demo semanal grabada</strong> de 20 minutos, con un sólo presentador rotativo.</li>
                <li><strong>Decisión de la semana</strong>: una decisión escrita, con contexto, alternativas descartadas y dueño.</li>
                <li><strong>Retro mensual</strong> con tres preguntas: qué guardamos, qué cambiamos, qué probamos.</li>
            </ul>
            <p>Las herramientas son secundarias. Lo que importa es que el equipo confíe en que las decisiones se toman a la luz, no en DMs privados que nadie puede consultar después.</p>
        </div>
    </div>
</article>

<?php get_footer(); ?>
