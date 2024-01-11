$(document).on('click', '.view', function () {
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
            $('#id').val(contactData.id_Contacto);
            $('#nombre').val(contactData.nombre_Contacto);
            $('#apellido').val(contactData.apellido_Contacto);
            $('#rut').val(contactData.rut_Contacto);
            $('#telefono').val(contactData.telefono_Contacto);
            $('#direccion').val(contactData.direccion_Contacto);
            $('#observacion').val(contactData.observacion_Contacto);
            // Show the modal
            //$("#myModal").modal("show");
        },
        error: function (request, status, error) {
            $('.modal-body').html(request.responseText);
        },
    });
});
