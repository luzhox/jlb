// intersectionObserver.js — Vanilla JS
document.addEventListener('DOMContentLoaded', () => {
    function buildThresholdList() {
        const steps = 10
        return Array.from({ length: steps + 1 }, (_, i) => i / steps)
    }

    function animateCounter(el) {
        const target   = parseInt(el.textContent, 10)
        if (isNaN(target)) return

        const duration = 2500
        const start    = performance.now()

        function step(timestamp) {
            const progress = Math.min((timestamp - start) / duration, 1)
            el.textContent = Math.ceil(progress * target).toLocaleString()
            if (progress < 1) requestAnimationFrame(step)
        }

        requestAnimationFrame(step)
    }

    function handleIntersect(entries) {
        entries.forEach(entry => {
            // Contadores animados
            if (
                entry.target.classList.contains('about-us__counters') &&
                entry.intersectionRatio >= 0.5 &&
                !localStorage.getItem('viewNumbers')
            ) {
                localStorage.setItem('viewNumbers', 'true')
                document.querySelectorAll('.about-us__count__animated')
                    .forEach(animateCounter)
            }

            // Valores de marca
            if (
                entry.target.id === 'valuesBrand' &&
                entry.intersectionRatio >= 0.5 &&
                !localStorage.getItem('valuesBrand')
            ) {
                localStorage.setItem('valuesBrand', 'true')
                setTimeout(() => {
                    const item    = document.querySelector('.steps-values__item__marketing')
                    const titleEl = document.querySelector('.titleSteps')
                    if (!item || !titleEl) return

                    item.classList.add('active')
                    titleEl.classList.remove('fade-in-fwd')
                    titleEl.classList.add('scale-in-center')
                    titleEl.textContent = item.dataset.title || ''
                }, 2000)
            }
        })
    }

    const options = {
        root: null,
        rootMargin: '0px',
        threshold: buildThresholdList(),
    }

    function observe(element) {
        if (!element) return
        new IntersectionObserver(handleIntersect, options).observe(element)
    }

    observe(document.getElementById('valuesBrand'))
    observe(document.querySelector('.about-us__counters'))
})
