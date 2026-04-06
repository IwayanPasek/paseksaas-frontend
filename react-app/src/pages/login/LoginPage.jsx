import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ShieldCheck, User, Key, Eye, EyeOff, AlertTriangle, Loader2, HelpCircle } from 'lucide-react';

const loginData = window.LOGIN_DATA || { master_wa: '6281234567890' };

export default function LoginPage() {
  const [formData, setFormData] = useState({ username: '', password: '', remember: false });
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [shake, setShake] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.username || !formData.password) { triggerError('Harap isi identifier dan keyphrase.'); return; }
    setIsLoading(true); setErrorMsg('');
    try {
      const res = await fetch('/login.php?api=1', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) });
      const data = await res.json();
      if (data.status === 'success') window.location.href = data.redirect;
      else { triggerError(data.message); setIsLoading(false); }
    } catch { triggerError('Gagal terhubung ke server otorisasi.'); setIsLoading(false); }
  };

  const triggerError = (msg) => { setErrorMsg(msg); setShake(true); setTimeout(() => setShake(false), 500); };

  const handleForgotPass = () => {
    window.open(`https://wa.me/${loginData.master_wa}?text=${encodeURIComponent('Halo Master Admin, saya butuh bantuan pemulihan akses (Lupa Password).')}`, '_blank');
  };

  return (
    <div className="min-h-screen bg-neutral-50 font-sans text-neutral-700 flex items-center justify-center p-6 relative overflow-hidden">
      <div className="absolute inset-0 opacity-[0.02]" style={{ backgroundImage: 'radial-gradient(#171717 1px, transparent 1px)', backgroundSize: '20px 20px' }} />

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0, ...(shake ? { x: [-10, 10, -10, 10, -5, 5, 0], transition: { duration: 0.4 } } : {}) }}
        className="w-full max-w-[400px] bg-white border border-neutral-200 p-7 md:p-9 rounded-2xl shadow-sm relative z-10">

        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-12 h-12 bg-neutral-900 text-white rounded-xl mb-4"><ShieldCheck size={24} strokeWidth={2} /></div>
          <h1 className="text-2xl font-bold text-neutral-900 tracking-tight mb-0.5">Pasek<span className="text-neutral-400">SaaS</span></h1>
          <p className="text-[10px] text-neutral-400 uppercase tracking-[0.25em] font-medium">Secure Access Node</p>
        </div>

        <AnimatePresence>
          {errorMsg && (
            <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} exit={{ opacity: 0, height: 0 }}
              className="bg-danger-50 border border-danger-100 p-3.5 rounded-xl flex items-start gap-2.5 overflow-hidden mb-5">
              <AlertTriangle size={16} className="text-danger-500 shrink-0 mt-0.5" />
              <p className="text-xs text-danger-500 leading-relaxed">{errorMsg}</p>
            </motion.div>
          )}
        </AnimatePresence>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-[10px] font-medium text-neutral-400 uppercase tracking-widest ml-0.5">Identifier</label>
            <div className="relative flex items-center bg-neutral-50 border border-neutral-200 rounded-xl focus-within:border-neutral-400 transition-all">
              <User size={16} className="absolute left-3.5 text-neutral-300" />
              <input type="text" value={formData.username} onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                placeholder="Username or Subdomain" disabled={isLoading}
                className="w-full bg-transparent border-none py-3 pl-10 pr-3.5 text-sm text-neutral-900 placeholder-neutral-400 outline-none disabled:opacity-50" />
            </div>
          </div>

          <div className="space-y-1.5">
            <div className="flex justify-between items-center px-0.5">
              <label className="text-[10px] font-medium text-neutral-400 uppercase tracking-widest">Keyphrase</label>
              <button type="button" onClick={handleForgotPass} className="text-[10px] font-medium text-neutral-400 hover:text-neutral-900 transition-colors flex items-center gap-1"><HelpCircle size={10} /> Lupa?</button>
            </div>
            <div className="relative flex items-center bg-neutral-50 border border-neutral-200 rounded-xl focus-within:border-neutral-400 transition-all">
              <Key size={16} className="absolute left-3.5 text-neutral-300" />
              <input type={showPassword ? 'text' : 'password'} value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                placeholder="••••••••" disabled={isLoading}
                className="w-full bg-transparent border-none py-3 pl-10 pr-10 text-sm text-neutral-900 placeholder-neutral-400 outline-none disabled:opacity-50" />
              <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3.5 text-neutral-300 hover:text-neutral-600 transition-colors">
                {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
              </button>
            </div>
          </div>

          <label className="flex items-center gap-2.5 cursor-pointer group w-fit ml-0.5">
            <div className="relative flex items-center justify-center">
              <input type="checkbox" checked={formData.remember} onChange={(e) => setFormData({ ...formData, remember: e.target.checked })} disabled={isLoading}
                className="appearance-none w-4 h-4 border-2 border-neutral-300 rounded checked:bg-neutral-900 checked:border-neutral-900 transition-colors group-hover:border-neutral-400" />
              {formData.remember && <ShieldCheck size={11} className="absolute text-white pointer-events-none" />}
            </div>
            <span className="text-xs text-neutral-500 group-hover:text-neutral-700 transition-colors">Ingat Sesi (30 Hari)</span>
          </label>

          <button type="submit" disabled={isLoading}
            className="w-full bg-neutral-900 text-white font-semibold py-3.5 rounded-xl hover:bg-neutral-800 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70 mt-1">
            {isLoading ? <><Loader2 size={16} className="animate-spin" /> AUTHORIZING...</> : 'AUTHORIZE ACCESS'}
          </button>
        </form>

        <div className="mt-7 text-center">
          <p className="text-[9px] tracking-[0.3em] uppercase text-neutral-300 font-medium">Control System v3.0.0</p>
        </div>
      </motion.div>
    </div>
  );
}
