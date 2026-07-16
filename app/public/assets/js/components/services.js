// services.js — toggle de vista card/tabla + búsqueda cliente-side para dash-services.php.
// Clon adaptado de bathrooms.js: mismo algoritmo de applyView/setToggleState/normalize,
// ids y storageKey distintos (contrato congelado en .doc/plan-services-rebranding.md §0.4).
// El confirm-delete de la tabla lo resuelve feedback.js (global vía vendor-scripts),
// por eso este archivo NO portea bindUnassignConfirmations() (específico de baños).
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

    function filterItems(query) {
        var normalizedQuery = normalize(query);

        document.querySelectorAll('.servicio-card, .servicio-row').forEach(function (item) {
            var searchable = normalize([
                item.dataset.searchCliente || '',
                item.dataset.searchObra || '',
                item.dataset.searchNro || '',
                item.dataset.searchTipos || '',
            ].join(' '));

            item.classList.toggle('hidden', normalizedQuery !== '' && !searchable.includes(normalizedQuery));
        });
    }

    applyView(getStoredView(), false);

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            applyView(toggle.getAttribute('data-view-toggle') === 'table' ? 'table' : 'grid', true);
        });
    });

    if (search) {
        search.addEventListener('input', function () {
            filterItems(search.value);
        });
    }
})();
