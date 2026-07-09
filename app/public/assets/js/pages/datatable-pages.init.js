/*
Template Name: Chubby - Admin & Dashboard Template
Author: Themesbrand
Website: https://themesbrand.com/
Contact: themesbrand@gmail.com
File: datatable for pages Js File
*/

// DEPRECADO (plan-mejoras-frontend.md, Sprint 2): usar assets/js/components/datatable.js
// para vistas nuevas. Todavía lo usan dash-users-list.php, dash-customers.php y
// dash-contracts.php — migrarlas es un paso pendiente, no se tocaron en este sprint.

// datatable
$(document).ready(function () {
    $('.datatable').DataTable({
        responsive: false
    });
    $(".dataTables_length select").addClass('form-select form-select-sm');
});