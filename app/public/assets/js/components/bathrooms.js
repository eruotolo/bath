(function () {
    'use strict';

    var storageKey = 'bathrooms-view';
    var grid = document.getElementById('banos-grid');
    var table = document.getElementById('banos-table');
    var search = document.getElementById('banos-local-search');
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

        document.documentElement.dataset.bathroomsView = view;
        grid.style.setProperty('display', tableActive ? 'none' : 'grid', 'important');
        table.style.setProperty('display', tableActive ? 'block' : 'none', 'important');

        toggles.forEach(function (toggle) {
            setToggleState(toggle, toggle.getAttribute('data-view-toggle') === view);
        });

        if (persist) storeView(view);
    }

    function bindUnassignConfirmations() {
        if (!window.Swal) return;

        document.querySelectorAll('[data-confirm-unassign]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                window.Swal.fire({
                    title: button.getAttribute('data-confirm-title') || '¿Retirar este baño?',
                    text: button.getAttribute('data-confirm-text') || 'Volverá a estar disponible.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, retirar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#f46a6a',
                }).then(function (result) {
                    if (result.isConfirmed && button.form) button.form.submit();
                });
            });
        });
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

        document.querySelectorAll('.bano-card, .bano-row').forEach(function (item) {
            var searchable = normalize([
                item.dataset.code || '',
                item.dataset.obra || '',
                item.dataset.cliente || '',
            ].join(' '));

            item.classList.toggle('hidden', normalizedQuery !== '' && !searchable.includes(normalizedQuery));
        });
    }

    applyView(getStoredView(), false);
    bindUnassignConfirmations();

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
