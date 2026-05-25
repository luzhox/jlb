---
description: Workflow completo (ui-senior → frontend-lead → qa-visual → seo-manager → wp-security) para un requerimiento
argument-hint: <descripción del requerimiento>
---

# /requerimiento $ARGUMENTS

Procesa un requerimiento completo del proyecto JLB en **pipeline de 5 etapas**,
una por agente especializado. Cada etapa puede bloquear el progreso si encuentra
algo crítico. Resultado final: un reporte consolidado APTO / APTO CON CAMBIOS /
NO APTO.

## Argumentos

- **`$ARGUMENTS`** — descripción del requerimiento en lenguaje natural. Puede ser
  un párrafo, una lista de bullets, o un link a un ticket/diseño. Ejemplo:
  > `/requerimiento Necesito un módulo "horario de clases" con tabla por nivel
  >  educativo. Datos: día, hora inicio/fin, asignatura. Filtrable por nivel.`

Si los argumentos vienen vacíos, pide al usuario una descripción antes de
arrancar.

## Etapas del pipeline

### Etapa 0 — Estructuración del prompt (tú, antes de delegar)

**Antes de invocar a ningún agente**, reformula `$ARGUMENTS` (que viene como
texto libre del usuario) en un brief estructurado de best-practice. Pega
ESE brief —no el texto original— a las siguientes etapas. La consigna estricta:

> Toma el prompt original del usuario y reescríbelo aplicando estructura
> profesional (rol, objetivo, contexto, restricciones, entregables, criterios
> de éxito) y formato claro para lograr un resultado preciso y completo.
> **Apégate estrictamente al objetivo solicitado**, analizando con cuidado lo
> que pide el prompt original. No inventes scope, no agregues features que el
> usuario no pidió, no asumas implicaciones no declaradas.

Formato obligatorio del brief estructurado:

```markdown
# Brief estructurado — <título corto inferido del requerimiento>

## Rol
<Para qué tipo de profesional/agente está pensada la tarea principal —
ej. "Arquitecto frontend WordPress que implementa un módulo ACF Flexible
Content nuevo".>

## Objetivo
<Una frase que captura QUÉ se debe lograr al final del pipeline. Verbo en
infinitivo, sin floritura. Ej. "Construir un módulo 'horario de clases'
administrable desde wp-admin que renderice una tabla filtrable por nivel
educativo en la página de cada nivel.">

## Contexto
- <Repo, stack, ubicación de archivos clave relevantes para este requerimiento.>
- <Patrones del boilerplate que aplican: módulo ACF Flexible Content, módulo
  jlb-*, options page, etc.>
- <Restricciones del proyecto vigentes: PHP-only ACF, no acf-json/, Tailwind
  v4 + SASS, GSAP scanner por data-attrs, etc.>

## Restricciones (lo que NO se debe hacer)
- <Cosas que el usuario explícitamente excluyó O que violarían invariantes
  del boilerplate. Si el usuario no dijo nada, lista al menos las invariantes
  documentadas en CLAUDE.md que aplican al cambio.>

## Entregables
- <Lista verificable de outputs concretos: archivos, campos ACF, screenshots,
  tests, etc. Cada item es algo que se puede tachar como ✓ al final.>

## Criterios de éxito
- <Cómo se determina que el requerimiento está terminado. Idealmente medibles
  o verificables visualmente.>

## Preguntas abiertas (si las hay)
- <Si el prompt original es ambiguo o falta información crítica, listar aquí
  las preguntas. Si hay alguna, **detén el pipeline** y pregunta al usuario
  antes de avanzar a Etapa 1.>
```

**Reglas estrictas para esta etapa:**

1. **No inventar scope.** Si el usuario pidió "un módulo de horario", no
   agregues "y un panel de admin de profesores". Cada bullet en Entregables
   debe ser inferible directamente del prompt original.
2. **Si hay ambigüedad**, levantala en "Preguntas abiertas" y **pausa el
   pipeline** — preguntar al usuario es siempre más barato que rehacer.
3. **Cuando el prompt original ya viene estructurado** (el usuario escribió
   secciones), respeta su estructura y solo añade lo que falte.
4. **Lenguaje del brief: español** (todo el proyecto es en español).
5. Imprime el brief al usuario como confirmación antes de pasar a Etapa 1.
   Si el usuario corrige, ajustas y vuelves a confirmar.

Una vez que el brief estructurado está confirmado (explícita o
implícitamente — si el usuario no objeta en su siguiente mensaje y
continúa, asumes OK), úsalo como input a Etapa 1.

### Etapa 1 — `ui-senior` (diseño UX, secuencial)

Invoca al agente `ui-senior` con el requerimiento. Su job:
- Validar viabilidad UX.
- Proponer estructura de información (qué campos ve el usuario, qué jerarquía).
- Identificar componentes del design system existentes que aplican (Figma /
  atoms / molecules).
- Especificar estados (vacío, error, loading) y responsive.

**Output esperado:** brief de UX con wireframe textual y referencias al sistema.

**Bloqueante:** si ui-senior identifica que el requerimiento es ambiguo o
contradice una heurística, pídele al usuario aclaración antes de continuar.

### Etapa 2 — `frontend-lead` (implementación, secuencial)

