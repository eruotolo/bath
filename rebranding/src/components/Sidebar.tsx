import React from 'react';
import { 
  LayoutDashboard, 
  Users, 
  Bath, 
  FileText, 
  ClipboardCheck, 
  Receipt, 
  FileCheck2, 
  Users2,
  Menu,
  X
} from 'lucide-react';
import { ViewType } from '../types';

interface SidebarProps {
  currentView: ViewType;
  setView: (view: ViewType) => void;
  isOpen: boolean;
  setIsOpen: (isOpen: boolean) => void;
}

export default function Sidebar({ currentView, setView, isOpen, setIsOpen }: SidebarProps) {
  const menuItems = [
    { id: 'tablero' as ViewType, name: 'Tablero Principal', icon: LayoutDashboard, category: 'General' },
    { id: 'clientes' as ViewType, name: 'Clientes', icon: Users, category: 'Operaciones' },
    { id: 'baños' as ViewType, name: 'Baños Químicos', icon: Bath, category: 'Operaciones' },
    { id: 'contratos' as ViewType, name: 'Obras & Contratos', icon: FileText, category: 'Operaciones' },
    { id: 'seguimientos' as ViewType, name: 'Servicios & Ruta', icon: ClipboardCheck, category: 'Operaciones' },
    { id: 'facturas' as ViewType, name: 'Facturas', icon: Receipt, category: 'Finanzas' },
    { id: 'certificados' as ViewType, name: 'Certificados m³', icon: FileCheck2, category: 'Finanzas' },
    { id: 'usuarios' as ViewType, name: 'Personal & Roles', icon: Users2, category: 'Administración' },
  ];

  const categories = ['General', 'Operaciones', 'Finanzas', 'Administración'];

  return (
    <>
      {/* Mobile Backdrop */}
      {isOpen && (
        <div 
          className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300"
          onClick={() => setIsOpen(false)}
        />
      )}

      {/* Navigation Drawer */}
      <aside className={`
        fixed inset-y-0 left-0 z-50 flex flex-col w-72 bg-white border-r border-slate-200 text-slate-800
        transform transition-transform duration-300 cubic-bezier(0.4, 0, 0.2, 1)
        lg:translate-x-0 lg:static lg:h-full
        ${isOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
        {/* Brand Header */}
        <div className="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/50">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/20">
              <Bath className="w-5 h-5 text-white" />
            </div>
            <div>
              <span className="font-sans font-bold text-lg tracking-tight text-slate-900 block leading-tight">
                Blanco
              </span>
              <span className="font-mono text-[9px] text-indigo-600 font-bold tracking-wider uppercase block mt-0.5">
                Servicios Ambientales
              </span>
            </div>
          </div>
          <button 
            onClick={() => setIsOpen(false)}
            className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors lg:hidden"
            id="close-sidebar-btn"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Navigation List */}
        <div className="flex-1 overflow-y-auto px-4 py-6 space-y-7 scrollbar-thin scrollbar-thumb-slate-200">
          {categories.map((category) => {
            const items = menuItems.filter(item => item.category === category);
            if (items.length === 0) return null;

            return (
              <div key={category} className="space-y-2">
                <h3 className="px-3 font-mono text-[10px] font-bold text-slate-400 tracking-widest uppercase">
                  {category}
                </h3>
                <nav className="space-y-1">
                  {items.map((item) => {
                    const Icon = item.icon;
                    const isActive = currentView === item.id;
                    return (
                      <button
                        key={item.id}
                        id={`nav-item-${item.id}`}
                        onClick={() => {
                          setView(item.id);
                          setIsOpen(false);
                        }}
                        className={`
                          w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium
                          transition-all duration-200 group relative
                          ${isActive 
                            ? 'bg-indigo-50 text-indigo-700 shadow-sm' 
                            : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'}
                        `}
                      >
                        <Icon className={`w-4 h-4 transition-transform group-hover:scale-110 ${isActive ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600'}`} />
                        <span className="font-sans">{item.name}</span>
                        {isActive && (
                          <span className="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse" />
                        )}
                      </button>
                    );
                  })}
                </nav>
              </div>
            );
          })}
        </div>

        {/* Support Center Widget (Sleek Theme Layout Pattern) */}
        <div className="p-4 border-t border-slate-100 mt-auto bg-slate-50/30">
          <div className="bg-slate-900 text-white rounded-2xl p-4">
            <p className="text-[9px] text-indigo-400 font-mono font-bold uppercase tracking-wider mb-1">Blanco Soporte</p>
            <p className="text-xs font-medium mb-3 leading-normal">¿Necesitas ayuda con las rutas, m³ o facturas?</p>
            <button 
              onClick={() => alert('Soporte Blanco: Contactando con la central de operaciones de Castro...')}
              className="w-full bg-indigo-500 hover:bg-indigo-600 py-2 rounded-xl text-[11px] font-semibold transition-colors text-white text-center"
            >
              Contactar Soporte
            </button>
          </div>
        </div>

        {/* Footer Brand Credit */}
        <div className="p-4 border-t border-slate-100 bg-slate-50/50 text-center">
          <p className="font-mono text-[9px] text-slate-400">
            © 2026 Blanco Servicios.
          </p>
          <p className="font-mono text-[8px] text-indigo-500/80 font-bold mt-0.5">
            Plataforma Eco-Sostenible v2.0
          </p>
        </div>
      </aside>
    </>
  );
}
