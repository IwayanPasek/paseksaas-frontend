/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/Hero.jsx
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { ShoppingBag, Sparkles, Zap, Clock, Package, ArrowRight, Star } from 'lucide-react';
import { S } from '../lib/store';

const TRUST_BADGES = (count) => [
    { icon: <Zap size={14} />, text: 'AI-Powered' },
    { icon: <Clock size={14} />, text: '24/7 Online' },
    { icon: <Package size={14} />, text: `${count} Layanan` },
];

export default function Hero({ onChat, productCount }) {
    return (
        <section className="relative pt-28 pb-24 px-6 min-h-[92vh] flex items-center overflow-hidden gradient-hero">
            {/* Dekorasi orb */}
            <div className="orb w-72 h-72 bg-primary-400 top-20 -left-20 animate-float" />
            <div className="orb w-96 h-96 bg-accent-400 -bottom-20 right-0 animate-float-slow" />
            <div className="orb w-48 h-48 bg-cyan-400 top-1/2 left-1/2 animate-float" style={{ animationDelay: '2s' }} />

            <div className="max-w-6xl mx-auto w-full grid md:grid-cols-2 gap-16 items-center relative z-10">
                {/* ── Konten Kiri ── */}
                <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.7 }}
                    className="text-center md:text-left">

                    {/* Badge AI */}
                    <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.2 }}
                        className="inline-flex items-center gap-2 px-4 py-2 rounded-full glass text-xs font-bold text-primary-600 uppercase tracking-widest mb-8 shadow-sm">
                        <span className="relative flex h-2 w-2">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75" />
                            <span className="relative inline-flex rounded-full h-2 w-2 bg-primary-500" />
                        </span>
                        AI-Powered Store
                    </motion.div>

                    <h1 className="text-4xl sm:text-5xl md:text-6xl font-black text-dark-900 leading-[1.08] mb-6 tracking-tight">
                        Solusi cerdas untuk<br />
                        <span className="gradient-text">segala kebutuhanmu.</span>
                    </h1>

                    <p className="text-base sm:text-lg text-dark-900/50 mb-10 font-medium leading-relaxed max-w-lg mx-auto md:mx-0">
                        {S.desc_toko || 'Temukan layanan terbaik kami dengan bantuan Asisten AI yang siap membantu 24/7.'}
                    </p>

                    {/* CTA Buttons */}
                    <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <a href="#katalog" className="group px-8 py-4 rounded-2xl gradient-primary text-white font-bold shadow-lg hover:shadow-2xl flex items-center justify-center gap-2.5 hover:-translate-y-1 transition-all active:scale-95">
                            <ShoppingBag size={18} /> Lihat Layanan
                            <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform" />
                        </a>
                        <button onClick={onChat} className="px-8 py-4 rounded-2xl glass font-bold text-dark-900 hover:bg-white flex items-center justify-center gap-2.5 hover:-translate-y-1 transition-all active:scale-95">
                            <Sparkles size={18} className="text-primary-500" /> Tanya AI Gratis
                        </button>
                    </div>

                    {/* Trust Badges */}
                    <div className="flex flex-wrap gap-6 mt-12 justify-center md:justify-start">
                        {TRUST_BADGES(productCount).map((b, i) => (
                            <div key={i} className="flex items-center gap-2 text-xs font-bold text-dark-900/40">
                                <span className="w-7 h-7 rounded-lg bg-white/80 shadow-sm flex items-center justify-center text-primary-500">
                                    {b.icon}
                                </span>
                                {b.text}
                            </div>
                        ))}
                    </div>
                </motion.div>

                {/* ── Visual Kanan ── */}
                <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.3, duration: 0.7 }}
                    className="hidden md:block relative">
                    <div className="aspect-square max-w-md mx-auto glass-card rounded-[2.5rem] overflow-hidden flex items-center justify-center relative">
                        {S.logo
                            ? <img src={`/assets/img/produk/${S.logo}`} className="w-full h-full object-cover opacity-40 blur-sm scale-110" alt="" />
                            : <div className="text-8xl font-black gradient-text opacity-30">{S.nama_toko.charAt(0)}</div>}
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="glass rounded-3xl p-6 text-center shadow-xl">
                                <div className="w-14 h-14 rounded-2xl gradient-primary text-white flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <Sparkles size={24} />
                                </div>
                                <p className="font-extrabold text-dark-900 text-sm">Asisten AI Siap Melayani</p>
                                <p className="text-[11px] text-dark-900/50 mt-1 font-medium">Tanya apapun, kapanpun</p>
                            </div>
                        </div>
                    </div>

                    {/* Floating Review Card */}
                    <motion.div animate={{ y: [0, -12, 0] }} transition={{ duration: 5, repeat: Infinity }}
                        className="absolute -bottom-4 -left-8 glass-card p-4 rounded-2xl flex items-start gap-3 w-56 z-10 shadow-xl">
                        <div className="w-8 h-8 rounded-xl bg-primary-100 text-primary-500 flex items-center justify-center shrink-0">
                            <Star size={14} />
                        </div>
                        <div>
                            <p className="text-xs font-bold text-dark-900">Terpercaya</p>
                            <p className="text-[10px] text-dark-900/50 mt-0.5">"Layanan cepat &amp; ramah!"</p>
                        </div>
                    </motion.div>
                </motion.div>
            </div>
        </section>
    );
}