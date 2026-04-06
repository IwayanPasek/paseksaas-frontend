import React from 'react';
import { motion } from 'framer-motion';
import { 
  Server, ExternalLink, MessageCircle, Check, X, 
  Clock, ShieldAlert, LogIn, Trash2, ShieldCheck, UserCog 
} from 'lucide-react';

export default function TenantList({ tenants, totalNodes, csrfToken }) {
  const handleAction = (id, action, name = '') => {
    const labels = {
      approve: 'menyetuju Pendaftaran',
      reject: 'menghapus permanen',
      suspend: 'menonaktifkan (SUSPEND)',
      unsuspend: 'mengaktifkan kembali'
    };

    if (!window.confirm(`Konfirmasi: Apakah Anda yakin ingin ${labels[action] || action} untuk ${name}?`)) return;
    
    if (action === 'impersonate') {
       submitImpersonate(id);
       return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'master.php';
    
    const inputs = {
       '_csrf_token': csrfToken,
       [`${action}_tenant`]: id
    };

    Object.entries(inputs).forEach(([k, v]) => {
       const inp = document.createElement('input');
       inp.type = 'hidden'; inp.name = k; inp.value = v;
       form.appendChild(inp);
    });
    
    document.body.appendChild(form);
    form.submit();
  };

  const submitImpersonate = async (id) => {
     const fd = new FormData();
     fd.append('_csrf_token', csrfToken);
     fd.append('impersonate_tenant', id);

     try {
        const res = await fetch('master.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.status === 'success') {
           window.open(`/login.php?impersonate_token=${data.token}`, '_blank');
        } else {
           alert(data.message || 'Gagal membuat token impersonasi.');
        }
     } catch (e) {
        alert('Gagal menyambung ke server.');
     }
  };

  return (
    <div className="text-neutral-300">
      <div className="bg-white/[0.02] px-6 py-4 border-b border-white/5 flex items-center justify-between">
         <span className="text-xs font-bold uppercase tracking-widest text-neutral-500">{totalNodes} Managed Nodes</span>
         <div className="flex items-center gap-4 text-[10px] font-bold uppercase tracking-wider text-neutral-600">
            <span className="flex items-center gap-1.5"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Active</span>
            <span className="flex items-center gap-1.5"><div className="w-1.5 h-1.5 rounded-full bg-amber-500" /> Pending</span>
            <span className="flex items-center gap-1.5"><div className="w-1.5 h-1.5 rounded-full bg-red-500" /> Suspended</span>
         </div>
      </div>

      <div className="divide-y divide-white/5">
        {tenants.length === 0 ? (
          <div className="text-center py-20">
            <Server className="mx-auto text-neutral-800 mb-4" size={48} strokeWidth={1} />
            <p className="text-neutral-500 text-sm italic">Infrastructure is empty. Deploy your first node.</p>
          </div>
        ) : (
          tenants.map((t, idx) => (
            <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: idx * 0.03 }}
              key={t.id_toko} className="px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-white/[0.01] transition-colors group">
              
              <div className="flex items-center gap-5 flex-1 min-w-0">
                <div className={`w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 border transition-all ${
                   t.status === 'pending' ? 'bg-amber-500/10 border-amber-500/20 text-amber-500' :
                   t.status === 'suspended' ? 'bg-red-500/10 border-red-500/20 text-red-500' :
                   'bg-indigo-500/10 border-indigo-500/20 text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white'
                }`}>
                  {t.nama_toko.substring(0, 1).toUpperCase()}
                </div>
                
                <div className="min-w-0">
                  <div className="flex items-center gap-3 mb-1">
                    <h3 className="font-bold text-white text-base truncate">{t.nama_toko}</h3>
                    <span className={`px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${
                       t.status === 'pending' ? 'bg-amber-500/10 text-amber-500' :
                       t.status === 'suspended' ? 'bg-red-500/10 text-red-500' :
                       'bg-blue-500/10 text-blue-400'
                    }`}>
                       {t.status}
                    </span>
                  </div>
                  <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span className="text-[11px] text-neutral-500 font-mono flex items-center gap-1.5 hover:text-indigo-400 cursor-pointer">
                       <ExternalLink size={10} /> {t.subdomain}.websitewayan.my.id
                    </span>
                    <span className="text-[10px] text-neutral-600 flex items-center gap-1.5">
                       <Clock size={10} /> Deployed {new Date(t.created_at).toLocaleDateString()}
                    </span>
                  </div>
                </div>
              </div>

              <div className="flex items-center gap-2">
                {t.status === 'pending' ? (
                    <>
                        <ActionButton icon={<Check size={14} />} label="APPROVE" color="emerald" onClick={() => handleAction(t.id_toko, 'approve', t.nama_toko)} />
                        <ActionButton icon={<Trash2 size={14} />} label="REJECT" color="red" onClick={() => handleAction(t.id_toko, 'reject', t.nama_toko)} />
                    </>
                ) : (
                    <>
                        <ActionButton icon={<UserCog size={14} />} label="LOGIN AS" color="indigo" onClick={() => handleAction(t.id_toko, 'impersonate')} />
                        
                        {t.status === 'active' ? (
                           <ActionButton icon={<ShieldAlert size={14} />} label="SUSPEND" color="red" onClick={() => handleAction(t.id_toko, 'suspend', t.nama_toko)} />
                        ) : (
                           <ActionButton icon={<ShieldCheck size={14} />} label="UNSUSPEND" color="emerald" onClick={() => handleAction(t.id_toko, 'unsuspend', t.nama_toko)} />
                        )}

                        <div className="w-px h-6 bg-white/5 mx-1" />
                        
                        <a href={`https://wa.me/${t.kontak_wa.replace(/\*/g,'')}`} target="_blank" rel="noreferrer" className="p-2.5 rounded-lg bg-white/5 text-neutral-500 hover:text-emerald-400 hover:bg-emerald-400/10 transition-all" title="WhatsApp Support">
                           <MessageCircle size={16} />
                        </a>
                        <a href={`https://${t.subdomain}.websitewayan.my.id`} target="_blank" rel="noreferrer" className="p-2.5 rounded-lg bg-white/5 text-neutral-500 hover:text-white hover:bg-white/10 transition-all" title="Visit Instance">
                           <ExternalLink size={16} />
                        </a>
                    </>
                )}
              </div>
            </motion.div>
          ))
        )}
      </div>
    </div>
  );
}

function ActionButton({ icon, label, color, onClick }) {
   const colors = {
      emerald: 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white',
      red: 'bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white',
      indigo: 'bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white',
   };

   return (
      <button onClick={onClick} className={`h-9 px-3 rounded-lg flex items-center justify-center gap-2 text-[10px] font-bold tracking-wider transition-all ${colors[color]}`}>
         {icon} {label}
      </button>
   );
}

