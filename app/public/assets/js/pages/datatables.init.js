/*
Template Name: Chubby - Admin & Dashboard Template
Author: Themesbrand
Website: https://themesbrand.com/
Contact: themesbrand@gmail.com
File: Datatables Js File
*/

$(document).ready(function () {
  $("#datatable").DataTable();

  //Buttons examples
  var table = $("#datatable-buttons").DataTable({
    lengthChange: false,
    buttons: ["copy", "excel", "pdf", "colvis"],
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar elementos _MENU_",
      info: "Mostrando _PAGE_ pagina de _PAGES_ paginas",
      infoEmpty: "Mostrando elemento 0 a 0 de 0 elementos",
      infoFiltered: "(filtrado por _MAX_ elementos en total)",
      emptyTable: "No hay datos disponibles en la tabla",
      loadingRecords: "Cargando...",
      zeroRecords: "No se encontraron registros coincidentes",
      aria: {
        sortAscending: ": permite ordenar la columna en orden ascendente",
        sortDescending: ": habilita ordenar la columna en orden descendente",
      },
      paginate: {
        first: "Primero",
        previous: "Anterior",
        next: "Siguiente",
        last: "Ultimo",
      },
    },
  });

  table
    .buttons()
    .container()
    .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");

  $(".dataTables_length select").addClass("form-select form-select-sm");
});
