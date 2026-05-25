/**
 * Parche para ACF Pro Flexible Content en esta instalación
 * (ACF Pro 6.8.1 + WP 6.9.4).
 *
 * Bugs nativos atacados (origen: wrapper <strong> inesperado dentro de cada
 * .layout que rompe los selectores con `>` direct-child que ACF Pro usa):
 *
 *   A) renderLayout lanza TypeError: e.children("input").attr("name").replace(...)
 *      porque `e.children("input")` queda vacío. Eso aborta closeLayout justo
 *      después del addClass("-collapsed"), pero también puede romper otros
 *      handlers que escuchan en la página.
 *
 *   B) El handler `mouseover: onHover` que inicializa el sortable de forma
 *      lazy nunca se dispara (probablemente porque el event delegation
 *      apunta a un selector roto por el wrapper). Resultado: drag-and-drop
 *      de módulos no funciona.
 *
 *   C) Si sortable llegara a inicializarse con la config nativa, el handle
 *      selector "> .acf-fc-layout-actions-wrap .acf-fc-layout-handle" tampoco
 *      matchea por el `>` que no atraviesa el <strong>.
 *
 * Lo que hace este parche (3 capas, todas idempotentes):
 *
 *   1) Wrap defensivo del prototipo `renderLayout` con guard: si el input
 *      esperado no existe, retorna sin tocar nada (evita la excepción global).
 *
 *   2) Click handler en capture phase sobre [data-name="collapse-layout"]
 *      para gestionar el plegado/desplegado nosotros mismos (toggle de la
 *      clase `-collapsed` en el .layout ancestro). El CSS del parche oculta
 *      .acf-fields descendientes.
 *
 *   3) Init manual de jQuery UI sortable en cada `.acf-flexible-content
 *      > .values` con un handle selector descendiente (sin `>`).
 *      Re-corre cuando aparecen nuevos flex fields (MutationObserver).
 *
 * Quitar este parche cuando ACF Pro publique fix oficial (o cuando se
 * identifique y elimine el origen del wrapper <strong>).
 */
