# Design System — Kresna × shadcn

> Sistema de diseño para el rediseño Fase 1 del boilerplate WordPress.
> Sustituye los tokens actuales de `src/main.css` y orienta los módulos `hero`, `cta`, `blog`, `formulario`, `testimonios` y el `footer`.
>
> **Audiencia primaria**: `frontend-lead` (implementa los tokens en `@theme` y los componentes), revisores humanos y futuras iteraciones.
> **No incluye**: PHP, JS, ni SASS final. Solo tokens CSS (Tailwind v4 `@theme`) y reglas de diseño.

---

## 1. Filosofía

Dos sistemas de diseño conviven en este boilerplate, en jerarquía explícita:

1. **shadcn/ui es la espina** — proporciona la disciplina: neutros OKLCH, hairlines en lugar de sombras pesadas, foco visible accesible, escala tipográfica clara, jerarquía conservadora. Es el "default" de cualquier componente: botón, input, card, badge, label. Si una pieza no tiene una razón explícita para ser "expresiva", se queda en shadcn puro.
2. **Kresna es la capa expresiva** — aparece sólo en piezas de gran formato (hero, footer, CTA principal) y aporta personalidad: radios grandes (28px), un color de marca saturado, un manuscrito (Caveat) usado con cuentagotas, un objeto decorativo recurrente ("lucky cube") y vídeo de fondo en cards de marca.

Cuando los dos sistemas pelean, gana shadcn (es el chasis); Kresna decora. La consecuencia práctica: 80 % de los píxeles del sitio son shadcn neutro y disciplinado; 20 % son momentos Kresna que resaltan precisamente porque el resto es contenido.

**Tres decisiones rectoras**:

