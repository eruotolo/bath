export type ClientStatus = 'Activo' | 'Inactivo';

export interface Client {
  id: string;
  rut: string;
  name: string;
  phone: string;
  email: string;
  address: string;
  region: string;
  city: string;
  comuna: string;
  status: ClientStatus;
  createdAt: string;
}

export type ToiletStatus = 'Activo' | 'Inactivo';
export type AllocationStatus = 'Disponible' | 'Asignado';

export interface Toilet {
  code: string;
  purchaseDate: string;
  observations: string;
  status: ToiletStatus;
  allocation: AllocationStatus;
  assignedWork?: string;
  assignedClient?: string;
}

export type ContractStatus = 'Activo' | 'Terminado';

export interface Contract {
  id: string;
  clientName: string;
  workName: string;
  status: ContractStatus;
  startDate: string;
  endDate: string;
  monthlyValue: number;
  totalValue: number;
  address: string;
  observations: string;
  assignedToilets: string[]; // Toilet codes
}

export type InvoiceStatus = 'Pagado' | 'Pendiente';

export interface Invoice {
  number: string;
  date: string;
  clientName: string;
  workName: string;
  amount: number;
  status: InvoiceStatus;
  paymentDate?: string;
  observations?: string;
}

export type ServiceType = 
  | 'Instalación' 
  | 'Reparación' 
  | 'Limpieza' 
  | 'Desinfección' 
  | 'Sanitización' 
  | 'Entrega Papel Higiénico' 
  | 'Entrega de Jabón Líquido' 
  | 'Retiro de Baños'
  | 'Otros';

export interface ServiceFollowUp {
  id: string;
  clientName: string;
  workName: string;
  isInvoiced: boolean;
  invoiceNumber?: string;
  date: string;
  types: ServiceType[];
  observations?: string;
  status: 'Completado' | 'Pendiente';
}

export interface Certificate {
  number: string;
  clientName: string;
  clientRut: string;
  workName: string;
  serviceDate: string;
  volumeM3: number; // Cubic meters of suction/disposal
  observations?: string;
}

export type UserCategory = 'Administrador' | 'Supervisor' | 'Operador';

export interface User {
  username: string;
  firstName: string;
  lastName: string;
  email: string;
  category: UserCategory;
  avatarUrl?: string;
}

export type ViewType = 
  | 'tablero'
  | 'clientes'
  | 'baños'
  | 'contratos'
  | 'seguimientos'
  | 'facturas'
  | 'certificados'
  | 'usuarios';