(function () {
    'use strict';

    var LOG = '[JLB ACF patch]';

    // ── Capa 1: wrap defensivo de renderLayout ──────────────────────────────
    function patchPrototype() {
        if (typeof window.acf === 'undefined' || typeof acf.getFieldType !== 'function') return false;
        var fcType = acf.getFieldType('flexible_content');
        if (!fcType || !fcType.prototype) return false;
        if (fcType.prototype._jlbPatched) return true;
        fcType.prototype._jlbPatched = true;

        var originalRender = fcType.prototype.renderLayout;
        fcType.prototype.renderLayout = function (e) {
            try {
                if (!e || typeof e.children !== 'function') return;
                var $input = e.children('input');
                if (!$input.length || !$input.attr('name')) return;
                return originalRender.apply(this, arguments);
            } catch (err) {
                if (window.console) console.warn(LOG, 'renderLayout suppressed:', err && err.message);
            }
        };

        // Override addSortable: el original usa handle "> .acf-fc-layout-actions-wrap
        // .acf-fc-layout-handle" cuyo `>` no atraviesa el wrapper <strong>. Usamos
        // selector descendiente para que sí encuentre el handle.
        var originalAddSortable = fcType.prototype.addSortable;
        fcType.prototype.addSortable = function (field) {
            try {
                var $ = window.jQuery;
                if (!$ || !field || typeof field.$layoutsWrap !== 'function') {
                    if (originalAddSortable) return originalAddSortable.apply(this, arguments);
                    return;
                }
                if (String(field.get && field.get('max')) === '1') return;

                var $wrap = field.$layoutsWrap();
                if (!$wrap.length) return;

                // Destruir sortable previo si existía.
                try { $wrap.sortable('destroy'); } catch (e) { /* no estaba inicializado */ }

                $wrap.sortable({
                    items: '> .layout',
                    handle: '.acf-fc-layout-handle', // sin `>` — atraviesa <strong>
                    forceHelperSize: true,
                    zIndex: 9999,
                    forcePlaceholderSize: true,
                    scroll: true,
                    stop: function () {
                        try { field.render && field.render(); } catch (e) {}
                    },
                    update: function () {
                        try { field.$input && field.$input().trigger('change'); } catch (e) {}
                    },
                });
            } catch (err) {
                if (window.console) console.warn(LOG, 'addSortable override failed:', err && err.message);
            }
        };

        return true;
    }

    // ── Capa 2: click handler en capture phase para collapse ────────────────
    function bindCollapseClicks() {
        if (window._jlbAcfCaptureClick) return;
        window._jlbAcfCaptureClick = true;

        document.addEventListener('click', function (e) {
            var trigger = e.target && e.target.closest && e.target.closest('[data-name="collapse-layout"]');
            if (!trigger) return;
            var layout = trigger.closest('.layout');
            if (!layout) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            var wasCollapsed = layout.classList.contains('-collapsed');
            layout.classList.toggle('-collapsed');
            if (window.console) console.log(LOG, wasCollapsed ? 'expand' : 'collapse', 'layout');
        }, true);

        document.addEventListener('click', function (e) {
            var trigger = e.target && e.target.closest && e.target.closest('.acf-fc-collapse-all');
            if (!trigger) return;
            var field = trigger.closest('.acf-field-flexible-content');
            if (!field) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            var layouts = field.querySelectorAll('.acf-flexible-content > .values > .layout');
            for (var i = 0; i < layouts.length; i++) layouts[i].classList.add('-collapsed');
        }, true);

        document.addEventListener('click', function (e) {
            var trigger = e.target && e.target.closest && e.target.closest('.acf-fc-expand-all');
            if (!trigger) return;
            var field = trigger.closest('.acf-field-flexible-content');
            if (!field) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            var layouts = field.querySelectorAll('.acf-flexible-content > .values > .layout');
            for (var i = 0; i < layouts.length; i++) layouts[i].classList.remove('-collapsed');
        }, true);
    }

    // ── Capa 3: init manual de sortable con handle selector descendiente ────
    function initSortableForAllFlexFields() {
        var $ = window.jQuery;
        if (!$) return;

        $('.acf-field-flexible-content').each(function () {
            var $field = $(this);
            var $container = $field.find('.acf-flexible-content').first().find('> .values');
            if (!$container.length) return;
            if ($container.data('uiSortable') || $container.data('jlbSortableInited')) return;

            // Localizar la instancia del field para callbacks (igual que ACF original).
            var fieldInst = null;
            try {
                if (window.acf && acf.getField) {
                    fieldInst = acf.getField($field);
                }
            } catch (e) {}

            // Si el field define max=1, ACF no inicializa sortable. Respetamos.
            if (fieldInst && fieldInst.get && String(fieldInst.get('max')) === '1') return;

            try {
                $container.sortable({
                    items: '> .layout',
                    // Handle SIN `>` para atravesar el wrapper <strong>.
                    handle: '.acf-fc-layout-handle',
                    forceHelperSize: true,
                    zIndex: 9999,
                    forcePlaceholderSize: true,
                    scroll: true,
                    stop: function () {
                        if (fieldInst && typeof fieldInst.render === 'function') {
                            try { fieldInst.render(); } catch (e) { /* swallow */ }
                        }
                    },
                    update: function () {
                        if (fieldInst && typeof fieldInst.$input === 'function') {
                            try { fieldInst.$input().trigger('change'); } catch (e) { /* swallow */ }
                        }
                    },
                });
                $container.data('jlbSortableInited', true);
                if (window.console) console.log(LOG, 'sortable initialized on', $container.get(0));
            } catch (err) {
                if (window.console) console.warn(LOG, 'sortable init failed:', err && err.message);
            }
        });
    }

    // Observador: re-inicializa sortable si nuevos flex fields aparecen
    // (ej. carga lazy de bloques en Gutenberg).
    function startMutationObserver() {
        if (window._jlbAcfMutationObserver) return;
        if (typeof MutationObserver === 'undefined') return;
        window._jlbAcfMutationObserver = true;

        var debounceTimer = null;
        var observer = new MutationObserver(function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(initSortableForAllFlexFields, 200);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // ── Bootstrap ──────────────────────────────────────────────────────────
    function init() {
        patchPrototype();
        bindCollapseClicks();
        initSortableForAllFlexFields();
        startMutationObserver();
    }

    init();

    if (window.acf && typeof acf.addAction === 'function') {
        acf.addAction('ready', init);
        // append/remove de filas/bloques también pueden añadir flex fields.
        acf.addAction('append', initSortableForAllFlexFields);
    }

    // Polling defensivo (raro).
    var n = 0;
    var iv = setInterval(function () {
        n++;
        init();
        if (n > 30) clearInterval(iv);
    }, 200);
})();
