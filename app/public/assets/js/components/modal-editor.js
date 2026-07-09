// ModalEditor.js — patrón unificado de "click en botón -> AJAX GET -> llenar campos de un modal".
// Reemplaza customer-edit.js, contactoEdit.js y contactoVer.js (3 scripts casi idénticos,
// ~27 líneas cada uno, mismo patrón: click -> fetch -> JSON.parse -> $('#campo').val(...)).
//
// Uso:
//   ModalEditor.init({
//     triggerSelector: '.editarCliente',
//     endpoint: 'controller/customer-get.php',
//     idParam: 'id_Cliente',
//     fieldMap: { idCliente: 'id_Cliente', rutCliente: 'rut_Cliente', ... }, // idCampoHtml: keyRespuesta
//   });

window.ModalEditor = (function ($) {
    function init(config) {
        $(document).on('click', config.triggerSelector, function () {
            var id = $(this).data('id');
            var payload = {};
            payload[config.idParam] = id;

            $.ajax({
                url: config.endpoint,
                type: 'post',
                data: payload,
                dataType: 'text',
                success: function (response) {
                    var data = JSON.parse(response);
                    Object.keys(config.fieldMap).forEach(function (fieldId) {
                        $('#' + fieldId).val(data[config.fieldMap[fieldId]]);
                    });
                    if (typeof config.onLoaded === 'function') {
                        config.onLoaded(data);
                    }
                },
                error: function (request) {
                    $('.modal-body').html(request.responseText);
                },
            });
        });
    }

    return { init: init };
})(jQuery);
