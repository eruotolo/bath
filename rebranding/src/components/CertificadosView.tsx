import React, { useState } from 'react';
import { 
  Search, 
  FileCheck, 
  Printer, 
  Download, 
  X, 
  Award, 
  ShieldCheck, 
  Droplet,
  FileSignature
} from 'lucide-react';
import { Certificate } from '../types';

interface CertificadosViewProps {
  certificates: Certificate[];
  searchTerm: string;
}

export default function CertificadosView({ certificates, searchTerm }: CertificadosViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [activeCert, setActiveCert] = useState<Certificate | null>(null);

  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredCerts = certificates.filter(c => 
    c.number.toLowerCase().includes(combinedSearch) ||
    c.clientName.toLowerCase().includes(combinedSearch) ||
    c.clientRut.toLowerCase().includes(combinedSearch) ||
    c.workName.toLowerCase().includes(combinedSearch)
  );

  return (
    <div className="space-y-6">
      
      {/* Top Search Controls */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Buscar por N° certificado, RUT, cliente..."
            value={localSearch}
            onChange={(e) => setLocalSearch(e.target.value)}
            id="certificados-local-search"
            className="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
          />
        </div>

        <div className="flex items-center space-x-1.5 px-4 py-2 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-2xl font-medium font-sans text-xs">
          <ShieldCheck className="w-4 h-4 text-indigo-600" />
          <span>Firma Digital Seremi Autorizada Activa</span>
        </div>
      </div>

      {/* Main certificates list */}
      <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-slate-50 bg-slate-50/50">
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Número Certificado</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Cliente / RUT</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Obra Faena</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">F. Servicio</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Volumen Disp. (m³)</th>
                <th className="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {filteredCerts.map((cert) => (
                <tr key={cert.number} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-6 py-4 font-mono font-extrabold text-slate-800 text-xs">
                    {cert.number}
                  </td>
                  <td className="px-6 py-4">
                    <span className="font-sans font-bold text-slate-900 block text-xs">{cert.clientName}</span>
                    <span className="font-mono text-[10px] text-slate-400 block mt-0.5">RUT: {cert.clientRut}</span>
                  </td>
                  <td className="px-6 py-4 font-sans text-xs text-slate-600 truncate max-w-[180px]">
                    {cert.workName}
                  </td>
                  <td className="px-6 py-4 font-sans text-xs text-slate-500">
                    {cert.serviceDate}
                  </td>
                  <td className="px-6 py-4 font-mono font-bold text-slate-800 text-xs">
                    <div className="flex items-center space-x-1.5">
                      <Droplet className="w-3.5 h-3.5 text-indigo-600" />
                      <span>{cert.volumeM3.toFixed(1)} m³</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="inline-flex items-center space-x-2">
                      <button
                        onClick={() => setActiveCert(cert)}
                        className="px-3 py-1 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg font-sans transition-colors flex items-center space-x-1 border border-slate-100"
                      >
                        <Printer className="w-3.5 h-3.5" />
                        <span>Ver Ficha</span>
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {filteredCerts.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                    No se encontraron certificados con los filtros proporcionados.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* PDF SIMULATION PREVIEW DIALOG (HIGH FIDELITY SCREENSHOT 15 INTEGRATION) */}
      {activeCert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onClick={() => setActiveCert(null)} />
          <div className="relative bg-white rounded-3xl border border-slate-100 shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-200">
            {/* Header top controllers */}
            <div className="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
              <span className="font-sans font-bold text-slate-800 text-sm">Vista Previa de Documento Oficial</span>
              <div className="flex items-center space-x-2">
                <button 
                  onClick={() => window.print()}
                  className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition-colors"
                  title="Imprimir"
                >
                  <Printer className="w-4 h-4" />
                </button>
                <button 
                  onClick={() => alert('Certificado digital descargado como PDF en su sistema.')}
                  className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition-colors"
                  title="Descargar PDF"
                >
                  <Download className="w-4 h-4" />
                </button>
                <button onClick={() => setActiveCert(null)} className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            {/* Document sheet container */}
            <div className="p-8 max-h-[70vh] overflow-y-auto space-y-6 bg-slate-100/50 select-text font-serif">
              
              {/* Virtual A4 sheet paper */}
              <div className="p-8 bg-white shadow-md border border-slate-200 mx-auto max-w-lg space-y-6 text-slate-800 relative">
                
                {/* Stamp overlay */}
                <div className="absolute top-24 right-8 border-4 border-indigo-500/30 text-indigo-500/70 rotate-12 px-3 py-1 font-sans font-extrabold text-[10px] tracking-widest uppercase rounded-lg">
                  AUTORIZADO SEREMI <br/> SALUD LOS LAGOS
                </div>

                {/* Head logos */}
                <div className="flex items-start justify-between border-b border-slate-200 pb-4">
                  <div className="font-sans">
                    <h4 className="font-bold text-xs uppercase tracking-tight text-slate-900 leading-none">Blanco Servicios Ambientales</h4>
                    <span className="text-[9px] text-slate-400 block mt-0.5">Disposición y Tratamiento de Aguas Servidas</span>
                    <span className="text-[9px] text-slate-400 block">Ruta 5 Sur km 1180, Castro, Chiloé</span>
                  </div>
                  <div className="text-right font-sans">
                    <span className="text-xs font-bold text-rose-600 block">CERTIFICADO N°</span>
                    <span className="font-mono font-extrabold text-sm text-slate-800">{activeCert.number}</span>
                  </div>
                </div>

                {/* Main Body Statement */}
                <div className="space-y-4">
                  <h3 className="font-sans font-extrabold text-sm text-center text-slate-900 tracking-wide uppercase">
                    Certificado de Disposición Final de Residuos
                  </h3>

                  <p className="text-[11px] leading-relaxed text-justify indent-6">
                    Se certifica por medio del presente documento que la empresa <strong className="text-slate-950 font-sans font-bold">{activeCert.clientName}</strong>, con RUT <strong className="font-mono">{activeCert.clientRut}</strong>, ha efectuado el retiro, transporte y disposición final de aguas servidas de sus servicios sanitarios portátiles.
                  </p>

                  <p className="text-[11px] leading-relaxed text-justify">
                    Las faenas asociadas corresponden a la obra <strong className="text-slate-950 font-sans font-bold">{activeCert.workName}</strong>, habiéndose recolectado un volumen total acumulado de:
                  </p>

                  {/* Volume box */}
                  <div className="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-1 my-4 font-sans">
                    <span className="text-[10px] text-slate-400 font-mono font-bold uppercase block">Volumen Sanitario Certificado</span>
                    <span className="text-3xl font-extrabold text-slate-900 block tracking-tight">{activeCert.volumeM3.toFixed(1)} Metros Cúbicos (m³)</span>
                    <span className="text-[9px] text-indigo-600 font-semibold block">Tratamiento de lodos autorizado • Código Disp: WD-908123</span>
                  </div>

                  <p className="text-[11px] leading-relaxed text-justify">
                    Dichos residuos fueron depositados en planta de tratamiento de lodos autorizada, de conformidad con las normativas sanitarias chilenas vigentes, Reglamento sobre Condiciones Sanitarias y Ambientales Básicas en los Lugares de Trabajo (D.S. N° 594), y las normativas dictadas by la Seremi de Salud de la Región de Los Lagos.
                  </p>
                </div>

                {/* Signatures */}
                <div className="grid grid-cols-2 gap-4 pt-10 border-t border-slate-100 font-sans text-center">
                  <div className="flex flex-col items-center justify-end">
                    <div className="w-24 h-1 border-b border-slate-300" />
                    <span className="text-[9px] font-bold text-slate-800 block mt-2">Supervisor Operaciones</span>
                    <span className="text-[8px] text-slate-400">Blanco Servicios</span>
                  </div>
                  <div className="flex flex-col items-center justify-end space-y-1">
                    {/* Simulated digital signature badge */}
                    <div className="px-2 py-0.5 bg-indigo-50 border border-indigo-200 rounded flex items-center space-x-1 text-indigo-700 text-[8px] font-semibold">
                      <FileSignature className="w-3 h-3 text-indigo-500" />
                      <span>FIRMADO DIGITALMENTE</span>
                    </div>
                    <span className="text-[9px] font-bold text-slate-800 block">Oficina de Control Ambiental</span>
                    <span className="text-[8px] text-slate-400">Seremi de Salud Chiloé</span>
                  </div>
                </div>

              </div>

            </div>

            {/* Footer controllers */}
            <div className="px-6 py-4 bg-slate-50 border-t border-slate-100 text-right">
              <button
                onClick={() => setActiveCert(null)}
                className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-sans text-xs font-semibold"
              >
                Cerrar Previsualización
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
