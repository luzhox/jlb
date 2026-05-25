#!/usr/bin/env bash
#
# PostToolUse hook — recordatorio de seguridad para módulos del boilerplate.
#
# Se dispara después de cada Edit / Write cuyo path coincide con
# modules/<slug>/<algo>.php. Inyecta un mensaje en stdout que Claude lee como
# contexto adicional, sugiriendo correr /qa-modulo <slug> o invocar al agente
# wp-security antes de cerrar el cambio.
#
# Entrada (stdin): JSON con la forma { "tool_input": { "file_path": "..." }, ... }
# Salida (stdout): texto plano — se concatena al contexto del modelo.
# Salida (exit code): 0 siempre (no bloqueante).
#
# Para deshabilitar localmente sin tocar el versionado, añade en
# .claude/settings.local.json:
#   { "hooks": {} }   # override total
# o un override más específico si tu harness lo soporta.

set -euo pipefail

# Leer JSON de stdin. Si no hay jq, fallback a grep.
input="$(cat)"

if command -v jq >/dev/null 2>&1; then
    file_path="$(printf '%s' "$input" | jq -r '.tool_input.file_path // empty' 2>/dev/null || true)"
else
    file_path="$(printf '%s' "$input" | grep -oE '"file_path"[[:space:]]*:[[:space:]]*"[^"]+"' | head -1 | sed -E 's/.*"file_path"[[:space:]]*:[[:space:]]*"([^"]+)".*/\1/' || true)"
fi

# Si no hay file_path, salir silenciosamente.
[ -z "${file_path:-}" ] && exit 0

# Solo nos interesan archivos PHP dentro de modules/<slug>/.
case "$file_path" in
    *"/modules/"*"/"*.php|modules/*/*.php)
        ;;
    *)
        exit 0
        ;;
esac

# Extraer el slug: parte después de modules/ y antes del próximo /.
slug="$(printf '%s' "$file_path" | sed -E 's|.*/modules/([^/]+)/.*|\1|')"

# Si la extracción falló (slug == file_path), salir.
[ "$slug" = "$file_path" ] && exit 0

cat <<EOF
🔒 wp-security-reminder

Detecté un cambio en modules/${slug}/. Antes de cerrar este cambio:

  • Si modificaste lógica (no solo Tailwind/copys), corre:
      /qa-modulo ${slug}

  • O invoca directamente al agente wp-security para revisar
    el archivo editado: ${file_path}

  • Para cambios puramente cosméticos (clases CSS, textos visibles,
    espaciado), este aviso se puede ignorar.

Recordatorio automático — desactivable en .claude/settings.local.json.
EOF

exit 0
