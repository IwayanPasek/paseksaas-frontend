/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/FaqSection.jsx
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { HelpCircle, ChevronDown } from 'lucide-react';

export default function FaqSection({ items, openIdx, onToggle }) {
    if (!items.length) return null;

    return (
        <section className="py-24 px-6 relative z-10">
            <div className="max-w-3xl mx-auto">
                {/* Header */}
                <div className="text-center mb-14">
                    <span className="w-14 h-14 gradient-primary text-white rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg">
                        <HelpCircle size={24} />
                    </span>
                    <h2 className="text-3xl md:text-4xl font-black text-dark-900 tracking-tight">Pertanyaan Umum</h2>
                    <p className="text-dark-900/50 font-medium mt-3 text-sm">
                        Jawaban cepat untuk pertanyaan yang sering ditanyakan.
                    </p>
                </div>

                {/* Accordion */}
                <div className="space-y-3">
                    {items.map((f, i) => (
                        <div key={f.id_faq} className="glass-card rounded-2xl overflow-hidden">
                            <button
                                onClick={() => onToggle(i)}
                                aria-expanded={openIdx === i}
                                className="w-full flex items-center justify-between p-5 text-left font-extrabold text-dark-900 text-sm focus:outline-none">
                                {f.pertanyaan}
                                <ChevronDown size={18}
                                    className={`text-dark-900/30 transition-transform duration-300 shrink-0 ml-4 ${openIdx === i ? 'rotate-180 text-primary-500' : ''}`} />
                            </button>

                            <AnimatePresence>
                                {openIdx === i && (
                                    <motion.div
                                        initial={{ height: 0, opacity: 0 }}
                                        animate={{ height: 'auto', opacity: 1 }}
                                        exit={{ height: 0, opacity: 0 }}
                                        transition={{ duration: 0.3 }}
                                        className="px-5 pb-5 text-sm text-dark-900/60 font-medium leading-relaxed">
                                        {f.jawaban}
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}