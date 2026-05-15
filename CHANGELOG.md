# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.0.0] - 2024

### Añadido
- Sistema modular basado en ACF Flexible Content
- Configuración de Webpack 5 compatible con Node.js 18+
- Soporte para SASS/SCSS
- Transpilación de JavaScript ES6+ con Babel
- Browser Sync integrado para desarrollo
- Optimización automática de assets para producción
- Módulos base: Hero, Hero Blog, Post, Texto
- Personalización del login de WordPress
- Sistema de menús personalizado
- Widgets personalizados
- Customizer de WordPress integrado
- Soporte para formatos de post personalizados
- Documentación completa del proyecto

### Actualizado
- Webpack actualizado de v4 a v5
- Todas las dependencias actualizadas para compatibilidad con Node.js 18+
- Reemplazado `optimize-css-assets-webpack-plugin` por `css-minimizer-webpack-plugin`
- Reemplazado `uglifyjs-webpack-plugin` por `terser-webpack-plugin`
- Eliminado `file-loader` (ahora usa `asset/resource` nativo de Webpack 5)
- Eliminado `node-sass` (ahora usa `sass` - dart-sass)
- `clean-webpack-plugin` actualizado a v4
- `mini-css-extract-plugin` actualizado a v2
- `css-loader` actualizado a v6
- `sass-loader` actualizado a v13
- `babel-loader` actualizado a v9
- `@babel/core` y `@babel/preset-env` actualizados a v7.23.0

### Cambiado
- Estructura de configuración de Webpack para Webpack 5
- Sistema de assets ahora usa módulos nativos de Webpack 5
- Configuración de minimizadores actualizada

### Documentación
- README.md completamente reescrito con documentación detallada
- SECURITY.md actualizado con política de seguridad
- CHANGELOG.md creado para seguimiento de versiones

---

## Tipos de Cambios

- **Añadido**: Para nuevas funcionalidades
- **Cambiado**: Para cambios en funcionalidades existentes
- **Deprecado**: Para funcionalidades que serán eliminadas pronto
- **Eliminado**: Para funcionalidades eliminadas
- **Corregido**: Para correcciones de bugs
- **Seguridad**: Para vulnerabilidades de seguridad

[1.0.0]: https://github.com/tu-usuario/tu-repo/releases/tag/v1.0.0
