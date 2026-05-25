---
description: Auditoría cruzada de un módulo (frontend-lead + wp-security en paralelo)
argument-hint: <slug>
---

# /qa-modulo $ARGUMENTS

Audita un módulo existente del directorio `modules/<slug>/` invocando a dos agentes
especializados en paralelo y consolidando sus hallazgos en un único reporte.

## Argumentos

- **`<slug>`** — slug kebab-case del módulo a auditar (ej. `jlb-hero`, `testimonios-grid`).
  Debe existir como `modules/<slug>/<slug>.php`. Si no se proporciona o no existe, lista los
  módulos disponibles y pide al usuario que elija.

## Lo que debes hacer

### 1. Validar existencia

- Confirma que `modules/<slug>/<slug>.php` existe.
- Lee el archivo para tener contexto antes de lanzar a los agentes.
- Localiza el layout correspondiente en `inc/acf-modules.php` (`name => <slug_snake>`).

### 2. Lanzar agentes EN PARALELO

Envía **una sola tool call con dos invocaciones de `Agent`** (no secuencial):

**Agente A — `frontend-lead`:**
```
Audita el módulo modules/<slug>/<slug>.php desde la perspectiva de arquitectura del
boilerplate. Verifica las invariantes:

  1. Early return si el campo principal está vacío.
  2. Toda salida escapada (esc_html / esc_url / esc_attr / wp_kses_post).
  3. Convención de nomenclatura: kebab en filesystem/CSS, snake en ACF name.
  4. Uso de template-parts/atoms/* cuando corresponde (image.php, button.php).
  5. Atributos data-gsap-* coherentes (sin wiring JS manual).
  6. Sin dependencias jQuery innecesarias en código nuevo.
  7. Coherencia con la arquitectura Atomic Design (atoms/molecules/organisms).
  8. Layout ACF registrado en inc/acf-modules.php con name/key correctos, no en acf-json/.

Devuelve hallazgos clasificados como BLOQUEANTE / IMPORTANTE / SUGERENCIA con file:line.
```

**Agente B — `wp-security`:**
```
Audita el módulo modules/<slug>/<slug>.php contra los vectores estándar (XSS, CSRF, SQLi,
caps, uploads, etc.). Aplica el contrato de seguridad documentado en tu prompt.

Devuelve hallazgos clasificados como CRÍTICO / ALTO / MEDIO / BAJO con file:line.
```

### 3. Consolidar y reportar

Cuando ambos agentes respondan, sintetiza un único reporte:

```
# QA módulo `<slug>` — <fecha>

## Veredicto
<APTO | APTO CON CAMBIOS | NO APTO>

## Hallazgos consolidados

### Arquitectura (frontend-lead)
- 🔴 BLOQUEANTE [archivo:línea] descripción
- 🟡 IMPORTANTE [archivo:línea] descripción
- 🔵 SUGERENCIA [archivo:línea] descripción

### Seguridad (wp-security)
- 🔴 CRÍTICO  [archivo:línea] descripción
- 🟠 ALTO     [archivo:línea] descripción
- 🟡 MEDIO    [archivo:línea] descripción
- 🔵 BAJO     [archivo:línea] descripción

## Verificaciones que pasan
- ...

## Próximos pasos
1. (acción concreta)
2. ...
```

**Reglas de veredicto:**
- **NO APTO** — hay al menos un BLOQUEANTE o CRÍTICO.
- **APTO CON CAMBIOS** — solo IMPORTANTE / ALTO / MEDIO.
- **APTO** — solo SUGERENCIA / BAJO o nada.

### 4. Si el módulo no existe

No fabriques un reporte vacío. Avisa al usuario:

```
❌ No existe modules/<slug>/<slug>.php.

Módulos disponibles:
  - jlb-hero
  - jlb-manifesto
  - ...

¿Cuál querías auditar? (O usa /nuevo-modulo <slug> para crearlo.)
```

## Qué NO hacer

- No corras los agentes en serie — el paralelismo es parte del valor del comando.
- No edites el módulo automáticamente — solo reporta. El usuario decide qué corregir.
- No mezcles severidades de los dos agentes en una sola lista — mantén las dos secciones
  separadas para que el usuario sepa de quién viene cada hallazgo.
