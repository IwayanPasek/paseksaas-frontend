/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/FaqSection.jsx
   Premium minimalist FAQ accordion
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { HelpCircle, ChevronDown } from 'lucide-react';

export default function FaqSection({ faqItems, activeIndex, onToggle }) {
    if (!faqItems || !faqItems.length) return null;

    return (
        <section className="py-24 px-6 relative z-10 bg-transparent">
            <div className="max-w-2xl mx-auto">
                {/* Header */}
                <div className="text-center mb-12">
                    <span className="w-11 h-11 bg-neutral-900 border border-neutral-800 text-white rounded-xl flex items-center justify-center mx-auto mb-4">
                        <HelpCircle size={20} className="text-indigo-400" />
                    </span>
                    <h2 className="text-2xl md:text-3xl font-bold text-white tracking-tight">Frequently Asked Questions</h2>
                    <p className="text-neutral-500 font-normal mt-2 text-sm">
                        Quick answers to common questions about our products and services.
                    </p>
                </div>

                {/* Accordion */}
                <div className="space-y-2">
                    {faqItems.map((item, index) => (
                        <div key={item.id_faq} className="glass-card rounded-xl overflow-hidden">
                            <button
                                onClick={() => onToggle(index)}
                                aria-expanded={activeIndex === index}
                                className="w-full flex items-center justify-between p-4 text-left font-medium text-white text-sm focus:outline-none hover:bg-white/5 transition-colors">
                                {item.pertanyaan}
                                <ChevronDown size={16}
                                    className={`text-neutral-500 transition-transform duration-200 shrink-0 ml-4 ${activeIndex === index ? 'rotate-180 text-white' : ''}`} />
                            </button>

                            <AnimatePresence>
                                {activeIndex === index && (
                                    <motion.div
                                        initial={{ height: 0, opacity: 0 }}
                                        animate={{ height: 'auto', opacity: 1 }}
                                        exit={{ height: 0, opacity: 0 }}
                                        transition={{ duration: 0.2 }}
                                        style={{ overflow: 'hidden' }}
                                        className="text-sm text-neutral-400 leading-relaxed border-t border-neutral-800/50 bg-black/20">
                                        <div className="px-4 py-4">
                                        {item.jawaban}
                                        </div>
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