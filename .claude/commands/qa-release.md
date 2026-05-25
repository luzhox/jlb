---
description: Auditoría pre-release de la rama actual (3 agentes en paralelo + sanity checks)
---

# /qa-release

Auditoría pre-merge sobre la rama actual contra `master`. Coordina los tres agentes
especializados en paralelo, corre sanity checks automatizables, y emite un veredicto
ejecutivo.

## Cuándo usarlo

- Antes de abrir un PR a `master`.
- Antes de tag/release.
- Después de un sprint que tocó múltiples módulos.

## Lo que debes hacer

### 1. Recolectar el alcance del cambio

Corre estos comandos en paralelo y úsa el output como contexto:

```bash
git status --porcelain
git diff --stat master...HEAD
git log --oneline master..HEAD
git diff master...HEAD -- '*.php'  # solo PHP
git diff master...HEAD -- 'src/' 'styles/' 'modules/'  # frontend
```

Si la rama actual es `master`, advierte al usuario y aborta — no tiene sentido comparar
master contra master.

### 2. Sanity checks automatizables (EN PARALELO)

Estos pasos no requieren agente; córrelos como Bash en paralelo:

1. **Lint PHP** — `find modules inc lib -name '*.php' -exec php -l {} \; 2>&1 | grep -v 'No syntax errors'`
   Cualquier output no vacío es BLOQUEANTE.

2. **Build de producción** — `npm run build 2>&1 | tail -20`
   Si falla, es BLOQUEANTE. Si pasa, verifica `build/.vite/manifest.json` exista.

3. **Manifest sano** — `test -f build/.vite/manifest.json && cat build/.vite/manifest.json | head -50`
   Que tenga al menos una entrada `main` con `file`.

4. **Secretos hardcodeados** — `git diff master...HEAD | grep -iE '(api[_-]?key|secret|password|token|bearer)\s*[:=]\s*["\047][^"\047]+' | head -20`
   Cualquier match es CRÍTICO (revisar con el usuario antes de continuar).

5. **`acf-json/` con cambios** — `git diff master...HEAD --name-only -- 'acf-json/'`
   Si hay archivos, es IMPORTANTE: contradice el contrato del boilerplate (ver
   `inc/acf-modules.php:18`). Los exports deben vivir en `inc/acf-modules.php`.

6. **`.env` o `wp-config.php`** — `git diff master...HEAD --name-only | grep -E '(\.env|wp-config\.php)$'`
   Si aparecen, es CRÍTICO — esos archivos no deben estar versionados con secretos.

### 3. Lanzar agentes EN PARALELO

Una sola tool call con tres invocaciones de `Agent`:

**Agente A — `frontend-lead`:**
```
Revisa la rama actual contra master. Diff resumido:
<pegar git diff --stat y archivos clave>

Audita desde la perspectiva de arquitectura del boilerplate:
  - Nuevos módulos siguen el patrón canónico (early return, escape, kebab/snake).
  - inc/acf-modules.php tiene los layouts registrados sin colisiones de key.
  - Vite: entries en src/main.js, manifest se genera.
  - Tailwind v4 tokens en src/main.css coherentes con _tokens.scss.
  - GSAP: nuevas animaciones usan data-gsap-* en lugar de wiring manual.
  - Atomic Design: nuevos componentes están en la capa correcta.

Devuelve hallazgos BLOQUEANTE / IMPORTANTE / SUGERENCIA con archivo:línea.
```

**Agente B — `wp-security`:**
```
Audita los cambios PHP de la rama actual contra master. Archivos modificados:
<lista de .php cambiados>

Aplica el contrato de seguridad estándar. Presta atención especial a:
  - Nuevos módulos en modules/<slug>/ que tocan campos ACF.
  - Cambios en inc/*.php (libraries, schema, seo, etc.).
  - Cualquier handler nuevo de AJAX/REST/forms.

Devuelve hallazgos CRÍTICO / ALTO / MEDIO / BAJO con archivo:línea.
```

**Agente C — `seo-manager`:**
```
Revisa la rama actual desde la perspectiva SEO y rendimiento:
  - inc/schema.php — JSON-LD coherente, sin duplicados.
  - inc/seo.php — meta description / canonical / OG / Twitter Cards no rotos.
  - LCP/CLS riesgos en módulos nuevos (imágenes sin width/height, fonts blocking).
  - Sitemap y robots.txt sin regresiones.
  - Core Web Vitals: build de Vite con code-splitting razonable, sin bundles > 300KB.

Devuelve hallazgos IMPORTANTE / SUGERENCIA con archivo:línea o métrica.
```

### 4. Consolidar y reportar

```
# QA pre-release — rama `<branch>` vs `master` — <fecha>

## Veredicto
**<APTO | APTO CON CAMBIOS | NO APTO>**

## Alcance
- Commits: <n>
- Archivos modificados: <n> (PHP: <n>, JS/CSS: <n>, otros: <n>)
- Módulos tocados: <lista>

## Sanity checks
- ✅ php -l: sin errores
- ✅ npm run build: OK (manifest generado)
- ❌ Secretos: <match encontrado> ← CRÍTICO
- ✅ acf-json/: sin cambios
- ✅ .env / wp-config.php: no versionados

## Hallazgos por dominio

### Arquitectura (frontend-lead)
- 🔴 BLOQUEANTE [archivo:línea] ...
- 🟡 IMPORTANTE [archivo:línea] ...
- 🔵 SUGERENCIA [archivo:línea] ...

### Seguridad (wp-security)
- 🔴 CRÍTICO  [archivo:línea] ...
- 🟠 ALTO     [archivo:línea] ...
- 🟡 MEDIO    [archivo:línea] ...
- 🔵 BAJO     [archivo:línea] ...

### SEO + Performance (seo-manager)
- 🟡 IMPORTANTE [archivo:línea] ...
- 🔵 SUGERENCIA [archivo:línea] ...

## Próximos pasos
1. (resolver bloqueantes en orden)
2. (re-correr /qa-release)
3. Abrir PR cuando veredicto sea APTO o APTO CON CAMBIOS aceptados.
```

**Reglas de veredicto:**
- **NO APTO** — cualquier BLOQUEANTE, CRÍTICO, o sanity check fallido.
- **APTO CON CAMBIOS** — solo IMPORTANTE / ALTO / MEDIO.
- **APTO** — solo SUGERENCIA / BAJO o nada.

**No abrir PR si el veredicto es NO APTO.** Indícalo explícitamente al usuario.

## Qué NO hacer

- No edites archivos automáticamente — solo reporta.
- No corras los agentes en serie — paraleliza.
- No omitas los sanity checks por "ahorrar tokens" — son rápidos y atrapan errores básicos.
- No emitas APTO si hay siquiera un CRÍTICO o BLOQUEANTE, aunque el resto esté limpio.
