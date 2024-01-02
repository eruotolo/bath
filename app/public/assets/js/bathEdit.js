$(document).on('click', '.editar', function () {
    let id_Bath = $(this).data('id');
    $.ajax({
        url: 'controller/bath-get.php',
        type: 'post',
        data: { id_Bath: id_Bath },
        dataType: 'text',
        success: function (response) {
            let bathData = JSON.parse(response);
            $('#idBath').val(bathData.id_Bath);
            $('#codigoBath').val(bathData.codigo_Bath);
            $('#fechaCompraBath').val(bathData.fechaCompra_Bath);
            $('#observacionBath').val(bathData.observacion_Bath);
            $('#estadoBath').val(bathData.estado_Bath);
        },
        error: function (request, status, error) {
            $('.modal-body').html(request.responseText);
        },
    });
});
