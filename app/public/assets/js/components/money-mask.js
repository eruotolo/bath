// MoneyMask.js — formato de miles en vivo (99.000) para inputs [data-money-mask].
// Se auto-inicializa: no requiere script por vista. El valor que viaja al submit
// incluye los puntos de miles; el controller que reciba el campo debe limpiarlos
// (str_replace('.', '', ...)) antes de validar/guardar el número.

window.MoneyMask = (function () {
    function clean(value) {
        return value.replace(/\D/g, '');
    }

    function format(value) {
        var digits = clean(value);
        if (digits.length === 0) return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function init(selector) {
        selector = selector || '[data-money-mask]';

        document.querySelectorAll(selector).forEach(function (el) {
            if (el.dataset.moneyMaskInitialized) return;
            el.dataset.moneyMaskInitialized = 'true';

            if (el.type === 'number') {
                el.type = 'text';
            }
            el.setAttribute('inputmode', 'numeric');

            el.addEventListener('input', function () {
                var cursorWasAtEnd = el.selectionEnd === el.value.length;
                el.value = format(el.value);
                if (cursorWasAtEnd) {
                    el.selectionStart = el.selectionEnd = el.value.length;
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init, format: format, clean: clean };
})();
