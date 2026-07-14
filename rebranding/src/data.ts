import { Client, Toilet, Contract, Invoice, ServiceFollowUp, Certificate, User } from './types';

export const INITIAL_CLIENTS: Client[] = [
  {
    id: 'C-01',
    rut: '7.116.892-K',
    name: 'MARIA BETILDA COÑUE PEREZ',
    phone: '+56 9 9643 2629',
    email: 'leovaldebenitog@gmail.com',
    address: 'Camino Principal S/N',
    region: 'Región de Los Lagos',
    city: 'Castro',
    comuna: 'Castro',
    status: 'Activo',
    createdAt: '2025-12-01'
  },
  {
    id: 'C-02',
    rut: '7.948.461-3',
    name: 'ARIDOS MARDONES',
    phone: '+56 9 9879 8642',
    email: 'omardones@aridosmardones.cl',
    address: 'Sector Linao Ruta 5',
    region: 'Región de Los Lagos',
    city: 'Ancud',
    comuna: 'Ancud',
    status: 'Activo',
    createdAt: '2025-02-12'
  },
  {
    id: 'C-03',
    rut: '9.952.635-4',
    name: 'RAUL MANSILLA MUÑOZ',
    phone: '+56 9 8428 5545',
    email: 'contacto@contacto.cl',
    address: 'Calle O\'Higgins 450',
    region: 'Región de Los Lagos',
    city: 'Quellón',
    comuna: 'Quellón',
    status: 'Activo',
    createdAt: '2025-01-10'
  },
  {
    id: 'C-04',
    rut: '12.203.237-6',
    name: 'JOSE ARTURO OYARZUN TORRES',
    phone: '+56 9 5550 1234',
    email: 'oyarzuntorre@gmail.com',
    address: 'Pasaje Dalcahue 12',
    region: 'Región de Los Lagos',
    city: 'Dalcahue',
    comuna: 'Dalcahue',
    status: 'Activo',
    createdAt: '2025-05-15'
  },
  {
    id: 'C-05',
    rut: '13.169.434-2',
    name: 'ARTURO VELASQUEZ CAIPILLAN',
    phone: '+56 9 9127 7983',
    email: 'velasquezcaipillan@gmail.com',
    address: 'Obra Central Castro',
    region: 'Región de Los Lagos',
    city: 'Castro',
    comuna: 'Castro',
    status: 'Activo',
    createdAt: '2026-05-06'
  },
  {
    id: 'C-06',
    rut: '14.378.433-8',
    name: 'CRISTIAN ALEJANDRO MANSILLA ALVAREZ',
    phone: '+56 9 8690 3789',
    email: 'cristian.mansilla.alvarez@gmail.com',
    address: 'Sector Petanes S/N',
    region: 'Región de Los Lagos',
    city: 'Chonchi',
    comuna: 'Chonchi',
    status: 'Activo',
    createdAt: '2025-10-02'
  },
  {
    id: 'C-07',
    rut: '15.270.112-1',
    name: 'LEOPOLDO JAIRO MUÑOZ VARGAS',
    phone: '+56 9 9293 7371',
    email: 'ysaldivia@thermowin.cl',
    address: 'Camino Chonchi Quellón km 14',
    region: 'Región de Los Lagos',
    city: 'Quellón',
    comuna: 'Quellón',
    status: 'Activo',
    createdAt: '2025-09-01'
  },
  {
    id: 'C-08',
    rut: '15.549.350-5',
    name: 'JORGE ANDRES VILLAR MUÑOZ',
    phone: '+56 9 4460 5093',
    email: 'adquisicion@rentalvaldivia.com',
    address: 'Obra Mercado Chonchi',
    region: 'Región de Los Lagos',
    city: 'Chonchi',
    comuna: 'Chonchi',
    status: 'Activo',
    createdAt: '2025-01-01'
  },
  {
    id: 'C-09',
    rut: '17.292.230-0',
    name: 'MAURICIO ALEJANDRO BARRIA VELASQUEZ',
    phone: '+56 9 3256 3913',
    email: 'mbarriavelasquez@gmail.com',
    address: 'Sector Curbita',
    region: 'Región de Los Lagos',
    city: 'Ancud',
    comuna: 'Ancud',
    status: 'Activo',
    createdAt: '2026-07-02'
  },
  {
    id: 'C-10',
    rut: '17.528.224-1',
    name: 'NICOLAS LAUTARO HICHE ALVAREZ',
    phone: '+56 9 9098 5496',
    email: 'ventas@ratacop.cl',
    address: 'Obra Alto Muro S/N',
    region: 'Región de Los Lagos',
    city: 'Quellón',
    comuna: 'Quellón',
    status: 'Activo',
    createdAt: '2026-01-30'
  }
];

