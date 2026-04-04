import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Cpu, Server, Globe, Lock, Phone, Database, 
  Plus, ExternalLink, MessageCircle, LogOut, 
  CheckCircle2, AlertTriangle, X 
} from 'lucide-react';

const masterData = window.MASTER_DATA || {
  admin_session: 'demo@node', total_nodes: 0, tenants: []
};

export default function MasterApp() {
  const [toast, setToast] = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Tangkap status dari URL (Redirect dari PHP)
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
      const status = params.get('status');
      const msg = params.get('msg') || '';
      
      if (status === 'success') showToast('Deploy Berhasil!', `Node ${msg} telah aktif di jaringan.`, 'success');
      else if (status === 'error') showToast('Deploy Gagal', msg, 'error');
      
      window.history.replaceState({}, document.title, 'master.php');
    }
  }, []);

  const showToast = (title, message, type) => {
    setToast({ title, message, type });
    setTimeout(() => setToast(null), 5000);
  };

  return (
    <div className="min-h-screen bg-[#080d1a] font-sans text-slate-300 selection:bg-amber-500/30 selection:text-amber-200">
      
      {/* ── TOAST NOTIFICATION ── */}
      <AnimatePresence>
        {toast && (
          <motion.div 
            initial={{ opacity: 0, y: -20, x: 20 }} animate={{ opacity: 1, y: 0, x: 0 }} exit={{ opacity: 0, y: -20, x: 20 }} 
            className="fixed top-6 right-6 z-[100] flex items-start gap-3 bg-[#0d1427]/90 backdrop-blur-xl p-4 rounded-2xl shadow-2xl border border-white/10 min-w-[300px]"
          >
            {toast.type === 'success' ? <CheckCircle2 className="text-emerald-400 shrink-0 mt-0.5" size={20} /> : <AlertTriangle className="text-rose-400 shrink-0 mt-0.5" size={20} />}
            <div className="flex-1">
              <h4 className="font-bold text-white text-sm mb-1">{toast.title}</h4>
              <p className="text-xs text-slate-400 font-medium leading-relaxed">{toast.message}</p>
            </div>
            <button onClick={() => setToast(null)} className="text-slate-500 hover:text-white transition-colors"><X size={16}/></button>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ── TOP NAVIGATION ── */}
      <nav className="border-b border-white/5 px-6 md:px-8 py-4 flex justify-between items-center bg-[#080d1a]/80 backdrop-blur-xl sticky top-0 z-50">
        <div className="flex items-center gap-3">
          <div className="bg-amber-500 text-slate-900 w-10 h-10 rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(245,158,11,0.3)]">
            <Cpu size={22} className="font-black" strokeWidth={2.5} />
          </div>
          <span className="font-extrabold text-xl tracking-tight text-white hidden sm:block">
            MASTER<span className="text-amber-500">CENTER</span>
          </span>
        </div>
        
        <div className="flex items-center gap-5 md:gap-8">
          <div className="hidden md:flex flex-col text-right">
            <span className="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-0.5">Admin Session</span>
            <span className="text-xs text-amber-500 font-mono font-bold bg-amber-500/10 px-2.5 py-1 rounded-md border border-amber-500/20">{masterData.admin_session}</span>
          </div>
          <a href="logout.php" className="flex items-center gap-2 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all border border-rose-500/20">
            <LogOut size={16} /> <span className="hidden sm:inline">LOGOUT</span>
          </a>
        </div>
      </nav>

      {/* ── MAIN DASHBOARD ── */}
      <main className="max-w-[1400px] mx-auto px-6 py-10 md:py-12">
        <div className="grid lg:grid-cols-12 gap-8 lg:gap-12">
          
          {/* KIRI: FORM PENDAFTARAN TENANT */}
          <div className="lg:col-span-5">
            <div className="mb-8">
              <h2 className="text-3xl md:text-4xl font-extrabold text-white mb-3 tracking-tight">Deploy New <span className="text-amber-500">Tenant</span></h2>
              <p className="text-slate-400 text-sm font-medium leading-relaxed">Inisialisasi subdomain baru untuk mengaktifkan instance AI Smart Shop.</p>
            </div>

            <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="bg-[#0d1427]/70 backdrop-blur-xl p-6 md:p-8 rounded-[2rem] border border-white/5 shadow-2xl">
              <form method="POST" action="master.php" onSubmit={() => setIsSubmitting(true)} className="space-y-6">
                <input type="hidden" name="register_tenant" value="1" />

                {/* Block 1: Identity */}
                <div className="space-y-3">
                  <div className="flex items-center gap-2 text-[10px] font-bold text-amber-500/80 uppercase tracking-widest ml-1"><Database size={12}/> Business Identity</div>
                  
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><Server size={16} className="text-slate-500" /></div>
                    <input type="text" name="nama_toko" required placeholder="Nama Toko (Misal: Laundry Kilat)" className="w-full bg-[#080d1a]/60 border border-white/10 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white placeholder-slate-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition-all" />
                  </div>
                  
                  <div className="flex items-center bg-[#080d1a]/60 border border-white/10 rounded-xl focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500 transition-all overflow-hidden">
                    <div className="pl-4 flex items-center pointer-events-none"><Globe size={16} className="text-slate-500" /></div>
                    <input type="text" name="subdomain" required placeholder="subdomain" className="bg-transparent border-none focus:ring-0 text-sm flex-1 py-3.5 px-3 outline-none text-white placeholder-slate-600 lowercase" />
                    <div className="pr-4 py-3.5 bg-white/5 border-l border-white/10">
                      <span className="text-slate-500 text-xs font-bold">.websitewayan.my.id</span>
                    </div>
                  </div>
                </div>

                {/* Block 2: Access */}
                <div className="space-y-3 pt-2">
                  <div className="flex items-center gap-2 text-[10px] font-bold text-amber-500/80 uppercase tracking-widest ml-1"><Lock size={12}/> Access Control</div>
                  
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><Lock size={16} className="text-slate-500" /></div>
                    <input type="password" name="password_toko" required placeholder="Set Password Admin Toko" className="w-full bg-[#080d1a]/60 border border-white/10 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white placeholder-slate-600 focus:border-amber-500 outline-none transition-all" />
                  </div>
                  
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><Phone size={16} className="text-slate-500" /></div>
                    <input type="text" name="kontak_wa" required placeholder="6281234567890 (WhatsApp)" className="w-full bg-[#080d1a]/60 border border-white/10 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white placeholder-slate-600 focus:border-amber-500 outline-none transition-all" />
                  </div>
                </div>

                {/* Block 3: AI Base */}
                <div className="space-y-3 pt-2">
                  <div className="flex items-center gap-2 text-[10px] font-bold text-amber-500/80 uppercase tracking-widest ml-1"><Cpu size={12}/> AI Knowledge Base</div>
                  <textarea name="knowledge_base" required rows="3" placeholder="Deskripsi umum toko agar AI memahami konteks bisnis..." className="w-full bg-[#080d1a]/60 border border-white/10 rounded-xl p-4 text-sm text-white placeholder-slate-600 focus:border-amber-500 outline-none transition-all resize-none leading-relaxed"></textarea>
                </div>

                <button type="submit" disabled={isSubmitting} className="w-full bg-amber-500 hover:bg-amber-400 text-slate-900 font-extrabold text-sm py-4 rounded-xl transition-all flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(245,158,11,0.2)] disabled:opacity-70 disabled:cursor-not-allowed mt-4">
                  {isSubmitting ? <span className="animate-pulse">INITIALIZING...</span> : <><Plus size={18} strokeWidth={3} /> INITIALIZE INSTANCE</>}
                </button>
              </form>
            </motion.div>
          </div>

          {/* KANAN: LIST TENANT AKTIF */}
          <div className="lg:col-span-7 mt-12 lg:mt-0">
            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-end mb-8 gap-4">
              <div>
                <h2 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                  Cloud <span className="text-slate-500 font-medium italic">Instances</span>
                </h2>
              </div>
              <div className="bg-emerald-500/10 border border-emerald-500/20 px-4 py-2 rounded-xl text-[11px] font-bold text-emerald-400 tracking-widest flex items-center gap-2 w-fit">
                <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> {masterData.total_nodes} ACTIVE NODES
              </div>
            </div>

            <div className="space-y-4">
              {masterData.tenants.length === 0 ? (
                <div className="text-center py-20 border-2 border-dashed border-white/10 rounded-[2.5rem] bg-white/5">
                  <Server className="mx-auto text-slate-600 mb-4 opacity-50" size={48} />
                  <p className="text-slate-400 font-bold text-sm">Belum ada tenant (node) yang dideploy di jaringan.</p>
                </div>
              ) : (
                masterData.tenants.map((t, idx) => (
                  <motion.div 
                    initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: idx * 0.05 }}
                    key={t.id_toko} 
                    className="bg-[#0d1427]/50 border border-white/5 p-5 md:p-6 rounded-3xl flex flex-col sm:flex-row sm:items-center justify-between gap-5 hover:bg-[#0d1427] hover:border-amber-500/30 transition-all group shadow-lg"
                  >
                    <div className="flex items-center gap-4 md:gap-5">
                      <div className="w-12 h-12 md:w-14 md:h-14 bg-[#080d1a] rounded-2xl flex items-center justify-center font-extrabold text-lg md:text-xl text-amber-500 border border-white/5 group-hover:bg-amber-500 group-hover:text-slate-900 transition-colors shadow-inner shrink-0">
                        {t.nama_toko.substring(0, 1).toUpperCase()}
                      </div>
                      <div className="min-w-0">
                        <h3 className="font-extrabold text-white text-base md:text-lg truncate">{t.nama_toko}</h3>
                        <div className="flex items-center gap-2 mt-1">
                          <span className="text-xs text-slate-500 font-mono truncate">{t.subdomain}.websitewayan.my.id</span>
                        </div>
                      </div>
                    </div>
                    
                    <div className="flex gap-2 sm:gap-3 ml-16 sm:ml-0 shrink-0">
                      <a href={`https://wa.me/${t.kontak_wa}`} target="_blank" className="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 hover:text-emerald-400 hover:bg-emerald-400/10 hover:border-emerald-400/20 transition-all shadow-sm" title="WhatsApp Admin">
                        <MessageCircle size={18} />
                      </a>
                      <a href={`https://${t.subdomain}.websitewayan.my.id`} target="_blank" className="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 hover:text-amber-400 hover:bg-amber-400/10 hover:border-amber-400/20 transition-all shadow-sm" title="Visit Instance">
                        <ExternalLink size={18} />
                      </a>
                    </div>
                  </motion.div>
                ))
              )}
            </div>
          </div>
          
        </div>
      </main>

      <footer className="mt-10 pb-8 text-center">
        <p className="text-[10px] text-slate-600 uppercase tracking-[0.4em] font-bold flex items-center justify-center gap-2">
          <Server size={12}/> Pasek Cloud Security &copy; {new Date().getFullYear()}
        </p>
      </footer>

    </div>
  );
}
