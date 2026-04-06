import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Server, Globe, Lock, Phone, Database, Cpu, Plus } from 'lucide-react';

export default function DeployForm({ csrfToken }) {
  const [isSubmitting, setIsSubmitting] = useState(false);

  return (
    <div>
      <div className="mb-6">
        <h2 className="text-2xl md:text-3xl font-bold text-neutral-900 mb-2 tracking-tight">Deploy New <span className="text-neutral-400">Tenant</span></h2>
        <p className="text-neutral-500 text-sm leading-relaxed">Inisialisasi subdomain baru untuk mengaktifkan instance AI Smart Shop.</p>
      </div>

      <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} className="card rounded-xl p-5 md:p-6">
        <form method="POST" action="master.php" onSubmit={() => setIsSubmitting(true)} className="space-y-5">
          <input type="hidden" name="register_tenant" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />

          <div className="space-y-3">
            <div className="flex items-center gap-1.5 text-[10px] font-medium text-neutral-400 uppercase tracking-widest ml-0.5"><Database size={11} /> Business Identity</div>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><Server size={14} className="text-neutral-300" /></div>
              <input type="text" name="nama_toko" required placeholder="Nama Toko (Misal: Laundry Kilat)" className="w-full bg-neutral-50 border border-neutral-200 rounded-xl py-3 pl-10 pr-3.5 text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-400 outline-none transition-all" />
            </div>
            <div className="flex items-center bg-neutral-50 border border-neutral-200 rounded-xl focus-within:border-neutral-400 transition-all overflow-hidden">
              <div className="pl-3.5 flex items-center pointer-events-none"><Globe size={14} className="text-neutral-300" /></div>
              <input type="text" name="subdomain" required placeholder="subdomain" className="bg-transparent border-none focus:ring-0 text-sm flex-1 py-3 px-2.5 outline-none text-neutral-900 placeholder-neutral-400 lowercase" />
              <div className="pr-3.5 py-3 bg-neutral-100 border-l border-neutral-200"><span className="text-neutral-400 text-xs font-medium px-2">.websitewayan.my.id</span></div>
            </div>
          </div>

          <div className="space-y-3">
            <div className="flex items-center gap-1.5 text-[10px] font-medium text-neutral-400 uppercase tracking-widest ml-0.5"><Lock size={11} /> Access Control</div>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><Lock size={14} className="text-neutral-300" /></div>
              <input type="password" name="password_toko" required placeholder="Set Password Admin Toko" className="w-full bg-neutral-50 border border-neutral-200 rounded-xl py-3 pl-10 pr-3.5 text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-400 outline-none transition-all" />
            </div>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><Phone size={14} className="text-neutral-300" /></div>
              <input type="text" name="kontak_wa" required placeholder="6281234567890 (WhatsApp)" className="w-full bg-neutral-50 border border-neutral-200 rounded-xl py-3 pl-10 pr-3.5 text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-400 outline-none transition-all" />
            </div>
          </div>

          <div className="space-y-3">
            <div className="flex items-center gap-1.5 text-[10px] font-medium text-neutral-400 uppercase tracking-widest ml-0.5"><Cpu size={11} /> AI Knowledge Base</div>
            <textarea name="knowledge_base" required rows="3" placeholder="Deskripsi umum toko agar AI memahami konteks bisnis..." className="w-full bg-neutral-50 border border-neutral-200 rounded-xl p-3.5 text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-400 outline-none transition-all resize-none leading-relaxed" />
          </div>

          <button type="submit" disabled={isSubmitting} className="w-full bg-neutral-900 hover:bg-neutral-800 text-white font-semibold text-sm py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed mt-2 active:scale-[0.98]">
            {isSubmitting ? <span className="animate-pulse">INITIALIZING...</span> : <><Plus size={16} strokeWidth={2.5} /> INITIALIZE INSTANCE</>}
          </button>
        </form>
      </motion.div>
    </div>
  );
}
