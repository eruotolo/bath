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
//
// Toolbar de la vista (opcional, recomendado):
//   <div class="table-toolbar-actions">
//     <div class="table-toolbar-buttons btn-group">
//       <button class="btn btn-outline-secondary btn-sm" data-export="csv">CSV</button>
//       <button class="btn btn-outline-secondary btn-sm" data-export="excel">Excel</button>
//       <button class="btn btn-outline-secondary btn-sm" data-export="pdf">PDF</button>
//       <button class="btn btn-outline-secondary btn-sm" data-action="colvis">Columnas</button>
//     </div>
//     <div class="table-toolbar-search"></div>
//     <a href="..." class="btn btn-light">Agregar</a>
//   </div>
// Si .table-toolbar-buttons existe, los data-export y data-action se conectan
// a la API de DataTables (los buttons deben estar definidos en config con name:).

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

    function relocateNativeControls($el) {
        var $wrapper = $el.closest('.dataTables_wrapper');
        // Si la tabla vive dentro de un .tab-pane (varias tablas por pagina, una por tab),
        // el buscador se reubica en el .table-toolbar-search de ese mismo pane. Si no,
        // cae al primero de la pagina (caso de una sola tabla, comportamiento previo).
        var $scope = $el.closest('.tab-pane');
        var $toolbarSearch = ($scope.length ? $scope.find('.table-toolbar-search') : $('.table-toolbar-search')).first();

        if ($toolbarSearch.length) {
            $wrapper.find('.dataTables_filter').appendTo($toolbarSearch);
        }

        $wrapper.find('.dataTables_length').insertBefore($wrapper.find('.dataTables_info'));
    }

    function bindToolbarButtons($el, table) {
        // Búsqueda global (igual que relocateNativeControls con .table-toolbar-search):
        // .table-toolbar-buttons vive en .table-toolbar, hermano de .table-card/.table-responsive,
        // no descendiente de $wrapper.parent() — un scoped find() ahí nunca lo encuentra.
        var $toolbar = $('.table-toolbar-buttons').first();
        if ($toolbar.length === 0) return;

        $toolbar.find('[data-export]').on('click', function (e) {
            e.preventDefault();
            var type = $(this).data('export');
            try {
                table.button(type + ':name').trigger();
            } catch (err) {
                console.warn('DataTables: button "' + type + '" no definido en config.buttons');
            }
        });

        $toolbar.find('[data-action]').on('click', function (e) {
            e.preventDefault();
            var $realBtn = $(this);
            var action = $realBtn.data('action');
            try {
                table.button(action + ':name').trigger();
                // El botón interno de DataTables Buttons está oculto (dom: 'lfrtip' sin 'B'),
                // así que su posición no sirve para anclar el panel flotante (colvis, etc.).
                // DataTables inserta el panel en el DOM en el siguiente tick, no de forma
                // síncrona con .trigger() — se reposiciona respecto al botón real un instante después.
                setTimeout(function () {
                    var $collection = $('.dt-button-collection').last();
                    if ($collection.length) {
                        var offset = $realBtn.offset();
                        $collection.css({
                            top: offset.top + $realBtn.outerHeight() + 6,
                            left: offset.left,
                            right: 'auto'
                        });
                    }
                }, 50);
            } catch (err) {
                console.warn('DataTables: action "' + action + '" no definido en config.buttons');
            }
        });
    }

    function defaultButtons() {
        return [
            { extend: 'csv',   name: 'csv',   text: '<i class="fas fa-file-csv me-1"></i> CSV',   className: 'd-none' },
            { extend: 'excel', name: 'excel', text: '<i class="fas fa-file-excel me-1"></i> Excel', className: 'd-none' },
            { extend: 'pdf',   name: 'pdf',   text: '<i class="fas fa-file-pdf me-1"></i> PDF',   className: 'd-none', orientation: 'landscape' },
            { extend: 'colvis', name: 'colvis', text: 'Columnas', className: 'd-none' },
        ];
    }

    function init(selector, options) {
        var $el = $(selector);
        if (!$el.length) return null;
        if ($el.data('dtInstance')) return $el.data('dtInstance');

        var config = $.extend(true, {
            pageLength: 10,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, 'All'],
            ],
            dom: 'lfrtip',
            responsive: true,
            language: spanishLanguage(),
            buttons: defaultButtons(),
        }, options || {});

        // Permite a la vista extender/override buttons manteniendo los defaults
        if (options && options.buttons) {
            config.buttons = options.buttons;
        }

        if ($el.attr('data-dt-state') === 'true') {
            config.stateSave = true;
        }

        var table = $el.DataTable(config);
        $el.data('dtInstance', table);

        $el.closest('.dataTables_wrapper').find('.dataTables_length select').addClass('form-select form-select-sm');
        relocateNativeControls($el);
        bindToolbarButtons($el, table);

        if ($el.attr('data-dt-column-filters') === 'true') {
            addColumnFilters($el, table);
        }

        return table;
    }

    return { init: init };
})(jQuery);