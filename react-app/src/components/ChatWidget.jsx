/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/ChatWidget.jsx
   Floating AI Chat — full-screen mobile, panel desktop.
   ═══════════════════════════════════════════════════ */

import React, { useRef, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, X, Send, MessageSquare, ExternalLink } from 'lucide-react';
import { S, fmt } from '../lib/store';

const CHIPS = ['Tampilkan semua layanan', 'Layanan termurah', 'Hubungi Admin'];

/* ── Bubble tunggal ── */
function Bubble({ msg }) {
    const isUser = msg.role === 'user';
    return (
        <div className={`flex flex-col gap-2 w-full animate-slide-up ${isUser ? 'items-end' : 'items-start'}`}>
            <div className={`flex gap-2.5 max-w-[85%] ${isUser ? 'flex-row-reverse' : ''}`}>
                {/* Avatar */}
                <div className={`w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 text-xs font-bold ${isUser ? 'bg-surface-200 text-dark-900/60' : 'gradient-primary text-white shadow-sm'}`}>
                    {isUser ? 'U' : <Sparkles size={12} />}
                </div>
                {/* Teks */}
                <div className={`p-3.5 text-[13px] font-medium leading-relaxed ${isUser
                    ? 'bg-dark-900 text-white rounded-2xl rounded-tr-sm'
                    : 'bg-white text-dark-900/80 rounded-2xl rounded-tl-sm border border-surface-200/50 shadow-sm'}`}>
                    {msg.text.split('\\n').map((line, i, arr) => (
                        <span key={i}>{line}{i < arr.length - 1 && <br />}</span>
                    ))}
                </div>
            </div>

            {/* Mini product cards */}
            {msg.products?.length > 0 && (
                <div className="flex gap-2 max-w-[90%] pl-10 overflow-x-auto no-scrollbar pb-1">
                    {msg.products.map(p => (
                        <div key={p.id_produk} className="glass-card p-3 rounded-xl w-[180px] shrink-0">
                            {p.foto_produk && (
                                <img src={`/assets/img/produk/${p.foto_produk}`}
                                    className="w-full h-20 object-cover rounded-lg mb-2 bg-surface-100" alt={p.nama_produk} />
                            )}
                            <div className="font-bold text-xs text-dark-900 line-clamp-1 mb-0.5">{p.nama_produk}</div>
                            <div className="font-extrabold text-primary-500 text-xs">Rp {fmt(p.harga)}</div>
                        </div>
                    ))}
                </div>
            )}

            {/* WhatsApp CTA */}
            {msg.showWaCard && (
                <div className="pl-10 max-w-[85%]">
                    <a href={`https://wa.me/${S.wa_num}`} target="_blank" rel="noopener noreferrer"
                        className="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors group">
                        <div className="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <MessageSquare size={16} />
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="font-bold text-emerald-800 text-xs">Chat via WhatsApp</p>
                            <p className="text-[10px] text-emerald-600/70 font-medium">Hubungi admin langsung</p>
                        </div>
                        <ExternalLink size={14} className="text-emerald-400 group-hover:text-emerald-600 transition-colors shrink-0" />
                    </a>
                </div>
            )}
        </div>
    );
}

/* ── Typing indicator ── */
function TypingDots() {
    return (
        <div className="flex gap-2.5 max-w-[85%]">
            <div className="w-7 h-7 rounded-lg gradient-primary text-white flex items-center justify-center shadow-sm">
                <Sparkles size={12} />
            </div>
            <div className="bg-white px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm border border-surface-200/50 flex items-center gap-1.5">
                {[0, 0.15, 0.3].map((delay, i) => (
                    <span key={i} className="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce" style={{ animationDelay: `${delay}s` }} />
                ))}
            </div>
        </div>
    );
}

