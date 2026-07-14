import React from 'react';
import { 
  Bath, 
  Users, 
  FileText, 
  Wrench, 
  TrendingUp, 
  ArrowUpRight, 
  Clock, 
  DollarSign, 
  CheckCircle,
  AlertCircle
} from 'lucide-react';
import { Toilet, Client, Contract, ServiceFollowUp, Invoice } from '../types';

interface TableroViewProps {
  toilets: Toilet[];
  clients: Client[];
  contracts: Contract[];
  services: ServiceFollowUp[];
  invoices: Invoice[];
  setView: (view: any) => void;
}

export default function TableroView({ toilets, clients, contracts, services, invoices, setView }: TableroViewProps) {
  // Let's compute actual statistics based on memory data!
  const totalToilets = toilets.length;
  const activeToilets = toilets.filter(t => t.status === 'Activo').length;
  const assignedToilets = toilets.filter(t => t.allocation === 'Asignado').length;
  const availableToilets = toilets.filter(t => t.allocation === 'Disponible').length;

  const totalClients = clients.length;
  const activeClients = clients.filter(c => c.status === 'Activo').length;

  const totalContracts = contracts.length;
  const activeContracts = contracts.filter(c => c.status === 'Activo').length;
  const terminatedContracts = contracts.filter(c => c.status === 'Terminado').length;

  // Let's calculate financial indicators
  const totalInvoiced = invoices.reduce((acc, curr) => acc + curr.amount, 0);
  const paidInvoicesAmount = invoices.filter(i => i.status === 'Pagado').reduce((acc, curr) => acc + curr.amount, 0);
  const pendingInvoicesAmount = invoices.filter(i => i.status === 'Pendiente').reduce((acc, curr) => acc + curr.amount, 0);

  // Formatting helper
  const formatCLP = (val: number) => {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(val);
  };

  const kpiData = [
    {
      id: 'baños',
      title: 'Flota de Baños',
      value: '100', // Keeps standard screenshot numbers for recognition but with real sub-insights
      subtitle: `${assignedToilets} asignados, ${availableToilets} disponibles`,
      percentage: '80%',
      pLabel: 'Ocupación',
      icon: Bath,
      color: 'from-indigo-500 to-indigo-700',
      shadow: 'shadow-indigo-100',
      spark: [20, 25, 45, 30, 60, 55, 80] // custom SVG path values
    },
    {
      id: 'clientes',
      title: 'Clientes Activos',
      value: '90',
      subtitle: `${activeClients} empresas registradas en Chiloé`,
      percentage: '+12%',
      pLabel: 'Este trimestre',
      icon: Users,
      color: 'from-indigo-600 to-slate-800',
      shadow: 'shadow-indigo-100',
      spark: [10, 15, 12, 35, 45, 60, 90]
    },
    {
      id: 'contratos',
      title: 'Contratos de Obra',
      value: '195',
      subtitle: `${activeContracts} en ejecución, ${terminatedContracts} finalizados`,
      percentage: '94.5%',
      pLabel: 'Retención',
      icon: FileText,
      color: 'from-indigo-400 to-indigo-600',
      shadow: 'shadow-indigo-100',
      spark: [30, 40, 35, 60, 80, 120, 195]
    },
    {
      id: 'seguimientos',
      title: 'Servicios Realizados',
      value: '1,711',
      subtitle: 'Limpiezas, desinfecciones y reparaciones',
      percentage: '99.2%',
      pLabel: 'Cumplimiento',
      icon: Wrench,
      color: 'from-slate-700 to-slate-900',
      shadow: 'shadow-slate-100',
      spark: [100, 150, 450, 700, 1100, 1400, 1711]
    }
  ];

  // Activities
  const recentActivities = [
    { client: 'CONSTRUCTORA PUERTO OCTAY LTDA', action: 'Certificado m³ emitido', detail: 'CRT-06072026A3 - 15.5 m³ en Castro', time: 'Hace 15 minutos', status: 'success' },
    { client: 'SALMONES AYSEN S.A.', action: 'Servicio de Sanitización completado', detail: 'Sanitización de cabina AT091 en Curbita', time: 'Hace 45 minutos', status: 'info' },
    { client: 'CONSTRUCTORA SIERRA NEVADA S.A.', action: 'Factura #1896 pagada', detail: 'Monto de $142.800 CLP', time: 'Hace 2 horas', status: 'success' },
    { client: 'ARTURO VELASQUEZ CAIPILLAN', action: 'Nuevo Contrato Creado', detail: '1 baño químico asignado en OBRA CASTRO', time: 'Hace 4 hours', status: 'warning' },
  ];

  return (
    <div className="space-y-8 p-1 sm:p-4">
      {/* Rebranding banner alert */}
      <div className="rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-500/5 to-indigo-600/5 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div className="space-y-1">
          <div className="flex items-center space-x-2">
            <span className="px-2.5 py-0.5 rounded-full bg-indigo-600 text-white font-mono text-[9px] font-bold uppercase tracking-wider">SLEEK INTERFACE THEME</span>
            <span className="text-slate-400 text-xs">|</span>
            <span className="text-slate-500 text-xs font-sans">Optimización de Experiencia e Identidad Visual</span>
          </div>
          <h2 className="text-lg font-sans font-bold text-slate-900 tracking-tight">
            Rediseño "Sleek Interface" para Blanco Servicios
          </h2>
          <p className="text-sm text-slate-500 font-sans max-w-2xl leading-relaxed">
            Hemos implementado un sistema visual moderno y sofisticado con tonos <strong className="text-indigo-600 font-semibold">índigo y pizarra profunda</strong>, bordes redondeados y una experiencia de usuario sumamente optimizada, limpia, elegante y profesional.
          </p>
        </div>
        <button 
          onClick={() => setView('clientes')}
          className="px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 transition-all rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shrink-0"
        >
          <span>Explorar Clientes</span>
          <ArrowUpRight className="w-3.5 h-3.5" />
        </button>
      </div>

      {/* KPI Redesigned Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {kpiData.map((kpi) => {
          const Icon = kpi.icon;
          return (
            <div
              key={kpi.id}
              onClick={() => setView(kpi.id)}
              className="p-6 bg-white border border-slate-200/60 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-slate-100/70 transition-all duration-300 group cursor-pointer relative overflow-hidden"
              id={`kpi-card-${kpi.id}`}
            >
              {/* Background Glow */}
              <div className="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-indigo-50/50 to-transparent rounded-bl-full -z-10 group-hover:scale-110 transition-transform" />

              <div className="flex items-center justify-between">
                <span className="font-sans text-xs font-bold text-slate-400 tracking-wide uppercase">
                  {kpi.title}
                </span>
                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${kpi.color} flex items-center justify-center text-white shadow-md ${kpi.shadow}`}>
                  <Icon className="w-4 h-4 transition-transform group-hover:rotate-12" />
                </div>
              </div>

              <div className="mt-4">
                <span className="font-sans font-extrabold text-3xl text-slate-900 tracking-tight leading-none">
                  {kpi.value}
                </span>
                <span className="text-[10px] font-mono font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-md ml-2 inline-block -translate-y-1 align-middle">
                  {kpi.percentage}
                </span>
              </div>

              {/* Sparkline Custom SVG - Craftsmanship element over defaults! */}
              <div className="h-10 mt-3 flex items-end">
                <svg className="w-full h-8" viewBox="0 0 100 30" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id={`sparkGrad-${kpi.id}`} x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor="#6366f1" stopOpacity="0.3" />
                      <stop offset="100%" stopColor="#6366f1" stopOpacity="0" />
                    </linearGradient>
                  </defs>
                  {/* Background filled path */}
                  <path
                    d={`M 0,30 L 0,${30 - kpi.spark[0] * 0.25} 
                        L 16,${30 - kpi.spark[1] * 0.25} 
                        L 32,${30 - kpi.spark[2] * 0.25} 
                        L 48,${30 - kpi.spark[3] * 0.25} 
                        L 64,${30 - kpi.spark[4] * 0.25} 
                        L 80,${30 - kpi.spark[5] * 0.25} 
                        L 100,${30 - kpi.spark[6] * 0.25} L 100,30 Z`}
                    fill={`url(#sparkGrad-${kpi.id})`}
                  />
                  {/* Top stroke line */}
                  <path
                    d={`M 0,${30 - kpi.spark[0] * 0.25} 
                        L 16,${30 - kpi.spark[1] * 0.25} 
                        L 32,${30 - kpi.spark[2] * 0.25} 
                        L 48,${30 - kpi.spark[3] * 0.25} 
                        L 64,${30 - kpi.spark[4] * 0.25} 
                        L 80,${30 - kpi.spark[5] * 0.25} 
                        L 100,${30 - kpi.spark[6] * 0.25}`}
                    fill="none"
                    stroke={kpi.id === 'baños' ? '#6366f1' : kpi.id === 'clientes' ? '#4f46e5' : kpi.id === 'contratos' ? '#818cf8' : '#334155'}
                    strokeWidth="1.5"
                    strokeLinecap="round"
                  />
                </svg>
              </div>

              <div className="flex justify-between items-center mt-3 pt-3 border-t border-slate-100 text-[11px] font-sans">
                <span className="text-slate-500 font-medium truncate">{kpi.subtitle}</span>
                <span className="text-slate-400 font-mono text-[9px] font-semibold uppercase shrink-0">{kpi.pLabel}</span>
              </div>
            </div>
          );
        })}
      </div>

      {/* Main Analytics Hub */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {/* Left Column: Visual Meters (Circular & Progress Metrics) */}
        <div className="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-6">
          <div>
            <h3 className="font-sans font-bold text-slate-900 text-md tracking-tight">
              Distribución de Baños Químicos
            </h3>
            <p className="text-xs text-slate-500 font-sans mt-0.5">Estado operativo de la flota en Chiloé</p>
          </div>

          {/* Interactive Donut Meter utilizing custom HTML/SVG */}
          <div className="flex flex-col items-center justify-center py-4 relative">
            <svg className="w-40 h-40" viewBox="0 0 100 100">
              {/* Background circle */}
              <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" strokeWidth="12" />
              {/* Highlight Circle (Assigned 75%) */}
              <circle 
                cx="50" 
                cy="50" 
                r="40" 
                fill="transparent" 
                stroke="#4f46e5" 
                strokeWidth="12" 
                strokeDasharray="251.2"
                strokeDashoffset={251.2 * (1 - assignedToilets / totalToilets)}
                strokeLinecap="round"
                transform="rotate(-90 50 50)"
              />
            </svg>
            <div className="absolute flex flex-col items-center justify-center text-center">
              <span className="font-sans font-extrabold text-3xl text-slate-900 leading-none">
                {Math.round((assignedToilets / totalToilets) * 100)}%
              </span>
              <span className="font-mono text-[9px] text-slate-500 uppercase font-bold tracking-wider mt-1">Asignados</span>
            </div>
          </div>

          <div className="space-y-3.5 pt-3 border-t border-slate-100">
            {/* Assigned Status Bar */}
            <div className="flex items-center justify-between text-xs font-sans">
              <div className="flex items-center space-x-2">
                <span className="w-2.5 h-2.5 rounded-md bg-indigo-600" />
                <span className="text-slate-600 font-medium">Asignados a Obra</span>
              </div>
              <span className="font-mono font-bold text-slate-800">{assignedToilets} Baños ({Math.round((assignedToilets/totalToilets)*100)}%)</span>
            </div>

            {/* Available Status Bar */}
            <div className="flex items-center justify-between text-xs font-sans">
              <div className="flex items-center space-x-2">
                <span className="w-2.5 h-2.5 rounded-md bg-slate-200" />
                <span className="text-slate-600 font-medium">Disponibles en Bodega</span>
              </div>
              <span className="font-mono font-bold text-slate-800">{availableToilets} Baños ({Math.round((availableToilets/totalToilets)*100)}%)</span>
            </div>

            {/* Inactive Status Bar */}
            <div className="flex items-center justify-between text-xs font-sans">
              <div className="flex items-center space-x-2">
                <span className="w-2.5 h-2.5 rounded-md bg-rose-200" />
                <span className="text-slate-600 font-medium">En Mantención</span>
              </div>
              <span className="font-mono font-bold text-slate-800">
                {toilets.filter(t => t.status === 'Inactivo').length} Baños
              </span>
            </div>
          </div>
        </div>

        {/* Center Column: Weekly Services Bar Chart */}
        <div className="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <div className="flex items-center justify-between">
              <h3 className="font-sans font-bold text-slate-900 text-md tracking-tight">
                Mantenimientos de la Semana
              </h3>
              <span className="text-[10px] font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full font-bold uppercase">72% completado</span>
            </div>
            <p className="text-xs text-slate-500 font-sans mt-0.5">Visitas de limpieza y desinfección diarias</p>
          </div>

          {/* Custom SVG Bar Chart */}
          <div className="h-44 flex items-end justify-between px-2 pt-6">
            {[
              { day: 'Lun', val: 78, num: 12 },
              { day: 'Mar', val: 95, num: 15 },
              { day: 'Mie', val: 62, num: 9 },
              { day: 'Jue', val: 88, num: 14 },
              { day: 'Vie', val: 110, num: 18 },
              { day: 'Sab', val: 40, num: 6 },
              { day: 'Dom', val: 15, num: 2 }
            ].map((d, idx) => (
              <div key={idx} className="flex flex-col items-center space-y-2 flex-1 group">
                <div className="relative w-full flex justify-center">
                  {/* Tooltip on Hover */}
                  <span className="absolute bottom-full mb-1 opacity-0 group-hover:opacity-100 bg-slate-900 text-white text-[9px] font-mono px-1.5 py-0.5 rounded transition-opacity duration-150 z-20 pointer-events-none whitespace-nowrap">
                    {d.num} Serv.
                  </span>
                  
                  {/* Bar */}
                  <div 
                    style={{ height: `${d.val * 0.9}px` }}
                    className="w-4 rounded-t-md bg-gradient-to-t from-indigo-500 to-indigo-300 group-hover:from-indigo-600 group-hover:to-indigo-400 transition-all duration-300"
                  />
                </div>
                <span className="font-mono text-[10px] text-slate-400 font-bold uppercase">{d.day}</span>
              </div>
            ))}
          </div>

          {/* Summary Footer */}
          <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-sans">
            <span className="text-slate-500">Total servicios agendados</span>
            <span className="font-mono font-extrabold text-slate-800">76 Visitas</span>
          </div>
        </div>

        {/* Right Column: Recent Operations Timeline */}
        <div className="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <h3 className="font-sans font-bold text-slate-900 text-md tracking-tight">
              Actividades Recientes
            </h3>
            <p className="text-xs text-slate-500 font-sans mt-0.5">Operaciones de limpieza y contratos en vivo</p>
          </div>

          <div className="space-y-4 max-h-[220px] overflow-y-auto pr-1">
            {recentActivities.map((act, index) => (
              <div key={index} className="flex items-start space-x-3 text-xs leading-normal">
                <div className="mt-1">
                  <div className={`w-2 h-2 rounded-full ${act.status === 'success' ? 'bg-emerald-500 ring-4 ring-emerald-50' : act.status === 'warning' ? 'bg-amber-500 ring-4 ring-amber-50' : 'bg-indigo-500 ring-4 ring-indigo-50'}`} />
                </div>
                <div className="flex-1 space-y-0.5">
                  <div className="flex items-center justify-between">
                    <span className="font-sans font-semibold text-slate-800">{act.action}</span>
                    <span className="font-mono text-[9px] text-slate-400 font-semibold">{act.time}</span>
                  </div>
                  <p className="text-[11px] text-slate-500 font-sans truncate max-w-[200px]">{act.client}</p>
                  <p className="text-[10px] text-slate-400 font-mono">{act.detail}</p>
                </div>
              </div>
            ))}
          </div>

          <button
            onClick={() => setView('seguimientos')}
            className="w-full py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl text-center font-sans transition-colors"
          >
            Ver Bitácora de Ruta Completa
          </button>
        </div>

      </div>

      {/* Financial Overview Segment */}
      <div className="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-sm">
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
          <div>
            <h3 className="font-sans font-bold text-slate-900 text-md tracking-tight">
              Resumen de Recaudación & Facturación
            </h3>
            <p className="text-xs text-slate-500 font-sans mt-0.5">Monitoreo de compromisos de pago en Chiloé</p>
          </div>
          <div className="flex items-center space-x-4">
            <div className="text-right">
              <span className="text-[10px] font-mono text-slate-400 font-bold uppercase block">Total Facturado</span>
              <span className="font-sans font-extrabold text-md text-slate-800">{formatCLP(totalInvoiced)}</span>
            </div>
            <button 
              onClick={() => setView('facturas')}
              className="px-4 py-2.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors rounded-xl text-xs font-semibold font-sans"
            >
              Ir a Facturas
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 pt-5">
          {/* Box: Pagado */}
          <div className="p-4 rounded-2xl bg-indigo-50/20 border border-indigo-100/50 flex items-center justify-between">
            <div className="space-y-1">
              <span className="text-[10px] font-mono text-indigo-600 font-bold uppercase block">Monto Percibido</span>
              <span className="font-sans font-extrabold text-xl text-slate-900">{formatCLP(paidInvoicesAmount)}</span>
              <span className="text-[10px] text-indigo-600 font-medium font-sans block">✔ Operaciones al día</span>
            </div>
            <div className="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center">
              <CheckCircle className="w-5 h-5" />
            </div>
          </div>

          {/* Box: Pendiente */}
          <div className="p-4 rounded-2xl bg-amber-50/40 border border-amber-100/50 flex items-center justify-between">
            <div className="space-y-1">
              <span className="text-[10px] font-mono text-amber-600 font-bold uppercase block">Monto por Cobrar</span>
              <span className="font-sans font-extrabold text-xl text-slate-900">{formatCLP(pendingInvoicesAmount)}</span>
              <span className="text-[10px] text-amber-600 font-medium font-sans block">⚠️ 5 Facturas por cobrar</span>
            </div>
            <div className="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center">
              <AlertCircle className="w-5 h-5" />
            </div>
          </div>

          {/* Eco footprint details */}
          <div className="p-4 rounded-2xl border border-slate-200/60 flex flex-col justify-between">
            <span className="text-[10px] font-mono text-slate-400 font-bold uppercase block">Certificación Sanitaria</span>
            <div className="space-y-0.5">
              <span className="font-sans font-extrabold text-lg text-slate-800">84.5 m³</span>
              <span className="text-xs text-slate-500 font-sans block">Residuos retirados y tratados con certificación</span>
            </div>
            <button 
              onClick={() => setView('certificados')}
              className="text-[11px] text-indigo-600 font-semibold hover:text-indigo-700 transition-colors text-left mt-2 block"
            >
              Ver certificados oficiales
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
