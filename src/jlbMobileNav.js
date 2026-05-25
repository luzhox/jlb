/**
 * jlbMobileNav — drawer mobile del template Jean Le Boulch.
 *
 * Comportamientos:
 *   · Toggle (hamburger) + drawer full-screen con cierre por ESC / backdrop / link
 *   · Submenús expandibles (acordeón: al abrir uno se cierran los demás)
 *   · Scroll-lock vía .jlb-menu-open en <html>, preservando posición
 *   · Focus-trap circular dentro del drawer mientras está abierto
 *   · Restauración del foco al abrir/cerrar
 *   · Cierre automático si se cruza el breakpoint a desktop (≥1181px)
 *   · Soporte prefers-reduced-motion (controlado por CSS)
 *
 * Markup esperado en header-jlb.php.
 */

const SELECTOR_MENU         = '[data-jlb-menu]'
const SELECTOR_TOGGLE       = '[data-jlb-menu-toggle]'
const SELECTOR_CLOSE        = '[data-jlb-menu-close]'
const SELECTOR_LINK         = '[data-jlb-menu-link]'
const SELECTOR_SUB_TOGGLE   = '[data-jlb-submenu-toggle]'
const SELECTOR_SUBMENU      = '[data-jlb-submenu]'
const CLASS_OPEN            = 'jlb-menu-open'
const FOCUSABLE_SELECTOR    = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'

function initJlbMobileNav() {
    const menu = document.querySelector(SELECTOR_MENU)
    const toggles = document.querySelectorAll(SELECTOR_TOGGLE)
    if (!menu || toggles.length === 0) return

    const closeBtns   = menu.querySelectorAll(SELECTOR_CLOSE)
    const links       = menu.querySelectorAll(SELECTOR_LINK)
    const subToggles  = menu.querySelectorAll(SELECTOR_SUB_TOGGLE)

    let lastFocused = null
    let scrollY = 0

    function getFocusable() {
        return [...menu.querySelectorAll(FOCUSABLE_SELECTOR)].filter(
            el => !el.hasAttribute('disabled') && !el.closest('[aria-hidden="true"]') && el.offsetParent !== null
        )
    }

    // ── Drawer principal ──────────────────────────────────────────────────────
    function open() {
        if (menu.getAttribute('aria-hidden') === 'false') return

        lastFocused = document.activeElement
        scrollY = window.scrollY

        menu.setAttribute('aria-hidden', 'false')
        toggles.forEach(t => t.setAttribute('aria-expanded', 'true'))
        document.documentElement.classList.add(CLASS_OPEN)
        document.documentElement.style.scrollBehavior = 'auto'

        // Foco al botón × tras un pequeño tick (deja arrancar la transición).
        const first = closeBtns[0] || getFocusable()[0]
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 80)
    }

    function close() {
        if (menu.getAttribute('aria-hidden') !== 'false') return

        menu.setAttribute('aria-hidden', 'true')
        toggles.forEach(t => t.setAttribute('aria-expanded', 'false'))
        document.documentElement.classList.remove(CLASS_OPEN)
        document.documentElement.style.scrollBehavior = ''

        // Cerrar todos los submenús para que al reabrir el drawer salgan plegados.
        subToggles.forEach(closeSubmenu)

        if (lastFocused && typeof lastFocused.focus === 'function') {
            lastFocused.focus({ preventScroll: true })
        }
    }

    function toggleDrawer() {
        if (menu.getAttribute('aria-hidden') === 'false') close()
        else open()
    }

    // ── Submenús (acordeón) ───────────────────────────────────────────────────
    function openSubmenu(toggle) {
        toggle.setAttribute('aria-expanded', 'true')
        const sub = document.getElementById(toggle.getAttribute('aria-controls'))
        if (sub) sub.setAttribute('data-jlb-submenu-open', 'true')
    }

    function closeSubmenu(toggle) {
        toggle.setAttribute('aria-expanded', 'false')
        const sub = document.getElementById(toggle.getAttribute('aria-controls'))
        if (sub) sub.removeAttribute('data-jlb-submenu-open')
    }

    function toggleSubmenu(toggle) {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true'
        if (isOpen) {
            closeSubmenu(toggle)
        } else {
            // Acordeón: cerrar los demás antes de abrir éste.
            subToggles.forEach(t => { if (t !== toggle) closeSubmenu(t) })
            openSubmenu(toggle)
        }
    }

    // ── Focus trap ────────────────────────────────────────────────────────────
    function trapFocus(e) {
        if (e.key !== 'Tab') return
        if (menu.getAttribute('aria-hidden') !== 'false') return

        const focusables = getFocusable()
        if (focusables.length === 0) return
        const first = focusables[0]
        const last  = focusables[focusables.length - 1]

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault()
            last.focus()
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault()
            first.focus()
        }
    }

    // ── Wiring ────────────────────────────────────────────────────────────────
    toggles.forEach(t => t.addEventListener('click', toggleDrawer))
    closeBtns.forEach(b => b.addEventListener('click', close))
    subToggles.forEach(t => t.addEventListener('click', () => toggleSubmenu(t)))

    links.forEach(a => a.addEventListener('click', () => {
        // Cerrar al tocar cualquier link de navegación. Para anchors (#…) damos
        // un pequeño respiro para que la transición de cierre no tape el target.
        close()
    }))

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && menu.getAttribute('aria-hidden') === 'false') {
            close()
            e.preventDefault()
        }
        trapFocus(e)
    })

    // Si el viewport pasa a desktop con el menú abierto, lo cerramos para que
    // no quede el scroll-lock pegado al hidratar styles del nav horizontal.
    const mql = window.matchMedia('(min-width: 1181px)')
    const onMqChange = e => {
        if (e.matches && menu.getAttribute('aria-hidden') === 'false') close()
    }
    if (mql.addEventListener) mql.addEventListener('change', onMqChange)
    else mql.addListener(onMqChange)

    // Estado inicial explícito (defensivo): el drawer arranca cerrado.
    menu.setAttribute('aria-hidden', 'true')
    // Eliminar el atributo `hidden` heredado del markup — ahora controlamos la
    // visibilidad sólo por CSS (visibility/pointer-events), para que también se
    // pueda animar el CIERRE en vez de hacer cut con display:none.
    menu.removeAttribute('hidden')
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbMobileNav)
} else {
    initJlbMobileNav()
}

export { initJlbMobileNav }
