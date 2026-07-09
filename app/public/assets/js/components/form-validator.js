// FormValidator.js — wrapper de PristineJS en español.
// Se auto-inicializa sobre form.needs-validation: no requiere script por vista.

window.FormValidator = (function () {
    function applyMessages() {
        if (typeof Pristine === 'undefined' || typeof Pristine.addMessages !== 'function') return;
        Pristine.addMessages('es', {
            required: 'Este campo es obligatorio',
            email: 'Ingresá un email válido',
            number: 'Ingresá solo números',
            integer: 'Ingresá un número entero',
            minlength: 'Ingresá al menos %s caracteres',
            maxlength: 'Ingresá como máximo %s caracteres',
            min: 'El valor mínimo es %s',
            max: 'El valor máximo es %s',
        });
        Pristine.setLocale('es');
    }

    function init(selector) {
        if (typeof Pristine === 'undefined') return;
        selector = selector || 'form.needs-validation';
        applyMessages();

        document.querySelectorAll(selector).forEach(function (form) {
            if (form.dataset.pristineInitialized) return;
            form.dataset.pristineInitialized = 'true';
            form.setAttribute('novalidate', 'novalidate');

            var pristine = new Pristine(form, {
                classTo: 'mb-4',
                errorClass: 'is-invalid',
                successClass: 'is-valid',
                errorTextParent: 'mb-4',
                errorTextTag: 'div',
                errorTextClass: 'invalid-feedback',
            });

            form.addEventListener('submit', function (e) {
                if (!pristine.validate()) {
                    e.preventDefault();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init };
})();
