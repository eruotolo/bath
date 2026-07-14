<?php

/**
 * Componente de tabla nativa reutilizable (sin DataTables): calco del shell
 * visual de dash-customers.php — buscador + acciones en su propia fila, card
 * rounded-3xl, thead mono uppercase, footer "Mostrando X-Y de Z" + paginacion.
 * Usalo para cualquier tabla nueva del proyecto en vez de repetir el markup.
 *
 * Uso:
 *   table_native_open([
 *       'table_id' => 'contratos-activos',
 *       'search_placeholder' => 'Buscar por obra, cliente...',
 *       'item_label' => 'Contratos',
 *       'per_page' => 10,
 *       'actions_html' => '<a class="dt-btn-add" href="...">Agregar</a>', // opcional
 *       'columns' => [
 *           ['label' => 'Código de Baño'],
 *           ['label' => 'Fecha de Inicio', 'align' => 'right'],
 *       ],
 *   ]);
 *   foreach ($rows as $row) {
 *       // <tr data-search="...">...</tr> a mano, con htmlspecialchars() en cada celda
 *   }
 *   table_native_close();
 *
 * Buscador y paginacion son 100% client-side via
 * assets/js/components/native-table.js: filtra <tr data-search="..."> por texto
 * y pagina el resultado filtrado, todo dentro de <table id="table_id">.
 */
/**
 * Botones de exportacion Excel/PDF con las mismas proporciones que dash-customers.php.
 * Devuelve el HTML listo para pasar como 'actions_html' a table_native_open().
 */
function table_native_export_buttons(string $csvUrl, string $pdfUrl, string $idPrefix = ''): string {
    $csvUrl = htmlspecialchars($csvUrl, ENT_QUOTES, 'UTF-8');
    $pdfUrl = htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8');
    $idPrefix = htmlspecialchars($idPrefix, ENT_QUOTES, 'UTF-8');
    return <<<HTML
        <a
            href="{$csvUrl}"
            id="export-{$idPrefix}-csv"
            class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 bg-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 active:scale-95 transition-all shadow-sm"
        >
            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
            <span>Excel</span>
        </a>
        <a
            href="{$pdfUrl}"
            id="export-{$idPrefix}-pdf"
            class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 bg-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 active:scale-95 transition-all shadow-sm"
        >
            <i data-lucide="file-down" class="w-3.5 h-3.5 text-rose-600 shrink-0"></i>
            <span>PDF</span>
        </a>
        HTML;
}

function table_native_open(array $opts): void {
    $tableId = htmlspecialchars((string) ($opts['table_id'] ?? ''), ENT_QUOTES, 'UTF-8');
    $searchId = htmlspecialchars((string) ($opts['search_id'] ?? ($tableId . '-search')), ENT_QUOTES, 'UTF-8');
    $searchPlaceholder = htmlspecialchars((string) ($opts['search_placeholder'] ?? 'Buscar...'), ENT_QUOTES, 'UTF-8');
    $itemLabel = htmlspecialchars((string) ($opts['item_label'] ?? 'Resultados'), ENT_QUOTES, 'UTF-8');
    $perPage = (int) ($opts['per_page'] ?? 9);
    $actionsHtml = $opts['actions_html'] ?? '';
    $columns = $opts['columns'] ?? [];
    ?>
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-6" data-table-native-toolbar="#<?php echo $tableId; ?>">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            <input
                type="text"
                id="<?php echo $searchId; ?>"
                data-table-search-input="#<?php echo $tableId; ?>"
                placeholder="<?php echo $searchPlaceholder; ?>"
                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
            >
        </div>
        <?php if ($actionsHtml !== ''): ?>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <?php echo $actionsHtml; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-table-native-wrap>
        <div class="overflow-x-auto">
            <table
                id="<?php echo $tableId; ?>"
                class="w-full text-left border-collapse"
                data-per-page="<?php echo $perPage; ?>"
                data-item-label="<?php echo $itemLabel; ?>"
            >
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50/50">
                        <?php foreach ($columns as $col): ?>
                            <?php
                                $align = $col['align'] ?? 'left';
                                $alignClass = $align === 'right' ? ' text-right' : ($align === 'center' ? ' text-center' : '');
                            ?>
                            <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase<?php echo $alignClass; ?>"><?php echo htmlspecialchars((string) $col['label'], ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
    <?php
}

function table_native_close(): void {
    ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between" data-table-native-pagination>
            <span class="font-mono text-[10px] text-slate-400 font-bold uppercase" data-table-native-summary></span>
            <div class="flex items-center space-x-1" data-table-native-pages></div>
        </div>
    </div>
    <?php
}
