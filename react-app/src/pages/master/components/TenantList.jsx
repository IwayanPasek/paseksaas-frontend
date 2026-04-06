import React from 'react';
import { motion } from 'framer-motion';
import { Server, ExternalLink, MessageCircle, Check, X, Clock, Mail } from 'lucide-react';

const csrfToken = window.MASTER_DATA?.csrf_token || '';

export default function TenantList({ tenants, totalNodes }) {
  const handleAction = (id, action) => {
    if (!window.confirm(`Apakah Anda yakin ingin ${action === 'approve' ? 'menyetujui' : 'menghapus'} pendaftaran ini?`)) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'master.php';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = action === 'approve' ? 'approve_tenant' : 'reject_tenant';
    actionInput.value = id;
    form.appendChild(actionInput);
    
    document.body.appendChild(form);
    form.submit();
  };

  return (
    <div>
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-end mb-6 gap-3">
        <h2 className="text-xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
          Cloud <span className="text-neutral-400 font-normal">Instances</span>
        </h2>
        <div className="bg-success-50 border border-success-100 px-3 py-1.5 rounded-lg text-[11px] font-medium text-success-600 tracking-wider flex items-center gap-1.5 w-fit">
          <span className="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse" /> {totalNodes} NODES IN NETWORK
        </div>
      </div>

      <div className="space-y-3">
        {tenants.length === 0 ? (
          <div className="text-center py-16 border-2 border-dashed border-neutral-200 rounded-xl bg-white">
            <Server className="mx-auto text-neutral-300 mb-3" size={40} />
            <p className="text-neutral-400 font-medium text-sm">Belum ada tenant (node) yang dideploy di jaringan.</p>
          </div>
        ) : (
          tenants.map((t, idx) => (
            <motion.div initial={{ opacity: 0, x: 16 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: idx * 0.04 }}
              key={t.id_toko} className={`card rounded-xl p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group transition-all border-l-4 ${t.status === 'pending' ? 'border-l-amber-400 bg-amber-50/30' : 'border-l-blue-500 bg-white'}`}>
              
              <div className="flex items-center gap-3 md:gap-4 flex-1 min-w-0">
                <div className={`w-10 h-10 md:w-11 md:h-11 rounded-xl flex items-center justify-center font-semibold text-base shrink-0 transition-colors ${t.status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-neutral-100 text-neutral-500 group-hover:bg-neutral-900 group-hover:text-white'}`}>
                  {t.nama_toko.substring(0, 1).toUpperCase()}
                </div>
                <div className="min-w-0">
                  <div className="flex items-center gap-2 mb-0.5">
                    <h3 className="font-semibold text-neutral-900 text-sm md:text-base truncate">{t.nama_toko}</h3>
                    {t.status === 'pending' && (
                        <span className="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[9px] font-bold rounded uppercase tracking-wider flex items-center gap-1">
                            <Clock size={8} /> PENDING
                        </span>
                    )}
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-xs text-neutral-400 font-mono truncate">{t.subdomain}.websitewayan.my.id</span>
                    {t.email && (
                        <span className="text-[10px] text-neutral-400 flex items-center gap-1">
                            <Mail size={10} /> {t.email}
                        </span>
                    )}
                  </div>
                </div>
              </div>

              <div className="flex gap-2 shrink-0">
                {t.status === 'pending' ? (
                    <>
                        <button onClick={() => handleAction(t.id_toko, 'approve')} className="h-9 px-3 rounded-lg bg-success-500 text-white flex items-center justify-center gap-1.5 text-xs font-bold hover:bg-success-600 transition-all shadow-sm shadow-success-200">
                           <Check size={14} /> APPROVE
                        </button>
                        <button onClick={() => handleAction(t.id_toko, 'reject')} className="w-9 h-9 rounded-lg bg-white border border-red-200 flex items-center justify-center text-red-400 hover:text-red-500 hover:bg-red-50 hover:border-red-300 transition-all" title="Reject">
                           <X size={15} />
                        </button>
                    </>
                ) : (
                    <>
                        <a href={`https://wa.me/${t.kontak_wa}`} target="_blank" rel="noreferrer" className="w-9 h-9 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400 hover:text-success-500 hover:bg-success-50 hover:border-success-100 transition-all" title="WhatsApp">
                        <MessageCircle size={15} />
                        </a>
                        <a href={`https://${t.subdomain}.websitewayan.my.id`} target="_blank" rel="noreferrer" className="w-9 h-9 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400 hover:text-neutral-900 hover:bg-neutral-200 transition-all" title="Visit">
                        <ExternalLink size={15} />
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
