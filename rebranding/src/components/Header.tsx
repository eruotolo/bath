import React, { useState } from 'react';
import { Menu, Bell, Search, ShieldAlert, Sparkles, Clock, Globe } from 'lucide-react';
import { ViewType, User } from '../types';

interface HeaderProps {
  currentView: ViewType;
  sidebarOpen: boolean;
  setSidebarOpen: (open: boolean) => void;
  currentUser: User;
  onSearch: (term: string) => void;
}

export default function Header({ currentView, sidebarOpen, setSidebarOpen, currentUser, onSearch }: HeaderProps) {
  const [searchTerm, setSearchTerm] = useState('');
  const [showNotifications, setShowNotifications] = useState(false);

  const viewTitles: Record<ViewType, string> = {
    tablero: 'Tablero Analítico',
    clientes: 'Directorio de Clientes',
    baños: 'Inventario de Baños Químicos',
    contratos: 'Gestión de Obras & Contratos',
    seguimientos: 'Servicios en Terreno & Ruta',
    facturas: 'Control de Facturación',
    certificados: 'Certificados de Disposición m³',
    usuarios: 'Personal de Operaciones',
  };

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchTerm(e.target.value);
    onSearch(e.target.value);
  };

  const mockNotifications = [
    { id: 1, text: 'Baño AT055 asignado a Curaco de Vélez', time: 'Hace 10 min', type: 'info' },
    { id: 2, text: 'Factura #1893 está vencida', time: 'Hace 1 hora', type: 'warning' },
    { id: 3, text: 'Certificado CRT-06072026A3 firmado digitalmente', time: 'Hace 3 horas', type: 'success' },
  ];

  return (
    <header className="h-20 border-b border-slate-100 bg-white px-6 flex items-center justify-between sticky top-0 z-30 shadow-sm shadow-slate-100/50">
      {/* Title & Mobile Hamburger */}
      <div className="flex items-center space-x-4">
        <button
          onClick={() => setSidebarOpen(!sidebarOpen)}
          className="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors lg:hidden"
          aria-label="Toggle menu"
          id="hamburger-btn"
        >
          <Menu className="w-5 h-5" />
        </button>

        <div>
          <h1 className="font-sans font-bold text-xl text-slate-900 tracking-tight">
            {viewTitles[currentView]}
          </h1>
          <div className="hidden sm:flex items-center space-x-2 mt-0.5">
            <span className="w-2 h-2 rounded-full bg-indigo-500 animate-pulse" />
            <span className="font-mono text-[10px] text-slate-500 font-semibold tracking-wider uppercase">
              Operaciones Chiloé • Servidor Activo
            </span>
          </div>
        </div>
      </div>

      {/* Action Area */}
      <div className="flex items-center space-x-4">
        {/* Global Search Bar */}
        <div className="hidden md:flex items-center relative w-64 lg:w-80">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" />
          <input
            type="text"
            placeholder="Buscar en el módulo actual..."
            value={searchTerm}
            onChange={handleSearchChange}
            id="global-search-input"
            className="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all font-sans"
          />
        </div>

        {/* Status indicator: Eco metric */}
        <div className="hidden lg:flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-medium">
          <Sparkles className="w-3.5 h-3.5" />
          <span className="text-[11px] font-sans">98% Eficiencia Ecológica</span>
        </div>

        {/* Notifications Trigger */}
        <div className="relative">
          <button
            onClick={() => setShowNotifications(!showNotifications)}
            className="p-2.5 rounded-xl border border-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-50 relative transition-all"
            id="notifications-bell-btn"
          >
            <Bell className="w-4 h-4" />
            <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 border-2 border-white" />
          </button>

          {/* Notifications Dropdown */}
          {showNotifications && (
            <div className="absolute right-0 mt-3 w-80 rounded-2xl border border-slate-100 bg-white shadow-xl shadow-slate-200/50 z-50 overflow-hidden divide-y divide-slate-50 animate-in fade-in slide-in-from-top-3 duration-200">
              <div className="px-4 py-3 bg-slate-50 flex items-center justify-between">
                <span className="text-xs font-semibold text-slate-700 font-sans">Notificaciones</span>
                <span className="text-[10px] font-mono text-indigo-600 font-bold uppercase bg-indigo-50 px-2 py-0.5 rounded-full">3 Nuevas</span>
              </div>
              <div className="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                {mockNotifications.map((n) => (
                  <div key={n.id} className="p-3.5 hover:bg-slate-50 transition-colors flex items-start space-x-3">
                    <span className={`w-2 h-2 rounded-full mt-1.5 shrink-0 ${n.type === 'warning' ? 'bg-amber-500' : n.type === 'success' ? 'bg-indigo-500' : 'bg-blue-500'}`} />
                    <div>
                      <p className="text-xs text-slate-600 leading-relaxed font-sans">{n.text}</p>
                      <span className="text-[9px] font-mono text-slate-400 block mt-1">{n.time}</span>
                    </div>
                  </div>
                ))}
              </div>
              <div className="px-4 py-2 bg-slate-50 text-center">
                <button 
                  onClick={() => setShowNotifications(false)}
                  className="text-[11px] font-medium text-indigo-600 hover:text-indigo-700 font-sans"
                >
                  Marcar todas como leídas
                </button>
              </div>
            </div>
          )}
        </div>

        {/* User Profile Info */}
        <div className="flex items-center space-x-3 pl-2 border-l border-slate-100">
          <div className="text-right hidden sm:block">
            <span className="text-xs font-bold text-slate-800 font-sans block leading-none">
              {currentUser.firstName} {currentUser.lastName}
            </span>
            <span className="text-[10px] font-mono text-indigo-600 font-bold uppercase tracking-wider mt-0.5 block leading-none">
              {currentUser.category}
            </span>
          </div>
          <img
            src={currentUser.avatarUrl || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=150&auto=format&fit=crop'}
            alt="Foto de perfil"
            referrerPolicy="no-referrer"
            className="w-10 h-10 rounded-xl object-cover ring-2 ring-indigo-500/10 shadow-md shadow-slate-100"
          />
        </div>
      </div>
    </header>
  );
}
