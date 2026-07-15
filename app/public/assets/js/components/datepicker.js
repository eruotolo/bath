// Datepicker.js — wrapper de Flatpickr en español sobre input[type="date"].
// Se auto-inicializa: no requiere script por vista. Flatpickr reemplaza visualmente
// el picker nativo del navegador. Usa altInput: el usuario ve/edita d-m-Y, pero el
// input real (name, value enviado al backend) sigue en Y-m-d -> cero cambios en
// controllers/DB.
//
// Casos opt-in vía data-attrs:
//   data-datepicker-min="today"          -> no permite fechas pasadas
//   data-datepicker-max="today"          -> no permite fechas futuras
//   data-datepicker-min-from="#selector" -> la fecha mínima es la de otro input (ej. fin >= inicio de contrato)

window.Datepicker = (function () {
    function init(selector) {
        if (typeof flatpickr === 'undefined') return;
        selector = selector || 'input[type="date"]:not([hidden])';

        document.querySelectorAll(selector).forEach(function (el) {
            if (el.dataset.flatpickrInitialized) return;
            el.dataset.flatpickrInitialized = 'true';

            var config = {
                locale: 'es',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                allowInput: true,
            };

            if (el.getAttribute('data-datepicker-min') === 'today') {
                config.minDate = 'today';
            }
            if (el.getAttribute('data-datepicker-max') === 'today') {
                config.maxDate = 'today';
            }

            var minFromSelector = el.getAttribute('data-datepicker-min-from');
            if (minFromSelector) {
                var sourceEl = document.querySelector(minFromSelector);
                if (sourceEl && sourceEl.value) {
                    config.minDate = sourceEl.value;
                }
            }

            var instance = flatpickr(el, config);

            if (minFromSelector) {
                var sourceEl2 = document.querySelector(minFromSelector);
                if (sourceEl2) {
                    sourceEl2.addEventListener('change', function () {
                        instance.set('minDate', sourceEl2.value || null);
                    });
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init };
})();
