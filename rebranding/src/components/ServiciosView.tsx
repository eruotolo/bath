import React, { useState } from 'react';
import { 
  Plus, 
  Search, 
  Wrench, 
  Calendar, 
  CheckSquare, 
  Square, 
  FileCheck, 
  X, 
  CheckCircle2, 
  Clipboard,
  SlidersHorizontal,
  Bath,
  ArrowRight
} from 'lucide-react';
import { ServiceFollowUp, Contract, ServiceType } from '../types';

interface ServiciosViewProps {
  services: ServiceFollowUp[];
  contracts: Contract[];
  onAddService: (newService: ServiceFollowUp) => void;
  searchTerm: string;
}

export default function ServiciosView({ services, contracts, onAddService, searchTerm }: ServiciosViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [isLoggingService, setIsLoggingService] = useState(false);
  const [filter, setFilter] = useState<'todos' | 'completados' | 'pendientes'>('todos');

  // Form states
  const [contractId, setContractId] = useState('');
  const [date, setDate] = useState(new Date().toISOString().split('T')[0]);
  const [selectedTypes, setSelectedTypes] = useState<ServiceType[]>([]);
  const [observations, setObservations] = useState('');

  const serviceCategories: ServiceType[] = [
    'Instalación',
    'Reparación',
    'Limpieza',
    'Desinfección',
    'Sanitización',
    'Entrega Papel Higiénico',
    'Entrega de Jabón Líquido',
    'Retiro de Baños',
    'Otros'
  ];

  const handleToggleType = (type: ServiceType) => {
    if (selectedTypes.includes(type)) {
      setSelectedTypes(selectedTypes.filter(t => t !== type));
    } else {
      setSelectedTypes([...selectedTypes, type]);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!contractId || selectedTypes.length === 0) {
      alert('Por favor seleccione un contrato y al menos un tipo de servicio realizado.');
      return;
    }

    const targetContract = contracts.find(c => c.id === contractId);
    if (!targetContract) return;

    const newService: ServiceFollowUp = {
      id: `S-${Math.floor(100000 + Math.random() * 900000)}`,
      clientName: targetContract.clientName,
      workName: targetContract.workName,
      isInvoiced: false,
      date,
      types: selectedTypes,
      observations: observations || 'Servicio realizado con éxito.',
      status: 'Completado'
    };

    onAddService(newService);
    setIsLoggingService(false);

    // Reset
    setContractId('');
    setDate(new Date().toISOString().split('T')[0]);
    setSelectedTypes([]);
    setObservations('');
  };

  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredServices = services.filter(s => {
    const matchesSearch = 
      s.clientName.toLowerCase().includes(combinedSearch) ||
      s.workName.toLowerCase().includes(combinedSearch) ||
      s.id.toLowerCase().includes(combinedSearch) ||
      s.types.some(t => t.toLowerCase().includes(combinedSearch));

    if (filter === 'completados') return matchesSearch && s.status === 'Completado';
    if (filter === 'pendientes') return matchesSearch && s.status === 'Pendiente';
    return matchesSearch;
  });

  return (
    <div className="space-y-6">
      
      {/* Top filter banner */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div className="flex items-center space-x-3 flex-1 max-w-lg">
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Buscar por obra o servicio..."
              value={localSearch}
              onChange={(e) => setLocalSearch(e.target.value)}
              id="servicios-local-search"
              className="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
            />
          </div>

          <div className="flex bg-slate-100 p-1 rounded-xl">
            <button
              onClick={() => setFilter('todos')}
              className={`px-3 py-1 text-xs font-semibold rounded-lg font-sans transition-all ${filter === 'todos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
            >
              Todos
            </button>
            <button
              onClick={() => setFilter('completados')}
              className={`px-3 py-1 text-xs font-semibold rounded-lg font-sans transition-all ${filter === 'completados' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`}
            >
              Completados
            </button>
          </div>
        </div>

        <button
          onClick={() => setIsLoggingService(true)}
          className="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans text-xs font-semibold flex items-center justify-center space-x-1.5 shadow-lg shadow-indigo-600/10 transition-all active:scale-95"
          id="log-service-btn"
        >
          <Plus className="w-3.5 h-3.5" />
          <span>Registrar Visita de Ruta</span>
        </button>
      </div>

      {/* Grid of completed tasks */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        {filteredServices.map((service) => (
          <div 
            key={service.id}
            className="p-5 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden"
          >
            {/* Stamp visual */}
            <div className="absolute top-4 right-4 flex items-center space-x-1.5 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-xl text-indigo-700 font-medium font-sans text-[10px] uppercase">
              <CheckCircle2 className="w-3.5 h-3.5 text-indigo-600" />
              <span>Completado</span>
            </div>

            <div className="space-y-0.5">
              <span className="font-mono text-[10px] text-slate-400 block font-semibold">CÓDIGO VISITA: {service.id}</span>
              <h3 className="font-sans font-bold text-slate-900 text-md truncate pr-20">{service.workName}</h3>
              <p className="font-sans text-xs text-slate-500 truncate max-w-xs">{service.clientName}</p>
            </div>

            <div className="flex items-center space-x-1.5 font-mono text-[11px] text-slate-400 mt-2.5">
              <Calendar className="w-3.5 h-3.5" />
              <span>Fecha visita: {service.date}</span>
            </div>

            {/* Checklist tags */}
            <div className="flex flex-wrap gap-1.5 mt-4">
              {service.types.map((type) => (
                <span 
                  key={type}
                  className="px-2.5 py-1 bg-slate-50 border border-slate-100 text-slate-600 rounded-lg text-[10px] font-semibold font-sans uppercase tracking-wider"
                >
                  {type}
                </span>
              ))}
            </div>

            {/* Observations area */}
            <div className="mt-4 p-3 bg-slate-50/50 rounded-2xl border border-slate-50 text-[11px] font-sans text-slate-500 leading-normal italic">
              <strong>Bitácora:</strong> "{service.observations || 'Sin observaciones especiales.'}"
            </div>

            {/* Footer billing badge info */}
            <div className="mt-4 pt-3 border-t border-slate-50 flex items-center justify-between text-[11px] font-sans">
              <span className="text-slate-400">Estado Facturación</span>
              {service.isInvoiced ? (
                <span className="font-bold text-indigo-600">Facturado en {service.invoiceNumber}</span>
              ) : (
                <span className="text-amber-600 font-semibold uppercase font-mono text-[10px]">Pendiente de Cobro</span>
              )}
            </div>
          </div>
        ))}
        {filteredServices.length === 0 && (
          <div className="col-span-2 p-10 bg-white rounded-3xl border border-dashed border-slate-200 text-center text-slate-400 font-sans text-sm">
            No se registran visitas que coincidan con los filtros.
          </div>
        )}
      </div>

      {/* Slide Drawer: LOG ROUTE VISIT (Checklist based) */}
      {isLoggingService && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setIsLoggingService(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                  <Clipboard className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Registrar Bitácora de Visita</h3>
                  <span className="text-[10px] font-sans text-slate-400 block mt-0.5">Ingresar la planilla de servicios ejecutados.</span>
                </div>
              </div>
              <button onClick={() => setIsLoggingService(false)} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="flex-1 p-6 space-y-5 overflow-y-auto">
              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Obra / Faena Sanitaria <span className="text-rose-500">*</span></label>
                <select
                  value={contractId}
                  onChange={(e) => setContractId(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                  required
                >
                  <option value="">Seleccione obra para registrar mantenimiento...</option>
                  {contracts
                    .filter(c => c.status === 'Activo')
                    .map((con) => (
                      <option key={con.id} value={con.id}>{con.workName} ({con.clientName})</option>
                    ))}
                </select>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Fecha del Servicio</label>
                <input
                  type="date"
                  value={date}
                  onChange={(e) => setDate(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                />
              </div>

              {/* Task Checklist (Advanced Grid selection UX instead of dull fields) */}
              <div className="space-y-2">
                <label className="font-sans text-xs font-bold text-slate-600 block">Tareas Sanitarias Completadas <span className="text-rose-500">*</span></label>
                <div className="grid grid-cols-1 gap-2 p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                  {serviceCategories.map((type) => {
                    const isChecked = selectedTypes.includes(type);
                    return (
                      <div 
                        key={type}
                        onClick={() => handleToggleType(type)}
                        className="flex items-center space-x-2.5 text-xs font-sans cursor-pointer select-none hover:bg-slate-100 p-1.5 rounded-lg transition-colors"
                      >
                        <div className={`w-4.5 h-4.5 rounded border flex items-center justify-center ${isChecked ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-300 text-transparent bg-white'}`}>
                          <CheckSquare className="w-3.5 h-3.5" />
                        </div>
                        <span className="text-slate-700 font-medium">{type}</span>
                      </div>
                    );
                  })}
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Observaciones de la Ruta / Estado de Cabinas</label>
                <textarea
                  placeholder="Cabinas limpiadas con amonio cuaternario, provisión de insumos completa."
                  value={observations}
                  onChange={(e) => setObservations(e.target.value)}
                  rows={3}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                />
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsLoggingService(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  id="submit-new-service"
                  className="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-semibold font-sans hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/10"
                >
                  Confirmar Servicio
                </button>
              </div>
            </form>
          </div>
        </>
      )}

    </div>
  );
}
