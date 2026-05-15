# Guía de Contribución

Gracias por tu interés en contribuir a este proyecto. Esta guía te ayudará a entender cómo puedes participar.

## 🚀 Inicio Rápido

1. Clona el repositorio
2. Instala las dependencias: `npm install`
3. Crea una rama para tu feature: `git checkout -b feature/mi-feature`
4. Realiza tus cambios
5. Compila para verificar: `npm run prod`
6. Haz commit de tus cambios
7. Crea un Pull Request

## 📝 Estándares de Código

### PHP

- Sigue los [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Usa indentación de 2 espacios (no tabs)
- Comenta funciones complejas
- Valida y sanitiza todas las entradas
- Escapa todas las salidas

### JavaScript

- Usa ES6+ cuando sea posible
- Sigue las convenciones de nomenclatura camelCase
- Comenta funciones complejas
- Mantén las funciones pequeñas y enfocadas

### CSS/SCSS

- Usa la metodología BEM cuando sea apropiado
- Organiza los estilos según la estructura del proyecto
- Usa variables SASS para colores, espaciados, etc.
- Comenta secciones importantes

## 🧪 Testing

Antes de enviar un PR:

- [ ] El código compila sin errores (`npm run prod`)
- [ ] No hay errores de PHP (verifica con PHP_CodeSniffer si está disponible)
- [ ] Los estilos se ven correctos en diferentes navegadores
- [ ] El JavaScript funciona correctamente
- [ ] Los módulos se renderizan correctamente

## 📦 Estructura de Commits

Usa mensajes de commit descriptivos:

```
feat: Agregar nuevo módulo de galería
fix: Corregir error en hero module
docs: Actualizar documentación de instalación
style: Ajustar espaciado en componentes
refactor: Reorganizar funciones helper
```

## 🔀 Proceso de Pull Request

1. **Actualiza tu rama**: Asegúrate de que tu rama esté actualizada con la rama principal
2. **Describe tus cambios**: Explica qué cambiaste y por qué
3. **Incluye screenshots**: Si hay cambios visuales, incluye capturas
4. **Menciona issues**: Si tu PR resuelve un issue, menciónalo

## 🐛 Reportar Bugs

Si encuentras un bug:

1. Verifica que no haya sido reportado ya
2. Crea un nuevo issue con:
   - Descripción clara del problema
   - Pasos para reproducir
   - Comportamiento esperado vs. actual
   - Versión de WordPress, PHP, Node.js
   - Screenshots si aplica

## 💡 Sugerir Features

Las sugerencias de nuevas funcionalidades son bienvenidas:

1. Crea un issue con la etiqueta "enhancement"
2. Describe la funcionalidad propuesta
3. Explica por qué sería útil
4. Si es posible, incluye ejemplos o mockups

## ❓ Preguntas

Si tienes preguntas sobre cómo contribuir:

- Revisa la documentación en README.md
- Busca en los issues existentes
- Crea un nuevo issue con la etiqueta "question"

## 📄 Licencia

Al contribuir, aceptas que tus contribuciones serán licenciadas bajo la misma licencia del proyecto (GPL v2 o posterior).

---

¡Gracias por contribuir! 🎉
