// SelectEnhanced.js — wrapper de Choices.js (v10) en español.
// Se auto-inicializa sobre [data-enhanced-select]: no requiere script por vista.
//
// Los selects en cascada (cliente -> contrato) ya existían antes de este componente,
// resueltos con jQuery haciendo $('#id_Contrato').html(respuestaAjax). Eso deja de
// funcionar en cuanto Choices.js toma el <select> (crea su propio DOM por encima y
// oculta el original), así que las vistas que migran a data-enhanced-select tienen
// que cambiar ese .html(...) por SelectEnhanced.setOptionsFromHtml(...), que sabe
// actualizar tanto un <select> nativo como una instancia de Choices ya inicializada.

window.SelectEnhanced = (function () {
    var instances = {};

    function spanishDefaults(el) {
        return {
            searchEnabled: true,
            shouldSort: true,
            searchPlaceholderValue: el.getAttribute('data-search-placeholder') || 'Buscar...',
            noResultsText: 'Sin resultados',
            noChoicesText: 'No hay opciones para elegir',
            itemSelectText: 'Click para seleccionar',
            loadingText: 'Cargando...',
        };
    }

    function init(selector) {
        if (typeof Choices === 'undefined') return;
        selector = selector || '[data-enhanced-select]';

        document.querySelectorAll(selector).forEach(function (el) {
            if (el.dataset.choicesInitialized) return;
            el.dataset.choicesInitialized = 'true';

            var instance = new Choices(el, spanishDefaults(el));
            if (el.id) {
                instances[el.id] = instance;
            }
        });
    }

    // Actualiza las opciones de un <select> (nativo o ya potenciado con Choices)
    // a partir de un fragmento HTML de <option>...</option> (la misma respuesta
    // que ya devuelven los endpoints de cascada existentes, ej. obtener_contratos.php).
    // selectedValue es opcional: fuerza cuál queda seleccionada (útil en pantallas
    // de edición, donde hay que precargar el valor que ya tenía guardado el registro).
    function setOptionsFromHtml(selectId, html, selectedValue) {
        var el = document.getElementById(selectId);
        if (!el) return;

        var temp = document.createElement('select');
        temp.innerHTML = html;
        var choices = Array.from(temp.options).map(function (opt) {
            // Un <select> nativo sin ningún <option selected> marca el primero
            // como .selected=true por defecto (comportamiento del parser HTML,
            // no algo que esté en el fragmento). En un <select multiple> eso hace
            // que Choices.js termine marcando TODAS las opciones como elegidas
            // (ver bug: cascada contrato->servicios seleccionaba todos los servicios
            // sin que el usuario tocara nada). Por eso, sin selectedValue explícito,
            // en selects múltiples se ignora opt.selected y arranca vacío.
            var selected = selectedValue != null
                ? String(opt.value) === String(selectedValue)
                : (el.multiple ? false : opt.selected);
            return { value: opt.value, label: opt.textContent.trim(), selected: selected, disabled: opt.disabled };
        });

        var instance = instances[selectId];
        if (instance) {
            instance.setChoices(choices, 'value', 'label', true);
        } else {
            el.innerHTML = html;
            if (selectedValue != null) {
                el.value = selectedValue;
            }
        }
    }

    // Engancha un select "padre" para que, al cambiar, pida por AJAX las opciones
    // del select "hijo" y las cargue (respetando si el hijo tiene Choices.js o no).
    // Dispara un 'change' sintético en el hijo después de poblarlo: Choices.js no
    // emite 'change' cuando setChoices() selecciona la primera opción automáticamente
    // (pasa siempre que solo llega una opción, o ninguna quedó marcada 'selected'),
    // así que sin esto una cascada encadenada (ej. contrato -> servicios) nunca se dispara.
    function cascade(config) {
        var parentEl = document.getElementById(config.parent);
        if (!parentEl) return;

        parentEl.addEventListener('change', function () {
            var value = parentEl.value;
            var childEl = document.getElementById(config.child);

            if (!value) {
                setOptionsFromHtml(config.child, '');
                if (childEl) childEl.dispatchEvent(new Event('change'));
                return;
            }

            var formData = new FormData();
            formData.append(config.paramName, value);

            fetch(config.endpoint, { method: 'POST', body: formData })
                .then(function (res) { return res.text(); })
                .then(function (html) {
                    setOptionsFromHtml(config.child, html);
                    if (childEl) childEl.dispatchEvent(new Event('change'));
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    return { init: init, setOptionsFromHtml: setOptionsFromHtml, cascade: cascade };
})();
