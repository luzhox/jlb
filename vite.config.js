import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import fullReload from 'vite-plugin-full-reload'
import { resolve } from 'path'

export default defineConfig({
  plugins: [tailwindcss(), fullReload(['**/*.php'], { delay: 300 })],

  build: {
    outDir: 'build',
    emptyOutDir: false, // no borrar build/ existente (Webpack convive durante transición)
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/main.js'),
      },
      external: ['jquery'],
      output: {
        globals: { jquery: 'jQuery' },
        entryFileNames: 'js/[name]-[hash].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: ({ name }) => (/\.css$/.test(name ?? '') ? 'css/[name]-[hash][extname]' : 'assets/[name]-[hash][extname]'),
      },
    },
  },

  server: {
    host: 'localhost',
    port: 5173,
    strictPort: true, // Si 5173 está ocupado, falla; evita que WP apunte a 5173 y Vite use otro puerto
    cors: true,
    // Origen del dev server (assets); la página la sirve WordPress en otro dominio/puerto
    origin: 'http://localhost:5173',
  },

  css: {
    preprocessorOptions: {
      scss: {
        // Suprimir warnings de deprecación de SASS legacy (@import)
        silenceDeprecations: ['legacy-js-api', 'import'],
      },
    },
  },
})
