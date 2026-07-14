// Buscador + paginacion client-side para tablas nativas (layouts/native-table.php).
// Un input con data-table-search-input="#miTabla" filtra <tr data-search="...">
// de esa tabla por texto (sin acentos ni mayusculas) y pagina el resultado
// filtrado usando data-per-page en el <table>. El footer "Mostrando X-Y de Z" y
// los botones de pagina se generan dentro de [data-table-native-wrap] (ver
// table_native_close() en el PHP), buscando el ancestro mas cercano a la tabla.

(function () {
    'use strict';

    function normalize(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function getState(table) {
        if (!table._nativeTableState) {
            table._nativeTableState = { query: '', page: 1 };
        }
        return table._nativeTableState;
    }

    function getRows(table) {
        return Array.prototype.slice.call(table.querySelectorAll('tbody tr[data-search]'));
    }

    function buildPageLink(label, extraClass, onClick) {
        var el = document.createElement('a');
        el.href = '#';
        el.textContent = label;
        el.className = 'px-3 py-1 rounded-lg border border-slate-100 text-xs font-semibold transition-colors ' + extraClass;
        el.addEventListener('click', function (e) {
            e.preventDefault();
            onClick();
        });
        return el;
    }

    function buildPageDisabled(label) {
        var el = document.createElement('button');
        el.textContent = label;
        el.disabled = true;
        el.className = 'px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed';
        return el;
    }

    function render(table) {
        var state = getState(table);
        var perPage = parseInt(table.getAttribute('data-per-page'), 10) || 9;
        var itemLabel = table.getAttribute('data-item-label') || 'Resultados';
        var rows = getRows(table);

        var matched = rows.filter(function (row) {
            if (state.query === '') return true;
            var searchable = normalize(row.getAttribute('data-search') || '');
            return searchable.indexOf(state.query) !== -1;
        });

        var total = matched.length;
        var totalPages = Math.max(1, Math.ceil(total / perPage));
        if (state.page > totalPages) state.page = totalPages;
        if (state.page < 1) state.page = 1;

        var start = (state.page - 1) * perPage;
        var end = start + perPage;
        var visible = matched.slice(start, end);

        rows.forEach(function (row) {
            row.classList.add('hidden');
        });
        visible.forEach(function (row) {
            row.classList.remove('hidden');
        });

        var wrap = table.closest('[data-table-native-wrap]');
        if (!wrap) return;

        var summary = wrap.querySelector('[data-table-native-summary]');
        var pages = wrap.querySelector('[data-table-native-pages]');

        if (summary) {
            var firstShown = total > 0 ? start + 1 : 0;
            var lastShown = Math.min(end, total);
            summary.textContent = 'Mostrando ' + firstShown + '-' + lastShown + ' de ' + total + ' ' + itemLabel;
        }

        if (!pages) return;
        pages.innerHTML = '';

        pages.appendChild(
            state.page > 1
                ? buildPageLink('Anterior', 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200', function () {
                    state.page -= 1;
                    render(table);
                })
                : buildPageDisabled('Anterior')
        );

        for (var p = 1; p <= totalPages; p++) {
            (function (page) {
                var active = page === state.page;
                pages.appendChild(
                    buildPageLink(
                        String(page),
                        active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200',
                        function () {
                            state.page = page;
                            render(table);
                        }
                    )
                );
            })(p);
        }

        pages.appendChild(
            state.page < totalPages
                ? buildPageLink('Siguiente', 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200', function () {
                    state.page += 1;
                    render(table);
                })
                : buildPageDisabled('Siguiente')
        );
    }

    function bindSearch(input) {
        var targetSelector = input.getAttribute('data-table-search-input');
        var table = targetSelector ? document.querySelector(targetSelector) : null;
        if (!table) return;

        input.addEventListener('input', function () {
            var state = getState(table);
            state.query = normalize(input.value);
            state.page = 1;
            render(table);
        });

        render(table);
    }

    document.querySelectorAll('[data-table-search-input]').forEach(bindSearch);
})();
