import React, { useState } from 'react';
import Sidebar from './components/Sidebar';
import Header from './components/Header';
import TableroView from './components/TableroView';
import ClientesView from './components/ClientesView';
import BanosView from './components/BanosView';
import ContratosView from './components/ContratosView';
import ServiciosView from './components/ServiciosView';
import FacturasView from './components/FacturasView';
import CertificadosView from './components/CertificadosView';
import UsuariosView from './components/UsuariosView';
import { ViewType, Client, Toilet, Contract, ServiceFollowUp, Invoice, User } from './types';
import { 
  INITIAL_CLIENTS, 
  INITIAL_TOILETS, 
  INITIAL_CONTRACTS, 
  INITIAL_SERVICES, 
  INITIAL_INVOICES, 
  INITIAL_CERTIFICATES, 
  INITIAL_USERS 
} from './data';

export default function App() {
  const [currentView, setView] = useState<ViewType>('tablero');
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [globalSearch, setGlobalSearch] = useState('');

  // Core application states (acting as a responsive client-side database)
  const [clients, setClients] = useState<Client[]>(INITIAL_CLIENTS);
  const [toilets, setToilets] = useState<Toilet[]>(INITIAL_TOILETS);
  const [contracts, setContracts] = useState<Contract[]>(INITIAL_CONTRACTS);
  const [services, setServices] = useState<ServiceFollowUp[]>(INITIAL_SERVICES);
  const [invoices, setInvoices] = useState<Invoice[]>(INITIAL_INVOICES);
  const [certificates, setCertificates] = useState(INITIAL_CERTIFICATES);
  const [users, setUsers] = useState<User[]>(INITIAL_USERS);

  // Default logged in administrator user
  const currentUser = users[0];

  // Actions handlers
  const handleAddClient = (newClient: Client) => {
    setClients([newClient, ...clients]);
  };

  const handleAddToilet = (newToilet: Toilet) => {
    setToilets([newToilet, ...toilets]);
  };

  const handleUpdateToiletList = (updatedToilets: Toilet[]) => {
    setToilets(updatedToilets);
  };

  const handleAddContract = (newContract: Contract) => {
    setContracts([newContract, ...contracts]);
  };

  const handleAddService = (newService: ServiceFollowUp) => {
    setServices([newService, ...services]);
    
    // Also simulate creating a corresponding Certificate if suction types are performed!
    if (newService.types.includes('Limpieza') || newService.types.includes('Sanitización')) {
      const isPuertoOctay = newService.clientName.includes('PUERTO OCTAY');
      const mockVol = isPuertoOctay ? 6.5 : 4.0;
      
      const newCert = {
        number: `CRT-${Date.now().toString().slice(-6)}`,
        clientName: newService.clientName,
        clientRut: clients.find(c => c.name === newService.clientName)?.rut || '12.345.678-9',
        workName: newService.workName,
        serviceDate: newService.date,
        volumeM3: mockVol,
        observations: `Generado automáticamente tras servicio completado ${newService.id}`
      };
      setCertificates([newCert, ...certificates]);
    }
  };

  const handleAddInvoices = (newInvoices: Invoice[]) => {
    setInvoices([...newInvoices, ...invoices]);
  };

  const handleUpdateInvoiceStatus = (invoiceNumber: string, status: 'Pagado') => {
    setInvoices(invoices.map(i => {
      if (i.number === invoiceNumber) {
        return {
          ...i,
          status,
          paymentDate: new Date().toISOString().split('T')[0]
        };
      }
      return i;
    }));
  };

  const handleAddUser = (newUser: User) => {
    setUsers([...users, newUser]);
  };

  const handleDeleteUser = (username: string) => {
    setUsers(users.filter(u => u.username !== username));
  };

  // Render view router
  const renderActiveView = () => {
    switch (currentView) {
      case 'tablero':
        return (
          <TableroView 
            toilets={toilets}
            clients={clients}
            contracts={contracts}
            services={services}
            invoices={invoices}
            setView={setView}
          />
        );
      case 'clientes':
        return (
          <ClientesView 
            clients={clients}
            contracts={contracts}
            toilets={toilets}
            onAddClient={handleAddClient}
            searchTerm={globalSearch}
          />
        );
      case 'baños':
        return (
          <BanosView 
            toilets={toilets}
            clients={clients}
            contracts={contracts}
            onAddToilet={handleAddToilet}
            onUpdateToilet={handleUpdateToiletList}
            searchTerm={globalSearch}
          />
        );
      case 'contratos':
        return (
          <ContratosView 
            contracts={contracts}
            clients={clients}
            toilets={toilets}
            onAddContract={handleAddContract}
            onUpdateToilet={handleUpdateToiletList}
            searchTerm={globalSearch}
          />
        );
      case 'seguimientos':
        return (
          <ServiciosView 
            services={services}
            contracts={contracts}
            onAddService={handleAddService}
            searchTerm={globalSearch}
          />
        );
      case 'facturas':
        return (
          <FacturasView 
            invoices={invoices}
            clients={clients}
            contracts={contracts}
            onAddInvoice={handleAddInvoices}
            onUpdateInvoiceStatus={handleUpdateInvoiceStatus}
            searchTerm={globalSearch}
          />
        );
      case 'certificados':
        return (
          <CertificadosView 
            certificates={certificates}
            searchTerm={globalSearch}
          />
        );
      case 'usuarios':
        return (
          <UsuariosView 
            users={users}
            onAddUser={handleAddUser}
            onDeleteUser={handleDeleteUser}
            searchTerm={globalSearch}
          />
        );
      default:
        return <TableroView toilets={toilets} clients={clients} contracts={contracts} services={services} invoices={invoices} setView={setView} />;
    }
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden bg-slate-50/50" id="app-root">
      {/* Dynamic Sidebar navigation */}
      <Sidebar 
        currentView={currentView}
        setView={(view) => {
          setView(view);
          setGlobalSearch(''); // Reset search on route change
        }}
        isOpen={sidebarOpen}
        setIsOpen={setSidebarOpen}
      />

      {/* Main content frame */}
      <div className="flex-1 flex flex-col h-full overflow-hidden">
        <Header 
          currentView={currentView}
          sidebarOpen={sidebarOpen}
          setSidebarOpen={setSidebarOpen}
          currentUser={currentUser}
          onSearch={setGlobalSearch}
        />

        {/* Scrollable View Panel */}
        <main className="flex-1 overflow-y-auto px-6 py-8">
          <div className="max-w-7xl mx-auto">
            {renderActiveView()}
          </div>
        </main>
      </div>
    </div>
  );
}
