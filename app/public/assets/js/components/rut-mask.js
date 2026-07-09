// RutMask.js — formato y validación de RUT chileno (12.345.678-9) en inputs [data-rut-mask].
// Se auto-inicializa: no requiere script por vista.
//
// Nota técnica: el plan original proponía usar IMask para esto, pero el RUT chileno tiene
// largo variable (7-8 dígitos de cuerpo) + dígito verificador que puede ser una letra (K),
// algo que el motor de máscaras declarativas de IMask no resuelve bien. Se implementa el
// formateo/validación a mano (mismo algoritmo módulo 11 que cualquier librería de RUT usaría),
// sin agregar dependencias nuevas.

window.RutMask = (function () {
    function cleanRut(value) {
        return value.replace(/[^0-9kK]/g, '').toUpperCase();
    }

    function formatRut(value) {
        var clean = cleanRut(value);
        if (clean.length === 0) return '';

        var body = clean.slice(0, -1);
        var dv = clean.slice(-1);
        if (body.length === 0) return dv;

        var bodyFormatted = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return bodyFormatted + '-' + dv;
    }

    function computeDv(body) {
        var sum = 0;
        var multiplier = 2;
        for (var i = body.length - 1; i >= 0; i--) {
            sum += parseInt(body[i], 10) * multiplier;
            multiplier = multiplier === 7 ? 2 : multiplier + 1;
        }
        var remainder = 11 - (sum % 11);
        if (remainder === 11) return '0';
        if (remainder === 10) return 'K';
        return String(remainder);
    }

    function isValid(value) {
        var clean = cleanRut(value);
        if (clean.length < 2) return false;
        var body = clean.slice(0, -1);
        var dv = clean.slice(-1);
        if (!/^\d+$/.test(body)) return false;
        return computeDv(body) === dv;
    }

    function init(selector) {
        selector = selector || '[data-rut-mask]';

        document.querySelectorAll(selector).forEach(function (el) {
            if (el.dataset.rutMaskInitialized) return;
            el.dataset.rutMaskInitialized = 'true';

            if (el.type === 'number') {
                el.type = 'text';
            }
            el.setAttribute('maxlength', '12');
            if (!el.getAttribute('placeholder')) {
                el.setAttribute('placeholder', '12.345.678-9');
            }

            el.addEventListener('input', function () {
                var cursorWasAtEnd = el.selectionEnd === el.value.length;
                el.value = formatRut(el.value);
                if (cursorWasAtEnd) {
                    el.selectionStart = el.selectionEnd = el.value.length;
                }
            });

            if (el.hasAttribute('readonly')) return;

            el.addEventListener('blur', function () {
                el.classList.remove('is-valid', 'is-invalid');
                if (!el.value) return;
                el.classList.add(isValid(el.value) ? 'is-valid' : 'is-invalid');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init, formatRut: formatRut, isValid: isValid };
})();
