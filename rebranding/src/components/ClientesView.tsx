import React, { useState } from 'react';
import { 
  Plus, 
  Search, 
  Phone, 
  Mail, 
  MapPin, 
  Eye, 
  MoreVertical, 
  X, 
  UserPlus, 
  FileText, 
  Bath, 
  ArrowRight,
  Check,
  Building
} from 'lucide-react';
import { Client, Contract, Toilet } from '../types';
import { CHILE_REGIONES, CHILE_COMUNAS_LOS_LAGOS } from '../data';

interface ClientesViewProps {
  clients: Client[];
  contracts: Contract[];
  toilets: Toilet[];
  onAddClient: (newClient: Client) => void;
  searchTerm: string;
}

export default function ClientesView({ clients, contracts, toilets, onAddClient, searchTerm }: ClientesViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [selectedClient, setSelectedClient] = useState<Client | null>(null);
  const [isAddingClient, setIsAddingClient] = useState(false);

  // Form states for creating a new client
  const [rut, setRut] = useState('');
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [address, setAddress] = useState('');
  const [region, setRegion] = useState('Región de Los Lagos');
  const [city, setCity] = useState('Castro');
  const [comuna, setComuna] = useState('Castro');

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!rut || !name || !email) {
      alert('Por favor complete los campos obligatorios (RUT, Nombre, Email).');
      return;
    }

    const newClient: Client = {
      id: `C-${Date.now().toString().slice(-3)}`,
      rut,
      name: name.toUpperCase(),
      phone: phone || '+56 9 ',
      email,
      address: address || 'Dirección no especificada',
      region,
      city,
      comuna,
      status: 'Activo',
      createdAt: new Date().toISOString().split('T')[0]
    };

    onAddClient(newClient);
    setIsAddingClient(false);
    
    // Reset form states
    setRut('');
    setName('');
    setPhone('');
    setEmail('');
    setAddress('');
    setRegion('Región de Los Lagos');
    setCity('Castro');
    setComuna('Castro');
  };

  // Combine parent global search with local module-level search
  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredClients = clients.filter(c => 
    c.name.toLowerCase().includes(combinedSearch) ||
    c.rut.toLowerCase().includes(combinedSearch) ||
    c.email.toLowerCase().includes(combinedSearch) ||
    c.comuna.toLowerCase().includes(combinedSearch)
  );

  // Gather specific details for the active detail drawer
  const clientContracts = selectedClient 
    ? contracts.filter(con => con.clientName.toLowerCase() === selectedClient.name.toLowerCase())
    : [];

  const clientToilets = selectedClient
    ? toilets.filter(t => t.assignedClient?.toLowerCase() === selectedClient.name.toLowerCase())
    : [];

  return (
    <div className="relative">
      {/* Top action header */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-6">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Buscar por RUT, Nombre, Comuna..."
            value={localSearch}
            onChange={(e) => setLocalSearch(e.target.value)}
            id="clientes-local-search"
            className="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
          />
        </div>

        <button
          onClick={() => setIsAddingClient(true)}
          className="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-sans text-xs font-semibold flex items-center justify-center space-x-2 shadow-lg shadow-indigo-600/15 active:scale-95 transition-all"
          id="add-client-btn"
        >
          <Plus className="w-4 h-4" />
          <span>Agregar Nuevo Cliente</span>
        </button>
      </div>

      {/* Main clients grid table */}
      <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-slate-50 bg-slate-50/50">
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">RUT</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Nombre Cliente</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Contacto</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Ubicación</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Estado</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {filteredClients.map((client) => (
                <tr 
                  key={client.id}
                  className="hover:bg-slate-50/75 transition-colors group cursor-pointer"
                  onClick={() => setSelectedClient(client)}
                >
                  <td className="px-6 py-4.5 font-mono text-xs text-slate-500 font-semibold">{client.rut}</td>
                  <td className="px-6 py-4.5">
                    <span className="font-sans font-bold text-slate-900 group-hover:text-indigo-600 transition-colors block text-sm">
                      {client.name}
                    </span>
                    <span className="font-sans text-[11px] text-slate-400 block mt-0.5">ID: {client.id}</span>
                  </td>
                  <td className="px-6 py-4.5 space-y-1">
                    <div className="flex items-center text-xs text-slate-600 font-sans">
                      <Phone className="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0" />
                      <span>{client.phone}</span>
                    </div>
                    <div className="flex items-center text-[11px] text-slate-500 font-sans">
                      <Mail className="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0" />
                      <span className="truncate max-w-[180px]">{client.email}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4.5">
                    <div className="flex items-center text-xs text-slate-600 font-sans">
                      <MapPin className="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0" />
                      <span>{client.comuna}</span>
                    </div>
                    <span className="text-[11px] text-slate-400 block mt-0.5 ml-5 truncate max-w-[150px]">{client.city}</span>
                  </td>
                  <td className="px-6 py-4.5">
                    <span className={`px-2.5 py-1 rounded-full text-[10px] font-semibold font-sans tracking-wide uppercase ${client.status === 'Activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-600'}`}>
                      {client.status}
                    </span>
                  </td>
                  <td className="px-6 py-4.5 text-right" onClick={(e) => e.stopPropagation()}>
                    <button
                      onClick={() => setSelectedClient(client)}
                      className="p-1.5 rounded-lg border border-slate-100 text-slate-500 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50/40 transition-all inline-flex items-center justify-center"
                      title="Ver Ficha Cliente"
                    >
                      <Eye className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
              {filteredClients.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                    No se encontraron clientes que coincidan con la búsqueda.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
        <div className="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
          <span className="font-mono text-[10px] text-slate-400 font-bold uppercase">Mostrando {filteredClients.length} de {clients.length} Clientes</span>
          <div className="flex items-center space-x-1">
            <button className="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed">Anterior</button>
            <button className="px-3 py-1 rounded-lg border border-slate-100 bg-indigo-600 text-xs font-semibold text-white">1</button>
            <button className="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed">Siguiente</button>
          </div>
        </div>
      </div>

      {/* Slide-over: CLIENT DETAILS DRAWER */}
      {selectedClient && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onClick={() => setSelectedClient(null)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            {/* Header */}
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-700">
                  <Building className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm truncate max-w-[200px]">{selectedClient.name}</h3>
                  <span className="font-mono text-[10px] text-slate-400 block mt-0.5">RUT: {selectedClient.rut}</span>
                </div>
              </div>
              <button 
                onClick={() => setSelectedClient(null)}
                className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Scrollable Content */}
            <div className="flex-1 overflow-y-auto p-6 space-y-7">
              {/* Personal Card details */}
              <div className="space-y-4">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Información General</h4>
                <div className="grid grid-cols-1 gap-y-3.5 text-xs">
                  <div>
                    <span className="text-slate-400 block font-sans">Teléfono</span>
                    <span className="font-sans font-bold text-slate-800">{selectedClient.phone}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block font-sans">Email</span>
                    <span className="font-sans font-bold text-slate-800">{selectedClient.email}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block font-sans">Dirección</span>
                    <span className="font-sans font-bold text-slate-800">{selectedClient.address}</span>
                  </div>
                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <span className="text-slate-400 block font-sans">Comuna</span>
                      <span className="font-sans font-bold text-slate-800">{selectedClient.comuna}</span>
                    </div>
                    <div>
                      <span className="text-slate-400 block font-sans">Ciudad</span>
                      <span className="font-sans font-bold text-slate-800">{selectedClient.city}</span>
                    </div>
                  </div>
                  <div>
                    <span className="text-slate-400 block font-sans">Región</span>
                    <span className="font-sans font-bold text-slate-800">{selectedClient.region}</span>
                  </div>
                </div>
              </div>

              {/* Active Contracts Summary */}
              <div className="space-y-3">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Contratos Activos</h4>
                {clientContracts.length > 0 ? (
                  <div className="space-y-2">
                    {clientContracts.map((con) => (
                      <div key={con.id} className="p-3.5 rounded-xl border border-slate-100 bg-slate-50/30 flex items-center justify-between text-xs font-sans hover:border-indigo-100 hover:bg-indigo-50/10 transition-colors">
                        <div>
                          <span className="font-bold text-slate-800 block truncate max-w-[200px]">{con.workName}</span>
                          <span className="text-[10px] font-mono text-slate-400 block mt-0.5">ID: {con.id}</span>
                        </div>
                        <div className="text-right">
                          <span className="font-bold text-slate-800 block">{new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(con.monthlyValue)} / mes</span>
                          <span className="text-[10px] font-mono text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded font-bold uppercase mt-1 inline-block">Activo</span>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-xs text-slate-400 font-sans italic">No registra contratos activos en este momento.</p>
                )}
              </div>

              {/* Bathrooms deployed */}
              <div className="space-y-3">
                <h4 className="font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase border-b border-slate-50 pb-1.5">Baños Químicos en Terreno</h4>
                {clientToilets.length > 0 ? (
                  <div className="grid grid-cols-2 gap-2">
                    {clientToilets.map((t) => (
                      <div key={t.code} className="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between font-sans">
                        <div className="flex items-center space-x-2">
                          <Bath className="w-4 h-4 text-indigo-600 shrink-0" />
                          <div>
                            <span className="text-xs font-bold text-slate-800 block">{t.code}</span>
                            <span className="text-[9px] font-mono text-slate-400 block">Cabina Activa</span>
                          </div>
                        </div>
                        <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-xs text-slate-400 font-sans italic">No registra cabinas de baños asignadas.</p>
                )}
              </div>
            </div>

            {/* Footer actions */}
            <div className="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
              <button 
                onClick={() => setSelectedClient(null)}
                className="w-full py-2.5 bg-slate-900 text-white rounded-xl font-sans text-xs font-semibold hover:bg-slate-800 transition-colors text-center block"
              >
                Cerrar Expediente
              </button>
            </div>
          </div>
        </>
      )}

      {/* Slide-over: ADD CLIENT FORM DRAWER */}
      {isAddingClient && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onClick={() => setIsAddingClient(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            {/* Header */}
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/10">
                  <UserPlus className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Registro de Nuevo Cliente</h3>
                  <span className="font-sans text-[10px] text-slate-400 block mt-0.5">Ingresar los datos en los campos obligatorios.</span>
                </div>
              </div>
              <button 
                onClick={() => setIsAddingClient(false)}
                className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Form Fields */}
            <form onSubmit={handleCreate} className="flex-1 overflow-y-auto p-6 space-y-5">
              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">RUT del Cliente <span className="text-rose-500">*</span></label>
                <input
                  type="text"
                  placeholder="e.g. 77.123.456-7"
                  value={rut}
                  onChange={(e) => setRut(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                  required
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Nombre Completo / Razón Social <span className="text-rose-500">*</span></label>
                <input
                  type="text"
                  placeholder="e.g. CONSTRUCTORA PUERTO OCTAY LTDA"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Teléfono de Contacto</label>
                  <input
                    type="tel"
                    placeholder="e.g. +56 9 9876 5432"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  />
                </div>
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Correo Electrónico <span className="text-rose-500">*</span></label>
                  <input
                    type="email"
                    placeholder="e.g. contacto@empresa.cl"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                    required
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Dirección Particular / Casa Matriz</label>
                <input
                  type="text"
                  placeholder="e.g. Sector Chulchuy Ruta W-85"
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Región</label>
                <select
                  value={region}
                  onChange={(e) => setRegion(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                >
                  {CHILE_REGIONES.map((r) => (
                    <option key={r} value={r}>{r}</option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Ciudad</label>
                  <input
                    type="text"
                    placeholder="e.g. Castro"
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  />
                </div>
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Comuna (Los Lagos)</label>
                  <select
                    value={comuna}
                    onChange={(e) => setComuna(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                  >
                    {CHILE_COMUNAS_LOS_LAGOS.map((com) => (
                      <option key={com} value={com}>{com}</option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Actions Footer */}
              <div className="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsAddingClient(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  id="submit-new-client"
                  className="flex-1 py-2.5 bg-emerald-500 text-white rounded-xl text-xs font-semibold font-sans hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/10"
                >
                  Registrar Cliente
                </button>
              </div>
            </form>
          </div>
        </>
      )}

    </div>
  );
}
