import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  ShieldCheck, User, Key, Eye, EyeOff, 
  AlertTriangle, Loader2, HelpCircle 
} from 'lucide-react';

const loginData = window.LOGIN_DATA || { master_wa: '6281234567890' };

export default function LoginApp() {
  const [formData, setFormData] = useState({ username: '', password: '', remember: false });
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [shake, setShake] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.username || !formData.password) {
      triggerError('Harap isi identifier dan keyphrase.');
      return;
    }

    setIsLoading(true);
    setErrorMsg('');

    try {
      const res = await fetch('/login.php?api=1', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      const data = await res.json();

      if (data.status === 'success') {
        window.location.href = data.redirect;
      } else {
        triggerError(data.message);
        setIsLoading(false);
      }
    } catch (err) {
      triggerError('Gagal terhubung ke server otorisasi.');
      setIsLoading(false);
    }
  };

  const triggerError = (msg) => {
    setErrorMsg(msg);
    setShake(true);
    setTimeout(() => setShake(false), 500); 
  };

  const handleForgotPass = () => {
    const text = `Halo Master Admin, saya butuh bantuan pemulihan akses (Lupa Password) untuk node/toko saya.`;
    window.open(`https://wa.me/${loginData.master_wa}?text=${encodeURIComponent(text)}`, '_blank');
  };

  const shakeAnimation = shake ? { x: [-10, 10, -10, 10, -5, 5, 0], transition: { duration: 0.4 } } : {};

  return (
    <div className="min-h-screen bg-[#080d1a] font-sans text-slate-300 flex items-center justify-center p-6 relative overflow-hidden">
      
      {/* Background Glow */}
      <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[100px] pointer-events-none"></div>

      <motion.div 
        initial={{ opacity: 0, y: 30 }} 
        animate={{ opacity: 1, y: 0, ...shakeAnimation }}
        className="w-full max-w-[420px] bg-[#0d1427]/80 backdrop-blur-xl border border-white/10 p-8 md:p-10 rounded-[2.5rem] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] relative z-10"
      >
        <div className="text-center mb-10">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-amber-500 text-slate-900 rounded-2xl mb-5 shadow-[0_0_30px_rgba(245,158,11,0.3)]">
            <ShieldCheck size={32} strokeWidth={2.5} />
          </div>
          <h1 className="text-3xl font-extrabold text-white tracking-tight mb-1">
            Pasek<span className="text-amber-500">SaaS</span>
          </h1>
          <p className="text-[10px] text-slate-500 uppercase tracking-[0.3em] font-bold">Secure Access Node</p>
        </div>

        {/* Error Alert Box */}
        <AnimatePresence>
          {errorMsg && (
            <motion.div 
              initial={{ opacity: 0, height: 0, mb: 0 }} 
              animate={{ opacity: 1, height: 'auto', mb: 24 }} 
              exit={{ opacity: 0, height: 0, mb: 0 }}
              className="bg-rose-500/10 border border-rose-500/20 p-4 rounded-2xl flex items-start gap-3 overflow-hidden"
            >
              <AlertTriangle size={18} className="text-rose-500 shrink-0 mt-0.5" />
              <p className="text-xs text-rose-400 font-medium leading-relaxed">{errorMsg}</p>
            </motion.div>
          )}
        </AnimatePresence>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-2">
            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Identifier</label>
            <div className="relative flex items-center bg-[#080d1a]/60 border border-white/10 rounded-2xl focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500 transition-all">
              <User size={18} className="absolute left-4 text-slate-500" />
              <input 
                type="text" 
                value={formData.username}
                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                placeholder="Username or Subdomain" 
                disabled={isLoading}
                className="w-full bg-transparent border-none py-4 pl-12 pr-4 text-sm text-white placeholder-slate-600 outline-none focus:ring-0 disabled:opacity-50"
              />
            </div>
          </div>

          <div className="space-y-2">
            <div className="flex justify-between items-center px-1">
              <label className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Keyphrase</label>
              <button type="button" onClick={handleForgotPass} className="text-[10px] font-bold text-slate-500 hover:text-amber-500 transition-colors flex items-center gap-1">
                <HelpCircle size={10} /> Lupa Keyphrase?
              </button>
            </div>
            <div className="relative flex items-center bg-[#080d1a]/60 border border-white/10 rounded-2xl focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500 transition-all">
              <Key size={18} className="absolute left-4 text-slate-500" />
              <input 
                type={showPassword ? 'text' : 'password'} 
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                placeholder="••••••••" 
                disabled={isLoading}
                className="w-full bg-transparent border-none py-4 pl-12 pr-12 text-sm text-white placeholder-slate-600 outline-none focus:ring-0 disabled:opacity-50"
              />
              <button 
                type="button" 
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-4 text-slate-500 hover:text-slate-300 transition-colors"
              >
                {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            </div>
          </div>

          <label className="flex items-center gap-3 cursor-pointer group w-fit ml-1">
            <div className="relative flex items-center justify-center">
              <input 
                type="checkbox" 
                checked={formData.remember}
                onChange={(e) => setFormData({ ...formData, remember: e.target.checked })}
                disabled={isLoading}
                className="appearance-none w-5 h-5 border-2 border-slate-600 rounded-md checked:bg-amber-500 checked:border-amber-500 transition-colors group-hover:border-amber-500/50"
              />
              {formData.remember && <ShieldCheck size={14} className="absolute text-slate-900 pointer-events-none" />}
            </div>
            <span className="text-xs font-bold text-slate-400 group-hover:text-slate-300 transition-colors">Ingat Sesi Saya (30 Hari)</span>
          </label>

          <button 
            type="submit" 
            disabled={isLoading}
            className="w-full bg-amber-500 text-slate-900 font-extrabold py-4 rounded-2xl hover:bg-amber-400 transform active:scale-95 transition-all shadow-[0_0_20px_rgba(245,158,11,0.2)] flex items-center justify-center gap-2 disabled:opacity-80 disabled:pointer-events-none mt-2"
          >
            {isLoading ? (
              <><Loader2 size={18} className="animate-spin" /> AUTHORIZING...</>
            ) : (
              'AUTHORIZE ACCESS'
            )}
          </button>
        </form>

        <div className="mt-8 text-center opacity-30">
          <p className="text-[9px] tracking-[0.4em] uppercase font-bold">Control System v3.0.0</p>
        </div>
      </motion.div>
    </div>
  );
}
