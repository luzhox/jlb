import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import fullReload from 'vite-plugin-full-reload'
import { resolve } from 'path'
import fs from 'fs'

// Hot file: marca de "dev server activo". El tema (lib/vite.php →
// bp_is_vite_dev) detecta este archivo para entrar en modo dev automáticamente,
// sin tener que tocar wp-config. Se crea al arrancar `npm run dev` y se borra al
// cerrar; `npm run build` también lo limpia (deja el sitio en modo producción).
const HOT_FILE = resolve(__dirname, '.vite-hot')
const removeHot = () => { try { if (fs.existsSync(HOT_FILE)) fs.unlinkSync(HOT_FILE) } catch (e) { /* noop */ } }

function jlbHotFile() {
  return {
    name: 'jlb-hot-file',
    apply: 'serve',
    configureServer(server) {
      fs.writeFileSync(HOT_FILE, new Date().toISOString())
      server.httpServer?.once('close', removeHot)
      for (const sig of ['SIGINT', 'SIGTERM', 'SIGHUP']) {
        process.once(sig, () => { removeHot(); process.exit() })
      }
      process.once('exit', removeHot)
    },
  }
}

function jlbHotClean() {
  return { name: 'jlb-hot-clean', apply: 'build', buildStart: removeHot }
}

export default defineConfig({
  plugins: [tailwindcss(), fullReload(['**/*.php'], { delay: 300 }), jlbHotFile(), jlbHotClean()],

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
