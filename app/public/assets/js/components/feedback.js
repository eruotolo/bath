// Feedback.js — wrapper de SweetAlert2 para reemplazar alert()/confirm() nativos.
// Se auto-inicializa: lee ?status=success|error&msg=... de la URL y limpia la query string,
// y engancha los links/botones con [data-confirm-delete] sin necesidad de script por vista.
//
// Re-skin: SweetAlert2 trae su propio theme (Open Sans, gris, radios de 5px) que no
// respeta el sistema visual Tailwind del resto de la app. Se restylea vía `customClass`
// (el flag "important" de tw/main.css hace que las utilities Tailwind ganen sobre el CSS
// vendorizado de SweetAlert2 a igualdad de specificity, mismo mecanismo que Choices.js).

window.Feedback = (function () {
    var POPUP_CLASS = 'rounded-3xl border border-slate-100 shadow-2xl font-sans';
    var TITLE_CLASS = 'font-sans text-base font-bold text-slate-900';
    var TEXT_CLASS = 'font-sans text-sm text-slate-600';
    var ACTIONS_CLASS = 'gap-3';
    var BUTTON_BASE = 'font-sans text-xs font-semibold rounded-xl px-5 py-2.5 border-0 shadow-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
    var CANCEL_BUTTON_CLASS = 'font-sans text-xs font-semibold rounded-xl px-5 py-2.5 border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-300';
    var DANGER_BUTTON_CLASS = BUTTON_BASE + ' bg-rose-500 hover:bg-rose-600 text-white focus-visible:ring-rose-300';
    var SUCCESS_BUTTON_CLASS = BUTTON_BASE + ' bg-emerald-500 hover:bg-emerald-600 text-white focus-visible:ring-emerald-300';
    var WARNING_BUTTON_CLASS = BUTTON_BASE + ' bg-amber-500 hover:bg-amber-600 text-white focus-visible:ring-amber-300';
    var PRIMARY_BUTTON_CLASS = BUTTON_BASE + ' bg-primary-600 hover:bg-primary-700 text-white focus-visible:ring-primary-300';

    function toast(msg, icon) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon || 'success',
            title: msg,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-2xl font-sans',
                title: 'font-sans text-sm text-slate-700',
            },
        });
    }

    function success(msg) {
        Swal.fire({
            icon: 'success',
            title: 'Listo',
            text: msg,
            confirmButtonText: 'Aceptar',
            buttonsStyling: false,
            customClass: {
                popup: POPUP_CLASS,
                title: TITLE_CLASS,
                htmlContainer: TEXT_CLASS,
                actions: ACTIONS_CLASS,
                confirmButton: SUCCESS_BUTTON_CLASS,
            },
        });
    }

    function error(msg) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            confirmButtonText: 'Aceptar',
            buttonsStyling: false,
            customClass: {
                popup: POPUP_CLASS,
                title: TITLE_CLASS,
                htmlContainer: TEXT_CLASS,
                actions: ACTIONS_CLASS,
                confirmButton: DANGER_BUTTON_CLASS,
            },
        });
    }

    function warning(msg) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: msg,
            confirmButtonText: 'Aceptar',
            buttonsStyling: false,
            customClass: {
                popup: POPUP_CLASS,
                title: TITLE_CLASS,
                htmlContainer: TEXT_CLASS,
                actions: ACTIONS_CLASS,
                confirmButton: WARNING_BUTTON_CLASS,
            },
        });
    }

    function confirm(options) {
        options = options || {};
        Swal.fire({
            title: options.title || '¿Estás seguro?',
            text: options.text || 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: options.confirmText || 'Sí, eliminar',
            cancelButtonText: options.cancelText || 'Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: POPUP_CLASS,
                title: TITLE_CLASS,
                htmlContainer: TEXT_CLASS,
                actions: ACTIONS_CLASS,
                confirmButton: options.dangerous === false ? PRIMARY_BUTTON_CLASS : DANGER_BUTTON_CLASS,
                cancelButton: CANCEL_BUTTON_CLASS,
            },
        }).then(function (result) {
            if (result.isConfirmed && typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
        });
    }

    function bindConfirmDeleteLinks() {
        document.querySelectorAll('[data-confirm-delete]').forEach(function (el) {
            if (el.dataset.confirmDeleteBound) return;
            el.dataset.confirmDeleteBound = 'true';

            el.addEventListener('click', function (e) {
                e.preventDefault();
                var href = el.getAttribute('href');
                confirm({
                    title: el.getAttribute('data-confirm-title') || '¿Eliminar este registro?',
                    text: el.getAttribute('data-confirm-text') || 'Esta acción no se puede deshacer.',
                    confirmText: el.getAttribute('data-confirm-confirm-text') || 'Sí, eliminar',
                    onConfirm: function () {
                        window.location.href = href;
                    },
                });
            });
        });
    }

    function readStatusFromQueryString() {
        var params = new URLSearchParams(window.location.search);
        var status = params.get('status');
        var msg = params.get('msg');
        if (!status || !msg) return;

        if (status === 'success') {
            toast(msg, 'success');
        } else if (status === 'error') {
            error(msg);
        } else if (status === 'warning') {
            warning(msg);
        }

        params.delete('status');
        params.delete('msg');
        var query = params.toString();
        var newUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }

    document.addEventListener('DOMContentLoaded', function () {
        readStatusFromQueryString();
        bindConfirmDeleteLinks();
    });

    return { toast: toast, success: success, error: error, warning: warning, confirm: confirm };
})();
