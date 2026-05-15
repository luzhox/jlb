// scrollHeader.js — Vanilla JS
document.addEventListener('DOMContentLoaded', () => {
    localStorage.removeItem('valuesBrand')
    localStorage.removeItem('viewNumbers')

    const masthead = document.getElementById('masthead')
    const brand    = document.getElementById('brand')

    if (!masthead) return

    let prevScrollpos = window.scrollY + 70

    window.addEventListener('scroll', () => {
        const currentScrollPos = window.scrollY

        if (window.scrollY > 70) {
            if (prevScrollpos > currentScrollPos) {
                masthead.classList.add('actived')
                masthead.style.top = '0'
                if (brand) brand.src = brand.dataset.brandtwo || brand.src
            } else {
                masthead.style.top = '-125px'
                prevScrollpos = currentScrollPos
            }
        } else {
            if (masthead.classList.contains('proyect')) {
                masthead.classList.remove('actived')
                return
            }
            masthead.classList.remove('actived')
            if (brand) brand.src = brand.dataset.brand || brand.src
        }
    }, { passive: true })
})
