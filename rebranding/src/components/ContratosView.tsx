import React, { useState } from 'react';
import { 
  Plus, 
  Search, 
  FileText, 
  Calendar, 
  DollarSign, 
  MapPin, 
  Briefcase, 
  Check, 
  X, 
  User, 
  Bath, 
  ArrowRight,
  TrendingUp,
  Map
} from 'lucide-react';
import { Contract, Client, Toilet } from '../types';

interface ContratosViewProps {
  contracts: Contract[];
  clients: Client[];
  toilets: Toilet[];
  onAddContract: (newContract: Contract) => void;
  onUpdateToilet: (updatedToilets: Toilet[]) => void;
  searchTerm: string;
}

export default function ContratosView({ contracts, clients, toilets, onAddContract, onUpdateToilet, searchTerm }: ContratosViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [activeTab, setActiveTab] = useState<'Activo' | 'Terminado'>('Activo');
  const [selectedContract, setSelectedContract] = useState<Contract | null>(null);
  const [isAddingContract, setIsAddingContract] = useState(false);

  // Form states for creating a new contract
  const [clientName, setClientName] = useState('');
  const [workName, setWorkName] = useState('');
  const [startDate, setStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [endDate, setEndDate] = useState(new Date(Date.now() + 365*24*60*60*1000).toISOString().split('T')[0]);
  const [monthlyValue, setMonthlyValue] = useState('');
  const [totalValue, setTotalValue] = useState('');
  const [address, setAddress] = useState('');
  const [observations, setObservations] = useState('');
  const [selectedToilets, setSelectedToilets] = useState<string[]>([]);

  // Calculate totals
  const activeContracts = contracts.filter(c => c.status === 'Activo');
  const monthlyRunRate = activeContracts.reduce((sum, c) => sum + c.monthlyValue, 0);

  const formatCLP = (val: number) => {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(val);
  };

  const handleCreateContract = (e: React.FormEvent) => {
    e.preventDefault();
    if (!clientName || !workName || !monthlyValue) {
      alert('Por favor complete los campos obligatorios.');
      return;
    }

    const newContractId = `K-${Date.now().toString().slice(-3)}`;
    const newContract: Contract = {
      id: newContractId,
      clientName,
      workName: workName.toUpperCase(),
      status: 'Activo',
      startDate,
      endDate,
      monthlyValue: Number(monthlyValue),
      totalValue: Number(totalValue) || Number(monthlyValue) * 12, // fallback approximation
      address: address || 'Dirección de obra no especificada',
      observations: observations || 'SO',
      assignedToilets: selectedToilets
    };

    onAddContract(newContract);

    // Update allocated toilets in memory
    if (selectedToilets.length > 0) {
      const updatedToilets = toilets.map(t => {
        if (selectedToilets.includes(t.code)) {
          return {
            ...t,
            allocation: 'Asignado' as const,
            assignedWork: workName.toUpperCase(),
            assignedClient: clientName
          };
        }
        return t;
      });
      onUpdateToilet(updatedToilets);
    }

    setIsAddingContract(false);

    // Reset fields
    setClientName('');
    setWorkName('');
    setStartDate(new Date().toISOString().split('T')[0]);
    setEndDate(new Date(Date.now() + 365*24*60*60*1000).toISOString().split('T')[0]);
    setMonthlyValue('');
    setTotalValue('');
    setAddress('');
    setObservations('');
    setSelectedToilets([]);
  };

  const handleToggleToiletSelection = (code: string) => {
    if (selectedToilets.includes(code)) {
      setSelectedToilets(selectedToilets.filter(c => c !== code));
    } else {
      setSelectedToilets([...selectedToilets, code]);
    }
  };

  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredContracts = contracts.filter(c => {
    const matchesTab = c.status === activeTab;
    const matchesSearch = 
      c.clientName.toLowerCase().includes(combinedSearch) ||
      c.workName.toLowerCase().includes(combinedSearch) ||
      c.address.toLowerCase().includes(combinedSearch);
    return matchesTab && matchesSearch;
  });

  return (
    <div className="space-y-6">
      
      {/* Run-rate Financial Header Card */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div className="p-5 rounded-3xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/10 flex items-center justify-between">
          <div className="space-y-1">
            <span className="text-[10px] font-mono font-bold uppercase tracking-wider text-indigo-100">Facturación Mensual Recurrente</span>
            <span className="font-sans font-extrabold text-2xl block">{formatCLP(monthlyRunRate)}</span>
            <span className="text-[10px] text-indigo-100 block">Suma de {activeContracts.length} contratos activos</span>
          </div>
          <TrendingUp className="w-8 h-8 text-indigo-100/50" />
        </div>

        <div className="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-between">
          <div className="space-y-1">
            <span className="text-[10px] font-mono text-slate-400 font-bold uppercase tracking-wider block">Total de Contratos</span>
            <span className="font-sans font-extrabold text-2xl text-slate-800 block">{contracts.length}</span>
            <span className="text-[10px] text-slate-500 block">{activeContracts.length} activos en Chiloé</span>
          </div>
          <Briefcase className="w-8 h-8 text-slate-200" />
        </div>

        <div className="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-between col-span-1 sm:col-span-2 lg:col-span-1">
          <div className="space-y-1">
            <span className="text-[10px] font-mono text-slate-400 font-bold uppercase block">Porcentaje de Activos</span>
            <span className="font-sans font-extrabold text-2xl text-slate-800 block">
              {Math.round((activeContracts.length / contracts.length) * 100)}%
            </span>
            <span className="text-[10px] text-slate-500 block">Alto índice de retención industrial</span>
          </div>
          <Check className="w-8 h-8 text-indigo-600 bg-indigo-50 p-2 rounded-full" />
        </div>
      </div>

      {/* Tabs and search controls */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div className="flex items-center space-x-2">
          <button
            onClick={() => setActiveTab('Activo')}
            className={`px-4 py-2 text-xs font-semibold rounded-xl font-sans transition-all duration-200 ${activeTab === 'Activo' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:text-slate-800'}`}
          >
            Contratos Activos ({contracts.filter(c => c.status === 'Activo').length})
          </button>
          <button
            onClick={() => setActiveTab('Terminado')}
            className={`px-4 py-2 text-xs font-semibold rounded-xl font-sans transition-all duration-200 ${activeTab === 'Terminado' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:text-slate-800'}`}
          >
            Terminados ({contracts.filter(c => c.status === 'Terminado').length})
          </button>
        </div>

        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <div className="relative">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Buscar por cliente, obra..."
              value={localSearch}
              onChange={(e) => setLocalSearch(e.target.value)}
              id="contratos-local-search"
              className="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
            />
          </div>

          <button
            onClick={() => setIsAddingContract(true)}
            className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans text-xs font-semibold flex items-center justify-center space-x-1.5 shadow-lg shadow-indigo-600/10 transition-all active:scale-95"
            id="new-contract-btn"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>Nuevo Contrato</span>
          </button>
        </div>
      </div>

      {/* Main Table */}
      <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-slate-50 bg-slate-50/50">
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">ID / Obra</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Cliente</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Fechas</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Valor Mensual</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Valor Total</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Ficha</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {filteredContracts.map((con) => (
                <tr 
                  key={con.id}
                  onClick={() => setSelectedContract(con)}
                  className="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                >
                  <td className="px-6 py-4.5">
                    <span className="font-sans font-bold text-slate-900 group-hover:text-indigo-600 transition-colors block text-sm">
                      {con.workName}
                    </span>
                    <span className="font-mono text-[10px] text-slate-400 block mt-0.5">Contrato: {con.id}</span>
                  </td>
                  <td className="px-6 py-4.5">
                    <span className="font-sans font-medium text-slate-600 text-xs block truncate max-w-[200px]">
                      {con.clientName}
                    </span>
                  </td>
                  <td className="px-6 py-4.5 space-y-0.5 font-mono text-[11px] text-slate-500">
                    <div className="flex items-center">
                      <Calendar className="w-3 h-3 text-slate-400 mr-1 shrink-0" />
                      <span>{con.startDate}</span>
                    </div>
                    <div className="flex items-center text-slate-400">
                      <ArrowRight className="w-3 h-3 text-slate-300 mr-1 shrink-0" />
                      <span>{con.endDate}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4.5 font-sans font-bold text-slate-800 text-xs">
                    {formatCLP(con.monthlyValue)}
                  </td>
                  <td className="px-6 py-4.5 font-sans font-semibold text-slate-500 text-xs">
                    {formatCLP(con.totalValue)}
                  </td>
                  <td className="px-6 py-4.5 text-right" onClick={(e) => e.stopPropagation()}>
                    <button
                      onClick={() => setSelectedContract(con)}
                      className="px-3 py-1 border border-slate-100 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50/50 transition-colors rounded-lg font-sans text-xs"
                    >
                      Ver Detalle
                    </button>
                  </td>
                </tr>
              ))}
              {filteredContracts.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                    No se encontraron contratos para el filtro seleccionado.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Slide Drawer: CONTRACT DETAIL VIEW */}
      {selectedContract && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setSelectedContract(null)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            {/* Header */}
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-700">
                  <FileText className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm truncate max-w-[200px]">{selectedContract.workName}</h3>
                  <span className="font-mono text-[10px] text-slate-400 block mt-0.5">Contrato ID: {selectedContract.id}</span>
                </div>
              </div>
              <button onClick={() => setSelectedContract(null)} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto p-6 space-y-6">
              {/* Client Info */}
              <div className="space-y-2">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Titular del Contrato</h4>
                <div className="flex items-center space-x-3 p-3 rounded-2xl border border-slate-100 bg-slate-50/50">
                  <User className="w-5 h-5 text-slate-400" />
                  <div>
                    <span className="font-sans font-bold text-xs text-slate-800 block truncate max-w-[200px]">{selectedContract.clientName}</span>
                    <span className="font-mono text-[10px] text-slate-400 block">Empresa Aliada</span>
                  </div>
                </div>
              </div>

              {/* Financial values */}
              <div className="space-y-3">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Montos Pactados</h4>
                <div className="grid grid-cols-2 gap-3">
                  <div className="p-3.5 rounded-2xl bg-indigo-50/20 border border-indigo-100/30">
                    <span className="font-mono text-[9px] text-indigo-600 font-bold block uppercase">Mensualidad</span>
                    <span className="font-sans font-extrabold text-sm text-slate-800 block mt-1">{formatCLP(selectedContract.monthlyValue)}</span>
                  </div>
                  <div className="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span className="font-mono text-[9px] text-slate-400 font-bold block uppercase">Valor Total Estimado</span>
                    <span className="font-sans font-extrabold text-sm text-slate-800 block mt-1">{formatCLP(selectedContract.totalValue)}</span>
                  </div>
                </div>
              </div>

              {/* Location with simulated Map widget (High Craftsmanship) */}
              <div className="space-y-3">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Ubicación de Faena</h4>
                <div className="flex items-start space-x-2 text-xs font-sans text-slate-600 leading-normal">
                  <MapPin className="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" />
                  <span>{selectedContract.address}</span>
                </div>
                {/* Visual Map Mockup */}
                <div className="h-28 rounded-2xl overflow-hidden relative border border-slate-100 shadow-inner flex items-center justify-center bg-slate-100/60">
                  <div className="absolute inset-0 opacity-20 bg-[radial-gradient(#6366f1_1px,transparent_1px)] [background-size:16px_16px]" />
                  <div className="absolute text-center space-y-1 z-10">
                    <Map className="w-5 h-5 text-indigo-600 mx-auto" />
                    <span className="font-mono text-[9px] font-bold text-slate-500 uppercase tracking-widest">Coordenadas Registradas GPS</span>
                  </div>
                  <div className="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur-md px-2 py-0.5 rounded text-[8px] text-white font-mono uppercase">Los Lagos, Chile</div>
                </div>
              </div>

              {/* Assigned cabines */}
              <div className="space-y-3">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Cabinas de Baño Asignadas</h4>
                {selectedContract.assignedToilets.length > 0 ? (
                  <div className="flex flex-wrap gap-2">
                    {selectedContract.assignedToilets.map((toiletCode) => (
                      <div key={toiletCode} className="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-xl font-mono text-xs font-extrabold">
                        <Bath className="w-3.5 h-3.5 text-indigo-600" />
                        <span>{toiletCode}</span>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-xs text-slate-400 font-sans italic">No registra cabinas asignadas en este momento.</p>
                )}
              </div>

              {/* Terms Observations */}
              <div className="space-y-2">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Términos Especiales</h4>
                <p className="text-xs font-sans text-slate-500 leading-relaxed italic">{selectedContract.observations}</p>
              </div>
            </div>

            <div className="p-4 border-t border-slate-100 bg-slate-50">
              <button 
                onClick={() => setSelectedContract(null)}
                className="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-sans text-xs font-semibold rounded-xl text-center"
              >
                Cerrar Detalle
              </button>
            </div>
          </div>
        </>
      )}

      {/* Slide Drawer: REGISTER NEW CONTRACT */}
      {isAddingContract && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setIsAddingContract(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                  <Briefcase className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Registrar Nuevo Contrato</h3>
                  <span className="text-[10px] font-sans text-slate-400 block mt-0.5">Establecer servicio sanitario para faenas.</span>
                </div>
              </div>
              <button onClick={() => setIsAddingContract(false)} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreateContract} className="flex-1 p-6 space-y-4 overflow-y-auto">
              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Cliente Titular <span className="text-rose-500">*</span></label>
                <select
                  value={clientName}
                  onChange={(e) => setClientName(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  required
                >
                  <option value="">Seleccione un cliente...</option>
                  {clients.map((c) => (
                    <option key={c.id} value={c.name}>{c.name} ({c.rut})</option>
                  ))}
                </select>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Nombre de Obra / Faena <span className="text-rose-500">*</span></label>
                <input
                  type="text"
                  placeholder="e.g. OBRA RUTA 5 - CHONCHI"
                  value={workName}
                  onChange={(e) => setWorkName(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Fecha Inicio</label>
                  <input
                    type="date"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                  />
                </div>
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Fecha Término</label>
                  <input
                    type="date"
                    value={endDate}
                    onChange={(e) => setEndDate(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Valor Mensual (CLP) <span className="text-rose-500">*</span></label>
                  <input
                    type="number"
                    placeholder="e.g. 125000"
                    value={monthlyValue}
                    onChange={(e) => setMonthlyValue(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                    required
                  />
                </div>
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Valor Total Estimado (CLP)</label>
                  <input
                    type="number"
                    placeholder="e.g. 1500000"
                    value={totalValue}
                    onChange={(e) => setTotalValue(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Dirección de la Faena</label>
                <input
                  type="text"
                  placeholder="e.g. Km 12 Camino a Rilán, Castro"
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all"
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Asignar Cabinas Disponibles</label>
                <div className="p-3 border border-slate-200 rounded-xl max-h-32 overflow-y-auto space-y-2 bg-slate-50/50">
                  {toilets
                    .filter(t => t.allocation === 'Disponible' && t.status === 'Activo')
                    .map((t) => {
                      const isChecked = selectedToilets.includes(t.code);
                      return (
                        <div 
                          key={t.code} 
                          onClick={() => handleToggleToiletSelection(t.code)}
                          className="flex items-center space-x-2 text-xs font-sans cursor-pointer select-none hover:bg-slate-100 px-2 py-1 rounded-md transition-colors"
                        >
                          <div className={`w-4.5 h-4.5 rounded border flex items-center justify-center ${isChecked ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 text-transparent bg-white'}`}>
                            <Check className="w-3 h-3" />
                          </div>
                          <span className="font-mono font-bold text-slate-700">{t.code}</span>
                          <span className="text-[10px] text-slate-400">({t.observations})</span>
                        </div>
                      );
                    })}
                  {toilets.filter(t => t.allocation === 'Disponible' && t.status === 'Activo').length === 0 && (
                    <p className="text-[11px] text-slate-400 font-sans italic text-center py-2">No hay cabinas disponibles en bodega.</p>
                  )}
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Condiciones / Observaciones</label>
                <textarea
                  placeholder="Mantenimiento los martes y sábados, incluir papel..."
                  value={observations}
                  onChange={(e) => setObservations(e.target.value)}
                  rows={3}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                />
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsAddingContract(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  id="submit-new-contract"
                  className="flex-1 py-2.5 bg-emerald-500 text-white rounded-xl text-xs font-semibold font-sans hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/10"
                >
                  Establecer Contrato
                </button>
              </div>
            </form>
          </div>
        </>
      )}

    </div>
  );
}
