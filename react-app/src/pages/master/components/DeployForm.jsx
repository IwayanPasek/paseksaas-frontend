import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Server, Globe, Lock, Phone, Database, Cpu, Plus } from 'lucide-react';

export default function DeployForm({ csrfToken }) {
  const [isSubmitting, setIsSubmitting] = useState(false);

  return (
    <div className="text-neutral-300">
      <form method="POST" action="master.php" onSubmit={() => setIsSubmitting(true)} className="space-y-6">
        <input type="hidden" name="register_tenant" value="1" />
        <input type="hidden" name="_csrf_token" value={csrfToken} />

        <div className="space-y-4">
          <div className="relative group">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-400"><Server size={14} className="text-neutral-600" /></div>
            <input type="text" name="nama_toko" required placeholder="Business Name" className="w-full bg-white/[0.03] border border-white/5 rounded-xl py-3 pl-10 pr-3.5 text-sm text-white placeholder-neutral-600 focus:border-indigo-500/50 outline-none transition-all" />
          </div>
          
          <div className="flex items-center bg-white/[0.03] border border-white/5 rounded-xl focus-within:border-indigo-500/50 transition-all overflow-hidden group">
            <div className="pl-3.5 flex items-center pointer-events-none group-focus-within:text-indigo-400"><Globe size={14} className="text-neutral-600" /></div>
            <input type="text" name="subdomain" required placeholder="subdomain" className="bg-transparent border-none focus:ring-0 text-sm flex-1 py-3 px-2.5 outline-none text-white placeholder-neutral-600 lowercase" />
            <div className="pr-3.5 py-3 bg-white/5 border-l border-white/5"><span className="text-neutral-500 text-[10px] font-bold px-2">.websitewayan.my.id</span></div>
          </div>
        </div>

        <div className="space-y-4">
          <div className="relative group">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none group-focus-within:text-indigo-400"><Lock size={14} className="text-neutral-600" /></div>
            <input type="password" name="password_toko" required placeholder="Admin Keyphrase" className="w-full bg-white/[0.03] border border-white/5 rounded-xl py-3 pl-10 pr-3.5 text-sm text-white placeholder-neutral-600 focus:border-indigo-500/50 outline-none transition-all" />
          </div>
          <div className="relative group">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none group-focus-within:text-indigo-400"><Phone size={14} className="text-neutral-600" /></div>
            <input type="text" name="kontak_wa" required placeholder="WhatsApp (62...)" className="w-full bg-white/[0.03] border border-white/5 rounded-xl py-3 pl-10 pr-3.5 text-sm text-white placeholder-neutral-600 focus:border-indigo-500/50 outline-none transition-all" />
          </div>
        </div>

        <div className="space-y-2">
          <label className="text-[10px] font-bold uppercase tracking-widest text-neutral-600 ml-1">AI Context & Persona</label>
          <textarea name="knowledge_base" required rows="3" placeholder="Provide background info for the AI Assistant..." className="w-full bg-white/[0.03] border border-white/5 rounded-xl p-3.5 text-sm text-white placeholder-neutral-600 focus:border-indigo-500/50 outline-none transition-all resize-none leading-relaxed" />
        </div>

        <button type="submit" disabled={isSubmitting} className="w-full bg-white text-black font-bold text-xs py-4 rounded-xl transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed mt-2 active:scale-[0.98] shadow-[0_0_20px_rgba(255,255,255,0.1)]">
          {isSubmitting ? <span className="animate-pulse">DEPLOYING...</span> : <><Plus size={16} strokeWidth={3} /> INITIALIZE NODE</>}
        </button>
      </form>
    </div>
  );
}