export const INITIAL_TOILETS: Toilet[] = [
  { code: 'AT055', purchaseDate: '2026-12-01', observations: 'Estado impecable, tipo estándar', status: 'Activo', allocation: 'Disponible' },
  { code: 'AT054', purchaseDate: '2025-12-01', observations: 'Reforzado con lava manos', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA ACHAO', assignedClient: 'CONSTRUCTORA PUERTO OCTAY LTDA' },
  { code: 'AT047', purchaseDate: '2025-12-01', observations: 'Estándar', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA ACHAO', assignedClient: 'CONSTRUCTORA PUERTO OCTAY LTDA' },
  { code: 'AT052', purchaseDate: '2025-12-01', observations: 'Estándar', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA ACHAO', assignedClient: 'CONSTRUCTORA PUERTO OCTAY LTDA' },
  { code: 'AT093', purchaseDate: '2025-02-01', observations: 'Especial faena', status: 'Activo', allocation: 'Asignado', assignedWork: 'SECTOR COMPU', assignedClient: 'INCOSUR SPA' },
  { code: 'AT091', purchaseDate: '2025-02-01', observations: 'Estándar', status: 'Activo', allocation: 'Asignado', assignedWork: 'LINAO-ANCUD', assignedClient: 'ARIDOS MARDONES' },
  { code: 'AT095', purchaseDate: '2025-02-01', observations: 'Limpieza profunda realizada', status: 'Activo', allocation: 'Disponible' },
  { code: 'AT094', purchaseDate: '2025-01-01', observations: 'Estándar', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA ACHAO', assignedClient: 'CONSTRUCTORA PUERTO OCTAY LTDA' },
  { code: 'AT012', purchaseDate: '2025-01-01', observations: 'Estándar con dispensador', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA PID PID', assignedClient: 'SONDAJES Y CONSTRUCCIONES PERFORROTER' },
  { code: 'AT009', purchaseDate: '2025-01-01', observations: 'Estándar', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA ACHAO', assignedClient: 'CONSTRUCTORA PUERTO OCTAY LTDA' },
  { code: 'AT010', purchaseDate: '2024-04-22', observations: 'Revisión técnica OK', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA MERCADO CHONCHI', assignedClient: 'JORGE ANDRES VILLAR MUÑOZ' },
  { code: 'AT011', purchaseDate: '2023-10-31', observations: 'Clásico gris', status: 'Activo', allocation: 'Asignado', assignedWork: 'GM CASTRO - PENINSULA DE RILAN (OC 8060)', assignedClient: 'CONSTRUCTORA PUERTO OCTAY LTDA' },
  { code: 'AT013', purchaseDate: '2025-07-09', observations: 'Válvula mejorada', status: 'Activo', allocation: 'Asignado', assignedWork: 'SECTOR NAL - YUSTE FUERTE AHUI', assignedClient: 'CONSTRUCTORA ARIMAQ ANCUD SPA.' },
  { code: 'AT014', purchaseDate: '2026-04-21', observations: 'Eco-flush', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA RAMPLA CHACAO', assignedClient: 'ING Y CONST. HARCHA LTDA' },
  { code: 'AT016', purchaseDate: '2026-07-01', observations: 'Uso rudo', status: 'Activo', allocation: 'Asignado', assignedWork: 'OBRA ANCUD - DEGAN', assignedClient: 'CONSTRUCTORA ARIMAQ ANCUD SPA.' }
];

export const INITIAL_CONTRACTS: Contract[] = [
  {
    id: 'K-195',
    clientName: 'APIA SPA',
    workName: 'CURACO DE VELEZ',
    status: 'Activo',
    startDate: '2025-12-05',
    endDate: '2026-12-05',
    monthlyValue: 130000,
    totalValue: 130000,
    address: 'Plaza de Armas Curaco de Vélez',
    observations: 'Servicio de mantenimiento los lunes y jueves',
    assignedToilets: ['AT055']
  },
  {
    id: 'K-196',
    clientName: 'APIA SPA',
    workName: 'OC 901160',
    status: 'Activo',
    startDate: '2025-08-06',
    endDate: '2026-08-06',
    monthlyValue: 100000,
    totalValue: 100000,
    address: 'Ruta W-100 Sector Quinchao',
    observations: 'Mantenimiento semanal único',
    assignedToilets: ['AT095']
  },
  {
    id: 'K-197',
    clientName: 'ARIDOS MARDONES',
    workName: 'LINAO-ANCUD',
    status: 'Activo',
    startDate: '2025-02-12',
    endDate: '2026-02-12',
    monthlyValue: 230000,
    totalValue: 460000,
    address: 'Cantera Linao km 25',
    observations: 'Dos baños requeridos de forma permanente en faena',
    assignedToilets: ['AT091']
  },
  {
    id: 'K-198',
    clientName: 'ARTURO VELASQUEZ CAIPILLAN',
    workName: 'OBRA CASTRO',
    status: 'Activo',
    startDate: '2026-05-06',
    endDate: '2027-05-06',
    monthlyValue: 125000,
    totalValue: 125000,
    address: 'Calle Galvarino Riveros 1200',
    observations: 'Mantenimiento bisemanal prioritario',
    assignedToilets: ['AT052']
  },
  {
    id: 'K-199',
    clientName: 'ASESORIA Y CONSTRUCCION ENTREVIGAS SPA',
    workName: 'MULTICANCHA CALLE INES DE BAZAN - CASTRO',
    status: 'Activo',
    startDate: '2025-12-09',
    endDate: '2026-06-09',
    monthlyValue: 125000,
    totalValue: 125000,
    address: 'Calle Inés de Bazán S/N',
    observations: 'Mantenimiento los martes',
    assignedToilets: ['AT047']
  },
  {
    id: 'K-200',
    clientName: 'C.A.V. CONSTRUCCIONES',
    workName: 'SECTOR PETANES',
    status: 'Activo',
    startDate: '2025-10-02',
    endDate: '2026-10-02',
    monthlyValue: 98000,
    totalValue: 98000,
    address: 'Cruce Petanes S/N',
    observations: 'Servicio estándar',
    assignedToilets: ['AT093']
  },
  {
    id: 'K-201',
    clientName: 'CABAÑAS NOMADES',
    workName: 'VILLA BORDEMAR',
    status: 'Activo',
    startDate: '2025-04-24',
    endDate: '2025-10-24',
    monthlyValue: 1000,
    totalValue: 1000,
    address: 'Sector Nercon Costanera',
    observations: 'Convenio turístico especial',
    assignedToilets: ['AT094']
  },
  {
    id: 'K-202',
    clientName: 'CHILOE GRAPPLING',
    workName: 'SECTOR QUILO - ANCUD',
    status: 'Activo',
    startDate: '2026-01-07',
    endDate: '2027-01-07',
    monthlyValue: 400000,
    totalValue: 400000,
    address: 'Camino Mar Brava km 5',
    observations: '4 baños químicos instalados para evento deportivo',
    assignedToilets: ['AT009']
  },
  {
    id: 'K-101',
    clientName: 'AGONI CONSTRUCCIONES LTDA',
    workName: 'VILLA GUARELO',
    status: 'Terminado',
    startDate: '2025-02-12',
    endDate: '2025-12-12',
    monthlyValue: 50000,
    totalValue: 50000,
    address: 'Población Guarelo Castro',
    observations: 'Servicio finalizado con éxito y baños retirados',
    assignedToilets: []
  },
  {
    id: 'K-102',
    clientName: 'AGONI CONSTRUCCIONES LTDA',
    workName: 'OBRA QUELLON',
    status: 'Terminado',
    startDate: '2024-01-01',
    endDate: '2024-12-31',
    monthlyValue: 1233464,
    totalValue: 24520,
    address: 'Ruta 5 Quellón Sur',
    observations: 'Facturación especial',
    assignedToilets: []
  },
  {
    id: 'K-103',
    clientName: 'APIA SPA',
    workName: 'CHACAO - ANCUD',
    status: 'Terminado',
    startDate: '2025-10-21',
    endDate: '2025-11-21',
    monthlyValue: 110000,
    totalValue: 110000,
    address: 'Embarcadero Chacao',
    observations: 'Servicio temporal de contingencia',
    assignedToilets: []
  }
];

export const INITIAL_SERVICES: ServiceFollowUp[] = [
  {
    id: 'S-269654',
    clientName: 'INGENIERIA Y CONSTRUCCION PRC S.A.',
    workName: 'RUTA 5 - QUELLON',
    isInvoiced: false,
    date: '2026-07-06',
    types: ['Limpieza', 'Sanitización', 'Entrega Papel Higiénico'],
    observations: 'Baños AT011, AT016 sanitizados sin novedades.',
    status: 'Completado'
  },
  {
    id: 'S-112356',
    clientName: 'CONSTRUCTORA SIERRA NEVADA S.A.',
    workName: 'OBRA CAMINO A CHONCHI - QUELLON',
    isInvoiced: true,
    invoiceNumber: '#1896',
    date: '2026-06-30',
    types: ['Limpieza', 'Sanitización', 'Entrega de Jabón Líquido'],
    observations: 'Se reporta daño menor en bisagra de puerta, reparada en terreno.',
    status: 'Completado'
  },
  {
    id: 'S-828031',
    clientName: 'C.A.V. CONSTRUCCIONES',
    workName: 'SECTOR PETANES',
    isInvoiced: false,
    date: '2026-07-03',
    types: ['Limpieza', 'Desinfección'],
    observations: 'Mantenimiento de rutina completado.',
    status: 'Completado'
  },
  {
    id: 'S-595615',
    clientName: 'APIA SPA',
    workName: 'OC 901160',
    isInvoiced: false,
    date: '2026-07-03',
    types: ['Reparación', 'Limpieza'],
    observations: 'Reemplazo de dispensador de jabón quebrado.',
    status: 'Completado'
  },
  {
    id: 'S-756553',
    clientName: 'CONSTRUCTORA PUERTO OCTAY LTDA',
    workName: 'GM CASTRO - PENINSULA DE RILAN (OC 8060)',
    isInvoiced: false,
    date: '2026-06-30',
    types: ['Limpieza', 'Desinfección', 'Sanitización'],
    observations: 'Limpieza profunda en 3 cabinas.',
    status: 'Completado'
  },
  {
    id: 'S-979855',
    clientName: 'SACRAMENTOS CONSULTORES LIMITADA',
    workName: 'SECTOR COINCO',
    isInvoiced: false,
    date: '2026-07-03',
    types: ['Instalación'],
    observations: 'Entrega inicial de baño químico AT055. Queda operativo.',
    status: 'Completado'
  },
  {
    id: 'S-251620',
    clientName: 'CONSTRUCTORA ANTUMALAL SPA',
    workName: 'SECTOR LLAU LLAO - CASTRO',
    isInvoiced: false,
    date: '2026-07-06',
    types: ['Limpieza'],
    observations: 'Servicio estándar rápido realizado.',
    status: 'Completado'
  }
];

export const INITIAL_INVOICES: Invoice[] = [
  {
    number: '#1896',
    date: '2026-07-06',
    clientName: 'CONSTRUCTORA SIERRA NEVADA S.A.',
    workName: 'OBRA CAMINO A CHONCHI - QUELLON',
    amount: 142800,
    status: 'Pagado',
    paymentDate: '2026-07-10',
    observations: 'Cancelado mediante transferencia electrónica Banco Estado.'
  },
  {
    number: '#1897',
    date: '2026-07-06',
    clientName: 'ARTURO VELASQUEZ CAIPILLAN',
    workName: 'OBRA CASTRO',
    amount: 148750,
    status: 'Pagado',
    paymentDate: '2026-07-09',
    observations: 'Pago puntual.'
  },
  {
    number: '#1848',
    date: '2026-06-05',
    clientName: 'FEDIR CHILE SPA',
    workName: 'ESCUELA ANA NELLY OYARZUN',
    amount: 357000,
    status: 'Pagado',
    paymentDate: '2026-06-15',
    observations: 'Orden de compra cancelada a 30 días.'
  },
  {
    number: '#1891',
    date: '2026-07-02',
    clientName: 'SALMONES AYSEN S.A.',
    workName: 'SECTOR CURBITA',
    amount: 148750,
    status: 'Pagado',
    paymentDate: '2026-07-05',
    observations: 'Facturación automática.'
  },
  {
    number: '#1892',
    date: '2026-07-20',
    clientName: 'SALMONES AYSEN S.A.',
    workName: 'Baño sector Chulchuy',
    amount: 148750,
    status: 'Pagado',
    paymentDate: '2026-07-22'
  },
  {
    number: '#1893',
    date: '2026-07-02',
    clientName: 'SALMONES AYSEN S.A.',
    workName: 'PILLIHUE',
    amount: 148750,
    status: 'Pendiente',
    observations: 'Pendiente confirmación de tesorería.'
  },
  {
    number: '#1890',
    date: '2026-07-01',
    clientName: 'TORALLA S.A.',
    workName: 'OBRA CHONCHI',
    amount: 3617600,
    status: 'Pendiente',
    observations: 'Valor acumulado por 4 meses de servicios industriales.'
  },
  {
    number: '#1889',
    date: '2026-07-01',
    clientName: 'CONSTRUCTORA PUERTO OCTAY LTDA',
    workName: 'OBRA ACHAO',
    amount: 480760,
    status: 'Pendiente'
  },
  {
    number: '#1888',
    date: '2026-07-01',
    clientName: 'CONSTRUCTORA PUERTO OCTAY LTDA',
    workName: 'OBRA ACHAO',
    amount: 480760,
    status: 'Pendiente'
  },
  {
    number: '#1887',
    date: '2026-07-01',
    clientName: 'CONSTRUCTORA PUERTO OCTAY LTDA',
    workName: 'GM CASTRO - PENINSULA DE RILAN (OC 8060)',
    amount: 547400,
    status: 'Pendiente'
  }
];

export const INITIAL_CERTIFICATES: Certificate[] = [
  {
    number: 'CRT-06072026A3',
    clientName: 'CONSTRUCTORA PUERTO OCTAY LTDA',
    clientRut: '77.775.300-2',
    workName: 'GM CASTRO - PENINSULA DE RILAN (OC 8060)',
    serviceDate: 'Junio 2026',
    volumeM3: 15.5,
    observations: 'Aprobado para vertido autorizado por Seremi de Salud.'
  },
  {
    number: 'CRT-06072026A2',
    clientName: 'ARTURO VELASQUEZ CAIPILLAN',
    clientRut: '13.169.434-2',
    workName: 'OBRA CASTRO',
    serviceDate: 'Junio-Julio 2026',
    volumeM3: 8.0,
    observations: 'Succión estándar de cámaras.'
  },
  {
    number: 'CRT-06072026A1',
    clientName: 'CONSTRUCTORA SIERRA NEVADA S.A.',
    clientRut: '96.721.780-K',
    workName: 'OBRA CAMINO A CHONCHI - QUELLON',
    serviceDate: 'Junio 2026',
    volumeM3: 12.0
  },
  {
    number: 'CRT-02072026A3',
    clientName: 'SALMONES AYSEN S.A.',
    clientRut: '76.650.680-1',
    workName: 'SECTOR CURBITA',
    serviceDate: 'Junio 2026',
    volumeM3: 22.5,
    observations: 'Limpieza de lodos industriales en planta acuícola.'
  },
  {
    number: 'CRT-02072026A2',
    clientName: 'SALMONES AYSEN S.A.',
    clientRut: '76.650.680-1',
    workName: 'PILLIHUE',
    serviceDate: 'Junio 2026',
    volumeM3: 10.0
  },
  {
    number: 'CRT-02072026A1',
    clientName: 'SALMONES AYSEN S.A.',
    clientRut: '76.650.680-1',
    workName: 'Baño sector Chulchuy',
    serviceDate: 'Junio 2026',
    volumeM3: 6.0
  }
];

export const INITIAL_USERS: User[] = [
  {
    username: 'eruotolo',
    firstName: 'Edgardo',
    lastName: 'Ruotolo',
    email: 'edgardoruotolo@gmail.com',
    category: 'Administrador',
    avatarUrl: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=150&auto=format&fit=crop'
  },
  {
    username: 'jsanchez',
    firstName: 'Juan Manuel',
    lastName: 'Sánchez',
    email: 'jsanchez@expanda.cl',
    category: 'Supervisor',
    avatarUrl: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop'
  },
  {
    username: 'kimberling',
    firstName: 'Kimberling',
    lastName: 'Añez',
    email: 'administrasion@ratacop.cl',
    category: 'Operador',
    avatarUrl: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=150&auto=format&fit=crop'
  }
];

export const CHILE_REGIONES = [
  'Región de Arica y Parinacota',
  'Región de Tarapacá',
  'Región de Antofagasta',
  'Región de Atacama',
  'Región de Coquimbo',
  'Región de Valparaíso',
  'Región Metropolitana',
  'Región de O\'Higgins',
  'Región del Maule',
  'Región de Ñuble',
  'Región del Biobío',
  'Región de la Araucanía',
  'Región de los Ríos',
  'Región de Los Lagos',
  'Región de Aysén',
  'Región de Magallanes'
];

export const CHILE_COMUNAS_LOS_LAGOS = [
  'Ancud',
  'Castro',
  'Chonchi',
  'Curaco de Vélez',
  'Dalcahue',
  'Puqueldón',
  'Queilén',
  'Quellón',
  'Quemchi',
  'Quinchao',
  'Puerto Montt',
  'Osorno'
];
