// bs-shim.js — reemplazo del bootstrap.bundle.min.js para los 3 behaviours que
// la app usa activamente: modal, dropdown, tab. Se carga en lugar del JS de
// Bootstrap despues de Fase 8 (plan-migracion-bootstrap-tailwind).
//
// Bootstrap CSS sigue cargado (las clases estructurales .modal / .dropdown-menu
// / .nav-tabs no se reescriben en Fase 9; lo hacemos cuando se desinstale el
// CSS tambien).
//
// Convenciones que respeta (compatibles con el markup ya migrado):
//   data-bs-toggle="modal" + data-bs-target="#id"        -> abre/cierra modal
//   data-bs-dismiss="modal"                              -> cierra modal desde dentro
//   data-bs-toggle="dropdown"                             -> toggle .show en .dropdown
//   data-bs-toggle="tab" + data-bs-target="#id"           -> cambia panel activo

(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    function firebackdropClick(modal) {
        if (!modal.classList.contains('show')) return;
        var rect = modal.getBoundingClientRect();
        var inDialog = (event.clientX >= rect.left && event.clientX <= rect.right &&
                         event.clientY >= rect.top && event.clientY <= rect.bottom);
        if (!inDialog) closeModal(modal);
    }

    function openModal(modal) {
        modal.classList.add('show');
        modal.style.display = 'block';
        modal.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        if (window.jQuery) {
            jQuery(modal).trigger('show.bs.modal');
        }
    }

    function closeModal(modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        if (window.jQuery) {
            jQuery(modal).trigger('hide.bs.modal');
        }
        if (window.jQuery) {
            jQuery(modal).trigger('hidden.bs.modal');
        }
    }

    function bindModals() {
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function (trigger) {
            if (trigger.__bsShimModalBound) return;
            trigger.__bsShimModalBound = true;
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = trigger.getAttribute('data-bs-target');
                if (!targetId) return;
                var modal = document.querySelector(targetId);
                if (!modal) return;
                openModal(modal);
            });
        });
        document.querySelectorAll('.modal').forEach(function (modal) {
            if (modal.__bsShimDismissBound) return;
            modal.__bsShimDismissBound = true;
            modal.addEventListener('click', function (e) {
                if (e.target === modal) firebackdropClick(modal, e);
            });
            modal.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeModal(modal);
                });
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var open = document.querySelector('.modal.show');
            if (open) closeModal(open);
        });
    }

    function closeAllDropdowns(except) {
        document.querySelectorAll('.dropdown.show').forEach(function (open) {
            if (open === except) return;
            open.classList.remove('show');
            var menu = open.querySelector('.dropdown-menu');
            if (menu) menu.classList.remove('show');
            var trigger = open.querySelector('[data-bs-toggle="dropdown"]');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }

    function bindDropdowns() {
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (trigger) {
            if (trigger.__bsShimDropdownBound) return;
            trigger.__bsShimDropdownBound = true;
            trigger.setAttribute('aria-expanded', 'false');
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var dropdown = trigger.closest('.dropdown');
                if (!dropdown) return;
                var isOpen = dropdown.classList.contains('show');
                closeAllDropdowns();
                if (isOpen) {
                    dropdown.classList.remove('show');
                    var m = dropdown.querySelector('.dropdown-menu');
                    if (m) m.classList.remove('show');
                    trigger.setAttribute('aria-expanded', 'false');
                } else {
                    dropdown.classList.add('show');
                    var m2 = dropdown.querySelector('.dropdown-menu');
                    if (m2) m2.classList.add('show');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        });
        document.addEventListener('click', function (e) {
            if (e.target.closest('.dropdown')) return;
            closeAllDropdowns();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            closeAllDropdowns();
        });
    }

    function bindTabs() {
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (trigger) {
            if (trigger.__bsShimTabBound) return;
            trigger.__bsShimTabBound = true;
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = trigger.getAttribute('data-bs-target') || trigger.getAttribute('href');
                if (!targetId) return;
                var targetIdClean = targetId.replace(/^#/, '');
                var target = document.getElementById(targetIdClean);
                if (!target) return;
                var tabList = trigger.closest('ul, [role="tablist"], .nav');
                if (tabList) {
                    tabList.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (t) {
                        t.classList.remove('active');
                        t.setAttribute('aria-selected', 'false');
                    });
                }
                trigger.classList.add('active');
                trigger.setAttribute('aria-selected', 'true');
                var tabContainer = target.parentElement;
                if (tabContainer) {
                    tabContainer.querySelectorAll('.tab-pane').forEach(function (pane) {
                        pane.classList.remove('active', 'show');
                    });
                }
                target.classList.add('active', 'show');
            });
        });
    }

    ready(function () {
        bindModals();
        bindDropdowns();
        bindTabs();
    });
})();