- **Un único color de marca saturado** (`--color-brand`, azul Kresna #1448BE family). Todo lo demás es neutro frío. No hay "secundario de marca": si necesitamos un acento extra usamos el color semantic correspondiente (warning, success) o una variante manuscrita en Caveat — nunca un segundo brand.
- **OKLCH para todos los colores nuevos**, fallback hex en comentario. La escala es perceptualmente uniforme y permite "subir un escalón" en dark mode girando solo el lightness.
- **Caveat sólo en 3 sitios definidos** (ver §3). Si aparece en un cuarto, la marca pierde el tono "premium con guiño humano" y se vuelve infantil. Es una restricción, no una guía.

---

## 2. Token map (para `src/main.css` `@theme`)

> Pegar el bloque siguiente reemplaza al `@theme` actual. Mantiene los nombres semánticos shadcn (`--color-background`, `--color-foreground`, etc.) que son los que generan las utilidades Tailwind v4 (`bg-background`, `text-foreground`, `border-border`, etc.).
>
> Los tokens legacy (`--color-primary`, `--color-sandwich`, `--font-heading`, …) se conservan al final como aliases retrocompatibles. Ver §8 para la migración.

```css
@import "tailwindcss";

@source "../**/*.php";
@source "../modules/**/*.php";

/* ─── Design tokens — Kresna × shadcn ──────────────────────────────────────── */
@theme {

  /* ── Color: neutros (shadcn-style, OKLCH) ───────────────────────────────── */
  /* Base de toda la UI. Light mode por defecto; dark en [data-theme="dark"]  */
  --color-background:        oklch(1     0      0);          /* #FFFFFF */
  --color-foreground:        oklch(0.141 0.005  285.823);    /* #09090B - zinc-950 */

  --color-card:              oklch(1     0      0);          /* #FFFFFF */
  --color-card-foreground:   oklch(0.141 0.005  285.823);    /* #09090B */

  --color-popover:           oklch(1     0      0);          /* #FFFFFF */
  --color-popover-foreground:oklch(0.141 0.005  285.823);    /* #09090B */

  --color-muted:             oklch(0.967 0.001  286.375);    /* #F4F4F5 - zinc-100 */
  --color-muted-foreground:  oklch(0.552 0.016  285.938);    /* #71717A - zinc-500 */

  --color-accent:            oklch(0.967 0.001  286.375);    /* #F4F4F5 */
  --color-accent-foreground: oklch(0.21  0.006  285.885);    /* #18181B - zinc-900 */

  --color-border:            oklch(0.92  0.004  286.32);     /* #E4E4E7 - zinc-200 */
  --color-input:             oklch(0.92  0.004  286.32);     /* #E4E4E7 */
  --color-ring:              oklch(0.705 0.015  286);        /* #A1A1AA - zinc-400 */

  /* ── Color: brand Kresna ────────────────────────────────────────────────── */
  /* Una sola escala de azul; el "primary" semántico apunta al 600.           */
  --color-brand-50:          oklch(0.97  0.02   254);        /* #EFF4FF */
  --color-brand-100:         oklch(0.93  0.05   254);        /* #DBE6FE */
  --color-brand-200:         oklch(0.86  0.10   254);        /* #BFD3FD */
  --color-brand-300:         oklch(0.78  0.14   254);        /* #94B5FB */
  --color-brand-400:         oklch(0.69  0.17   254);        /* #5B9FFB */
  --color-brand-500:         oklch(0.58  0.20   254);        /* #1E5DD7 */
  --color-brand-600:         oklch(0.48  0.21   258);        /* #1448BE - hero Kresna */
  --color-brand-700:         oklch(0.40  0.18   258);        /* #103A9E */
  --color-brand-800:         oklch(0.33  0.14   258);        /* #0D2F82 */
  --color-brand-900:         oklch(0.25  0.10   258);        /* #0A2566 */

  --color-primary:           var(--color-brand-600);         /* alias semántico */
  --color-primary-foreground:oklch(0.985 0      0);          /* #FAFAFA */

  /* ── Color: brand-dark (subscribe button del footer) ────────────────────── */
  /* Un negro cálido, NO el background. Usado sólo en buttons primarios sobre */
  /* surfaces claras tipo card f0f1f5.                                        */
  --color-brand-dark:        oklch(0.18  0.005  286);        /* #111214 */
  --color-brand-dark-foreground: oklch(1 0 0);

  /* ── Color: secundario / accent expresivo ───────────────────────────────── */
  /* Reservado. Ver Open Question #1. Por defecto, sin uso visual:            */
  --color-secondary:         var(--color-muted);
  --color-secondary-foreground: var(--color-accent-foreground);

  /* ── Color: semantic ────────────────────────────────────────────────────── */
  --color-destructive:       oklch(0.577 0.245  27.325);     /* #DC2626 - red-600 */
  --color-destructive-foreground: oklch(0.985 0 0);
  --color-warning:           oklch(0.768 0.171  70);         /* #F59E0B - amber-500 */
  --color-warning-foreground:oklch(0.141 0.005  285.823);
  --color-success:           oklch(0.62  0.16   148);        /* #16A34A - green-600 */
  --color-success-foreground:oklch(0.985 0 0);
  --color-info:              var(--color-brand-500);
  --color-info-foreground:   oklch(0.985 0 0);

  /* ── Tipografía ─────────────────────────────────────────────────────────── */
  /* DM Sans hace de body Y de display (mismo family, distinto peso/tracking) */
  /* Caveat solo aparece en los 3 sitios definidos en §3.                     */
  --font-sans:        'DM Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
  --font-display:     'DM Sans', ui-sans-serif, system-ui, sans-serif;
  --font-handwritten: 'Caveat', 'Brush Script MT', cursive;
  --font-mono:        'Geist Mono', ui-monospace, 'SF Mono', Menlo, monospace;

  /* Escala fluid tipográfica — basada en min/max viewports razonables.       */
  /* Cada token es { font-size }; los line-height y weight viven en §3.       */
  --text-xs:      clamp(0.75rem,   0.71rem + 0.18vw, 0.8125rem);  /* 12 → 13 */
  --text-sm:      clamp(0.8125rem, 0.78rem + 0.18vw, 0.875rem);   /* 13 → 14 */
  --text-base:    clamp(0.9375rem, 0.91rem + 0.13vw, 1rem);       /* 15 → 16 */
  --text-lg:      clamp(1rem,      0.96rem + 0.22vw, 1.125rem);   /* 16 → 18 */
  --text-xl:      clamp(1.125rem,  1.07rem + 0.27vw, 1.25rem);    /* 18 → 20 */
  --text-2xl:     clamp(1.25rem,   1.16rem + 0.45vw, 1.5rem);     /* 20 → 24 */
  --text-3xl:     clamp(1.5rem,    1.32rem + 0.91vw, 2rem);       /* 24 → 32 */
  --text-4xl:     clamp(1.875rem,  1.59rem + 1.45vw, 2.625rem);   /* 30 → 42 */
  --text-5xl:     clamp(2.25rem,   1.77rem + 2.36vw, 3.5rem);     /* 36 → 56 */
  --text-display: clamp(2.75rem,   2.02rem + 3.64vw, 4.75rem);    /* 44 → 76 — h1 hero */

  /* ── Espaciado — base 4px (Tailwind v4 default) ─────────────────────────── */
  /* Nota: Tailwind v4 deriva spacing-N de --spacing × N. Aquí declaramos     */
  /* las pocas constantes que el footer Kresna pide específicas.              */
  --spacing: 0.25rem; /* 4px */
  /* utilidades clave que se usarán: spacing-2 (8), spacing-3 (12),
     spacing-4 (16), spacing-6 (24), spacing-8 (32), spacing-10 (40),
     spacing-12 (48), spacing-16 (64), spacing-20 (80), spacing-24 (96),
     spacing-32 (128) */

  /* ── Layout ─────────────────────────────────────────────────────────────── */
  --container-narrow:  43.75rem; /*  700px - artículo lectura            */
  --container-content: 71.875rem;/* 1150px - footer Kresna y la mayoría  */
  --container-wide:    80rem;    /* 1280px - hero, lista blog full       */

  /* ── Breakpoints ────────────────────────────────────────────────────────── */
  --breakpoint-sm:  40rem;   /* 640px  - tablet small */
  --breakpoint-md:  48rem;   /* 768px  - tablet */
  --breakpoint-lg:  64rem;   /* 1024px - desktop */
  --breakpoint-xl:  80rem;   /* 1280px - desktop wide */
  --breakpoint-2xl: 96rem;   /* 1536px - desktop xl */

  /* ── Radios ─────────────────────────────────────────────────────────────── */
  /* 4   = inputs pequeños, badges densos                                     */
  /* 6   = botón shadcn default                                               */
  /* 8   = inputs, botones medianos, badges                                   */
  /* 12  = card hairline shadcn                                               */
  /* 16  = card surface elevada                                               */
  /* 22  = lucky cube interno (rotated, da sensación de gema)                 */
  /* 28  = cards Kresna (footer hero, video card) — el "Kresna look"         */
  /* full= avatares, dots, pills                                              */
  --radius-xs:   4px;
  --radius-sm:   6px;
  --radius-md:   8px;
  --radius-lg:   12px;
  --radius-xl:   16px;
  --radius-cube: 22px;
  --radius-2xl:  28px;
  --radius-full: 9999px;

  /* ── Bordes hairline ────────────────────────────────────────────────────── */
  --border-hairline: 1px solid var(--color-border);
  --border-hairline-strong: 1px solid oklch(0.85 0.005 286.32);

  /* ── Sombras ────────────────────────────────────────────────────────────── */
  /* Filosofía: 90 % del sistema es hairline (border) + cero sombra.          */
  /* Las sombras dramáticas se reservan para piezas Kresna (cube, card hero,  */
  /* botón "Subscribe"). Si una card tiene sombra Y border, una de las dos   */
  /* sobra.                                                                   */
  --shadow-none:     none;
  --shadow-hairline: 0 0 0 1px var(--color-border);                                       /* sustituye border en card */
  --shadow-sm:       0 1px 2px 0 rgb(0 0 0 / 0.05);                                       /* botón shadcn default    */
  --shadow-card:     0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px -1px rgb(0 0 0 / 0.04);     /* card surface neutra     */
  --shadow-elevated: 0 4px 12px -2px rgb(0 0 0 / 0.08), 0 2px 4px -2px rgb(0 0 0 / 0.04); /* dropdown, popover       */
  --shadow-dramatic: 0 6px 20px rgb(0 0 0 / 0.28), 0 2px 8px rgb(0 0 0 / 0.15);           /* botón Subscribe Kresna  */
  --shadow-cube:     0 18px 32px -10px rgb(20 72 190 / 0.45),
                     0 6px 12px -4px rgb(20 72 190 / 0.30),
                     inset 0 1px 0 rgb(255 255 255 / 0.25),
                     inset 0 -2px 8px rgb(10 37 102 / 0.35);                              /* lucky cube              */
  --shadow-brand-card: 0 12px 40px rgb(21 76 189 / 0.25);                                 /* card vídeo footer       */
  --shadow-brand-glow: 0 0 0 4px rgb(20 72 190 / 0.15);                                   /* focus ring sobre brand  */

  /* ── Gradientes (named, reusables) ──────────────────────────────────────── */
  --gradient-cube:      linear-gradient(135deg, #5b9ffb 0%, #1e5dd7 55%, #1448be 100%);
  --gradient-brand:     linear-gradient(135deg, var(--color-brand-500) 0%, var(--color-brand-700) 100%);
  --gradient-card-fade: linear-gradient(180deg, transparent 0%, rgb(0 0 0 / 0.55) 100%);  /* overlay vídeo card    */

  /* ── Watermark Kresna (texto gigante semitransparente) ──────────────────── */
  --color-watermark: rgb(0 0 0 / 0.04);

  /* ── Transiciones ───────────────────────────────────────────────────────── */
  --ease-smooth:   cubic-bezier(0.4, 0, 0.2, 1);
  --ease-out:      cubic-bezier(0.16, 1, 0.3, 1);
  --duration-fast: 150ms;
  --duration-base: 250ms;
  --duration-slow: 400ms;

  /* ── Aliases legacy retrocompatibles (ver §8) ───────────────────────────── */
  /* Estos NO desaparecen aún. Permiten que SASS legacy y módulos viejos      */
  /* sigan compilando mientras se migra módulo por módulo.                    */
  --color-primary-hover:  var(--color-brand-700);
  --color-sandwich:       var(--color-brand-200);   /* azul claro deco */
  --color-gray-dark:      var(--color-foreground);
  --color-gray-light:     var(--color-muted);
  --color-active-menu:    var(--color-brand-400);
  --color-submenu-hover:  var(--color-brand-50);
  --font-heading:         var(--font-display);      /* Poppins → DM Sans */
  --font-body:            var(--font-sans);         /* Open Sans → DM Sans */
  --shadow-md:            var(--shadow-card);
  --shadow-lg:            var(--shadow-elevated);
}

/* ─── Dark mode — espejo de tokens ──────────────────────────────────────────
   Aplica con [data-theme="dark"] en <html>. El switch lo gestionará un
   pequeño JS (no incluido aquí) que lee prefers-color-scheme + override
   manual en localStorage.

   Decisiones críticas (justificadas en §7):
   - background no es negro puro: es zinc-950 OKLCH (~14 % L)
   - brand sube de 600 → 400 (más luminoso) para contraste sobre fondo oscuro
   - card es ligeramente más clara que el background, no más oscura
   - el watermark Kresna en dark es blanco con 6 % opacidad
─────────────────────────────────────────────────────────────────────────── */
:root[data-theme="dark"] {
  --color-background:        oklch(0.141 0.005  285.823);    /* #09090B */
  --color-foreground:        oklch(0.985 0      0);          /* #FAFAFA */

  --color-card:              oklch(0.18  0.006  286);        /* #111114 - sutilmente más claro */
  --color-card-foreground:   oklch(0.985 0      0);

  --color-popover:           oklch(0.18  0.006  286);
  --color-popover-foreground:oklch(0.985 0      0);

  --color-muted:             oklch(0.21  0.006  285.885);    /* #18181B */
  --color-muted-foreground:  oklch(0.705 0.015  286);        /* #A1A1AA */

  --color-accent:            oklch(0.21  0.006  285.885);
  --color-accent-foreground: oklch(0.985 0      0);

  --color-border:            oklch(1     0      0 / 0.10);   /* hairline blanco bajo */
  --color-input:             oklch(1     0      0 / 0.15);
  --color-ring:              oklch(0.552 0.016  285.938);

  /* Brand sube en luminosidad — 600 perdería contraste sobre #09090B */
  --color-primary:           var(--color-brand-400);
  --color-primary-foreground:oklch(0.141 0.005  285.823);    /* texto oscuro sobre brand-400 */

  /* brand-dark ya no es la acción "fuerte" — ahora la acción primaria es brand;
     brand-dark se conserva por compatibilidad pero conviene NO usarlo en dark. */
  --color-brand-dark:        oklch(0.985 0      0);          /* invierte: blanco */
  --color-brand-dark-foreground: oklch(0.141 0.005  285.823);

  --color-destructive:       oklch(0.704 0.191  22.216);     /* red-500, sube L */
  --color-warning:           oklch(0.828 0.189  84);         /* amber-400 */
  --color-success:           oklch(0.72  0.17   148);        /* green-500 */

  /* Sombras: en dark prácticamente no hay drop-shadow visible. Sustituimos   */
  /* por glow brand sutil en piezas elevadas.                                 */
  --shadow-card:     0 0 0 1px var(--color-border);
  --shadow-elevated: 0 8px 24px -8px rgb(0 0 0 / 0.6), 0 0 0 1px var(--color-border);
  --shadow-dramatic: 0 8px 32px rgb(0 0 0 / 0.5), 0 0 0 1px rgb(255 255 255 / 0.08);
  --shadow-brand-card: 0 12px 40px rgb(20 72 190 / 0.45);   /* el azul brilla más */
  --shadow-cube:     0 18px 32px -10px rgb(91 159 251 / 0.55),
                     0 6px 12px -4px rgb(20 72 190 / 0.40),
                     inset 0 1px 0 rgb(255 255 255 / 0.30),
                     inset 0 -2px 8px rgb(10 37 102 / 0.50);

  --color-watermark: rgb(255 255 255 / 0.06);
  --gradient-card-fade: linear-gradient(180deg, transparent 0%, rgb(0 0 0 / 0.75) 100%);
}
```

---

## 3. Reglas tipográficas

### 3.1 Familias

| Token | Family | Cuándo |
|---|---|---|
| `--font-sans` | DM Sans | Body, labels, navegación, párrafos. **Default global**. |
| `--font-display` | DM Sans (peso 600/700) | H1–H3, watermark gigante. Mismo family que sans, distinto peso/tracking. |
| `--font-handwritten` | Caveat | **Solo 3 sitios** — ver §3.4. |
| `--font-mono` | Geist Mono | Código inline, valores de configuración, tags técnicos. Uso marginal. |

DM Sans + Caveat se cargan vía Google Fonts en el `<head>` (`frontend-lead` añadirá `wp_enqueue_style` con `display=swap`). Pesos a precargar: DM Sans 400/500/600/700 y Caveat 500/700.

### 3.2 Escala

| Token | Tamaño | Line-height | Weight | Letter-spacing | Uso |
|---|---|---|---|---|---|
| `text-display` | 44 → 76 px | 1.02 | 700 | -0.02em | H1 hero (single page). |
| `text-5xl` | 36 → 56 px | 1.05 | 700 | -0.02em | H1 página interior, big numbers. |
| `text-4xl` | 30 → 42 px | 1.10 | 600 | -0.015em | H2 sección destacada. |
| `text-3xl` | 24 → 32 px | 1.15 | 600 | -0.01em | H2 estándar, título de card grande. |
| `text-2xl` | 20 → 24 px | 1.25 | 600 | -0.005em | H3, subhead. |
| `text-xl` | 18 → 20 px | 1.35 | 600 | 0 | H4, lead paragraph. |
| `text-lg` | 16 → 18 px | 1.55 | 400 | 0 | Body grande (article body). |
| `text-base` | 15 → 16 px | 1.55 | 400 | 0 | **Body default**. |
| `text-sm` | 13 → 14 px | 1.45 | 400 | 0 | Meta, helper text, footnote. |
| `text-xs` | 12 → 13 px | 1.40 | 500 | 0.01em | Micro labels, tags, badges densos. |

Regla de oro: **un solo nivel de display por sección**. Si el hero usa `text-display`, el siguiente módulo arranca en `text-3xl` o menor. Saltar más de 2 niveles dentro de la misma sección rompe la jerarquía.

### 3.3 Caveat — los 3 sitios autorizados

Caveat es expresivo. Si se usa en un cuarto sitio sin autorización, se pierde el "signature touch":

1. **Footer / overline manuscrito** — `Stay in touch!`, `Feeling lucky?`. Tamaño 1.5em sobre el título DM Sans inmediatamente debajo. Ya cubierto por el spec del cliente.
2. **Footer / column titles** — `Navigation`, `Company` (y futuras columnas). Tamaño igual al body, weight 500. Ya cubierto por el spec del cliente.
3. **Sección label de hero / CTA** — un overline opcional sobre H1 que el cliente pueda activar (`<span class="font-handwritten">Hi there 👋</span>`). Es el tercer sitio autorizado y se reserva como herramienta de personalidad para piezas hero — usar máximo 1 vez por página.

Cualquier propuesta de uso de Caveat **fuera de estos tres** debe abrirse como Open Question.

### 3.4 Watermark

El watermark "Kresna" del footer es DM Sans 700, fluido, fill `var(--color-watermark)`:

```
font-size: clamp(8rem, 18vw, 16rem);
line-height: 0.85;
letter-spacing: -0.06em;
color: var(--color-watermark);
user-select: none;
pointer-events: none;
```

Es el único sitio donde un texto crece a más de 5xl. Reservado al footer; no replicar en módulos interiores.

---

## 4. Componentes Atom

### 4.1 Button — 4 variantes

Todas comparten:

- `font-family: var(--font-sans)`, `font-weight: 500`, `letter-spacing: -0.005em`.
- Tamaños: `sm` (h:32, px:12, text-sm), `md` (h:40, px:16, text-sm), `lg` (h:48, px:20, text-base). Default = `md`.
- Radius: `md` (8px) por defecto. Los botones dentro de cards Kresna pueden subir a `lg`.
- Foco: `outline: 2px solid var(--color-ring); outline-offset: 2px`. En variant `primary` el ring usa `--shadow-brand-glow`.
- `disabled`: `opacity: 0.5; pointer-events: none`.
- Transition: `background-color, color, box-shadow 150ms var(--ease-out)`.

| Variant | Background | Foreground | Border | Hover | Cuándo usar |
|---|---|---|---|---|---|
| `primary` | `--color-primary` | `--color-primary-foreground` | none | brand-700 | Acción principal (1 por sección). |
| `secondary` | `--color-muted` | `--color-accent-foreground` | none | muted darker | Acción secundaria neutra. |
| `ghost` | transparent | `--color-foreground` | none | bg `--color-muted` | Acciones terciarias / nav. |
| `outline` | transparent | `--color-foreground` | hairline | bg `--color-muted` | Cancelar, descartar. |
| `brand-dark` | `--color-brand-dark` | white | none | brand-dark + glow brand | **Sólo** Subscribe footer y CTAs sobre cards Kresna. Siempre con `--shadow-dramatic`. |
| `link` | none | `--color-primary` | none | underline | Acción terciaria embebida en texto. |
| `destructive` | `--color-destructive` | `--color-destructive-foreground` | none | red-700 | Borrar, irreversible. |

### 4.2 Input + Label + Helper + Error

Spec base (input default, shadcn):

- `height: 40px` (`sm` 32, `lg` 48). `padding: 0 12px`. `border: 1px solid var(--color-input)`. `radius: var(--radius-md)`.
- `background: var(--color-background)`. `color: var(--color-foreground)`. Placeholder `color: var(--color-muted-foreground)`.
- `font-size: var(--text-sm)`.
- Focus: `border-color: var(--color-ring); box-shadow: 0 0 0 3px oklch(from var(--color-ring) l c h / 0.25);` — variante shadcn del focus ring.
- Disabled: `opacity: 0.5; cursor: not-allowed`.

Estructura típica (pseudo-html):

```html
<!-- pseudo-html -->
<div class="form-field">
  <label class="form-label">Email</label>
  <input class="form-input" type="email" placeholder="tu@email.com">
  <p class="form-helper">Te enviaremos un código de verificación.</p>
  <!-- en error: -->
  <p class="form-error" role="alert">Formato de email inválido.</p>
</div>
```

- `label`: `font-size: var(--text-sm); font-weight: 500; color: var(--color-foreground); margin-bottom: 6px;`
- `helper`: `font-size: var(--text-xs); color: var(--color-muted-foreground); margin-top: 6px;`
- `error`: `font-size: var(--text-xs); color: var(--color-destructive); margin-top: 6px;` Sustituye al helper visualmente cuando hay error.
- En estado error: `border-color: var(--color-destructive); box-shadow: 0 0 0 3px oklch(from var(--color-destructive) l c h / 0.20);`

### 4.3 Badge / Pill

Tamaño compact. `height: 22px; padding: 0 8px; radius: var(--radius-full); font-size: var(--text-xs); font-weight: 500;`.

Variantes:

| Variant | Background | Foreground | Uso |
|---|---|---|---|
| `default` | `--color-muted` | `--color-foreground` | Tag genérico (categoría blog). |
| `brand` | `--color-brand-50` | `--color-brand-700` | Tag de marca (categoría destacada). En dark: `brand-900` / `brand-200`. |
| `outline` | transparent + hairline | `--color-foreground` | Counts, status neutro. |
| `success` | success-50 (mix muted + success @ 12%) | `--color-success` | "En vivo", "OK". |
| `warning` | warning-50 | `--color-warning` | "Beta", "Borrador". |
| `handwritten` | transparent | `--color-foreground` | **Variante Caveat** — sólo en section labels (sitio #3 autorizado). `font-family: var(--font-handwritten); font-size: var(--text-xl); font-weight: 600`. Sin pill background. |

---

## 5. Componentes Molecule

### 5.1 Card — 3 variantes

| Variant | Background | Border / Shadow | Radius | Padding | Uso |
|---|---|---|---|---|---|
| `hairline` | `--color-card` | `1px solid var(--color-border)`, no shadow | `var(--radius-lg)` (12) | 24px | Default shadcn. Lista blog, lista equipo, agenda. |
| `surface` | `--color-card` | `var(--shadow-card)` | `var(--radius-xl)` (16) | 24-32px | Card destacada, KPI, callout. |
| `brand-video` | `--color-brand-600` (fallback) + vídeo bg | `var(--shadow-brand-card)`, no border | `var(--radius-2xl)` (28) | 32-40px | Card hero del footer, hero principal opcional. |

Composición visual `brand-video` (pseudo-html):

```html
<!-- pseudo-html -->
<article class="card card--brand-video">
  <video autoplay muted loop playsinline class="card__bg" poster="…">…</video>
  <div class="card__overlay"></div> <!-- gradient-card-fade -->
  <div class="card__body">…</div>   <!-- contenido sobre vídeo -->
</article>
```

- El overlay (`var(--gradient-card-fade)`) es obligatorio para garantizar contraste WCAG AA del texto blanco.
- En dark mode el overlay se intensifica (token ya ajustado).
- Fallback sin vídeo: el `--color-brand-600` cubre el fondo y el patrón sigue siendo legible.

### 5.2 Lucky cube

Decorativo, reusable. 3 tamaños:

| Size | Width × Height | Inner radius | Rotación |
|---|---|---|---|
| `sm` | 72 × 72 | 18 | -10° |
| `md` | 96 × 96 | 22 | -10° |
| `lg` | 120 × 120 | 28 | -8° |

Specs comunes:

```
background: var(--gradient-cube);
border-radius: var(--radius-cube);   /* o el inner del tamaño */
box-shadow: var(--shadow-cube);
transform: rotate(-10deg);
```

Marca interna ("punto de dado" o monograma): círculo blanco 18 % opacidad, centrado. Reserva un spot en la cara visible para una mini-letra "K" o un dot pattern (decisión de craft, no de sistema).

Cuándo usar: footer (ya definido), CTA principal (esquina superior derecha de la card como "guiño"), página 404 (centro). **Máximo 1 cube visible por viewport** — su poder está en la rareza.

### 5.3 Section label + handwritten arrow

El bloque `Feeling lucky? ↘` del footer es reutilizable como atom compuesto:

```html
<!-- pseudo-html -->
<div class="section-label">
  <span class="section-label__text">Feeling lucky?</span>
  <svg class="section-label__arrow" viewBox="0 0 48 32" aria-hidden="true">
    <!-- path manuscrita: curva con flecha al final -->
  </svg>
</div>
```

Reglas:

- `section-label__text` usa `--font-handwritten`, tamaño `var(--text-xl)` o `var(--text-2xl)`, color `--color-foreground`.
- La flecha SVG es `currentColor` con `stroke-width: 2.5; fill: none; stroke-linecap: round; stroke-linejoin: round`.
- Ángulo y dirección de la flecha varían según contexto (apunta a lo siguiente importante: cube, botón, foto).

Uso autorizado: footer (2 instancias ya), hero / CTA (sitio #3 de Caveat). No usar en blog list ni en formularios.

---

## 6. Specs por módulo

### 6.1 `hero`

**Estructura**:

- Container: `--container-wide` (1280px), padding lateral 24-32px.
- Min-height: `clamp(480px, 70vh, 720px)`.
- Composición: imagen/vídeo de fondo full-bleed + overlay + contenido sobre.

**Composición visual**:

```
┌─────────────────────────────────────────────────────────────┐
│ [vídeo o imagen full bg]                                    │
│ [overlay gradient: transparent → black 0.5 (bottom-up)]     │
│                                                             │
│   <span handwritten> Hi there 👋  ← (opcional, sitio #3)    │
│   <h1 text-display>  Headline en 2 líneas                   │
│   <p  text-lg>       Subline 1-2 frases breve               │
│                                                             │
│   [btn primary]  [btn ghost / outline]                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Reglas**:

- El H1 usa `text-display`, color `--color-foreground` invertido (blanco). Con vídeo obligatoriamente con overlay.
- Subline `text-lg` color `oklch(1 0 0 / 0.85)`.
- CTA primary obligatorio; secundario opcional. Si hay 2 botones, el primario va primero (left-to-right).
- Slider Swiper actual se conserva — cada slide es una instancia de la composición. Pagination dots con `--color-brand-400`, prev/next como botones `ghost` blancos.
- En mobile: H1 baja a `text-4xl`, alineación left siempre (no center — center mata la jerarquía cuando el botón se pone debajo).
- Acento Kresna: el `<span handwritten>` es opcional vía ACF flag `mostrar_overline_manuscrito`. Si no se activa, el hero queda 100 % shadcn.

### 6.2 `cta`

**Estructura**:

- Container: `--container-content` (1150px).
- Padding vertical: 64-96px.
- Layout: `grid-template-columns: 1fr auto` en desktop (texto izquierda, decoración derecha) o stacked en mobile.

**Variantes de fondo**:

- `cta--surface`: bg `--color-muted`, border-radius 28px en desktop (full-bleed en mobile). Contenido en negro.
- `cta--brand`: bg `--color-brand-600`, sin border. Contenido en blanco. Es la variante "destacada".
- `cta--video` (opcional, equivalente a `brand-video` card): vídeo + overlay. Para CTAs muy importantes.

**Composición visual** (variante `brand`):

```
┌────────────────────────────────────────────────────────────┐
│                                                            │
│   <h2 text-3xl>  Listos para empezar?                      │
│   <p  text-lg>   Una línea de soporte.                     │
│   [btn brand-dark]                          [LUCKY CUBE]   │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Reglas**:

- El cube va en la esquina superior derecha del contenedor, parcialmente saliendo (`transform: translate(15%, -25%) rotate(-10deg)`). Solo en variantes `brand` y `video`. En `surface` no hay cube (sería ruido).
- Botón en variante `brand`: `brand-dark` (negro sobre azul) — máximo contraste.
- Botón en variante `surface`: `primary` o `outline`.
- Dos botones permitidos pero recomendado uno solo en CTA.
- Alineación: izquierda por defecto (la "center hero CTA" es un patrón cansado y debilita la jerarquía con el cube decorativo).

### 6.3 `blog` (lista)

**Estructura**:

- Container: `--container-content` (1150px).
- Header de sección: H2 + subline + "Ver todo →" alineado a la derecha en desktop.
- Grid:
  - Mobile (<640): 1 columna.
  - Tablet (640-1024): 2 columnas.
  - Desktop (≥1024): 3 columnas.
- Gap: 24px (`spacing-6`) en mobile, 32px (`spacing-8`) en desktop.

**Card** (variante `hairline` shadcn):

```
┌──────────────────────────────────────┐
│ [imagen 16:10, radius-lg arriba]     │
│                                      │
│  [badge brand]  ← categoría          │
│  <h3 text-xl>  Título 2 líneas máx   │
│  <p  text-sm>  Excerpt 3 líneas      │
│                                      │
│  ─────────────                       │ ← divider hairline opcional
│  <time text-xs muted>  Fecha · 5 min │
└──────────────────────────────────────┘
```

**Reglas**:

- Card: `bg-card`, `border-hairline`, `radius-lg`, `padding-6`. Hover: `border-color: var(--color-foreground / 0.2); transform: translateY(-2px); transition base.
- Imagen: aspect-ratio 16/10, `object-fit: cover`. Border-radius solo arriba (top-left, top-right) `var(--radius-lg)`.
- Categoría: `badge brand`, max 1.
- H3: max 2 líneas (`-webkit-line-clamp: 2`), `text-xl`, weight 600, color `--color-foreground`.
- Excerpt: max 3 líneas, `text-sm`, color `--color-muted-foreground`.
- Meta (time + reading time): `text-xs`, `--color-muted-foreground`. Sin botón "Leer más" — el card entero es link (anchor wrapper sobre toda la card con clase `.stretched-link` para no anidar `<a>`).
- **Eliminar el botón "Leer más" actual** del módulo — redundante con la card entera clickable.

### 6.4 `formulario`

**Estructura**:

- Container: `--container-narrow` (700px) si solo formulario, o `--container-content` (1150px) si tiene imagen lateral (`formulario--con-imagen`).
- Layout con imagen: `grid-template-columns: 1fr 1fr` en desktop, stacked en mobile (imagen abajo, formulario arriba).
- Padding vertical sección: 64-96px.

**Card del formulario**:

- `bg-card`, `border-hairline`, `radius-xl` (16), `padding-8` (32) en desktop, `padding-6` (24) en mobile.
- Sin sombra. La card hairline es suficiente.

**Layout interno**:

```
<h2 text-3xl>  Título
<p  text-lg>   Subtítulo
<p  text-base muted-foreground>  Descripción

[form fields stacked, gap-6]
  - Label arriba (text-sm, weight 500, mb-1.5)
  - Input shadcn (h-10, border, radius-md)
  - Helper o error debajo (text-xs)

[btn primary, full-width en mobile, auto en desktop]
```

**Reglas**:

- **Label SIEMPRE arriba del input**, nunca placeholder-as-label (anti-patrón accesible).
- Asterisco rojo (`*`) en labels de campos requeridos, no "(obligatorio)" en texto.
- Errores inline debajo de cada campo, no en un bloque global al final.
- Botón submit: full-width en mobile (`w-full`), `w-auto` y alineado a la derecha en desktop.
- Estados de envío: spinner dentro del botón con el label "Enviando..." (frontend-lead implementará el JS).
- CF7 hereda los estilos de `.form-input`, `.form-label` mediante override CSS — frontend-lead deberá mapear las clases CF7 a las del sistema.

### 6.5 `testimonios`

**Estructura**:

- Container: `--container-content` (1150px).
- Header de sección: H2 + subtítulo opcional (centrado o izquierda según contexto).
- Layout:
  - Mobile (<768): Swiper carousel, 1 card visible.
  - Desktop (≥768): grid de 3 columnas estáticas SI hay 3 items, o Swiper con `slidesPerView: 2.5` SI hay más de 3.
- Gap: 24px.

**Card testimonio** (variante `hairline`):

```
┌──────────────────────────────────────┐
│ "                                    │
│  Este servicio cambió cómo enfocamos │
│  el problema. Recomendado al 100%.   │ ← cita text-base, italics opcional
│                                    " │
│                                      │
│  ★★★★★                              │ ← stars opcional, color brand
│                                      │
│  ┌───┐                               │
│  │AVA│  Nombre Apellido              │ ← weight 600
│  │TAR│  CEO — Empresa                │ ← text-sm muted-foreground
│  └───┘                               │
└──────────────────────────────────────┘
```

**Reglas**:

- Card: `bg-card`, `border-hairline`, `radius-lg`, `padding-6` a `padding-8`.
- Cita: `text-base`, color `--color-foreground`. Las comillas grandes decorativas (`"` antes y `"` después) usan `--font-display`, `text-3xl`, `--color-brand-300` (sutiles, no dominan).
- Estrellas: 16px, color `--color-warning` (amber). Las "off" en `--color-border`.
- Avatar: `width: 48; height: 48; radius: full; object-fit: cover; border: 2px solid var(--color-background);`.
- Nombre: `text-sm weight 600`. Cargo: `text-xs --color-muted-foreground`.
- En Swiper desktop: pagination dots usando `--color-brand-400` (activo) y `--color-border` (inactivo). Prev/Next: ghost buttons con icon chevron.
- **No usar fondos colorados en cards de testimonios** — la cita debe respirar. La excepción es 1 testimonio "destacado" puntual que puede ir en `card surface` con sombra sutil, pero no lo hacemos sistémicamente.

### 6.6 `footer`

**Verificación**: el spec literal del cliente está cerrado. Los tokens definidos en §2 cubren todos sus valores:

| Spec cliente | Token que lo cubre |
|---|---|
| Wrapper 1150px | `--container-content` |
| Card izquierda radius 28px | `--radius-2xl` |
| Card izquierda shadow `0 12px 40px rgba(21,76,189,0.25)` | `--shadow-brand-card` |
| Card izquierda fallback `#1e4fc0` | `--color-brand-500` (≈ #1E5DD7, prácticamente igual; usar var) |
| Card derecha bg `#f0f1f5` | `--color-muted` (resuelve a oklch(0.967 0.001 286.375) ≈ `#F4F4F5` — diferencia ~ΔE 1.5, imperceptible). **Si el cliente exige el hex exacto**, definir `--color-card-soft: #f0f1f5` como override puntual. |
| Card derecha radius 28px | `--radius-2xl` |
| Cube 96×96 radius 22 gradient | `--radius-cube`, `--gradient-cube`, `--shadow-cube` |
| Watermark `rgba(0,0,0,0.04)` | `--color-watermark` |
| Body color `#2d3148` | Recomendación: usar `--color-foreground`. La diferencia con `#09090B` es marginal en cards `f0f1f5`. **Si cliente insiste**, alias `--color-body-warm: #2d3148`. |
| Botón Subscribe `#111214` + shadow doble | `--color-brand-dark` + `--shadow-dramatic` |
| Caveat en "Stay in touch!", "Feeling lucky?", titles | Sitios autorizados #1 y #2 (§3.3) |

Open mini-decision: si el cliente exige `#f0f1f5` exacto y no aceptamos la deriva ΔE, agregar `--color-card-soft: oklch(0.962 0.003 264);` y aplicarlo solo en la card derecha del footer. No es necesario propagar a todo el sistema.

---

## 7. Variante oscura

El bloque `[data-theme="dark"]` ya está incluido en §2. Decisiones críticas con justificación:

### 7.1 ¿Qué se oscurece?

- **Todos los neutros**: background → zinc-950 (`oklch(0.141 ...)`), foreground → casi blanco. La card no es más oscura que el background — es más clara (`oklch(0.18 ...)`), creando una jerarquía "el contenido sale del fondo, no se hunde".
- **Borders**: pasan a blanco con baja opacidad (`oklch(1 0 0 / 0.10)`). Esto evita el "border negro" invisible y mantiene el hairline visible.
- **Sombras**: prácticamente desaparecen. Las sustituye un hairline más fuerte. Las shadows decorativas (cube, brand-card) se conservan pero con tintes brand más saturados.

### 7.2 ¿Qué se mantiene saturado?

- **Brand**: sube de 600 → 400 (más luminoso) para mantener contraste WCAG AA sobre fondo oscuro. El brand-600 sobre #09090B daría 4.1:1 (justo en límite); brand-400 da 7.2:1. Trade-off: el azul se ve "más vivo" en dark, lo cual encaja con la estética shadcn dark.
- **Lucky cube**: el gradient se mantiene casi idéntico — el cube debe sentirse igual de "joya" en ambos modos. Solo el shadow se intensifica para que no se "pierda" en el fondo oscuro.
- **Semantic** (destructive, warning, success): suben un escalón de luminosidad para legibilidad. El warning amber-400 en dark vs amber-500 en light es la única excepción a "bajar saturación" — el amber funciona mejor brillante.

### 7.3 Watermark en dark

`--color-watermark: rgb(255 255 255 / 0.06)` — blanco con 6 % opacidad (vs 4 % en light). El mayor opacity compensa que el blanco sobre negro tiene menos "presencia perceptual" que el negro sobre blanco.

### 7.4 Vídeo bg en dark

El overlay `--gradient-card-fade` se intensifica de `0 → 0.55` a `0 → 0.75`. Razón: en dark el contraste percibido entre vídeo (medio brillo) y texto blanco se reduce; un overlay más fuerte garantiza WCAG AA. El vídeo en sí no se modifica.

### 7.5 Toggle — ¿global o por página?

Recomendación: **global** (toggle en navbar / footer, persiste en `localStorage`, default sigue `prefers-color-scheme`). El "dark por página" complica el modelo mental del usuario. Ver Open Question #3.

---

## 8. Migración del legacy

Los tokens actuales en `src/main.css` y su destino:

| Token actual | Destino | Acción |
|---|---|---|
| `--color-primary: #065A98` | `--color-primary` apunta a `--color-brand-600 (#1448BE)` | **Cambia el hex** — el cliente confirmó nueva familia azul Kresna. Mantener el nombre para no romper utilidades `bg-primary`. |
| `--color-primary-hover: #0668AD` | alias a `--color-brand-700` | Conservar el nombre. |
| `--color-sandwich: #98c9ec` | alias a `--color-brand-200` | Conservar el nombre durante 1 sprint, luego deprecar. Su uso decorativo (banner secundario) ahora se cubre con `--color-brand-50` o `--color-muted` según contexto. |
| `--color-gray-dark: #383A3F` | alias a `--color-foreground` | Conservar el nombre (lo usa SASS legacy). |
| `--color-gray-light: #dbe3f2` | alias a `--color-muted` | Conservar el nombre. **Nota**: el azulado del original cambia a un gris OKLCH neutro — pequeña deriva visual aceptable. |
| `--color-active-menu: #5daee8` | alias a `--color-brand-400` | Conservar. |
| `--color-submenu-hover: #eff3fc` | alias a `--color-brand-50` | Conservar. |
| `--font-heading: 'Poppins'` | alias a `--font-display` (DM Sans) | Conservar nombre. **Cambia el family** — Poppins → DM Sans. |
| `--font-body: 'Open Sans'` | alias a `--font-sans` (DM Sans) | Conservar nombre. Cambia family. |
| `--text-xs … --text-3xl` (clamp) | Reemplazados por la nueva escala (§3.2) | **Reescribir**. La escala nueva es más conservadora en min y tiene 2 escalones más arriba (`text-4xl`, `text-5xl`, `text-display`). |
| `--radius-sm/md/lg/xl/full` | Conservar nombres, mismos valores excepto `xl` | `xl` cambia de 16 → 16 (igual). Añadidos: `--radius-xs (4)`, `--radius-cube (22)`, `--radius-2xl (28)`. |
| `--shadow-sm/md/lg/card` | Conservar nombres como aliases. Nuevos: `--shadow-hairline, --shadow-elevated, --shadow-dramatic, --shadow-cube, --shadow-brand-card, --shadow-brand-glow` | Aliases puestos en §2. |

**Eliminar (en una segunda fase, no ahora)**:

- `--color-sandwich` cuando ningún módulo legacy lo referencie (el blog viejo lo usa en banners decorativos — auditar antes de eliminar).
- Las clases componentes legacy `.btn-primary`, `.btn-secondary`, `.card`, `.overlay` del bloque `@layer components` actual: **reemplazar** por las del nuevo sistema (variantes Button §4.1, Card §5.1). Mientras coexistan, ambas clases deben renderizar idéntico — los aliases lo garantizan.

**Impacto en SASS legacy** (`styles/sass/utilities/_variables.scss`):

- `$primary: var(--color-primary)` → sigue funcionando, pero ahora resuelve al nuevo brand-600 (azul Kresna). Posible deriva visual en módulos que usaron `$primary` decorativamente — auditar.
- `$primary-hover: var(--color-primary-hover)` → idem.
- Los SASS aliases camelCase legacy (`$colorPrimary`, etc.) seguir funcionando porque apuntan a la misma var.
- `styles/sass/basics/_tokens.scss` ya no es la fuente — frontend-lead debe **vaciarlo** (dejar solo un comentario apuntando a `src/main.css`) o mantenerlo idéntico a `@theme` mientras dura la migración. Recomiendo vaciarlo: la deuda de mantener dos fuentes ya nos costó tiempo (el bug del navbar `--color-active-menu` ausente).

**Plan de migración propuesto** (ejecuta `frontend-lead`):

1. Reemplazar el `@theme` block en `src/main.css` con el de §2.
2. Vaciar `styles/sass/basics/_tokens.scss` (mantener solo el `:root {}` con un comentario apuntando a `@theme`).
3. Cargar DM Sans + Caveat en `inc/libraries.php` vía `wp_enqueue_style` Google Fonts.
4. Auditar módulos uno a uno. Por cada módulo, validar que usa utilidades Tailwind (no clases legacy SASS) y que el render visual no diverge >5 % del actual (excepto donde el rediseño lo pide explícitamente).
5. Borrar `--color-sandwich` y aliases obsoletos cuando ningún módulo los referencie.

---

## 9. Open Questions

Decisiones humanas necesarias **antes** de que `frontend-lead` implemente:

1. **Color secundario / accent expresivo** — Hoy `--color-secondary` apunta a `--color-muted` (sin presencia visual). Opciones:
   - (a) Verde lima Kresna (~`oklch(0.85 0.20 130)`) como accent puntual en CTAs alternativos. Rompe la disciplina "1 color de marca" pero da personalidad de agencia.
   - (b) Naranja shadcn-friendly (`oklch(0.78 0.18 65)`) como accent neutro, alineado con el "sandbox shadcn".
   - (c) Sin accent secundario — todos los acentos van con brand y semantic. **Recomendación mía**: (c) hasta que aparezca un caso de uso real.

2. **Mapeo Caveat sitio #3** — Confirmamos que el tercer sitio autorizado es el "overline opcional de hero/CTA". Pregunta: ¿queremos que el cliente pueda activarlo desde ACF (campo `overline_manuscrito`) o lo gestionamos como decisión de diseño (sólo home y página de campañas)?

3. **Toggle dark mode — global o por página** — Recomendación §7.5 es global persistido en localStorage con default `prefers-color-scheme`. Confirmar antes de que `frontend-lead` implemente el JS y el switch UI.

4. **Hex exacto `#f0f1f5` vs OKLCH neutro en card derecha del footer** — La deriva es ΔE ~1.5 (imperceptible para 95 % de gente). ¿Aceptamos la deriva o creamos `--color-card-soft` solo para el footer? Misma decisión para el body color `#2d3148`.

5. **Lucky cube — ¿quién lo dibuja?** — El cube actualmente es un SVG/CSS divs. Para que sea reusable en CTA y 404, necesitamos un partial PHP en `template-parts/atoms/lucky-cube.php`. ¿Lo abordamos en esta Fase 1 o en Fase 2? Si Fase 2, en Fase 1 el cube vive embebido solo en el footer.

6. **Vídeo en card brand — peso y formato** — La card brand-video del footer carga un vídeo. ¿Especificación de peso máx (sugiero <1.5 MB), formato (MP4 H.264 + WebM AV1 fallback), duración bucle (sugiero 6-10s)? Esto impacta LCP. Coordinar con `seo-manager`.

7. **Campos ACF — ¿renombrar?** — La estructura actual (`titulo`, `subtitulo`, `imagen_fondo`, `boton_principal`, etc.) sigue funcionando. Si el rediseño introduce el overline manuscrito, el slot del cube y el toggle de variante (surface/brand/video) en CTA, propongo a `frontend-lead` añadir:

| Módulo | Campo nuevo | Tipo | Uso |
|---|---|---|---|
| `hero` | `overline_manuscrito` | Text | Caveat overline opcional |
| `hero` | `tipo_fondo` | Select (image/video) | Permite vídeo bg |
| `hero` | `video_fondo` | File | MP4/WebM |
| `cta` | `variante` | Select (surface/brand/video) | Reemplaza `fondo` actual |
| `cta` | `mostrar_cube` | True/False | Activa lucky cube decorativo |
| `cta` | `video_fondo` | File | Solo si variante = video |
| `testimonios` | `destacado` | True/False | Marca un testimonio para card surface elevada |

No tocar nombres existentes (`titulo`, `subtitulo`, `boton_principal`, …) — solo añadir.

---

## Resumen

Tres decisiones más importantes:

1. **shadcn como espina, Kresna como capa expresiva con presupuesto pequeño** — 80 % shadcn neutro, 20 % momentos Kresna (radios 28px, brand saturado, cube, manuscrito). Esto resuelve la tensión "queremos un sistema disciplinado pero con personalidad" sin caer en decoración por todas partes.
2. **OKLCH + dark mode como mirror estructural, no como tema** — los tokens de dark son un espejo semántico (no una segunda paleta inventada). El brand sube en luminosidad (600 → 400), las sombras se reemplazan por hairlines, el cube conserva su gradient. Esto evita la deuda de mantener dos paletas paralelas.
3. **Caveat con presupuesto de 3 sitios y nada más** — sin esta restricción la marca se vuelve infantil rápido. Los 3 sitios están definidos (footer overlines, footer column titles, hero/CTA overline opcional) y cualquier uso adicional pasa por revisión.

Open questions críticas que el humano debe resolver antes de implementar:

- **Q1**: ¿Hay color accent secundario o nos quedamos en "1 color de marca + semantic"? (Recomiendo lo segundo.)
- **Q3**: Toggle dark global con localStorage + `prefers-color-scheme`, ¿confirmamos?
- **Q5**: Lucky cube como template-part PHP reusable en Fase 1 o lo dejamos embebido al footer y lo extraemos en Fase 2?
- **Q7**: ¿Aprobamos los 7 campos ACF nuevos propuestos para hero/cta/testimonios?

Una vez resueltas, `frontend-lead` puede empezar la migración siguiendo el plan §8.
