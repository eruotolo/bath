// services.js — toggle de vista card/tabla + búsqueda y paginación client-side
// para dash-services.php. Clon adaptado de bathrooms.js: mismo algoritmo de
// applyView/setToggleState/normalize, ids y storageKey distintos (contrato
// congelado en .doc/plan-services-rebranding.md §0.4).
// La tabla la pagina native-table.js (cargo via data-table-search-input); las
// cards las pagina este archivo (calco visual del footer de native-table).
(function () {
    'use strict';

    var storageKey = 'services-view';
    var grid = document.getElementById('servicios-grid');
    var table = document.getElementById('servicios-table');
    var search = document.getElementById('servicios-local-search');
    var toggles = document.querySelectorAll('[data-view-toggle]');

    if (!grid || !table) return;

    function getStoredView() {
        try {
            var storedView = window.localStorage.getItem(storageKey);
            return storedView === 'table' ? 'table' : 'grid';
        } catch (error) {
            return 'grid';
        }
    }

    function storeView(view) {
        try {
            window.localStorage.setItem(storageKey, view);
        } catch (error) {
            return;
        }
    }

    function setToggleState(toggle, active) {
        toggle.classList.toggle('bg-white', active);
        toggle.classList.toggle('text-slate-900', active);
        toggle.classList.toggle('shadow-sm', active);
        toggle.classList.toggle('text-slate-500', !active);
        toggle.classList.toggle('hover:text-slate-800', !active);
        toggle.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    function applyView(view, persist) {
        var tableActive = view === 'table';

        document.documentElement.dataset.servicesView = view;
        grid.style.setProperty('display', tableActive ? 'none' : 'grid', 'important');
        table.style.setProperty('display', tableActive ? 'block' : 'none', 'important');

        toggles.forEach(function (toggle) {
            setToggleState(toggle, toggle.getAttribute('data-view-toggle') === view);
        });

        if (persist) storeView(view);
    }

    function normalize(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    // --- Paginacion de cards (calco visual de native-table.js, pero sobre
    // .servicio-card en vez de <tr>). native-table.js queda intocado; cada vista
    // tiene su propio paginador y su propio estado de pagina. ---
    var cardsPagination = document.getElementById('servicios-cards-pagination');
    var PER_PAGE = cardsPagination ? (parseInt(cardsPagination.getAttribute('data-per-page'), 10) || 9) : 9;
    var cardsState = { query: '', page: 1 };

    function getCards() {
        return Array.prototype.slice.call(grid.querySelectorAll('.servicio-card'));
    }

    function cardMatches(card) {
        if (cardsState.query === '') return true;
        var searchable = normalize([
            card.dataset.searchCliente || '',
            card.dataset.searchObra || '',
            card.dataset.searchNro || '',
            card.dataset.searchTipos || '',
        ].join(' '));
        return searchable.indexOf(cardsState.query) !== -1;
    }

    function buildCardsPageLink(label, extraClass, onClick) {
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

    function buildCardsPageDisabled(label) {
        var el = document.createElement('button');
        el.textContent = label;
        el.disabled = true;
        el.className = 'px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed';
        return el;
    }

    function buildCardsPageEllipsis() {
        var el = document.createElement('span');
        el.textContent = '...';
        el.className = 'px-2 py-1 text-xs font-semibold text-slate-400 select-none';
        return el;
    }

    // Ventana deslizante: primera y ultima pagina + siblingCount a cada lado.
    function getCardsPageItems(totalPages, currentPage, siblingCount) {
        var pageSet = {};
        var add = function (p) {
            if (p >= 1 && p <= totalPages) pageSet[p] = true;
        };
        add(1);
        add(totalPages);
        for (var i = currentPage - siblingCount; i <= currentPage + siblingCount; i++) {
            add(i);
        }

        var sorted = Object.keys(pageSet).map(Number).sort(function (a, b) { return a - b; });
        var items = [];
        var prev = null;
        sorted.forEach(function (page) {
            if (prev !== null && page - prev > 1) {
                items.push('...');
            }
            items.push(page);
            prev = page;
        });
        return items;
    }

    function renderCardsFooter(total, totalPages, start, end) {
        if (!cardsPagination) return;

        var summary = cardsPagination.querySelector('[data-cards-summary]');
        var pages = cardsPagination.querySelector('[data-cards-pages]');

        if (summary) {
            var firstShown = total > 0 ? start + 1 : 0;
            var lastShown = Math.min(end, total);
            var itemLabel = cardsPagination.getAttribute('data-item-label') || 'Servicios';
            summary.textContent = 'Mostrando ' + firstShown + '-' + lastShown + ' de ' + total + ' ' + itemLabel;
        }

        if (!pages) return;
        pages.innerHTML = '';

        pages.appendChild(
            cardsState.page > 1
                ? buildCardsPageLink('Anterior', 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200', function () {
                    cardsState.page -= 1;
                    renderCards();
                })
                : buildCardsPageDisabled('Anterior')
        );

        getCardsPageItems(totalPages, cardsState.page, 1).forEach(function (item) {
            if (item === '...') {
                pages.appendChild(buildCardsPageEllipsis());
                return;
            }
            (function (page) {
                var active = page === cardsState.page;
                pages.appendChild(
                    buildCardsPageLink(
                        String(page),
                        active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200',
                        function () {
                            cardsState.page = page;
                            renderCards();
                        }
                    )
                );
            })(item);
        });

        pages.appendChild(
            cardsState.page < totalPages
                ? buildCardsPageLink('Siguiente', 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200', function () {
                    cardsState.page += 1;
                    renderCards();
                })
                : buildCardsPageDisabled('Siguiente')
        );
    }

    function renderCards() {
        var cards = getCards();
        var matched = cards.filter(cardMatches);

        var total = matched.length;
        var totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
        if (cardsState.page > totalPages) cardsState.page = totalPages;
        if (cardsState.page < 1) cardsState.page = 1;

        var start = (cardsState.page - 1) * PER_PAGE;
        var end = start + PER_PAGE;
        var visible = matched.slice(start, end);

        // Filtro + paginacion sobre el mismo .hidden: una sola funcion lo controla
        // para que no compitan (mismo principio que native-table.js sobre los <tr>).
        cards.forEach(function (card) {
            card.classList.add('hidden');
        });
        visible.forEach(function (card) {
            card.classList.remove('hidden');
        });

        renderCardsFooter(total, totalPages, start, end);
    }

    applyView(getStoredView(), false);

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            applyView(toggle.getAttribute('data-view-toggle') === 'table' ? 'table' : 'grid', true);
        });
    });

    renderCards();

    if (search) {
        search.addEventListener('input', function () {
            cardsState.query = normalize(search.value);
            cardsState.page = 1;
            renderCards();
        });
    }
})();
