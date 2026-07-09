// Feedback.js — wrapper de SweetAlert2 para reemplazar alert()/confirm() nativos.
// Se auto-inicializa: lee ?status=success|error&msg=... de la URL y limpia la query string,
// y engancha los links/botones con [data-confirm-delete] sin necesidad de script por vista.

window.Feedback = (function () {
    function toast(msg, icon) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon || 'success',
            title: msg,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    function success(msg) {
        Swal.fire({
            icon: 'success',
            title: 'Listo',
            text: msg,
            confirmButtonText: 'Aceptar',
        });
    }

    function error(msg) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            confirmButtonText: 'Aceptar',
        });
    }

    function confirm(options) {
        options = options || {};
        Swal.fire({
            title: options.title || '¿Estás seguro?',
            text: options.text || 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Sí, eliminar',
            cancelButtonText: options.cancelText || 'Cancelar',
            confirmButtonColor: '#f46a6a',
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

    return { toast: toast, success: success, error: error, confirm: confirm };
})();
