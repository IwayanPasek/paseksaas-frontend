/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/Hero.jsx
   Premium minimalist hero section
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { ShoppingBag, Sparkles, Star } from 'lucide-react';
import { StoreData } from '@/lib/store';

export default function Hero({ onChat, productCount }) {
    return (
        <section className="relative pt-32 pb-24 px-6 min-h-[90vh] flex flex-col justify-center items-center bg-transparent overflow-hidden text-center">
            {/* Subtle Gradient Glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[500px] bg-indigo-500/10 custom-blur rounded-full pointer-events-none mix-blend-screen" style={{ filter: 'blur(100px)' }}></div>
            
            <div className="max-w-4xl mx-auto relative z-10 w-full flex flex-col items-center">
                
                {/* Store Presentation Logo/Avatar */}
                <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }} 
                    className="w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden mb-8 border border-white/10 shadow-[0_0_40px_rgba(255,255,255,0.05)] bg-[#0A0A0A] flex flex-col items-center justify-center">
                    {StoreData.logo 
                        ? <img src={`/assets/img/produk/${StoreData.logo}`} className="w-full h-full object-cover" alt={StoreData.storeName} />
                        : <span className="font-display font-bold text-4xl text-neutral-500">{StoreData.storeName.charAt(0)}</span>
                    }
                </motion.div>

                {/* Main Headline */}
                <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1, duration: 0.5 }}
                    className="text-5xl sm:text-7xl font-display font-bold text-white tracking-tighter leading-[1.05] mb-6 drop-shadow-sm">
                    {StoreData.storeName}
                </motion.h1>

                {/* Subheading */}
                <motion.p initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2, duration: 0.5 }}
                    className="text-lg sm:text-xl text-neutral-400 max-w-2xl font-light leading-relaxed mb-10">
                    {StoreData.storeDescription || 'A premium collection curated specifically to meet the standards of your modern lifestyle.'}
                </motion.p>

                {/* Call to Actions */}
                <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3, duration: 0.5 }}
                    className="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                    <button onClick={() => document.getElementById('katalog')?.scrollIntoView({behavior: 'smooth'})} 
                        className="w-full sm:w-auto px-8 py-4 bg-white text-black rounded-full font-semibold text-sm flex items-center justify-center gap-3 hover:scale-105 active:scale-95 transition-all shadow-[0_0_30px_rgba(255,255,255,0.15)]">
                        Start Shopping <ShoppingBag size={16} />
                    </button>
                    
                    <button onClick={onChat} 
                        className="w-full sm:w-auto px-8 py-4 bg-[#0A0A0A] border border-white/10 text-white rounded-full font-medium text-sm flex items-center justify-center gap-3 hover:bg-white/5 active:scale-95 transition-all relative overflow-hidden group">
                        <div className="absolute inset-0 bg-gradient-to-r from-indigo-500/0 via-indigo-500/10 to-indigo-500/0 opacity-0 group-hover:opacity-100 group-hover:translate-x-full transition-all duration-1000"></div>
                        <Sparkles size={16} className="text-indigo-400" /> Ask AI Assistant
                    </button>
                </motion.div>

                {/* Trust Badges Minimalist */}
                <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.5, duration: 1 }}
                    className="flex items-center gap-6 mt-14 text-xs font-medium text-neutral-500">
                    <div className="flex items-center gap-1.5"><Star size={12} className="text-yellow-500" /> Premium Quality</div>
                    <div className="w-1 h-1 rounded-full bg-neutral-800"></div>
                    <div className="flex items-center gap-1.5"><Sparkles size={12} className="text-indigo-400" /> AI Powered</div>
                    <div className="w-1 h-1 rounded-full bg-neutral-800"></div>
                    <div>{productCount} Collections Available</div>
                </motion.div>
            </div>
            
            {/* Scroll Indicator */}
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1, duration: 1 }}
                className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-neutral-600 hidden md:flex">
                <span className="text-[10px] uppercase font-bold tracking-widest">Scroll</span>
                <div className="w-px h-8 bg-gradient-to-b from-neutral-600 to-transparent"></div>
            </motion.div>
        </section>
    );
}