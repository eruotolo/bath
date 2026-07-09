// DataTable.js — init único y compartido de DataTables (jQuery), en español.
// Reemplaza el script inline de ~45 líneas duplicado en 4 vistas.
//
// Uso:
//   DataTable.init('#datatable-buttons', { order: [[2, 'desc']], columnDefs: [...] });
// Devuelve la instancia de DataTables para que la vista siga agregando lógica propia
// (filtros custom, listeners de modales, etc.) exactamente como antes.
//
// Features opt-in vía data-attrs en el <table>:
//   data-dt-state="true"            -> persiste orden/página/búsqueda entre refrescos (localStorage)
//   data-dt-column-filters="true"   -> agrega un input de búsqueda por columna (excepto .no-filter)

window.DataTable = (function ($) {
    function spanishLanguage() {
        return {
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ entradas',
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
        };
    }

    function addColumnFilters($el, table) {
        var $headerRow = $el.find('thead tr').first();
        var $filterRow = $('<tr class="dt-column-filters"></tr>');

        $headerRow.find('th').each(function (index) {
            var $th = $('<th></th>');
            if (!$(this).hasClass('no-filter')) {
                var $input = $('<input type="text" class="form-control form-control-sm" placeholder="Filtrar...">');
                $input.on('click', function (e) {
                    e.stopPropagation();
                });
                $input.on('keyup change', function () {
                    var value = this.value;
                    if (table.column(index).search() !== value) {
                        table.column(index).search(value).draw();
                    }
                });
                $th.append($input);
            }
            $filterRow.append($th);
        });

        $el.find('thead').append($filterRow);
    }

    function init(selector, options) {
        var $el = $(selector);
        if (!$el.length) return null;
        if ($el.data('dtInstance')) return $el.data('dtInstance');

        var config = $.extend(true, {
            lengthMenu: [
                [50, 100, -1],
                [50, 100, 'All'],
            ],
            responsive: true,
            language: spanishLanguage(),
            buttons: [
                {
                    extend: 'collection',
                    text: 'Exportar',
                    buttons: ['copy', 'excel', 'pdf'],
                },
            ],
        }, options || {});

        if ($el.attr('data-dt-state') === 'true') {
            config.stateSave = true;
        }

        var table = $el.DataTable(config);
        $el.data('dtInstance', table);

        table.buttons().container().appendTo(selector + '_wrapper .col-md-6:eq(0)');
        $el.closest('.dataTables_wrapper').find('.dataTables_length select').addClass('form-select form-select-sm');

        if ($el.attr('data-dt-column-filters') === 'true') {
            addColumnFilters($el, table);
        }

        return table;
    }

    return { init: init };
})(jQuery);
