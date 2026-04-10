import React, { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ShieldCheck, User, Mail, Store, Globe, Key, Eye, EyeOff, AlertTriangle, Loader2, CheckCircle2, ChevronRight, ArrowLeft } from 'lucide-react';

const registerData = window.REGISTER_DATA || { siteDomain: 'websitewayan.my.id' };

export default function RegisterPage() {
    const [step, setStep] = useState(1);
    const [formData, setFormData] = useState({
        ownerName: '',
        email: '',
        storeName: '',
        subdomain: '',
        password: '',
        confirmPassword: '',
        _csrf_token: registerData.csrfToken
    });
    const [showPassword, setShowPassword] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [errorMsg, setErrorMsg] = useState('');
    const [subdomainStatus, setSubdomainStatus] = useState('idle'); // idle, checking, available, taken
    const [shake, setShake] = useState(false);
    const [isSuccess, setIsSuccess] = useState(false);

    // Debounced Subdomain Check
    useEffect(() => {
        if (formData.subdomain.length < 3) {
            setSubdomainStatus('idle');
            return;
        }

        const handler = setTimeout(async () => {
            setSubdomainStatus('checking');
            try {
                const res = await fetch(`/register.php?api=check-subdomain&sub=${encodeURIComponent(formData.subdomain)}`);
                const data = await res.json();
                setSubdomainStatus(data.available ? 'available' : 'taken');
            } catch {
                setSubdomainStatus('idle');
            }
        }, 600); // 600ms delay

        return () => clearTimeout(handler);
    }, [formData.subdomain]);

    const triggerError = (msg) => {
        setErrorMsg(msg);
        setShake(true);
        setTimeout(() => setShake(false), 500);
    };

    const nextStep = () => {
        if (step === 1) {
            if (!formData.ownerName || !formData.email) {
                triggerError('Please complete your personal data.');
                return;
            }
            if (!formData.email.includes('@')) {
                triggerError('Invalid email format.');
                return;
            }
        }
        setErrorMsg('');
        setStep(step + 1);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (formData.password !== formData.confirmPassword) {
            triggerError('Password confirmation does not match.');
            return;
        }
        if (subdomainStatus !== 'available') {
            triggerError('Subdomain is not available or hasn\'t been checked.');
            return;
        }

        setIsLoading(true);
        setErrorMsg('');

        try {
            const res = await fetch('/register.php?api=submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();
            if (data.status === 'success') {
                setIsSuccess(true);
            } else {
                triggerError(data.message);
            }
        } catch {
            triggerError('Failed to connect to the registration server.');
        } finally {
            setIsLoading(false);
        }
    };

    if (isSuccess) {
        return (
            <div className="min-h-screen bg-[#0A0A0A] font-sans text-neutral-300 flex items-center justify-center p-6 relative overflow-hidden">
                <div className="glow-bg opacity-40"></div>
                <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }}
                    className="w-full max-w-[450px] glass-card p-10 rounded-2xl text-center relative z-10 border-neutral-800">
                    <div className="w-20 h-20 bg-blue-500/10 text-blue-400 rounded-full flex items-center justify-center mx-auto mb-6 border border-blue-500/20">
                        <CheckCircle2 size={40} />
                    </div>
                    <h2 className="text-2xl font-bold text-white mb-4">Registration Successful!</h2>
                    <p className="text-neutral-400 text-sm leading-relaxed mb-8">
                        The store <strong>{formData.storeName}</strong> has been registered. Your account is currently being reviewed by the Master Admin. We will inform you once access is active.
                    </p>
                    <a href="/" className="inline-flex items-center gap-2 text-blue-400 font-medium hover:text-blue-300 transition-colors">
                        <ArrowLeft size={16} /> Back to Home
                    </a>
                </motion.div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#0A0A0A] font-sans text-neutral-300 flex items-center justify-center p-6 relative overflow-hidden">
            <div className="glow-bg"></div>
            <div className="absolute inset-0 opacity-[0.03]" style={{ backgroundImage: 'radial-gradient(#ffffff 1px, transparent 1px)', backgroundSize: '30px 30px' }} />

            <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0, ...(shake ? { x: [-10, 10, -10, 10, -5, 5, 0], transition: { duration: 0.4 } } : {}) }}
                className="w-full max-w-[480px] glass-card p-8 md:p-10 rounded-2xl relative z-10 border-neutral-800">

                <div className="text-center mb-10">
                    <div className="inline-flex items-center justify-center w-12 h-12 bg-white text-black rounded-xl mb-4 shadow-[0_0_20px_rgba(255,255,255,0.1)]">
                        <ShieldCheck size={24} strokeWidth={2} />
                    </div>
                    <h1 className="text-2xl font-bold text-white tracking-tight mb-1">Create Your Store</h1>
                    <p className="text-[10px] text-neutral-500 uppercase tracking-[0.2em] font-medium">Infrastructure Request Node</p>
                </div>

                {/* Progress Bar */}
                <div className="flex gap-2 mb-8">
                    {[1, 2, 3].map(i => (
                        <div key={i} className={`h-1 flex-1 rounded-full transition-all duration-500 ${step >= i ? 'bg-blue-500' : 'bg-neutral-800'}`} />
                    ))}
                </div>

                <AnimatePresence mode="wait">
                    {errorMsg && (
                        <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }}
                            className="bg-red-500/10 border border-red-500/20 p-3.5 rounded-xl flex items-start gap-2.5 mb-6">
                            <AlertTriangle size={16} className="text-red-400 shrink-0 mt-0.5" />
                            <p className="text-xs text-red-400 leading-relaxed">{errorMsg}</p>
                        </motion.div>
                    )}
                </AnimatePresence>

                <form onSubmit={handleSubmit} className="space-y-5">
                    {step === 1 && (
                        <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} className="space-y-4">
                            <InputField label="Owner Name" icon={<User size={16} />} placeholder="Your full name"
                                value={formData.ownerName} onChange={v => setFormData({ ...formData, ownerName: v })} />
                            <InputField label="Email Address" icon={<Mail size={16} />} placeholder="email@example.com" type="email"
                                value={formData.email} onChange={v => setFormData({ ...formData, email: v })} />
                            <button type="button" onClick={nextStep}
                                className="w-full bg-white text-black font-bold py-4 rounded-xl hover:bg-neutral-200 transition-all flex items-center justify-center gap-2 mt-4 shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                                Continue <ChevronRight size={18} />
                            </button>
                        </motion.div>
                    )}

                    {step === 2 && (
                        <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} className="space-y-4">
                            <InputField label="Store Name" icon={<Store size={16} />} placeholder="Example: Digital Store"
                                value={formData.storeName} onChange={v => setFormData({ ...formData, storeName: v })} />
                            
                            <div className="space-y-1.5 px-0.5">
                                <div className="flex justify-between items-center">
                                    <label className="text-[10px] font-medium text-neutral-500 uppercase tracking-widest">Subdomain Request</label>
                                    <SubdomainIndicator status={subdomainStatus} />
                                </div>
                                <div className="relative flex items-center bg-black/40 border border-neutral-800 rounded-xl focus-within:border-blue-500/50 transition-all">
                                    <Globe size={16} className="absolute left-3.5 text-neutral-600" />
                                    <input type="text" value={formData.subdomain} onChange={(e) => setFormData({ ...formData, subdomain: e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '') })}
                                        placeholder="store-name"
                                        className="w-full bg-transparent border-none py-3.5 pl-10 pr-24 text-sm text-white outline-none" />
                                    <span className="absolute right-3.5 text-[11px] font-medium text-neutral-600">.{registerData.siteDomain}</span>
                                </div>
                            </div>

                            <div className="flex gap-3 mt-4">
                                <button type="button" onClick={() => setStep(1)} className="px-5 py-4 rounded-xl border border-neutral-800 font-medium text-sm hover:bg-white/5 transition-colors text-neutral-400">
                                    Back
                                </button>
                                <button type="button" onClick={nextStep}
                                    className="flex-1 bg-white text-black font-bold py-4 rounded-xl hover:bg-neutral-200 transition-all flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                                    Continue <ChevronRight size={18} />
                                </button>
                            </div>
                        </motion.div>
                    )}

                    {step === 3 && (
                        <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} className="space-y-4">
                            <div className="space-y-1.5 px-0.5">
                                <label className="text-[10px] font-medium text-neutral-500 uppercase tracking-widest">Setup Password</label>
                                <div className="relative flex items-center bg-black/40 border border-neutral-800 rounded-xl focus-within:border-blue-500/50 transition-all">
                                    <Key size={16} className="absolute left-3.5 text-neutral-600" />
                                    <input type={showPassword ? 'text' : 'password'} value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                        placeholder="Min. 8 characters"
                                        className="w-full bg-transparent border-none py-3.5 pl-10 pr-10 text-sm text-white outline-none" />
                                    <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3.5 text-neutral-600 hover:text-white transition-colors">
                                        {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                                    </button>
                                </div>
                                <div className="flex gap-1 mt-2 px-1">
                                    {[1, 2, 3, 4].map((i) => (
                                        <div key={i} className={`h-1 flex-1 rounded-full ${formData.password.length >= i * 2 ? 'bg-blue-500' : 'bg-neutral-800'}`} />
                                    ))}
                                </div>
                                <p className="text-[9px] text-neutral-600 mt-1.5 ml-1 italic">Strength: {formData.password.length < 4 ? 'Too Short' : formData.password.length < 8 ? 'Weak' : 'Secure'}</p>
                            </div>
                            <InputField label="Confirm Password" icon={<Key size={16} />} placeholder="Repeat password" type={showPassword ? 'text' : 'password'}
                                value={formData.confirmPassword} onChange={v => setFormData({ ...formData, confirmPassword: v })} />

                            <div className="flex gap-3 mt-6">
                                <button type="button" onClick={() => setStep(2)} className="px-5 py-4 rounded-xl border border-neutral-800 font-medium text-sm hover:bg-white/5 transition-colors text-neutral-400">
                                    Back
                                </button>
                                <button type="submit" disabled={isLoading}
                                    className="flex-1 bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-500 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                    {isLoading ? <><Loader2 size={18} className="animate-spin" /> Registering...</> : 'Request Store Access'}
                                </button>
                            </div>
                        </motion.div>
                    )}
                </form>

                <div className="mt-8 text-center">
                    <p className="text-xs text-neutral-500">
                        Already have access? <a href="/login.php" className="text-white font-semibold hover:underline">Login here</a>
                    </p>
                </div>
            </motion.div>
        </div>
    );
}

