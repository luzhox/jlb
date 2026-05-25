---
description: Loop de corrección visual (qa-visual diagnostica → ui-senior valida → frontend-lead implementa → qa-visual re-valida) contra Figma o un síntoma reportado
argument-hint: <sección/módulo o URL> [+ link Figma node-id] [+ qué se ve mal]
---

# /correccion-visual $ARGUMENTS

Corrige discrepancias visuales de una sección/módulo del front contra su
diseño (Figma) o contra un síntoma reportado. Orquesta **tres agentes** en un
loop corto y verificable:

```
qa-visual (diagnóstico) → ui-senior (criterio UX) → frontend-lead (implementación) → qa-visual (re-validación)
        └──────────────────────── repetir hasta APTO o reportar bloqueante ◄────────────────┘
```

A diferencia de `/requerimiento` (que construye algo nuevo en 5 etapas), este
command **arregla algo que ya existe y se ve mal**. Es más rápido y enfocado en
paridad visual + no-regresión.

## Argumentos

`$ARGUMENTS` es texto libre que debe contener, en cualquier orden:

- **Target (obligatorio)** — qué arreglar: nombre de sección/módulo
  (`jlb-experience`, "slider de testimoniales", "footer") o una URL
  (`http://jlb-school.local/`, una página interna).
- **Referencia Figma (opcional pero recomendado)** — un link
  `figma.com/design/<fileKey>/...?node-id=<id>` del componente/sección. Si se
  da, la comparación es **pixel-perfect contra ese nodo**. Archivo Figma del
  home JLB conocido: fileKey `HwpjEfjaNCY1d0sEYqYFSE`.
