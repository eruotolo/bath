// elevation.js — modal de elevación (step-up) para Usuario (nivel 1).
// Intercepta clicks en cualquier elemento [data-requires-elevation] antes de
// navegar/submitir, abre un modal SweetAlert2 pidiendo email+password de un
// Admin, hace POST a controller/auth-elevate.php (T4) y, si responde {ok:true},
// ejecuta la acción original (navegar al href o submitear el form).
// Si falla, muestra error genérico y deja reintentar (el rate-limit lo maneja
// el backend). Referencia: .doc/plan-roles-permisos.md §5 pasos 1 y 4.

(function () {
    if (window.ElevationBound) return;
    window.ElevationBound = true;

    var POPUP_CLASS = 'rounded-3xl border border-slate-100 shadow-2xl font-sans';
    var TITLE_CLASS = 'font-sans text-base font-bold text-slate-900';
    var TEXT_CLASS = 'font-sans text-sm text-slate-600';
    var ACTIONS_CLASS = 'gap-3';
    var INPUT_CLASS = 'swal2-input w-full mt-0 mb-3 px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans';
    var BUTTON_BASE = 'font-sans text-xs font-semibold rounded-xl px-5 py-2.5 border-0 shadow-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
    var CANCEL_BUTTON_CLASS = 'font-sans text-xs font-semibold rounded-xl px-5 py-2.5 border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-300';
    var CONFIRM_BUTTON_CLASS = BUTTON_BASE + ' bg-indigo-500 hover:bg-indigo-600 text-white focus-visible:ring-indigo-300';

    var EMAIL_FIELD_ID = 'elevation-email';
    var PASSWORD_FIELD_ID = 'elevation-password';

    function navigateOrSubmit(trigger) {
        var form = trigger.form;
        if (form && trigger.type === 'submit') {
            form.submit();
            return;
        }
        var href = trigger.getAttribute('href');
        if (href) {
            window.location.href = href;
        }
    }

    function performOriginalAction(trigger) {
        if (trigger.hasAttribute('data-confirm-delete') && window.Feedback && typeof window.Feedback.confirm === 'function') {
            window.Feedback.confirm({
                title: trigger.getAttribute('data-confirm-title') || '¿Eliminar este registro?',
                text: trigger.getAttribute('data-confirm-text') || 'Esta acción no se puede deshacer.',
                confirmText: trigger.getAttribute('data-confirm-confirm-text') || 'Sí, eliminar',
                onConfirm: function () { navigateOrSubmit(trigger); }
            });
            return;
        }
        navigateOrSubmit(trigger);
    }

    function openElevation(trigger) {
        var action = trigger.getAttribute('data-action');
        var entidad = trigger.getAttribute('data-entidad');
        var id = trigger.getAttribute('data-id');

        if (!action || !entidad || !id) {
            if (window.Feedback) {
                window.Feedback.error('Configuración de permisos incompleta para esta acción.');
            }
            return;
        }

        Swal.fire({
            title: 'Autorización requerida',
            html:
                '<p class="' + TEXT_CLASS + ' mb-4">Esta acción requiere autorización de un Administrador. Ingresá sus credenciales.</p>' +
                '<input id="' + EMAIL_FIELD_ID + '" class="' + INPUT_CLASS + '" type="email" autocomplete="username" placeholder="Email del administrador">' +
                '<input id="' + PASSWORD_FIELD_ID + '" class="' + INPUT_CLASS + '" type="password" autocomplete="current-password" placeholder="Contraseña">',
            showCancelButton: true,
            confirmButtonText: 'Autorizar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusConfirm: false,
            showLoaderOnConfirm: true,
            buttonsStyling: false,
            customClass: {
                popup: POPUP_CLASS,
                title: TITLE_CLASS,
                htmlContainer: 'font-sans',
                actions: ACTIONS_CLASS,
                confirmButton: CONFIRM_BUTTON_CLASS,
                cancelButton: CANCEL_BUTTON_CLASS
            },
            preConfirm: function () {
                var emailEl = document.getElementById(EMAIL_FIELD_ID);
                var passEl = document.getElementById(PASSWORD_FIELD_ID);
                var email = emailEl ? emailEl.value.trim() : '';
                var password = passEl ? passEl.value : '';

                if (!email || !password) {
                    Swal.showValidationMessage('Ingresá email y contraseña del administrador');
                    return;
                }

                return fetch('controller/auth-elevate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, password: password, action: action, entidad: entidad, id: id })
                }).then(function (response) {
                    return response.json().then(function (data) {
                        return { status: response.status, data: data };
                    });
                }).then(function (res) {
                    if (res.status === 429) {
                        throw new Error('Demasiados intentos fallidos. Aguardá unos minutos e intentá nuevamente.');
                    }
                    if (!res.data || !res.data.ok) {
                        throw new Error('Credenciales inválidas o sin permisos');
                    }
                    return res.data;
                }).catch(function (err) {
                    if (passEl) passEl.value = '';
                    Swal.showValidationMessage(err && err.message ? err.message : 'Error de conexión. Intentá nuevamente.');
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); }
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.ok) {
                performOriginalAction(trigger);
            }
        });
    }

    // Delegación en fase captura sobre document: corre ANTES que los handlers
    // de feedback.js (fase burbuja), así el link con data-confirm-delete +
    // data-requires-elevation (ej. Inactivar) es propiedad exclusiva del gate
    // de elevación y no abre dos modales.
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-requires-elevation]');
        if (!trigger) return;
        event.preventDefault();
        event.stopPropagation();
        openElevation(trigger);
    }, true);
})();
