/**
 * Dark mode controller
 *
 * Modelo de 3 estados en localStorage('theme'): 'light' | 'dark' | 'system'.
 *   - 'system' (default): respeta prefers-color-scheme, escucha cambios.
 *   - 'light' / 'dark':   override manual, persiste entre sesiones.
 *
 * El header.php aplica el tema antes del paint para evitar FOUC; este script
 * gestiona el toggle y emite un CustomEvent('bp:theme-change') por si otros
 * módulos necesitan reaccionar.
 *
 * Toggle UI: botón con [data-bp-dark-toggle] (template-parts/atoms/dark-toggle.php).
 * Cycle: light → dark → system → light.
 */

const STORAGE_KEY = 'theme'
const VALID = ['light', 'dark', 'system']

function readPreference() {
    try {
        const v = localStorage.getItem(STORAGE_KEY)
        return VALID.includes(v) ? v : 'system'
    } catch (e) {
        return 'system'
    }
}

function writePreference(value) {
    try {
        if (value === 'system') {
            localStorage.removeItem(STORAGE_KEY)
        } else {
            localStorage.setItem(STORAGE_KEY, value)
        }
    } catch (e) {
        /* localStorage podría estar bloqueado */
    }
}

function systemPrefersDark() {
    return (
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    )
}

function effectiveTheme(pref) {
    if (pref === 'dark') return 'dark'
    if (pref === 'light') return 'light'
    return systemPrefersDark() ? 'dark' : 'light'
}

function applyTheme(pref) {
    const eff = effectiveTheme(pref)
    if (eff === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark')
    } else {
        document.documentElement.removeAttribute('data-theme')
    }
    document.querySelectorAll('[data-bp-dark-toggle]').forEach((btn) => {
        btn.setAttribute('aria-pressed', eff === 'dark' ? 'true' : 'false')
    })
    document.dispatchEvent(
        new CustomEvent('bp:theme-change', { detail: { preference: pref, effective: eff } })
    )
}

function cycle(current) {
    const order = ['light', 'dark', 'system']
    const idx   = order.indexOf(current)
    return order[(idx + 1) % order.length]
}

function init() {
    let pref = readPreference()
    applyTheme(pref)

    // Click en el toggle: cicla light → dark → system
    document.addEventListener('click', (e) => {
        const target = e.target instanceof Element ? e.target.closest('[data-bp-dark-toggle]') : null
        if (!target) return
        e.preventDefault()
        pref = cycle(pref)
        writePreference(pref)
        applyTheme(pref)
    })

    // Cambio del system preference: solo aplica si el user está en 'system'
    if (typeof window.matchMedia === 'function') {
        const mq = window.matchMedia('(prefers-color-scheme: dark)')
        const onChange = () => {
            if (pref === 'system') applyTheme('system')
        }
        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', onChange)
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(onChange) // Safari < 14
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true })
} else {
    init()
}
