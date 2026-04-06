/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/Hero.jsx
   Premium minimalist hero section
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { ArrowRight, Sparkles, Zap, Clock, Package } from 'lucide-react';
import { S } from '@/lib/store';

const TRUST_BADGES = (count) => [
    { icon: <Zap size={13} />, text: 'AI-Powered' },
    { icon: <Clock size={13} />, text: '24/7 Online' },
    { icon: <Package size={13} />, text: `${count} Layanan` },
];

export default function Hero({ onChat, productCount }) {
    return (
        <section className="relative pt-32 pb-28 px-6 min-h-[88vh] flex items-center bg-neutral-50">
            {/* Subtle grid pattern */}
            <div className="absolute inset-0 opacity-[0.03]" style={{ backgroundImage: 'radial-gradient(#171717 1px, transparent 1px)', backgroundSize: '24px 24px' }} />

            <div className="max-w-6xl mx-auto w-full grid md:grid-cols-2 gap-16 items-center relative z-10">
                {/* ── Left Content ── */}
                <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.5 }}
                    className="text-center md:text-left">

                    {/* Badge */}
                    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.15 }}
                        className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-neutral-200 bg-white text-[11px] font-medium text-neutral-600 mb-8">
                        <span className="relative flex h-1.5 w-1.5">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-500 opacity-75" />
                            <span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-success-500" />
                        </span>
                        AI-Powered Store
                    </motion.div>

                    <h1 className="text-4xl sm:text-5xl md:text-[3.5rem] font-bold text-neutral-900 leading-[1.1] mb-6 tracking-tight">
                        Solusi cerdas untuk<br />
                        <span className="text-neutral-400">segala kebutuhanmu.</span>
                    </h1>

                    <p className="text-base sm:text-lg text-neutral-500 mb-10 font-normal leading-relaxed max-w-md mx-auto md:mx-0">
                        {S.desc_toko || 'Temukan layanan terbaik kami dengan bantuan Asisten AI yang siap membantu 24/7.'}
                    </p>

                    {/* CTA Buttons */}
                    <div className="flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                        <a href="#katalog" className="group px-7 py-3.5 rounded-xl bg-neutral-900 text-white font-medium text-sm flex items-center justify-center gap-2.5 hover:bg-neutral-800 transition-all active:scale-[0.98]">
                            Lihat Layanan
                            <ArrowRight size={15} className="group-hover:translate-x-0.5 transition-transform" />
                        </a>
                        <button onClick={onChat} className="px-7 py-3.5 rounded-xl border border-neutral-200 bg-white font-medium text-sm text-neutral-700 flex items-center justify-center gap-2.5 hover:border-neutral-300 hover:bg-neutral-50 transition-all active:scale-[0.98]">
                            <Sparkles size={15} className="text-neutral-400" /> Tanya AI Gratis
                        </button>
                    </div>

                    {/* Trust Badges */}
                    <div className="flex flex-wrap gap-5 mt-10 justify-center md:justify-start">
                        {TRUST_BADGES(productCount).map((b, i) => (
                            <div key={i} className="flex items-center gap-2 text-xs font-medium text-neutral-400">
                                <span className="w-6 h-6 rounded-md bg-neutral-100 flex items-center justify-center text-neutral-500">
                                    {b.icon}
                                </span>
                                {b.text}
                            </div>
                        ))}
                    </div>
                </motion.div>

                {/* ── Right Visual ── */}
                <motion.div initial={{ opacity: 0, scale: 0.96 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.2, duration: 0.5 }}
                    className="hidden md:block relative">
                    <div className="aspect-square max-w-sm mx-auto card rounded-2xl overflow-hidden flex items-center justify-center relative">
                        {S.logo
                            ? <img src={`/assets/img/produk/${S.logo}`} className="w-full h-full object-cover opacity-20 scale-110" alt="" />
                            : <div className="text-8xl font-bold text-neutral-200">{S.nama_toko.charAt(0)}</div>}
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="bg-white border border-neutral-200 rounded-xl p-5 text-center shadow-sm">
                                <div className="w-11 h-11 rounded-xl bg-neutral-900 text-white flex items-center justify-center mx-auto mb-3">
                                    <Sparkles size={20} />
                                </div>
                                <p className="font-semibold text-neutral-900 text-sm">Asisten AI Siap Melayani</p>
                                <p className="text-[11px] text-neutral-400 mt-1">Tanya apapun, kapanpun</p>
                            </div>
                        </div>
                    </div>

                    {/* Floating Card */}
                    <motion.div animate={{ y: [0, -8, 0] }} transition={{ duration: 4, repeat: Infinity, ease: 'easeInOut' }}
                        className="absolute -bottom-3 -left-6 bg-white border border-neutral-200 p-3.5 rounded-xl flex items-start gap-3 w-52 z-10 shadow-sm">
                        <div className="w-7 h-7 rounded-lg bg-neutral-100 text-neutral-500 flex items-center justify-center shrink-0">
                            <Sparkles size={13} />
                        </div>
                        <div>
                            <p className="text-xs font-semibold text-neutral-900">Terpercaya</p>
                            <p className="text-[10px] text-neutral-400 mt-0.5">"Layanan cepat & ramah!"</p>
                        </div>
                    </motion.div>
                </motion.div>
            </div>
        </section>
    );
}