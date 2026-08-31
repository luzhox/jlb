# Fuentes self-hosted del tema JLB

## KG Second Chances Solid (fuente principal del cliente)

Esta fuente **no está en Google Fonts** y requiere **licencia comercial** de
Kimberly Geswein. Por eso se sirve desde aquí (self-hosting), no por CDN.

### Cómo activarla

1. Consigue el archivo con licencia y conviértelo a **WOFF2** (recomendado).
   Si solo tienes `.ttf`/`.otf`, conviértelo en https://transfonter.org/ o
   https://cloudconvert.com/ttf-to-woff2 (marca solo WOFF2 + WOFF).
2. Copia el/los archivo(s) en **esta carpeta** con este nombre exacto:

   ```
   fonts/KGSecondChancesSolid.woff2   ← recomendado (todos los navegadores modernos)
   fonts/KGSecondChancesSolid.woff    ← opcional, fallback navegadores viejos
   fonts/KGSecondChancesSolid.ttf     ← opcional, último recurso
   fonts/KGSecondChancesSolid.otf     ← opcional, último recurso
   ```

3. Listo. No hay que tocar código ni recompilar: `inc/libraries.php` detecta el
   archivo (`file_exists`), declara el `@font-face` con `font-display: swap` y
   precarga el WOFF2 automáticamente.

### Mientras el archivo NO exista

El CSS usa `font-family: "KG Second Chances Solid", "Caveat", cursive`, así que
cae a **Caveat** (cargada por Google Fonts). No se rompe nada; simplemente no se
ve la fuente de marca hasta que subas el archivo.

### Por qué "se rompía al subir"

En local se veía bien porque la fuente estaba **instalada en el sistema
operativo** del diseñador. En producción nadie la tiene instalada → el navegador
caía a Caveat/cursive. El self-hosting la empaqueta con el sitio y la sirve a
todos los visitantes.

> Nombre de familia CSS: `"KG Second Chances Solid"` — no cambiar; los 16
> parciales SCSS de `styles/sass/organisms/` lo referencian con ese nombre.
