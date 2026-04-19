import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ShieldCheck, User, Key, Eye, EyeOff, AlertTriangle, Loader2, HelpCircle, ArrowRight } from 'lucide-react';

const loginData = window.LOGIN_DATA || { masterWhatsapp: '6281234567890' };

export default function LoginPage() {
  const [formData, setFormData] = useState({ username: '', password: '', _csrf_token: loginData.csrfToken, remember: false });
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [shake, setShake] = useState(false);
  const userRef = React.useRef(null);

  React.useEffect(() => {
    userRef.current?.focus();
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.username || !formData.password) { triggerError('Please enter both username and password.'); return; }
    setIsLoading(true); setErrorMessage('');
    try {
      const response = await fetch('/login.php?api=1', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify(formData) 
      });
      const data = await response.json();
      if (data.status === 'success') window.location.assign(data.redirect);
      else {
        triggerError(data.message);
        // If session expired (CSRF rotated), reload to get a fresh token
        if (data.message && data.message.includes('Session expired')) {
          setTimeout(() => window.location.reload(), 1500);
        }
        setIsLoading(false);
      }
    } catch { triggerError('Failed to connect to the authorization server.'); setIsLoading(false); }
  };

  const triggerError = (msg) => { setErrorMessage(msg); setShake(true); setTimeout(() => setShake(false), 500); };

  const handleForgotPass = () => {
    window.open(`https://wa.me/${loginData.masterWhatsapp}?text=${encodeURIComponent('Hello Master Admin, I need help recovering access (Forgot Password).')}`, '_blank');
  };

  return (
    <div className="min-h-screen bg-[#0A0A0A] font-sans text-neutral-300 flex items-center justify-center p-6 relative overflow-hidden">
      <div className="glow-bg"></div>
      <div className="absolute inset-0 opacity-[0.03]" style={{ backgroundImage: 'radial-gradient(#ffffff 1px, transparent 1px)', backgroundSize: '30px 30px' }} />

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0, ...(shake ? { x: [-10, 10, -10, 10, -5, 5, 0], transition: { duration: 0.4 } } : {}) }}
        className="w-full max-w-[400px] glass-card p-8 md:p-10 rounded-2xl relative z-10 border-neutral-800">

        <div className="text-center mb-10">
          <div className="inline-flex items-center justify-center w-12 h-12 bg-white text-black rounded-xl mb-4 shadow-[0_0_20px_rgba(255,255,255,0.1)]">
            <ShieldCheck size={24} strokeWidth={2} />
          </div>
          <h1 className="text-2xl font-bold text-white tracking-tight mb-1">Pasek<span className="text-blue-500">SaaS</span></h1>
          <p className="text-[10px] text-neutral-500 uppercase tracking-[0.25em] font-medium">Secure Access Node</p>
        </div>

        <AnimatePresence>
          {errorMessage && (
            <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} exit={{ opacity: 0, height: 0 }}
              className="bg-red-500/10 border border-red-500/20 p-3.5 rounded-xl flex items-start gap-2.5 overflow-hidden mb-6">
              <AlertTriangle size={16} className="text-red-400 shrink-0 mt-0.5" />
              <p className="text-xs text-red-400 leading-relaxed">{errorMessage}</p>
            </motion.div>
          )}
        </AnimatePresence>

        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="space-y-1.5 px-0.5">
            <label className="text-[10px] font-medium text-neutral-500 uppercase tracking-widest">Username</label>
            <div className="relative flex items-center bg-black/40 border border-neutral-800 rounded-xl focus-within:border-blue-500/50 transition-all">
              <User size={16} className="absolute left-3.5 text-neutral-600" />
              <input type="text" ref={userRef} value={formData.username} onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                placeholder="Username or Subdomain" disabled={isLoading}
                className="w-full bg-transparent border-none py-3.5 pl-10 pr-3.5 text-sm text-white placeholder:text-neutral-700 outline-none disabled:opacity-50" />
            </div>
          </div>

          <div className="space-y-1.5 px-0.5">
            <div className="flex justify-between items-center">
              <label className="text-[10px] font-medium text-neutral-500 uppercase tracking-widest">Password</label>
              <button type="button" onClick={handleForgotPass} className="text-[10px] font-medium text-neutral-600 hover:text-white transition-colors flex items-center gap-1"><HelpCircle size={10} /> Forgot?</button>
            </div>
            <div className="relative flex items-center bg-black/40 border border-neutral-800 rounded-xl focus-within:border-blue-500/50 transition-all">
              <Key size={16} className="absolute left-3.5 text-neutral-600" />
              <input type={showPassword ? 'text' : 'password'} value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                placeholder="••••••••" disabled={isLoading}
                className="w-full bg-transparent border-none py-3.5 pl-10 pr-10 text-sm text-white placeholder:text-neutral-700 outline-none disabled:opacity-50" />
              <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3.5 text-neutral-600 hover:text-white transition-colors">
                {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
              </button>
            </div>
          </div>

          <label className="flex items-center gap-2.5 cursor-pointer group w-fit ml-0.5 mt-2">
            <div className="relative flex items-center justify-center">
              <input type="checkbox" checked={formData.remember} onChange={(e) => setFormData({ ...formData, remember: e.target.checked })} disabled={isLoading}
                className="appearance-none w-4 h-4 border-2 border-neutral-700 rounded checked:bg-blue-600 checked:border-blue-600 transition-colors group-hover:border-neutral-500" />
              {formData.remember && <ShieldCheck size={11} className="absolute text-white pointer-events-none" />}
            </div>
            <span className="text-xs text-neutral-500 group-hover:text-neutral-400 transition-colors">Remember Session (30 Days)</span>
          </label>

          <button type="submit" disabled={isLoading}
            className="w-full bg-white text-black font-bold py-4 rounded-xl hover:bg-neutral-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-50 mt-2 shadow-[0_0_20px_rgba(255,255,255,0.1)]">
            {isLoading ? <><Loader2 size={18} className="animate-spin" /> AUTHORIZING...</> : 'AUTHORIZE ACCESS'}
          </button>
        </form>

        <div className="mt-10 pt-8 border-t border-neutral-900 text-center">
          <p className="text-xs text-neutral-500 mb-4">
            Don't have a store? <a href="/register.php" className="text-white font-semibold hover:underline">Register Now</a>
          </p>
          <p className="text-[9px] tracking-[0.3em] uppercase text-neutral-700 font-medium italic">Control System v3.1.0</p>
        </div>
      </motion.div>
    </div>
  );
}
