// menuMobile.js — Toggle drawer mobile del masthead Kresna
//
// Vanilla JS. Engancha el botón sandwich (.site-header-sandwich) que existe
// solo en <lg, y toggle:
//   - aria-expanded del botón (true / false)
//   - .active en el botón (puede animar los 3 spans del icono burger → X)
//   - .hidden (utility Tailwind) del drawer (#site-navegation-mobile)
//   - .drawer-open en <body> (hook para deshabilitar scroll si se desea)
//
// El drawer está estructurado en header.php con la nav principal y el dark
// toggle dentro. Cuando se quita .hidden queda visible (display revierte a
// block, default de div).
//
// Cierra automáticamente al:
//   - hacer click en un link del drawer (navegación interna)
//   - tecla Escape
//   - resize a >= lg (1024px)
//   - click fuera del header
//
// Reemplaza al menuMobile.js legacy que toggle .active en .site-header-nav
// (el nav DESKTOP), lo cual no funcionaba con la nueva estructura porque ese
// nav vive en lg+ y mobile usa drawer separado.

const BREAKPOINT_LG = 1024

function closeDrawer(btn, drawer) {
    btn.setAttribute('aria-expanded', 'false')
    btn.classList.remove('active')
    drawer.classList.add('hidden')
    document.body.classList.remove('drawer-open')
}

function openDrawer(btn, drawer) {
    btn.setAttribute('aria-expanded', 'true')
    btn.classList.add('active')
    drawer.classList.remove('hidden')
    document.body.classList.add('drawer-open')
}

document.addEventListener('DOMContentLoaded', () => {
    const btn    = document.querySelector('.site-header-sandwich')
    const drawer = document.getElementById('site-navegation-mobile')
    if (!btn || !drawer) return

    btn.addEventListener('click', (e) => {
        e.stopPropagation()
        const isOpen = btn.getAttribute('aria-expanded') === 'true'
        if (isOpen) {
            closeDrawer(btn, drawer)
        } else {
            openDrawer(btn, drawer)
        }
    })

    // Click en un link del drawer → cerrar (navegación interna)
    drawer.addEventListener('click', (e) => {
        if (e.target.closest('a')) {
            closeDrawer(btn, drawer)
        }
    })

    // Tecla Escape → cerrar y devolver focus al botón
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') {
            closeDrawer(btn, drawer)
            btn.focus()
        }
    })

    // Resize a desktop → cerrar
    let lastWidth = window.innerWidth
    window.addEventListener('resize', () => {
        const w = window.innerWidth
        if (lastWidth < BREAKPOINT_LG && w >= BREAKPOINT_LG) {
            closeDrawer(btn, drawer)
        }
        lastWidth = w
    }, { passive: true })

    // Click fuera del header → cerrar
    document.addEventListener('click', (e) => {
        const header = document.getElementById('masthead')
        if (!header) return
        if (btn.getAttribute('aria-expanded') === 'true' && !header.contains(e.target)) {
            closeDrawer(btn, drawer)
        }
    })
})