function InputField({ label, icon, placeholder, value, onChange, type = "text" }) {
    return (
        <div className="space-y-1.5 px-0.5 w-full">
            <label className="text-[10px] font-medium text-neutral-500 uppercase tracking-widest">{label}</label>
            <div className="relative flex items-center bg-black/40 border border-neutral-800 rounded-xl focus-within:border-blue-500/50 transition-all">
                <div className="absolute left-3.5 text-neutral-600">{icon}</div>
                <input type={type} value={value} onChange={(e) => onChange(e.target.value)}
                    placeholder={placeholder}
                    className="w-full bg-transparent border-none py-3.5 pl-10 pr-3.5 text-sm text-white placeholder:text-neutral-700 outline-none" />
            </div>
        </div>
    );
}

function SubdomainIndicator({ status }) {
    if (status === 'idle') return null;
    if (status === 'checking') return <span className="flex items-center gap-1 text-[9px] text-neutral-500"><Loader2 size={10} className="animate-spin" /> Checking...</span>;
    if (status === 'available') return <span className="flex items-center gap-1 text-[9px] text-blue-400 font-bold"><CheckCircle2 size={10} /> AVAILABLE</span>;
    if (status === 'taken') return <span className="flex items-center gap-1 text-[9px] text-red-400 font-bold"><AlertTriangle size={10} /> TAKEN</span>;
    return null;
}