- **Síntoma (opcional)** — qué se ve mal en palabras del usuario
  ("el play no abre", "las flechas se congelan", "doble play", "overlay tapa el
  texto"). Orienta el diagnóstico.

Si falta el target, **pídelo antes de arrancar**. Si no hay referencia Figma ni
síntoma, pídele al usuario al menos uno (no se puede "corregir" sin un criterio
de qué está mal).

## Etapa 0 — Encuadre (tú, antes de delegar)

1. Identifica el/los **selector(es) y archivo(s)** del target:
   - Módulo → `modules/<slug>/<slug>.php` + `styles/sass/organisms/_<slug>.scss`
     (recuerda snake↔kebab) + JS en `src/` si aplica.
   - Confirma la clase CSS real del `<section>` (no asumas: p.ej. noticias usa
     `.jlb-news`, no `.jlb-noticias`).
2. Si hay link Figma, extrae `fileKey` y `node-id`.
3. **Verifica cómo se sirven los assets** (clave, ver Gotchas):
   - Lee `wp-config.php`: ¿`VITE_DEV_SERVER` está en `true`?
   - Si **true** + `npm run dev` corriendo → los cambios SCSS/JS se ven al
     instante por HMR. Confírmalo (`curl -s localhost:5173/@vite/client`).
   - Si **false** → el sitio sirve `build/` y **cualquier cambio CSS/JS NO se
     verá hasta `npm run build`**. Avísale al usuario y ofrece: (a) activar dev
     mode, o (b) rebuild tras cada lote. No sigas a ciegas.
4. Imprime un mini-plan: target, archivos, referencia, y qué agentes correrás.

## Etapa 1 — `qa-visual` (diagnóstico)

Invoca `qa-visual` para fotografiar el estado actual y compararlo con la
referencia. Consigna:

- Capturar el target en vivo con Playwright (`qa/lib/wp-playwright.mjs`,
  credenciales en `qa/.env`):
  - **Desktop = 1440px** (ancho del frame Figma → objetivo pixel-perfect).
    Tablet (834) y mobile (390) solo para "no se rompe".
  - **Sin admin bar** (sesión deslogueada o `show_admin_bar=false`): Figma no
    la tiene y descuadra el header sticky.
  - **Animaciones desactivadas** para comparar el estado final
    (`*{animation:none!important;transition:none!important}` o
    `prefers-reduced-motion`), y scroll completo para disparar lazy/GSAP.
  - `element.screenshot()` sobre el selector real de la sección.
- Si hay referencia Figma: traerla con el MCP de Figma (`get_screenshot` del
  `node-id` a `maxDimension` ≈ borde largo del nodo; `get_variable_defs` para
  colores/medidas dudosas) y guardarla en `qa/figma-ref/`.
- Reportar una **lista de discrepancias concretas y medibles**: tipografía
  (familia/tamaño/peso/line-height/color), espaciados, colores, radios,
  posición/recorte de imágenes, orden y presencia de elementos, estados.
  Cuantificar cuando se pueda ("título 48px vs 44px", "gap 32 vs 44").
- Reportar **errores de consola JS**.
- Distinguir explícitamente: ¿la discrepancia es de **código** (CSS/markup) o de
  **datos** (contenido ACF de la página)? Una sección puede "faltar" porque el
  módulo no está agregado en la página, no porque el código falle.

**Output:** lista priorizada de hallazgos (BLOQUEANTE / IMPORTANTE / MENOR) con
rutas de evidencia (`qa/figma-ref/*` vs `qa/live/*`).

## Etapa 2 — `ui-senior` (criterio UX)

Invoca `ui-senior` con la lista de discrepancias. Su job:

- Validar cuáles discrepancias son **reales** y cuáles son aceptables (p.ej.
  diferencias de copy/longitud de texto no son bugs de layout).
- Definir el **criterio de aceptación visual** para cada fix: qué debe cumplirse
  para considerarlo "igual a Figma" en desktop.
- Decidir el comportamiento **responsive** (tablet/mobile son propuesta nuestra,
  no pixel-perfect): qué debe reflujar y cómo.
- Señalar invariantes de UX/accesibilidad a respetar (área clickeable, foco,
  contraste, reduced-motion).

**Output:** criterios de aceptación + decisiones responsive + notas a11y. Si una
discrepancia depende de una decisión del usuario (p.ej. "¿1 o varios items?"),
**pausa y pregunta** antes de implementar.

## Etapa 3 — `frontend-lead` (implementación)

Invoca `frontend-lead` con: hallazgos (Etapa 1) + criterios (Etapa 2) + archivos
del target. Implementa las correcciones respetando las invariantes del
boilerplate (early return, escape, snake/kebab, template-parts, GSAP por
data-attrs, tokens en el orden `src/main.css` → `_tokens.scss`).

Debe tener presentes los **Gotchas** de abajo (cascada `@layer`, datos ACF,
plays horneados, etc.). Si la corrección es de **datos** y no de código, usar
WP-CLI vía el socket de Local (ver Gotchas) para inspeccionar/sembrar el
contenido de la página — y reflejar el cambio en el seeder (`bin/seed-jlb.php`)
para que no regrese.

**Output:** archivos creados/modificados + qué cambió y por qué.

## Etapa 4 — `qa-visual` (re-validación)

Vuelve a invocar `qa-visual` con los criterios de aceptación de la Etapa 2:

- Re-capturar el target (mismas condiciones que Etapa 1) y confirmar que cada
  criterio se cumple.
- **No-regresión**: revisar que las secciones vecinas (y el header/footer) no se
  rompieron, y que no aparecieron errores JS.
- Validar tablet/mobile ("no se rompe").

Si queda algún criterio sin cumplir → **vuelve a Etapa 3** (loop). Máximo
sugerido: 3 vueltas; si tras eso sigue fallando, reporta el bloqueante en vez de
seguir iterando a ciegas.

## Etapa 5 — Consolidación

```
# Corrección visual: <target>
Fecha: <yyyy-mm-dd>   Referencia: <node-id Figma o "síntoma reportado">

## Veredicto
**APTO | APTO CON PENDIENTES | BLOQUEADO**

## Discrepancias → fixes
| # | Hallazgo (Etapa 1) | Severidad | Fix aplicado | Estado |
|---|---|---|---|---|
| 1 | ... | BLOQUEANTE | ... | ✅ |

## Archivos tocados
- modules/...  ·  styles/sass/organisms/_...  ·  src/...
- (datos) page_id <n> vía WP-CLI  ·  bin/seed-jlb.php actualizado

## Evidencia
- figma-ref: qa/figma-ref/<...>.png
- antes/después: qa/live/<...>.png

## Pendientes / decisiones del usuario
- ...

## Recordatorio de publicación
- Si se está en dev mode, para producción: `npm run build` + `VITE_DEV_SERVER`
  en `false`.
```

## Gotchas del proyecto (que el frontend-lead DEBE conocer)

Estas son lecciones reales de este boilerplate — evítan horas de depuración:

1. **Dev vs build.** Con `VITE_DEV_SERVER=false` el sitio sirve `build/`; ningún
   cambio SCSS/JS se ve sin `npm run build`. Confírmalo en Etapa 0.
2. **Cascada `@layer`.** Todo `styles/sass/style.scss` se compila dentro de
   `@layer legacy`/`@layer components` (capas de baja prioridad). El CSS de
   **vendors sin capa (Swiper, etc.) GANA SIEMPRE**, sin importar especificidad.
   Si un override a vendor no aplica (típico: `display`), usa `!important` dentro
   de la capa o sube especificidad — no pierdas tiempo creyendo que es caché.
   (Síntoma clásico: el slide con `display:grid` aparece como `block` y la
   columna de la imagen colapsa a `height:0`.)
3. **Datos ACF ≠ código.** El home (`front-page.php`) renderiza desde los
   módulos ACF de la página front (`page_on_front`, hoy id 68), **no** desde el
   fallback estático. Si una sección "falta", probablemente el módulo no está
   agregado a la página. Inspecciona/edita con WP-CLI vía el socket de Local:
   ```bash
   PHP="/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php"
   WP="/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar"
   SOCK="$(ls -t "$HOME/Library/Application Support/Local/run/"*/mysql/mysqld.sock | head -1)"
   SITE="/Users/luismorales/Local Sites/jlb-school/app/public"
   "$PHP" -d mysqli.default_socket="$SOCK" "$WP" --path="$SITE" eval-file <script.php>
   ```
   (`wp db query` falla porque usa el cliente mysql del sistema con
   `/tmp/mysql.sock`; usa `wp eval`/`eval-file`, que sí respeta el socket.)
   Tras cambiar datos, refleja el cambio en `bin/seed-jlb.php`.
4. **Plays "horneados".** Varios PNG de `assets/figma-home/` (los `video-*.png`)
   traen el botón play dibujado en la imagen. Si además agregas un play por CSS,
   sale doble. Decide: cubrir el horneado con el play CSS centrado, o no añadir.
5. **`element.screenshot()` y el header sticky.** El header fijo puede aparecer
   sobre la captura de una sección; no es bug real, es artefacto de captura.
6. **Pixel-perfect = desktop 1440.** Tablet/mobile son propuesta responsive
   nuestra, NO se comparan pixel-perfect contra Figma.

## Cuándo saltar agentes

| Caso | Agentes |
|---|---|
| Discrepancia visual clara vs Figma | los 3 (diagnóstico → criterio → fix → re-QA) |
| Bug visual obvio sin matiz UX (ej. typo en un color) | qa-visual → frontend-lead → qa-visual |
| Duda de diseño (¿cómo debería verse?) | ui-senior primero, luego decidir |

Cuando saltes un agente, decláralo al inicio y por qué.

## Qué NO hacer

- No declarar APTO sin la **re-validación** (Etapa 4) con evidencia after.
- No editar código en las etapas de diagnóstico/QA — eso es de la Etapa 3.
- No tocar el **header/menú JLB** salvo que el target sea explícitamente ese
  (está aprobado pixel-perfect).
- No asumir que "no se ve el cambio" = "el fix está mal": primero descarta
  dev/build y cascada `@layer`.
- No comparar tablet/mobile contra Figma como si fueran pixel-perfect.
