$(document).ready(function () {
    var table = $('#datatable-buttons').DataTable({
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All'],
        ], // Define los valores para la opción "Show Entries"
        buttons: [
            {
                extend: 'collection',
                text: 'Exportar',
                buttons: ['copy', 'excel', 'pdf'],
            },
            {
                extend: 'colvis', // Cambiado a extend 'colvis'
                text: 'Visibilidad de columnas', // Traducción
            },
        ],
        language: {
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ entradas', // Personaliza el texto de "Show Entries"
            info: 'Mostrando _PAGE_ de _PAGES_ páginas',
            infoEmpty: 'Mostrando 0 a 0 de 0 elementos',
            infoFiltered: '(filtrado de _MAX_ elementos en total)',
            emptyTable: 'No hay datos disponibles en la tabla',
            loadingRecords: 'Cargando...',
            zeroRecords: 'No se encontraron registros coincidentes',
            aria: {
                sortAscending: ': permite ordenar la columna en orden ascendente',
                sortDescending: ': habilita ordenar la columna en orden descendente',
            },
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último',
            },
        },
    });

    table.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

    $('.dataTables_length select').addClass('form-select form-select-sm');
});