/* ── Main widget ── */
export default function ChatWidget({ open, onClose, messages, input, setInput, onSend, typing, onChip, totalItems }) {
    const endRef = useRef(null);
    useEffect(() => { endRef.current?.scrollIntoView({ behavior: 'smooth' }); }, [messages, typing]);

    return (
        <motion.div
            initial={{ opacity: 0, y: 50, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 20, scale: 0.95 }}
            transition={{ type: 'spring', damping: 28 }}
            className={`fixed bottom-0 right-0 w-[100vw] h-[100dvh] md:w-[400px] ${totalItems > 0 ? 'md:bottom-28' : 'md:bottom-24'} md:right-8 md:h-[600px] bg-white md:rounded-[2rem] shadow-2xl flex flex-col z-[100] border border-surface-200/50 overflow-hidden`}>

            {/* Header */}
            <div className="px-5 py-4 flex items-center justify-between border-b border-surface-200/50 shrink-0 bg-white">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl gradient-primary text-white flex items-center justify-center shadow-md">
                        <Sparkles size={18} />
                    </div>
                    <div>
                        <h3 className="font-extrabold text-dark-900 text-sm">Asisten {S.nama_toko}</h3>
                        <div className="flex items-center gap-1.5 text-[10px] text-dark-900/40 font-bold uppercase tracking-wider mt-0.5">
                            <span className="relative flex h-1.5 w-1.5">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                                <span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500" />
                            </span>
                            Online
                        </div>
                    </div>
                </div>
                <button onClick={onClose} aria-label="Tutup chat"
                    className="w-8 h-8 rounded-xl bg-surface-100 text-dark-900/50 flex items-center justify-center hover:bg-surface-200 transition-colors">
                    <X size={16} />
                </button>
            </div>

            {/* Quick chips */}
            <div className="flex gap-2 px-4 pt-3 pb-2 overflow-x-auto no-scrollbar shrink-0 bg-surface-50/50">
                {CHIPS.map((c, i) => (
                    <button key={i} onClick={() => onChip(c)}
                        className="px-3.5 py-1.5 glass rounded-full text-[11px] font-bold text-dark-900/70 hover:bg-primary-500 hover:text-white shadow-sm whitespace-nowrap transition-all active:scale-95">
                        {c === 'Hubungi Admin' ? '💬 ' : '✨ '}{c}
                    </button>
                ))}
            </div>

            {/* Messages */}
            <div className="flex-1 bg-surface-50 p-5 overflow-y-auto flex flex-col gap-4">
                {messages.length === 0 && !typing && (
                    <div className="flex-1 flex flex-col items-center justify-center text-center py-8">
                        <div className="w-16 h-16 rounded-2xl gradient-primary text-white flex items-center justify-center mb-4 shadow-lg">
                            <Sparkles size={28} />
                        </div>
                        <h4 className="font-extrabold text-dark-900 text-sm mb-1">Halo! 👋</h4>
                        <p className="text-xs text-dark-900/40 font-medium max-w-[220px]">
                            Saya asisten AI {S.nama_toko}. Mau tanya tentang layanan kami?
                        </p>
                    </div>
                )}

                {messages.map((msg, idx) => <Bubble key={idx} msg={msg} />)}
                {typing && <TypingDots />}
                <div ref={endRef} />
            </div>

            {/* Input Bar */}
            <div className="p-4 bg-white border-t border-surface-200/50 shrink-0">
                <div className="flex items-end gap-2 bg-surface-50 border-2 border-surface-200/50 rounded-2xl p-1.5 focus-within:border-primary-500 transition-all">
                    <textarea
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); onSend(); } }}
                        placeholder="Ketik pesan..."
                        disabled={typing}
                        className="flex-1 bg-transparent border-none outline-none px-3 py-2 text-[13px] font-medium text-dark-900 resize-none max-h-[80px] min-h-[38px] placeholder:text-dark-900/30"
                    />
                    <button onClick={onSend} disabled={typing || !input.trim()} aria-label="Kirim pesan"
                        className="w-9 h-9 shrink-0 rounded-xl gradient-primary text-white flex items-center justify-center active:scale-95 transition-transform disabled:opacity-40 shadow-sm">
                        <Send size={14} />
                    </button>
                </div>
            </div>
        </motion.div>
    );
}