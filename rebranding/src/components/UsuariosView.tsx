import React, { useState } from 'react';
import { 
  Plus, 
  Search, 
  Users, 
  X, 
  UserPlus, 
  Mail, 
  ShieldCheck, 
  Lock, 
  User,
  Key,
  Trash2
} from 'lucide-react';
import { User as UserType, UserCategory } from '../types';

interface UsuariosViewProps {
  users: UserType[];
  onAddUser: (newUser: UserType) => void;
  onDeleteUser: (username: string) => void;
  searchTerm: string;
}

export default function UsuariosView({ users, onAddUser, onDeleteUser, searchTerm }: UsuariosViewProps) {
  const [localSearch, setLocalSearch] = useState('');
  const [isAddingUser, setIsAddingUser] = useState(false);

  // Form states
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [category, setCategory] = useState<UserCategory>('Operador');

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!username || !password || !firstName || !lastName || !email) {
      alert('Por favor complete todos los campos obligatorios.');
      return;
    }

    if (password !== confirmPassword) {
      alert('Las contraseñas no coinciden.');
      return;
    }

    if (users.some(u => u.username.toLowerCase() === username.toLowerCase())) {
      alert('Este nombre de usuario ya está registrado.');
      return;
    }

    const newUser: UserType = {
      username: username.toLowerCase().trim(),
      firstName: firstName.trim(),
      lastName: lastName.trim(),
      email: email.trim(),
      category,
      avatarUrl: `https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=150&auto=format&fit=crop` // random mockup avatar
    };

    onAddUser(newUser);
    setIsAddingUser(false);

    // Reset fields
    setUsername('');
    setPassword('');
    setConfirmPassword('');
    setFirstName('');
    setLastName('');
    setEmail('');
    setCategory('Operador');
  };

  const combinedSearch = (searchTerm || localSearch).toLowerCase();

  const filteredUsers = users.filter(u => 
    u.username.toLowerCase().includes(combinedSearch) ||
    u.firstName.toLowerCase().includes(combinedSearch) ||
    u.lastName.toLowerCase().includes(combinedSearch) ||
    u.email.toLowerCase().includes(combinedSearch) ||
    u.category.toLowerCase().includes(combinedSearch)
  );

  return (
    <div className="space-y-6">
      
      {/* Top search controls */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Buscar por usuario, nombre, rol..."
            value={localSearch}
            onChange={(e) => setLocalSearch(e.target.value)}
            id="usuarios-local-search"
            className="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
          />
        </div>

        <button
          onClick={() => setIsAddingUser(true)}
          className="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans text-xs font-semibold flex items-center justify-center space-x-1.5 shadow-lg shadow-indigo-600/10 transition-all active:scale-95"
          id="new-user-btn"
        >
          <UserPlus className="w-3.5 h-3.5" />
          <span>Crear Nuevo Usuario</span>
        </button>
      </div>

      {/* Grid layout cards (beautiful visual UX revamp) */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredUsers.map((u) => (
          <div 
            key={u.username}
            className="p-6 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group flex flex-col justify-between"
          >
            {/* Visual background element */}
            <div className="absolute top-0 right-0 w-24 h-24 bg-slate-50 rounded-bl-full -z-10 group-hover:scale-105 transition-transform" />

            {/* Top Profile block */}
            <div className="flex items-start space-x-4">
              <img
                src={u.avatarUrl || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=150&auto=format&fit=crop'}
                alt={u.username}
                referrerPolicy="no-referrer"
                className="w-14 h-14 rounded-2xl object-cover ring-4 ring-indigo-50 shadow"
              />
              <div className="space-y-0.5">
                <span className={`px-2 py-0.5 rounded-lg text-[9px] font-bold font-sans tracking-wide uppercase inline-block ${u.category === 'Administrador' ? 'bg-rose-50 text-rose-700 border border-rose-100' : u.category === 'Supervisor' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-slate-50 text-slate-700 border border-slate-100'}`}>
                  {u.category}
                </span>
                <h3 className="font-sans font-extrabold text-slate-900 text-sm leading-tight mt-1">{u.firstName} {u.lastName}</h3>
                <span className="font-mono text-[10px] text-slate-400 block">@{u.username}</span>
              </div>
            </div>

            {/* Middle email contact */}
            <div className="my-5 flex items-center space-x-2 text-xs font-sans text-slate-500">
              <Mail className="w-4 h-4 text-slate-400 shrink-0" />
              <span className="truncate max-w-[180px]">{u.email}</span>
            </div>

            {/* Footer Admin Actions */}
            <div className="pt-3 border-t border-slate-50 flex items-center justify-between text-[11px] font-sans">
              <span className="text-indigo-600 font-bold flex items-center">
                <ShieldCheck className="w-3.5 h-3.5 text-indigo-500 mr-1 shrink-0" />
                <span>Acceso Autorizado</span>
              </span>

              {/* Prevent deleting own core user */}
              {u.username !== 'eruotolo' ? (
                <button
                  onClick={() => {
                    if (confirm(`¿Está seguro de eliminar al usuario @${u.username}? Se revocarán sus credenciales de inmediato.`)) {
                      onDeleteUser(u.username);
                    }
                  }}
                  className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                  title="Eliminar Cuenta"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              ) : (
                <span className="font-mono text-[9px] text-slate-400 font-bold uppercase tracking-wider">SuperUser</span>
              )}
            </div>
          </div>
        ))}
        {filteredUsers.length === 0 && (
          <div className="col-span-3 p-10 text-center text-slate-400 font-sans text-sm">
            No se registran usuarios con este filtro.
          </div>
        )}
      </div>

      {/* Slide Drawer: REGISTER NEW USER & SET CREDENTIALS */}
      {isAddingUser && (
        <>
          <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" onClick={() => setIsAddingUser(false)} />
          <div className="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                  <UserPlus className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-sans font-bold text-slate-900 text-sm">Crear Cuenta de Operador</h3>
                  <span className="text-[10px] font-sans text-slate-400 block mt-0.5">Registrar credenciales y asignar categoría.</span>
                </div>
              </div>
              <button onClick={() => setIsAddingUser(false)} className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="flex-1 p-6 space-y-4 overflow-y-auto">
              
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Nombre <span className="text-rose-500">*</span></label>
                  <input
                    type="text"
                    placeholder="e.g. Esteban"
                    value={firstName}
                    onChange={(e) => setFirstName(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                    required
                  />
                </div>
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Apellido <span className="text-rose-500">*</span></label>
                  <input
                    type="text"
                    placeholder="e.g. Ruiz"
                    value={lastName}
                    onChange={(e) => setLastName(e.target.value)}
                    className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                    required
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Nombre de Usuario (Acceso) <span className="text-rose-500">*</span></label>
                <div className="relative">
                  <User className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    placeholder="e.g. eruiz"
                    value={username}
                    onChange={(e) => setUsername(e.target.value)}
                    className="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                    required
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Email <span className="text-rose-500">*</span></label>
                <input
                  type="email"
                  placeholder="e.g. esteban.ruiz@blanco.cl"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                  required
                />
              </div>

              <div className="space-y-1.5">
                <label className="font-sans text-xs font-bold text-slate-600 block">Categoría de Acceso</label>
                <select
                  value={category}
                  onChange={(e) => setCategory(e.target.value as UserCategory)}
                  className="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                >
                  <option value="Operador">Operador (Visualización y visitas de ruta)</option>
                  <option value="Supervisor">Supervisor (Control de stock y contratos)</option>
                  <option value="Administrador">Administrador (Control total financiero/personal)</option>
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Contraseña <span className="text-rose-500">*</span></label>
                  <div className="relative">
                    <Lock className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                      type="password"
                      placeholder="••••••••"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                      required
                    />
                  </div>
                </div>
                <div className="space-y-1.5">
                  <label className="font-sans text-xs font-bold text-slate-600 block">Repetir Contraseña <span className="text-rose-500">*</span></label>
                  <div className="relative">
                    <Key className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                      type="password"
                      placeholder="••••••••"
                      value={confirmPassword}
                      onChange={(e) => setConfirmPassword(e.target.value)}
                      className="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                      required
                    />
                  </div>
                </div>
              </div>

              <div className="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsAddingUser(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  id="submit-new-user"
                  className="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-semibold font-sans hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/10"
                >
                  Registrar Personal
                </button>
              </div>
            </form>
          </div>
        </>
      )}

    </div>
  );
}
