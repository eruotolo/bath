$(document).on('click', '.editarCliente', function () {
    let id_Cliente = $(this).data('id');
    $.ajax({
        url: 'controller/customer-get.php',
        type: 'post',
        data: { id_Cliente: id_Cliente },
        dataType: 'text',
        success: function (response) {
            // Parse the JSON response to an object
            var clienteData = JSON.parse(response);
            // Fill the form fields with received data

            $('#idCliente').val(clienteData.id_Cliente);
            $('#rutCliente').val(clienteData.rut_Cliente);
            $('#nombreCliente').val(clienteData.nombre_Cliente);
            $('#telefonoCliente').val(clienteData.telefono_Cliente);
            $('#emailCliente').val(clienteData.email_Cliente);
            $('#direccionCliente').val(clienteData.direccion_Cliente);
            $('#comunaCliente').val(clienteData.comuna_Cliente);
            $('#ciudadCliente').val(clienteData.ciudad_Cliente);
            $('#regionCliente').val(clienteData.region_Cliente);
        },
        error: function (request, status, error) {
            $('.modal-body').html(request.responseText);
        },
    });
});
