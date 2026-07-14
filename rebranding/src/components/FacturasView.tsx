import React, { useState } from 'react';
import { 
  Plus, 
  Search, 
  Receipt, 
  Check, 
  AlertTriangle, 
  FileSpreadsheet, 
  ArrowUpRight, 
  DollarSign, 
  X, 
  TrendingUp, 
  ChevronRight,
  UploadCloud,
  FileCheck2
} from 'lucide-react';
import { Invoice, Client, Contract } from '../types';

interface FacturasViewProps {
  invoices: Invoice[];
  clients: Client[];
  contracts: Contract[];
  onAddInvoice: (newInvoices: Invoice[]) => void;
  onUpdateInvoiceStatus: (invoiceNumber: string, status: 'Pagado') => void;
  searchTerm: string;
}

export default function FacturasView({ invoices, clients, contracts, onAddInvoice, onUpdateInvoiceStatus, searchTerm }: FacturasViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [filter, setFilter] = useState<'todos' | 'Pagado' | 'Pendiente'>('todos');
  const [isUploading, setIsUploading] = useState(false);
  
  // Excel staging playground state
  const [dragActive, setDragActive] = useState(false);
  const [stagedInvoices, setStagedInvoices] = useState<Invoice[]>([]);
  const [uploadSuccessMessage, setUploadSuccessMessage] = useState('');

  // Invoice creator manual form
  const [isManualAdding, setIsManualAdding] = useState(false);
  const [manualNumber, setManualNumber] = useState('');
  const [manualDate, setManualDate] = useState(new Date().toISOString().split('T')[0]);
  const [selectedContractId, setSelectedContractId] = useState('');
  const [manualAmount, setManualAmount] = useState('');
  const [manualObservations, setManualObservations] = useState('');

  const formatCLP = (val: number) => {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(val);
  };

  const handleManualCreate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!manualNumber || !selectedContractId || !manualAmount) {
      alert('Por favor complete todos los campos obligatorios.');
      return;
    }

    const targetContract = contracts.find(c => c.id === selectedContractId);
    if (!targetContract) return;

    const newInvoice: Invoice = {
      number: manualNumber.startsWith('#') ? manualNumber : `#${manualNumber}`,
      date: manualDate,
      clientName: targetContract.clientName,
      workName: targetContract.workName,
      amount: Number(manualAmount),
      status: 'Pendiente',
      observations: manualObservations || 'Factura emitida manualmente'
    };

    onAddInvoice([newInvoice]);
    setIsManualAdding(false);

    // Reset
    setManualNumber('');
    setManualAmount('');
    setSelectedContractId('');
    setManualObservations('');
  };

  // Simulated Excel Template Parsing!
  const handleLoadDemoExcel = () => {
    // Generate mock invoices that are staged in the sandbox
    const parsedFromExcel: Invoice[] = [
      {
        number: '#1898',
        date: '2026-07-12',
        clientName: 'TORALLA S.A.',
        workName: 'OBRA CHONCHI',
        amount: 850000,
        status: 'Pendiente',
        observations: 'Leído de plantilla Excel: Mantenimiento mensual cabinas'
      },
      {
        number: '#1899',
        date: '2026-07-12',
        clientName: 'ARIDOS MARDONES',
        workName: 'LINAO-ANCUD',
        amount: 273700,
        status: 'Pendiente',
        observations: 'Leído de plantilla Excel: Suministros adicionales'
      },
      {
        number: '#1900',
        date: '2026-07-13',
        clientName: 'APIA SPA',
        workName: 'CURACO DE VELEZ',
        amount: 154700,
        status: 'Pendiente',
        observations: 'Leído de plantilla Excel: Factura regular'
      }
    ];

    setStagedInvoices(parsedFromExcel);
    setUploadSuccessMessage('✔ Archivo "Facturas_Julio_Blanco.xlsx" procesado con éxito. 3 registros nuevos listos para importar.');
  };

  const handleImportStaged = () => {
    if (stagedInvoices.length === 0) return;
    onAddInvoice(stagedInvoices);
    setStagedInvoices([]);
    setUploadSuccessMessage('');
    setIsUploading(false);
  };

  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredInvoices = invoices.filter(inv => {
    const matchesSearch = 
      inv.number.toLowerCase().includes(combinedSearch) ||
      inv.clientName.toLowerCase().includes(combinedSearch) ||
      inv.workName.toLowerCase().includes(combinedSearch);

    if (filter === 'todos') return matchesSearch;
    return matchesSearch && inv.status === filter;
  });

  return (
    <div className="space-y-6">
      
      {/* Financial KPIs */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div className="p-5 rounded-3xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-800 flex items-center justify-between">
          <div className="space-y-1">
            <span className="text-[10px] font-mono font-bold uppercase tracking-wider block text-indigo-600">Recaudación Lograda</span>
            <span className="font-sans font-extrabold text-xl">
              {formatCLP(invoices.filter(i => i.status === 'Pagado').reduce((sum, i) => sum + i.amount, 0))}
            </span>
            <span className="text-[10px] text-slate-500 block">Ingresos depositados y confirmados</span>
          </div>
          <TrendingUp className="w-8 h-8 text-indigo-600/30" />
        </div>

        <div className="p-5 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-800 flex items-center justify-between">
          <div className="space-y-1">
            <span className="text-[10px] font-mono font-bold uppercase tracking-wider block text-amber-600">Por Recaudar</span>
            <span className="font-sans font-extrabold text-xl">
              {formatCLP(invoices.filter(i => i.status === 'Pendiente').reduce((sum, i) => sum + i.amount, 0))}
            </span>
            <span className="text-[10px] text-slate-500 block">Compromisos de pago vigentes</span>
          </div>
          <AlertTriangle className="w-8 h-8 text-amber-600/30" />
        </div>

        <div className="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-between col-span-1 sm:col-span-2 lg:col-span-1">
          <div className="space-y-1">
            <span className="text-[10px] font-mono text-slate-400 font-bold uppercase block">Eficiencia de Cobro</span>
            <span className="font-sans font-extrabold text-xl text-slate-800 block">
              {Math.round((invoices.filter(i => i.status === 'Pagado').length / invoices.length) * 100)}%
            </span>
            <span className="text-[10px] text-indigo-600 block">✔ Cartera de clientes saludable</span>
          </div>
          <Check className="w-8 h-8 text-indigo-600 bg-indigo-50 p-2 rounded-full" />
        </div>
      </div>

      {/* Main search / actions controls */}
      <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div className="flex items-center space-x-3 flex-1 max-w-lg">
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Buscar por N° factura o cliente..."
              value={localSearch}
              onChange={(e) => setLocalSearch(e.target.value)}
              id="facturas-local-search"
              className="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
            />
          </div>

          <div className="flex bg-slate-100 p-1 rounded-xl">
            <button
              onClick={() => setFilter('todos')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-lg font-sans transition-all ${filter === 'todos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
            >
              Todas
            </button>
            <button
              onClick={() => setFilter('Pagado')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-lg font-sans transition-all ${filter === 'Pagado' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
            >
              Pagadas
            </button>
            <button
              onClick={() => setFilter('Pendiente')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-lg font-sans transition-all ${filter === 'Pendiente' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
            >
              Pendientes
            </button>
          </div>
        </div>

        {/* Action triggers */}
        <div className="flex items-center space-x-3 shrink-0">
          <button
            onClick={() => setIsUploading(true)}
            className="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 active:scale-95 transition-all"
            id="excel-import-btn"
          >
            <FileSpreadsheet className="w-4 h-4 text-indigo-600" />
            <span>Subir Excel / CSV</span>
          </button>

          <button
            onClick={() => setIsManualAdding(true)}
            className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-indigo-600/10 transition-all active:scale-95"
            id="new-invoice-btn"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>Emitir Factura</span>
          </button>
        </div>
      </div>

      {/* Main invoices table */}
      <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-slate-50 bg-slate-50/50">
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">N° Factura</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Fecha Emisión</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Cliente / Obra</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Monto Neto</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Estado</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {filteredInvoices.map((inv) => (
                <tr key={inv.number} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-6 py-4 font-mono font-extrabold text-slate-800 text-sm">{inv.number}</td>
                  <td className="px-6 py-4 font-mono text-xs text-slate-500">{inv.date}</td>
                  <td className="px-6 py-4">
                    <span className="font-sans font-bold text-slate-900 block text-xs truncate max-w-[200px]">{inv.clientName}</span>
                    <span className="font-sans text-[10px] text-slate-400 block mt-0.5 truncate max-w-[200px]">{inv.workName}</span>
                  </td>
                  <td className="px-6 py-4 font-sans font-extrabold text-slate-800 text-sm">
                    {formatCLP(inv.amount)}
                  </td>
                  <td className="px-6 py-4">
                    <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold font-sans tracking-wide uppercase ${inv.status === 'Pagado' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>
                      {inv.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right">
                    {inv.status === 'Pendiente' ? (
                      <button
                        onClick={() => {
                          if (confirm(`¿Registrar el pago de la factura ${inv.number} por un monto de ${formatCLP(inv.amount)}?`)) {
                            onUpdateInvoiceStatus(inv.number, 'Pagado');
                          }
                        }}
                        className="px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors rounded-lg font-sans text-xs font-bold"
                      >
                        Registrar Pago
                      </button>
                    ) : (
                      <span className="text-xs font-medium text-slate-400 font-sans italic">Pago Conciliado</span>
                    )}
                  </td>
                </tr>
              ))}
              {filteredInvoices.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                    No se encontraron facturas registradas en este filtro.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Slide Drawer: EXCEL FILE UPLOAD / SANDBOX (HIGH FIDELITY SCREENSHOT 13 INTEGRATION) */}
      {isUploading && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setIsUploading(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-xl bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                  <FileSpreadsheet className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Importador de Planillas Excel</h3>
                  <span className="text-[10px] font-sans text-slate-400 block mt-0.5">Conciliar facturación externa de forma masiva.</span>
                </div>
              </div>
              <button onClick={() => {
                setIsUploading(false);
                setStagedInvoices([]);
                setUploadSuccessMessage('');
              }} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Sandbox content */}
            <div className="flex-1 p-6 space-y-6 overflow-y-auto">
              
              {/* Drag and Drop Landing Zone */}
              <div 
                className={`
                  p-8 rounded-3xl border-2 border-dashed transition-all duration-300 text-center cursor-pointer flex flex-col items-center justify-center space-y-3
                  ${dragActive 
                    ? 'border-indigo-500 bg-indigo-50/20' 
                    : stagedInvoices.length > 0 
                      ? 'border-indigo-200 bg-indigo-50/5' 
                      : 'border-slate-200 bg-slate-50/50 hover:border-slate-300 hover:bg-slate-50'}
                `}
                onDragOver={(e) => { e.preventDefault(); setDragActive(true); }}
                onDragLeave={() => setDragActive(false)}
                onDrop={(e) => { e.preventDefault(); setDragActive(false); handleLoadDemoExcel(); }}
                onClick={handleLoadDemoExcel}
              >
                <div className="w-14 h-14 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-md shadow-indigo-500/5">
                  <UploadCloud className="w-7 h-7" />
                </div>
                <div className="space-y-1">
                  <p className="font-sans font-bold text-slate-800 text-xs">Arrastre su planilla Excel aquí o haga clic</p>
                  <p className="font-sans text-[10px] text-slate-400">Formatos soportados: .xlsx, .xls, .csv • Hasta 10MB</p>
                </div>
                <button 
                  type="button"
                  className="px-4 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors font-sans text-[11px] font-bold rounded-xl"
                >
                  Cargar Archivo Excel de Prueba
                </button>
              </div>

              {/* Status alerts */}
              {uploadSuccessMessage && (
                <div className="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 text-xs text-indigo-800 font-sans leading-relaxed">
                  {uploadSuccessMessage}
                </div>
              )}

              {/* Interactive Staging preview grid */}
              {stagedInvoices.length > 0 && (
                <div className="space-y-3">
                  <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1">Previsualización de Carga</h4>
                  <div className="border border-slate-100 rounded-2xl overflow-hidden divide-y divide-slate-50 max-h-56 overflow-y-auto">
                    {stagedInvoices.map((stg) => (
                      <div key={stg.number} className="p-3 bg-white hover:bg-slate-50/50 transition-colors flex items-center justify-between text-xs font-sans">
                        <div className="space-y-0.5">
                          <div className="flex items-center space-x-1.5">
                            <span className="font-mono font-extrabold text-slate-800">{stg.number}</span>
                            <span className="text-[10px] text-slate-400">• {stg.date}</span>
                          </div>
                          <span className="text-slate-600 block truncate max-w-[200px]">{stg.clientName} ({stg.workName})</span>
                        </div>
                        <div className="text-right">
                          <span className="font-extrabold text-slate-800 block">{formatCLP(stg.amount)}</span>
                          <span className="text-[9px] font-mono text-amber-600 bg-amber-50 px-1 rounded uppercase font-bold mt-1 inline-block">Pendiente</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {/* Actions */}
            <div className="p-4 border-t border-slate-100 bg-slate-50 flex items-center space-x-3">
              <button
                type="button"
                onClick={() => {
                  setIsUploading(false);
                  setStagedInvoices([]);
                  setUploadSuccessMessage('');
                }}
                className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center"
              >
                Cerrar Importador
              </button>
              <button
                type="button"
                onClick={handleImportStaged}
                disabled={stagedInvoices.length === 0}
                className={`flex-1 py-2.5 rounded-xl text-xs font-semibold font-sans transition-all text-center flex items-center justify-center space-x-1 ${stagedInvoices.length > 0 ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-600/10' : 'bg-slate-100 text-slate-400 cursor-not-allowed'}`}
              >
                <FileCheck2 className="w-4 h-4" />
                <span>Confirmar e Importar {stagedInvoices.length > 0 ? `(${stagedInvoices.length})` : ''}</span>
              </button>
            </div>
          </div>
        </>
      )}

      {/* Slide Drawer: MANUAL INVOICE EMISSION */}
      {isManualAdding && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setIsManualAdding(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                  <Receipt className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Emitir Factura Manual</h3>
                  <span className="text-[10px] font-sans text-slate-400 block mt-0.5">Crear un compromiso de pago directo.</span>
                </div>
              </div>
              <button onClick={() => setIsManualAdding(false)} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleManualCreate} className="flex-1 p-6 space-y-4 overflow-y-auto">
              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Número de Factura <span className="text-rose-500">*</span></label>
                <input
                  type="text"
                  placeholder="e.g. 1901"
                  value={manualNumber}
                  onChange={(e) => setManualNumber(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                  required
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Fecha Emisión</label>
                <input
                  type="date"
                  value={manualDate}
                  onChange={(e) => setManualDate(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Obra / Contrato Asociado <span className="text-rose-500">*</span></label>
                <select
                  value={selectedContractId}
                  onChange={(e) => setSelectedContractId(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                  required
                >
                  <option value="">Seleccione obra de destino...</option>
                  {contracts
                    .filter(c => c.status === 'Activo')
                    .map((con) => (
                      <option key={con.id} value={con.id}>{con.workName} ({con.clientName})</option>
                    ))}
                </select>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Monto Neto Cobrado (CLP) <span className="text-rose-500">*</span></label>
                <input
                  type="number"
                  placeholder="e.g. 148750"
                  value={manualAmount}
                  onChange={(e) => setManualAmount(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                  required
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Observaciones / Glosa</label>
                <textarea
                  placeholder="Servicio regular de lavado de baños..."
                  value={manualObservations}
                  onChange={(e) => setManualObservations(e.target.value)}
                  rows={3}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                />
              </div>

              <div className="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsManualAdding(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-semibold font-sans hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/10"
                >
                  Emitir Factura
                </button>
              </div>
            </form>
          </div>
        </>
      )}

    </div>
  );
}
