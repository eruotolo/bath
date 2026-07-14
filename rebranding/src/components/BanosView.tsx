import React, { useState } from 'react';
import { 
  Plus, 
  Search, 
  Bath, 
  CheckCircle2, 
  AlertTriangle, 
  Wrench, 
  MapPin, 
  Calendar, 
  SlidersHorizontal, 
  Grid, 
  List,
  X,
  FileSpreadsheet
} from 'lucide-react';
import { Toilet, Client, Contract } from '../types';

interface BanosViewProps {
  toilets: Toilet[];
  clients: Client[];
  contracts: Contract[];
  onAddToilet: (newToilet: Toilet) => void;
  onUpdateToilet: (updatedToilets: Toilet[]) => void;
  searchTerm: string;
}

export default function BanosView({ toilets, clients, contracts, onAddToilet, onUpdateToilet, searchTerm }: BanosViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [filter, setFilter] = useState<'todos' | 'disponible' | 'asignado' | 'inactivo'>('todos');
  const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');
  const [isAddingToilet, setIsAddingToilet] = useState(false);

  // Assignment Modal State
  const [assigningToilet, setAssigningToilet] = useState<Toilet | null>(null);
  const [selectedContractId, setSelectedContractId] = useState('');

  // Form states
  const [code, setCode] = useState('');
  const [purchaseDate, setPurchaseDate] = useState(new Date().toISOString().split('T')[0]);
  const [observations, setObservations] = useState('');
  const [status, setStatus] = useState<'Activo' | 'Inactivo'>('Activo');

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!code) {
      alert('Por favor complete el campo Código.');
      return;
    }

    if (toilets.some(t => t.code.toUpperCase() === code.toUpperCase())) {
      alert('Ya existe un baño con este código.');
      return;
    }

    const newToilet: Toilet = {
      code: code.toUpperCase(),
      purchaseDate,
      observations: observations || 'SO (Sin observaciones)',
      status,
      allocation: 'Disponible'
    };

    onAddToilet(newToilet);
    setIsAddingToilet(false);

    // Reset
    setCode('');
    setPurchaseDate(new Date().toISOString().split('T')[0]);
    setObservations('');
    setStatus('Activo');
  };

  const handleAssignSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!assigningToilet || !selectedContractId) return;

    const targetContract = contracts.find(c => c.id === selectedContractId);
    if (!targetContract) return;

    const updatedToilets = toilets.map(t => {
      if (t.code === assigningToilet.code) {
        return {
          ...t,
          allocation: 'Asignado' as const,
          assignedWork: targetContract.workName,
          assignedClient: targetContract.clientName
        };
      }
      return t;
    });

    onUpdateToilet(updatedToilets);
    setAssigningToilet(null);
    setSelectedContractId('');
  };

  const handleDeallocate = (code: string) => {
    if (!confirm(`¿Está seguro de retirar este baño (${code}) de su obra actual? Volverá a estar "Disponible".`)) return;

    const updatedToilets = toilets.map(t => {
      if (t.code === code) {
        return {
          ...t,
          allocation: 'Disponible' as const,
          assignedWork: undefined,
          assignedClient: undefined
        };
      }
      return t;
    });

    onUpdateToilet(updatedToilets);
  };

  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredToilets = toilets.filter(t => {
    // Search filter
    const matchesSearch = t.code.toLowerCase().includes(combinedSearch) ||
      (t.assignedWork || '').toLowerCase().includes(combinedSearch) ||
      (t.assignedClient || '').toLowerCase().includes(combinedSearch);

    // Pill tab filter
    if (filter === 'disponible') return matchesSearch && t.allocation === 'Disponible' && t.status === 'Activo';
    if (filter === 'asignado') return matchesSearch && t.allocation === 'Asignado' && t.status === 'Activo';
    if (filter === 'inactivo') return matchesSearch && t.status === 'Inactivo';
    return matchesSearch;
  });

  return (
    <div className="space-y-6">
      
      {/* Search and control banner */}
      <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        {/* Left: Search + Pill toggles */}
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
          <div className="relative max-w-xs">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Código o Faena..."
              value={localSearch}
              onChange={(e) => setLocalSearch(e.target.value)}
              id="baños-local-search"
              className="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-emerald-500 transition-colors font-sans"
            />
          </div>

          <div className="flex items-center bg-slate-100 p-1 rounded-xl gap-0.5">
            {[
              { id: 'todos', label: 'Todos' },
              { id: 'disponible', label: 'Disponibles' },
              { id: 'asignado', label: 'Asignados' },
              { id: 'inactivo', label: 'Mantención' },
            ].map((p) => (
              <button
                key={p.id}
                onClick={() => setFilter(p.id as any)}
                className={`
                  px-3 py-1.5 text-xs font-semibold rounded-lg font-sans transition-all duration-200
                  ${filter === p.id 
                    ? 'bg-white text-slate-900 shadow-sm' 
                    : 'text-slate-500 hover:text-slate-800'}
                `}
              >
                {p.label}
              </button>
            ))}
          </div>
        </div>

        {/* Right: Actions */}
        <div className="flex items-center space-x-3 shrink-0">
          <div className="bg-slate-100 p-1 rounded-xl flex items-center space-x-0.5">
            <button
              onClick={() => setViewMode('grid')}
              className={`p-1.5 rounded-lg transition-all ${viewMode === 'grid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
              title="Vista de Cabinas"
            >
              <Grid className="w-4 h-4" />
            </button>
            <button
              onClick={() => setViewMode('table')}
              className={`p-1.5 rounded-lg transition-all ${viewMode === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
              title="Vista de Tabla"
            >
              <List className="w-4 h-4" />
            </button>
          </div>

          <button
            onClick={() => setIsAddingToilet(true)}
            className="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-emerald-500/10 transition-all active:scale-95"
            id="new-toilet-btn"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>Registrar Baño</span>
          </button>
        </div>
      </div>

      {/* Grid Mode visualization */}
      {viewMode === 'grid' ? (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          {filteredToilets.map((toilet) => {
            const isInactive = toilet.status === 'Inactivo';
            const isAssigned = toilet.allocation === 'Asignado';

            return (
              <div 
                key={toilet.code}
                className={`
                  p-5 rounded-3xl border transition-all duration-300 relative overflow-hidden group flex flex-col justify-between h-48 bg-white
                  ${isInactive 
                    ? 'border-amber-100 shadow-sm shadow-amber-500/5 hover:border-amber-200' 
                    : isAssigned 
                      ? 'border-blue-100 shadow-sm shadow-blue-500/5 hover:border-blue-200' 
                      : 'border-emerald-100 shadow-sm shadow-emerald-500/5 hover:border-emerald-200'}
                `}
              >
                {/* Visual Accent */}
                <div className={`absolute top-0 left-0 right-0 h-1.5 ${isInactive ? 'bg-amber-400' : isAssigned ? 'bg-blue-500' : 'bg-emerald-400'}`} />

                {/* Top Code Section */}
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <Bath className={`w-4 h-4 ${isInactive ? 'text-amber-500' : isAssigned ? 'text-blue-500' : 'text-emerald-500'}`} />
                    <span className="font-mono font-extrabold text-sm text-slate-800">{toilet.code}</span>
                  </div>
                  <span className={`w-2.5 h-2.5 rounded-full ${isInactive ? 'bg-amber-400 animate-pulse' : isAssigned ? 'bg-blue-500' : 'bg-emerald-400'}`} />
                </div>

                {/* Body metadata */}
                <div className="space-y-1 my-3">
                  {isAssigned ? (
                    <div className="space-y-0.5">
                      <span className="text-[10px] font-mono font-bold text-blue-600 block uppercase">Asignado</span>
                      <p className="text-xs font-sans font-bold text-slate-800 truncate max-w-[150px]">{toilet.assignedWork}</p>
                      <p className="text-[9px] font-sans text-slate-400 truncate max-w-[150px]">{toilet.assignedClient}</p>
                    </div>
                  ) : isInactive ? (
                    <div className="space-y-0.5">
                      <span className="text-[10px] font-mono font-bold text-amber-600 block uppercase">Mantención</span>
                      <p className="text-[11px] font-sans text-slate-500 italic truncate max-w-[150px]">{toilet.observations}</p>
                    </div>
                  ) : (
                    <div className="space-y-0.5">
                      <span className="text-[10px] font-mono font-bold text-emerald-600 block uppercase">En Bodega</span>
                      <p className="text-[11px] font-sans text-slate-500 font-medium">Disponible para obra</p>
                    </div>
                  )}
                </div>

                {/* Footer contextual actions */}
                <div className="pt-2 border-t border-slate-50 flex items-center justify-between text-[10px] font-sans">
                  <span className="text-slate-400 font-mono">C: {toilet.purchaseDate.slice(2, 7)}</span>
                  {isAssigned ? (
                    <button 
                      onClick={() => handleDeallocate(toilet.code)}
                      className="text-slate-400 hover:text-rose-600 font-bold hover:underline transition-colors"
                    >
                      Retirar baño
                    </button>
                  ) : !isInactive ? (
                    <button 
                      onClick={() => setAssigningToilet(toilet)}
                      className="text-emerald-600 hover:text-emerald-700 font-bold hover:underline transition-colors"
                    >
                      Asignar a obra
                    </button>
                  ) : (
                    <span className="text-amber-600 font-medium font-sans">Mecánico</span>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      ) : (
        /* Traditional clean list view */
        <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="border-b border-slate-50 bg-slate-50/50">
                  <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Código</th>
                  <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">F. Adquisición</th>
                  <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Ubicación Actual</th>
                  <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Observaciones</th>
                  <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Estado Técnico</th>
                  <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acción</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50">
                {filteredToilets.map((toilet) => (
                  <tr key={toilet.code} className="hover:bg-slate-50/50 transition-colors">
                    <td className="px-6 py-4 font-mono font-extrabold text-slate-800 text-sm">{toilet.code}</td>
                    <td className="px-6 py-4 font-mono text-xs text-slate-500">{toilet.purchaseDate}</td>
                    <td className="px-6 py-4">
                      {toilet.allocation === 'Asignado' ? (
                        <div className="space-y-0.5">
                          <span className="font-sans font-bold text-xs text-slate-800 block">{toilet.assignedWork}</span>
                          <span className="font-sans text-[10px] text-slate-400 block">{toilet.assignedClient}</span>
                        </div>
                      ) : (
                        <span className="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-bold uppercase font-sans">Bodega Central</span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-xs font-sans text-slate-500 italic max-w-xs truncate">{toilet.observations}</td>
                    <td className="px-6 py-4">
                      <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold font-sans tracking-wide uppercase ${toilet.status === 'Activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>
                        {toilet.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      {toilet.allocation === 'Asignado' ? (
                        <button
                          onClick={() => handleDeallocate(toilet.code)}
                          className="px-3 py-1 bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors text-xs font-semibold rounded-lg font-sans"
                        >
                          Retirar
                        </button>
                      ) : toilet.status === 'Activo' ? (
                        <button
                          onClick={() => setAssigningToilet(toilet)}
                          className="px-3 py-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors text-xs font-semibold rounded-lg font-sans"
                        >
                          Asignar
                        </button>
                      ) : (
                        <span className="text-xs font-medium text-slate-400 italic">No asignable</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Slide Drawer: REGISTER NEW TOILET */}
      {isAddingToilet && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setIsAddingToilet(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center">
                  <Bath className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Registrar Nueva Cabina</h3>
                  <span className="text-[10px] font-sans text-slate-400 block mt-0.5">Ingresar al inventario de Blanco Servicios.</span>
                </div>
              </div>
              <button onClick={() => setIsAddingToilet(false)} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="flex-1 p-6 space-y-5 overflow-y-auto">
              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Código Único de Cabina <span className="text-rose-500">*</span></label>
                <input
                  type="text"
                  placeholder="e.g. AT096"
                  value={code}
                  onChange={(e) => setCode(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                  required
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Fecha de Compra / Ingreso</label>
                <input
                  type="date"
                  value={purchaseDate}
                  onChange={(e) => setPurchaseDate(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Estado Inicial</label>
                <select
                  value={status}
                  onChange={(e) => setStatus(e.target.value as any)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all"
                >
                  <option value="Activo">Activo (Disponible para faenas)</option>
                  <option value="Inactivo">Inactivo (En reparación/mantención)</option>
                </select>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Observaciones Técnicas</label>
                <textarea
                  placeholder="e.g. Cabina estándar reforzada, SO"
                  value={observations}
                  onChange={(e) => setObservations(e.target.value)}
                  rows={4}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                />
              </div>

              <div className="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsAddingToilet(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-emerald-500 text-white rounded-xl text-xs font-semibold font-sans hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/10"
                >
                  Guardar en Inventario
                </button>
              </div>
            </form>
          </div>
        </>
      )}

      {/* Dialog: ASSIGN TOILET TO WORK */}
      {assigningToilet && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onClick={() => setAssigningToilet(null)} />
          <div className="relative bg-white rounded-3xl border border-slate-100 shadow-2xl w-full max-w-md p-6 overflow-hidden animate-in zoom-in-95 duration-200 space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <SlidersHorizontal className="w-5 h-5 text-emerald-500" />
                <h3 className="font-sans font-bold text-slate-900 text-md">Asignar Cabina {assigningToilet.code}</h3>
              </div>
              <button onClick={() => setAssigningToilet(null)} className="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <X className="w-5 h-5" />
              </button>
            </div>

            <p className="text-xs text-slate-500 font-sans leading-normal">
              Seleccione una obra/contrato activo de Blanco Servicios para asignar esta cabina de forma inmediata.
            </p>

            <form onSubmit={handleAssignSubmit} className="space-y-4">
              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Contrato de Obra Destino</label>
                <select
                  value={selectedContractId}
                  onChange={(e) => setSelectedContractId(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  required
                >
                  <option value="">Seleccione obra activa...</option>
                  {contracts
                    .filter(c => c.status === 'Activo')
                    .map((con) => (
                      <option key={con.id} value={con.id}>
                        {con.workName} ({con.clientName})
                      </option>
                    ))}
                </select>
              </div>

              <div className="pt-4 flex items-center space-x-3 border-t border-slate-50">
                <button
                  type="button"
                  onClick={() => setAssigningToilet(null)}
                  className="flex-1 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="flex-1 py-2 bg-emerald-500 text-white hover:bg-emerald-600 rounded-xl text-xs font-semibold font-sans shadow-lg shadow-emerald-500/10"
                >
                  Confirmar Asignación
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