Invoca `frontend-lead` pasándole el brief de ui-senior + el requerimiento
original. Su job:
- Diseñar la arquitectura técnica (módulo ACF? Bloque Gutenberg? Refactor
  existente?).
- Implementar archivos: PHP módulo, registro ACF en `inc/acf-modules.php`,
  estilos SASS/Tailwind, JS/GSAP si aplica.
- Respetar invariantes del boilerplate: early return, escape, snake/kebab,
  template-parts.

**Output esperado:** lista de archivos creados/modificados + descripción de la
implementación.

**Bloqueante:** si frontend-lead detecta que la propuesta UX requiere romper
una invariante de arquitectura, vuelve a ui-senior con la objeción.

### Etapa 3 — `qa-visual` + `seo-manager` + `wp-security` (EN PARALELO, revisores)

Una vez implementado, lanza los tres agentes **en una sola tool call** con
múltiples invocaciones de Agent (paralelo). Son revisores independientes:

- **`qa-visual`** — valida con Playwright que la UI funciona en wp-admin y
  frontend (login → navegar → interactuar → screenshot + asserts). Reporta
  pass/fail con evidencia.
- **`seo-manager`** — audita impacto SEO: Core Web Vitals (LCP/CLS/INP),
  structured data, meta tags, canonical, sitemap. Reporta IMPORTANTE /
  SUGERENCIA.
- **`wp-security`** — audita seguridad del PHP modificado: XSS, CSRF, SQLi,
  caps, uploads, headers, secrets. Reporta CRÍTICO / ALTO / MEDIO / BAJO.

Bloqueante: si wp-security encuentra CRÍTICO o ALTO, o qa-visual reporta FALLA,
no se puede mergear sin corregir.

### Etapa 4 — Consolidación + veredicto

Sintetiza los outputs de las etapas en un único reporte. Encabezado obligatorio:
incluir el **brief estructurado** generado en Etapa 0 textualmente, para que la
trazabilidad sea completa (lo que se pidió ↔ lo que se entregó).

```
# Requerimiento: <título corto del brief estructurado>
Fecha: <yyyy-mm-dd>

## Brief estructurado (de Etapa 0)
<el brief textual que produjo la Etapa 0>

## Veredicto final
**APTO | APTO CON CAMBIOS | NO APTO**

## Resumen ejecutivo
- (qué se construyó, archivos clave)
- (decisiones de diseño tomadas)

## Etapa 1 — UX (ui-senior)
- Brief: ...
- Componentes del DS reutilizados: ...
- Estados especiales: ...

## Etapa 2 — Implementación (frontend-lead)
- Archivos creados/modificados: ...
- Patrón aplicado: ...
- Deuda introducida (si la hay): ...

## Etapa 3 — Reviews paralelos

### qa-visual
- 🟢 Pass / 🔴 Falla
- Screenshots: qa/<...>.png
- Asserts validados: ...

### seo-manager
- 🟡 IMPORTANTE / 🔵 SUGERENCIA con archivo:línea

### wp-security
- 🔴 CRÍTICO / 🟠 ALTO / 🟡 MEDIO / 🔵 BAJO con archivo:línea

## Próximos pasos
1. (resolver bloqueantes si los hay)
2. (re-correr el pipeline tras corregir)
3. Si APTO → commit + PR; si APTO CON CAMBIOS → discutir trade-offs;
   si NO APTO → no abrir PR.
```

## Reglas de veredicto

- **NO APTO** — si wp-security devuelve CRÍTICO **o** qa-visual reporta FALLA
  **o** frontend-lead detectó violación de invariante no resuelta.
- **APTO CON CAMBIOS** — solo IMPORTANTE / ALTO / MEDIO / SUGERENCIA en
  reviews; UX implementada como brief.
- **APTO** — solo BAJO / SUGERENCIA o nada en reviews; qa-visual pasa.

## Cuándo SALTAR una etapa

A veces el requerimiento no necesita las 5. Aplicar criterio:

| Tipo de cambio | Etapas necesarias |
|---|---|
| Módulo nuevo / refactor UI grande | Las 5 |
| Cambio solo PHP/backend (sin UI nueva) | frontend-lead → wp-security |
| Solo copy o microcopy | ui-senior → frontend-lead → qa-visual |
| Solo SEO técnico (sitemap, schema) | seo-manager → frontend-lead → wp-security |
| Bug fix puntual sin cambio UX | frontend-lead → qa-visual → wp-security |

Cuando saltes etapas, **declara explícitamente** al inicio del pipeline cuáles
y por qué.

## Paralelismo crítico

En Etapa 3, los tres revisores DEBEN lanzarse en **una sola tool call con
varios bloques `Agent`** — no secuencialmente. Son independientes y revisan
los mismos archivos. Si los lanzas en serie, multiplicas el tiempo por 3
innecesariamente.

## Qué NO hacer

- No saltees `wp-security` si el cambio tocó PHP que maneja input.
- No declares APTO si hay BLOQUEANTE / CRÍTICO sin resolver.
- No edites código durante la consolidación — eso es trabajo de las etapas 1-2.
  El reporte solo describe, no corrige.
- No abras PR automáticamente. La decisión final de commit/PR es del usuario.
