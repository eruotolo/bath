$(document).on('click', '.editar', function () {
    let id_Contacto = $(this).data('id');
    $.ajax({
        url: 'controller/contact-get.php',
        type: 'post',
        data: { id_Contacto: id_Contacto },
        dataType: 'text',
        success: function (response) {
            // Parse the JSON response to an object
            var contactData = JSON.parse(response);
            // Fill the form fields with received data
            $('#idC').val(contactData.id_Contacto);
            $('#idCC').val(contactData.id_Cliente);
            $('#nombreC').val(contactData.nombre_Contacto);
            $('#apellidoC').val(contactData.apellido_Contacto);
            $('#rutC').val(contactData.rut_Contacto);
            $('#telefonoC').val(contactData.telefono_Contacto);
            $('#direccionC').val(contactData.direccion_Contacto);
            // Show the modal
            //$("#myModal").modal("show");
        },
        error: function (request, status, error) {
            $('.modal-body').html(request.responseText);
        },
    });
});
