import React, { useState, useEffect } from 'react';
import { 
  Activity, Users, MessageSquare, Database, BarChart3, 
  Settings, Key, AlertTriangle, ShieldCheck, Plus, Terminal 
} from 'lucide-react';
import { 
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer 
} from 'recharts';
import Toast from '@/components/ui/Toast';
import MasterNav from './components/MasterNav';
import DeployForm from './components/DeployForm';
import TenantList from './components/TenantList';

const masterData = window.MASTER_DATA || { 
  adminSession: '...', 
  stats: { totalTenants: 0, activeTenants: 0, totalInteractions: 0, totalServices: 0 },
  tenants: [], 
  auditLogs: [],
  growth: [],
  csrfToken: '' 
};

export default function MasterPage() {
  const [toast, setToast] = useState(null);
  const [tab, setTab] = useState('overview');

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
      if (params.get('status') === 'success') setToast({ title: 'Operation Successful', message: params.get('msg'), type: 'success' });
      else setToast({ title: 'Failed', message: params.get('msg'), type: 'error' });
      setTimeout(() => setToast(null), 5000);
      window.history.replaceState({}, document.title, 'master.php');
    }
  }, []);

  return (
    <div className="min-h-screen bg-[#050505] font-sans text-neutral-300 selection:bg-indigo-500/30">
      <Toast toast={toast} onClose={() => setToast(null)} />
      
      {/* Hyper-Admin Navbar */}
      <nav className="border-b border-white/5 bg-[#0A0A0A]/80 backdrop-blur-xl sticky top-0 z-50">
        <div className="max-w-[1400px] mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-6">
             <div className="flex items-center gap-2 pr-6 border-r border-white/10">
                <div className="w-8 h-8 rounded bg-white text-black flex items-center justify-center font-bold text-lg shadow-[0_0_15px_rgba(255,255,255,0.2)]">P</div>
                <span className="font-bold text-white tracking-tight hidden sm:block">Hyper-Admin <span className="text-indigo-400">Pasek</span></span>
             </div>
             
             <div className="flex items-center gap-1">
                <TabButton active={tab === 'overview'} onClick={() => setTab('overview')} icon={<Activity size={16} />} label="Overview" />
                <TabButton active={tab === 'infra'} onClick={() => setTab('infra')} icon={<Database size={16} />} label="Infrastructure" />
                <TabButton active={tab === 'audit'} onClick={() => setTab('audit')} icon={<Terminal size={16} />} label="Audit Logs" />
             </div>
          </div>
          
          <div className="flex items-center gap-4">
             <div className="text-right hidden sm:block">
                <p className="text-[10px] uppercase tracking-widest text-neutral-500 font-bold">Node Session</p>
                <p className="text-xs font-mono text-indigo-400">{masterData.adminSession}</p>
             </div>
             <div className="w-8 h-8 rounded-full bg-neutral-800 border border-white/10 flex items-center justify-center">
                <ShieldCheck size={16} className="text-emerald-400" />
             </div>
          </div>
        </div>
      </nav>

      <main className="max-w-[1400px] mx-auto px-6 py-10">
        
        {/* KPI Cards */}
        {tab === 'overview' && (
          <div className="space-y-10 reveal">
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
               <KPICard title="Total Tenants" value={masterData.stats.totalTenants} sub={`${masterData.stats.activeTenants} Active Nodes`} icon={<Users className="text-indigo-400" />} />
               <KPICard title="AI Interactions" value={masterData.stats.totalInteractions.toLocaleString()} sub="Global Queries" icon={<MessageSquare className="text-teal-400" />} />
               <KPICard title="Global Catalog" value={masterData.stats.totalServices.toLocaleString()} sub="Services Indexed" icon={<Database className="text-amber-400" />} />
               <KPICard title="System Status" value="Healthy" sub="Latency 120ms" icon={<Activity className="text-emerald-400" />} />
            </div>

            {/* Growth Analytics Section */}
            <div className="grid lg:grid-cols-12 gap-8">
               <div className="lg:col-span-8 bg-[#0A0A0A] border border-white/5 rounded-2xl p-6 shadow-2xl">
                  <div className="flex items-center justify-between mb-8">
                     <div>
                        <h3 className="text-white font-bold text-lg">Tenant Growth Trend</h3>
                        <p className="text-xs text-neutral-500">New registration trend in the last 14 days.</p>
                     </div>
                     <BarChart3 size={20} className="text-neutral-600" />
                  </div>
                  
                  <div className="h-[300px] w-full">
                     <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={masterData.growth}>
                           <defs>
                              <linearGradient id="colorCount" x1="0" y1="0" x2="0" y2="1">
                                 <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3}/>
                                 <stop offset="95%" stopColor="#6366f1" stopOpacity={0}/>
                              </linearGradient>
                           </defs>
                           <XAxis dataKey="date" stroke="#404040" fontSize={11} tickLine={false} axisLine={false} />
                           <YAxis hide />
                           <Tooltip contentStyle={{ backgroundColor: '#000', border: '1px solid #333', fontSize: '12px' }} />
                           <Area type="monotone" dataKey="count" stroke="#6366f1" strokeWidth={3} fillOpacity={1} fill="url(#colorCount)" />
                        </AreaChart>
                     </ResponsiveContainer>
                  </div>
               </div>

               <div className="lg:col-span-4 space-y-6">
                  <div className="bg-[#0A0A0A] border border-white/5 rounded-2xl p-6">
                     <h3 className="text-white font-bold text-sm mb-4 uppercase tracking-widest flex items-center gap-2">
                        <Plus size={14} className="text-indigo-400" /> Quick Deploy
                     </h3>
                     <DeployForm csrfToken={masterData.csrfToken} />
                  </div>
               </div>
            </div>
          </div>
        )}

        {/* Infrastructure Management Tab */}
        {tab === 'infra' && (
           <div className="reveal">
              <div className="flex items-center justify-between mb-8">
                 <div>
                    <h2 className="text-2xl font-bold text-white tracking-tight">Infrastructure Management</h2>
                    <p className="text-sm text-neutral-500 mt-1">Manage tenant lifecycle and capacity limits.</p>
                 </div>
                 <div className="flex items-center gap-3">
                    <div className="px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center gap-2">
                       <div className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" /> Platform Online
                    </div>
                 </div>
              </div>
              
              <div className="bg-[#0A0A0A] border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
                 <TenantList tenants={masterData.tenants} totalNodes={masterData.stats.totalTenants} csrfToken={masterData.csrfToken} />
              </div>
           </div>
        )}

        {/* Audit Logs Tab */}
        {tab === 'audit' && (
           <div className="reveal space-y-6">
              <div className="flex items-center justify-between">
                 <h2 className="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                    <Terminal size={24} className="text-indigo-400" /> System Audit Trail
                 </h2>
                 <button className="text-xs text-neutral-500 hover:text-white transition-colors">Export Logs (JSON)</button>
              </div>

              <div className="bg-[#0A0A0A] border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
                 <table className="w-full text-left border-collapse">
                    <thead>
                       <tr className="bg-white/[0.02] border-b border-white/5">
                          <th className="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-neutral-500">Timestamp</th>
                          <th className="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-neutral-500">Action</th>
                          <th className="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-neutral-500">Entity</th>
                          <th className="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-neutral-500">Details</th>
                          <th className="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-neutral-500">Origin</th>
                       </tr>
                    </thead>
                    <tbody className="divide-y divide-white/5">
                       {masterData.auditLogs.map(log => (
                          <tr key={log.id} className="hover:bg-white/[0.01] transition-colors group">
                             <td className="px-6 py-4 text-[11px] font-mono text-neutral-500">{log.date}</td>
                             <td className="px-6 py-4">
                                <span className={`px-2 py-1 rounded text-[10px] font-bold ${
                                   log.type === 'CREATE' ? 'bg-emerald-500/10 text-emerald-400' :
                                   log.type === 'DELETE' ? 'bg-red-500/10 text-red-400' :
                                   log.type === 'IMPERSONATE' ? 'bg-amber-500/10 text-amber-400' :
                                   'bg-indigo-500/10 text-indigo-400'
                                }`}>
                                   {log.type}
                                </span>
                             </td>
                             <td className="px-6 py-4 text-xs font-semibold text-neutral-300">{log.entity} <span className="text-neutral-600">#{log.entityId}</span></td>
                             <td className="px-6 py-4 text-xs text-neutral-400 max-w-xs truncate">{log.details}</td>
                             <td className="px-6 py-4 text-[10px] font-mono text-neutral-600">{log.ipAddress}</td>
                          </tr>
                       ))}
                    </tbody>
                 </table>
              </div>
           </div>
        )}

      </main>

      <footer className="mt-8 pb-12 text-center reveal delay-500">
        <p className="text-[10px] text-neutral-600 uppercase tracking-[0.4em] font-medium flex items-center justify-center gap-2">
          <Activity size={10} className="text-indigo-900" /> Pasek Hyper-Admin Infrastructure v3.1.2 
        </p>
      </footer>
    </div>
  );
}

function TabButton({ active, onClick, icon, label }) {
   return (
      <button onClick={onClick} className={`px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-all ${
         active ? 'bg-white/10 text-white' : 'text-neutral-500 hover:text-white hover:bg-white/5'
      }`}>
         {icon} {label}
      </button>
   );
}

function KPICard({ title, value, sub, icon }) {
   return (
      <div className="bg-[#0A0A0A] border border-white/5 p-6 rounded-2xl shadow-xl hover:border-white/10 transition-colors group">
         <div className="flex items-center justify-between mb-4">
            <span className="text-[10px] uppercase font-bold tracking-widest text-neutral-500 group-hover:text-neutral-400 transition-colors">{title}</span>
            <div className="p-2 rounded-lg bg-white/5">{icon}</div>
         </div>
         <div className="text-3xl font-display font-bold text-white mb-1">{value}</div>
         <div className="text-xs text-neutral-600 font-medium">{sub}</div>
      </div>
   );
}

