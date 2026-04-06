import React, { useState, useEffect, useRef, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  Sparkles, MessageSquare, X, Send, Image as ImageIcon,
  ShoppingBag, Filter, Search, ShoppingCart,
  Plus, Minus, ChevronDown, HelpCircle, ExternalLink,
  Zap, Clock, Package, Star, ArrowRight
} from 'lucide-react';

/* ═══════════════════════════════════════════════════
   DATA LAYER
   ═══════════════════════════════════════════════════ */
const S = window.STORE_DATA || { id_toko: 1, nama_toko: 'Toko AI', desc_toko: '', wa_num: '6281234567890', logo: null, products: [], categories: [], faq: [] };
const cats = Array.isArray(S.categories) ? S.categories : [];
const prods = Array.isArray(S.products) ? S.products : [];
const faqs = Array.isArray(S.faq) ? S.faq : [];
const fmt = (n) => Number(n).toLocaleString('id-ID');

/* ═══════════════════════════════════════════════════
   SUB-COMPONENTS
   ═══════════════════════════════════════════════════ */

// ── Navbar ──
function Navbar({ scrolled }) {
  return (
    <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${scrolled ? 'glass shadow-lg py-3' : 'bg-transparent py-5'}`}>
      <div className="max-w-6xl mx-auto px-6 flex justify-between items-center">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl gradient-primary text-white flex items-center justify-center shadow-lg overflow-hidden shrink-0 text-sm font-black">
            {S.logo ? <img src={`/assets/img/produk/${S.logo}`} alt={S.nama_toko} className="w-full h-full object-cover" /> : S.nama_toko.charAt(0).toUpperCase()}
          </div>
          <span className="font-extrabold text-lg text-dark-900 tracking-tight truncate max-w-[180px] sm:max-w-none">{S.nama_toko}</span>
        </div>
        <div className="flex items-center gap-3">
          <a href="#katalog" className="hidden sm:flex px-5 py-2 rounded-full text-sm font-bold text-dark-900/70 hover:text-dark-900 transition-colors">Katalog</a>
          <a href="/login.php" className="hidden md:flex px-5 py-2.5 rounded-full gradient-primary text-white text-sm font-bold shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all" aria-label="Login ke Admin Area">Admin Area</a>
        </div>
      </div>
    </nav>
  );
}

// ── Hero ──
function Hero({ onChat, productCount }) {
  return (
    <section className="relative pt-28 pb-24 px-6 min-h-[92vh] flex items-center overflow-hidden gradient-hero">
      {/* Floating orbs */}
      <div className="orb w-72 h-72 bg-primary-400 top-20 -left-20 animate-float" />
      <div className="orb w-96 h-96 bg-accent-400 -bottom-20 right-0 animate-float-slow" />
      <div className="orb w-48 h-48 bg-cyan-400 top-1/2 left-1/2 animate-float" style={{ animationDelay: '2s' }} />

      <div className="max-w-6xl mx-auto w-full grid md:grid-cols-2 gap-16 items-center relative z-10">
        <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.7 }} className="text-center md:text-left">
          {/* Badge */}
          <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.2 }}
            className="inline-flex items-center gap-2 px-4 py-2 rounded-full glass text-xs font-bold text-primary-600 uppercase tracking-widest mb-8 shadow-sm">
            <span className="relative flex h-2 w-2"><span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75" /><span className="relative inline-flex rounded-full h-2 w-2 bg-primary-500" /></span>
            AI-Powered Store
          </motion.div>

          <h1 className="text-4xl sm:text-5xl md:text-6xl font-black text-dark-900 leading-[1.08] mb-6 tracking-tight">
            Solusi cerdas untuk<br />
            <span className="gradient-text">segala kebutuhanmu.</span>
          </h1>
          <p className="text-base sm:text-lg text-dark-900/50 mb-10 font-medium leading-relaxed max-w-lg mx-auto md:mx-0">{S.desc_toko || 'Temukan layanan terbaik kami dengan bantuan Asisten AI yang siap membantu 24/7.'}</p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
            <a href="#katalog" className="group px-8 py-4 rounded-2xl gradient-primary text-white font-bold shadow-lg hover:shadow-2xl flex items-center justify-center gap-2.5 hover:-translate-y-1 transition-all active:scale-95">
              <ShoppingBag size={18} /> Lihat Layanan <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform" />
            </a>
            <button onClick={onChat} className="px-8 py-4 rounded-2xl glass font-bold text-dark-900 hover:bg-white flex items-center justify-center gap-2.5 hover:-translate-y-1 transition-all active:scale-95">
              <Sparkles size={18} className="text-primary-500" /> Tanya AI Gratis
            </button>
          </div>

          {/* Trust badges */}
          <div className="flex flex-wrap gap-6 mt-12 justify-center md:justify-start">
            {[
              { icon: <Zap size={14} />, text: 'AI-Powered' },
              { icon: <Clock size={14} />, text: '24/7 Online' },
              { icon: <Package size={14} />, text: `${productCount} Layanan` },
            ].map((b, i) => (
              <div key={i} className="flex items-center gap-2 text-xs font-bold text-dark-900/40">
                <span className="w-7 h-7 rounded-lg bg-white/80 shadow-sm flex items-center justify-center text-primary-500">{b.icon}</span>{b.text}
              </div>
            ))}
          </div>
        </motion.div>

        {/* Hero Visual */}
        <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.3, duration: 0.7 }} className="hidden md:block relative">
          <div className="aspect-square max-w-md mx-auto glass-card rounded-[2.5rem] overflow-hidden flex items-center justify-center relative">
            {S.logo
              ? <img src={`/assets/img/produk/${S.logo}`} className="w-full h-full object-cover opacity-40 blur-sm scale-110" alt="" />
              : <div className="text-8xl font-black gradient-text opacity-30">{S.nama_toko.charAt(0)}</div>}
            <div className="absolute inset-0 flex items-center justify-center">
              <div className="glass rounded-3xl p-6 text-center shadow-xl">
                <div className="w-14 h-14 rounded-2xl gradient-primary text-white flex items-center justify-center mx-auto mb-3 shadow-lg"><Sparkles size={24} /></div>
                <p className="font-extrabold text-dark-900 text-sm">Asisten AI Siap Melayani</p>
                <p className="text-[11px] text-dark-900/50 mt-1 font-medium">Tanya apapun, kapanpun</p>
              </div>
            </div>
          </div>

          {/* Floating card */}
          <motion.div animate={{ y: [0, -12, 0] }} transition={{ duration: 5, repeat: Infinity }} className="absolute -bottom-4 -left-8 glass-card p-4 rounded-2xl flex items-start gap-3 w-56 z-10 shadow-xl">
            <div className="w-8 h-8 rounded-xl bg-primary-100 text-primary-500 flex items-center justify-center shrink-0"><Star size={14} /></div>
            <div>
              <p className="text-xs font-bold text-dark-900">Terpercaya</p>
              <p className="text-[10px] text-dark-900/50 mt-0.5">"Layanan cepat & ramah!"</p>
            </div>
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
}

// ── Product Card ──
function ProductCard({ prod, cartItem, onAsk, onAdd, onQty, imgLoaded, onImgLoad }) {
  const catName = cats.find(c => c.id_kategori == prod.id_kategori)?.nama_kategori;
  return (
    <motion.div layout initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, scale: 0.95 }}
      transition={{ duration: 0.3 }} whileHover={{ y: -6 }}
      className="glass-card rounded-[1.8rem] overflow-hidden flex flex-col group">
      {/* Image */}
      <div className="aspect-[4/3] bg-surface-100 relative overflow-hidden">
        {!imgLoaded && prod.foto_produk && <div className="skeleton absolute inset-0" />}
        {prod.foto_produk ? (
          <img src={`/assets/img/produk/${prod.foto_produk}`} alt={prod.nama_produk} loading="lazy"
            onLoad={onImgLoad} onError={(e) => { e.target.style.display = 'none'; }}
            className={`w-full h-full object-cover transition-all duration-700 ${imgLoaded ? 'opacity-100 group-hover:scale-110' : 'opacity-0'}`} />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-surface-300"><ImageIcon size={36} /></div>
        )}
        {catName && (
          <span className="absolute top-4 left-4 px-3 py-1.5 rounded-full glass text-[10px] font-black text-primary-600 uppercase tracking-wider">{catName}</span>
        )}
      </div>
      {/* Info */}
      <div className="p-5 flex flex-col flex-1">
        <h3 className="font-extrabold text-base text-dark-900 mb-1.5 leading-snug line-clamp-1">{prod.nama_produk}</h3>
        <p className="text-xs text-dark-900/50 mb-4 line-clamp-2 flex-1 font-medium leading-relaxed">{prod.deskripsi}</p>
        <div className="border-t border-surface-200/60 pt-4 mb-4 flex items-baseline gap-1">
          <span className="text-[10px] text-dark-900/40 font-bold">Rp</span>
          <span className="font-black text-xl text-dark-900">{fmt(prod.harga)}</span>
        </div>
        <div className="flex flex-col gap-2">
          <button onClick={() => onAsk(prod.nama_produk)} className="w-full py-2.5 rounded-xl bg-primary-50 text-primary-600 font-bold text-xs flex justify-center items-center gap-2 hover:bg-primary-500 hover:text-white transition-all active:scale-95" aria-label={`Tanya AI tentang ${prod.nama_produk}`}>
            <Sparkles size={13} /> Tanya AI
          </button>
          {cartItem ? (
            <div className="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl p-1.5 h-[42px]">
              <button onClick={() => onQty(prod.id_produk, -1)} className="w-8 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all" aria-label="Kurangi jumlah"><Minus size={15} strokeWidth={3} /></button>
              <span className="font-black text-emerald-600 text-sm">{cartItem.qty}</span>
              <button onClick={() => onQty(prod.id_produk, 1)} className="w-8 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all" aria-label="Tambah jumlah"><Plus size={15} strokeWidth={3} /></button>
            </div>
          ) : (
            <button onClick={() => onAdd(prod)} className="w-full h-[42px] rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 font-bold text-xs flex justify-center items-center gap-2 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 active:scale-95 transition-all" aria-label={`Tambah ${prod.nama_produk} ke keranjang`}>
              <ShoppingCart size={13} /> Tambah
            </button>
          )}
        </div>
      </div>
    </motion.div>
  );
}

// ── FAQ Section ──
function FaqSection({ items, openIdx, onToggle }) {
  if (!items.length) return null;
  return (
    <section className="py-24 px-6 relative z-10">
      <div className="max-w-3xl mx-auto">
        <div className="text-center mb-14">
          <span className="w-14 h-14 gradient-primary text-white rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg"><HelpCircle size={24} /></span>
          <h2 className="text-3xl md:text-4xl font-black text-dark-900 tracking-tight">Pertanyaan Umum</h2>
          <p className="text-dark-900/50 font-medium mt-3 text-sm">Jawaban cepat untuk pertanyaan yang sering ditanyakan.</p>
        </div>
        <div className="space-y-3">
          {items.map((f, i) => (
            <div key={f.id_faq} className="glass-card rounded-2xl overflow-hidden">
              <button onClick={() => onToggle(i)} className="w-full flex items-center justify-between p-5 text-left font-extrabold text-dark-900 text-sm focus:outline-none" aria-expanded={openIdx === i}>
                {f.pertanyaan}
                <ChevronDown size={18} className={`text-dark-900/30 transition-transform duration-300 shrink-0 ml-4 ${openIdx === i ? 'rotate-180 text-primary-500' : ''}`} />
              </button>
              <AnimatePresence>
                {openIdx === i && (
                  <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: 'auto', opacity: 1 }} exit={{ height: 0, opacity: 0 }} transition={{ duration: 0.3 }}
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

// ── Cart Sheet ──
function CartSheet({ cart, open, onClose, onQty, total, onCheckout }) {
  if (!open || !cart.length) return null;
  return (
    <>
      <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} onClick={onClose} className="fixed inset-0 bg-dark-900/50 backdrop-blur-sm z-[70]" />
      <motion.div initial={{ y: '100%' }} animate={{ y: 0 }} exit={{ y: '100%' }} transition={{ type: 'spring', damping: 25, stiffness: 200 }}
        className="fixed bottom-0 left-0 right-0 md:left-1/2 md:-translate-x-1/2 md:w-[480px] md:bottom-8 bg-white rounded-t-[2rem] md:rounded-[2rem] shadow-2xl z-[80] overflow-hidden flex flex-col max-h-[85vh]">
        <div className="p-5 border-b border-surface-200/50 flex justify-between items-center shrink-0">
          <h3 className="font-black text-lg text-dark-900 flex items-center gap-2"><ShoppingCart className="text-emerald-500" size={20} /> Keranjang</h3>
          <button onClick={onClose} className="w-8 h-8 flex items-center justify-center bg-surface-100 text-dark-900/50 rounded-full hover:bg-surface-200 transition-colors" aria-label="Tutup keranjang"><X size={16} /></button>
        </div>
        <div className="flex-1 overflow-y-auto p-5 space-y-3 bg-surface-50">
          {cart.map(item => (
            <div key={item.id_produk} className="bg-white p-4 rounded-2xl border border-surface-200/50 shadow-sm flex justify-between items-center gap-4">
              <div className="flex items-center gap-3 flex-1 min-w-0">
                {item.foto_produk && <img src={`/assets/img/produk/${item.foto_produk}`} className="w-11 h-11 rounded-xl object-cover bg-surface-100 shrink-0" alt="" />}
                <div className="min-w-0">
                  <h4 className="font-bold text-dark-900 text-sm line-clamp-1">{item.nama_produk}</h4>
                  <p className="text-xs font-black text-primary-500 mt-0.5">Rp {fmt(item.harga)}</p>
                </div>
              </div>
              <div className="flex items-center bg-emerald-50 border border-emerald-200 rounded-xl p-1 h-9 shrink-0">
                <button onClick={() => onQty(item.id_produk, -1)} className="w-7 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all"><Minus size={13} strokeWidth={3} /></button>
                <span className="font-black text-emerald-600 text-xs w-6 text-center">{item.qty}</span>
                <button onClick={() => onQty(item.id_produk, 1)} className="w-7 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all"><Plus size={13} strokeWidth={3} /></button>
              </div>
            </div>
          ))}
        </div>
        <div className="p-5 bg-white border-t border-surface-200/50 shrink-0">
          <div className="flex justify-between items-center mb-4">
            <span className="text-sm font-bold text-dark-900/50">Total</span>
            <span className="text-2xl font-black text-dark-900">Rp {fmt(total)}</span>
          </div>
          <button onClick={onCheckout} className="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all active:scale-95">
            <MessageSquare size={16} /> Checkout via WhatsApp
          </button>
        </div>
      </motion.div>
    </>
  );
}

// ── Chat Widget ──
function ChatWidget({ open, onClose, messages, input, setInput, onSend, typing, onChip, totalItems }) {
  const endRef = useRef(null);
  useEffect(() => { endRef.current?.scrollIntoView({ behavior: 'smooth' }); }, [messages, typing]);

  const chips = ['Tampilkan semua layanan', 'Layanan termurah', 'Hubungi Admin'];

  return (
    <motion.div initial={{ opacity: 0, y: 50, scale: 0.95 }} animate={{ opacity: 1, y: 0, scale: 1 }} exit={{ opacity: 0, y: 20, scale: 0.95 }}
      transition={{ type: 'spring', damping: 28 }}
      className={`fixed bottom-0 right-0 w-[100vw] h-[100dvh] md:w-[400px] ${totalItems > 0 ? 'md:bottom-28' : 'md:bottom-24'} md:right-8 md:h-[600px] bg-white md:rounded-[2rem] shadow-2xl flex flex-col z-[100] border border-surface-200/50 overflow-hidden`}>
      {/* Header */}
      <div className="px-5 py-4 flex items-center justify-between border-b border-surface-200/50 shrink-0 bg-white">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl gradient-primary text-white flex items-center justify-center shadow-md"><Sparkles size={18} /></div>
          <div>
            <h3 className="font-extrabold text-dark-900 text-sm">Asisten {S.nama_toko}</h3>
            <div className="flex items-center gap-1.5 text-[10px] text-dark-900/40 font-bold uppercase tracking-wider mt-0.5">
              <span className="relative flex h-1.5 w-1.5"><span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" /><span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500" /></span>Online
            </div>
          </div>
        </div>
        <button onClick={onClose} className="w-8 h-8 rounded-xl bg-surface-100 text-dark-900/50 flex items-center justify-center hover:bg-surface-200 transition-colors" aria-label="Tutup chat"><X size={16} /></button>
      </div>

      {/* Quick chips */}
      <div className="flex gap-2 px-4 pt-3 pb-2 overflow-x-auto no-scrollbar shrink-0 bg-surface-50/50">
        {chips.map((c, i) => (
          <button key={i} onClick={() => onChip(c)} className="px-3.5 py-1.5 glass rounded-full text-[11px] font-bold text-dark-900/70 hover:bg-primary-500 hover:text-white shadow-sm whitespace-nowrap transition-all active:scale-95">
            {c === 'Hubungi Admin' ? '💬 ' : '✨ '}{c}
          </button>
        ))}
      </div>

      {/* Messages */}
      <div className="flex-1 bg-surface-50 p-5 overflow-y-auto flex flex-col gap-4">
        {messages.length === 0 && !typing && (
          <div className="flex-1 flex flex-col items-center justify-center text-center py-8">
            <div className="w-16 h-16 rounded-2xl gradient-primary text-white flex items-center justify-center mb-4 shadow-lg"><Sparkles size={28} /></div>
            <h4 className="font-extrabold text-dark-900 text-sm mb-1">Halo! 👋</h4>
            <p className="text-xs text-dark-900/40 font-medium max-w-[220px]">Saya asisten AI {S.nama_toko}. Mau tanya tentang layanan kami?</p>
          </div>
        )}
        {messages.map((msg, idx) => (
          <div key={idx} className={`flex flex-col gap-2 w-full animate-slide-up ${msg.role === 'user' ? 'items-end' : 'items-start'}`}>
            <div className={`flex gap-2.5 max-w-[85%] ${msg.role === 'user' ? 'flex-row-reverse' : ''}`}>
              <div className={`w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 text-xs font-bold ${msg.role === 'user' ? 'bg-surface-200 text-dark-900/60' : 'gradient-primary text-white shadow-sm'}`}>
                {msg.role === 'user' ? 'U' : <Sparkles size={12} />}
              </div>
              <div className={`p-3.5 text-[13px] font-medium leading-relaxed ${msg.role === 'user' ? 'bg-dark-900 text-white rounded-2xl rounded-tr-sm' : 'bg-white text-dark-900/80 rounded-2xl rounded-tl-sm border border-surface-200/50 shadow-sm'}`}>
                {msg.text.split('\\n').map((line, i) => <span key={i}>{line}{i < msg.text.split('\\n').length - 1 && <br />}</span>)}
              </div>
            </div>
            {/* Product cards in chat */}
            {msg.products?.length > 0 && (
              <div className="flex gap-2 max-w-[90%] pl-10 overflow-x-auto no-scrollbar pb-1">
                {msg.products.map(p => (
                  <div key={p.id_produk} className="glass-card p-3 rounded-xl w-[180px] shrink-0">
                    {p.foto_produk && <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-20 object-cover rounded-lg mb-2 bg-surface-100" alt={p.nama_produk} />}
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
                  <div className="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm"><MessageSquare size={16} /></div>
                  <div className="flex-1 min-w-0">
                    <p className="font-bold text-emerald-800 text-xs">Chat via WhatsApp</p>
                    <p className="text-[10px] text-emerald-600/70 font-medium">Hubungi admin langsung</p>
                  </div>
                  <ExternalLink size={14} className="text-emerald-400 group-hover:text-emerald-600 transition-colors shrink-0" />
                </a>
              </div>
            )}
          </div>
        ))}
        {typing && (
          <div className="flex gap-2.5 max-w-[85%]">
            <div className="w-7 h-7 rounded-lg gradient-primary text-white flex items-center justify-center shadow-sm"><Sparkles size={12} /></div>
            <div className="bg-white px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm border border-surface-200/50 flex items-center gap-1.5">
              <span className="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce" /><span className="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce" style={{ animationDelay: '0.15s' }} /><span className="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce" style={{ animationDelay: '0.3s' }} />
            </div>
          </div>
        )}
        <div ref={endRef} />
      </div>

      {/* Input */}
      <div className="p-4 bg-white border-t border-surface-200/50 shrink-0">
        <div className="flex items-end gap-2 bg-surface-50 border-2 border-surface-200/50 rounded-2xl p-1.5 focus-within:border-primary-500 transition-all">
          <textarea value={input} onChange={(e) => setInput(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); onSend(); } }}
            placeholder="Ketik pesan..." disabled={typing}
            className="flex-1 bg-transparent border-none outline-none px-3 py-2 text-[13px] font-medium text-dark-900 resize-none max-h-[80px] min-h-[38px] placeholder:text-dark-900/30" />
          <button onClick={onSend} disabled={typing || !input.trim()}
            className="w-9 h-9 shrink-0 rounded-xl gradient-primary text-white flex items-center justify-center active:scale-95 transition-transform disabled:opacity-40 shadow-sm" aria-label="Kirim pesan"><Send size={14} /></button>
        </div>
      </div>
    </motion.div>
  );
}

/* ═══════════════════════════════════════════════════
   MAIN APP
   ═══════════════════════════════════════════════════ */
export default function App() {
  const [selectedCat, setSelectedCat] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [isScrolled, setIsScrolled] = useState(false);
  const [imgLoaded, setImgLoaded] = useState({});
  const [openFaq, setOpenFaq] = useState(null);
  const [cart, setCart] = useState([]);
  const [cartOpen, setCartOpen] = useState(false);
  const [chatOpen, setChatOpen] = useState(false);
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState('');
  const [isTyping, setIsTyping] = useState(false);

  useEffect(() => {
    if (!localStorage.getItem('ai_session')) localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));
    const onScroll = () => setIsScrolled(window.scrollY > 50);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);
  useEffect(() => { if (cart.length === 0) setCartOpen(false); }, [cart]);

  // ── Filter ──
  const filtered = prods.filter(p => {
    const mc = selectedCat === 'all' || p.id_kategori == selectedCat;
    const ms = p.nama_produk.toLowerCase().includes(searchQuery.toLowerCase());
    return mc && ms;
  });

  // ── Cart logic ──
  const addToCart = (prod) => setCart(prev => [...prev, { ...prod, qty: 1 }]);
  const updateQty = (id, d) => setCart(prev => prev.map(i => i.id_produk === id ? { ...i, qty: i.qty + d } : i).filter(i => i.qty > 0));
  const totalItems = cart.reduce((a, i) => a + i.qty, 0);
  const totalPrice = cart.reduce((a, i) => a + i.harga * i.qty, 0);
  const checkoutWA = () => {
    let t = `Halo Admin *${S.nama_toko}*! Saya ingin memesan:\n\n`;
    cart.forEach(i => { t += `▪️ ${i.qty}x ${i.nama_produk} - Rp ${fmt(i.harga * i.qty)}\n`; });
    t += `\n*Total: Rp ${fmt(totalPrice)}*\n\nMohon diproses ya kak!`;
    window.open(`https://wa.me/${S.wa_num}?text=${encodeURIComponent(t)}`, '_blank');
  };

  // ── Chat logic ──
  const handleSend = useCallback(async (text = inputText, skipBubble = false) => {
    const msg = text.trim();
    if (!msg || isTyping) return;
    if (!skipBubble) setMessages(prev => [...prev, { role: 'user', text: msg }]);
    setInputText(''); setIsTyping(true);
    if (!chatOpen) setChatOpen(true);
    try {
      const res = await fetch('/api/chat', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_toko: S.id_toko, session_id: localStorage.getItem('ai_session'), user_message: msg }) });
      const data = await res.json();
      const reply = { role: 'ai', text: data.reply || 'Maaf, saya tidak mengerti.', products: data.db_result || [], showWaCard: false };
      if (data.reply && /whatsapp|pesan|hubungi/i.test(data.reply)) reply.showWaCard = true;
      setMessages(prev => [...prev, reply]);
    } catch { setMessages(prev => [...prev, { role: 'ai', text: '⚠️ Gagal terhubung ke server AI.' }]); }
    finally { setIsTyping(false); }
  }, [inputText, isTyping, chatOpen]);

  const tanyaProduk = (nama) => { setChatOpen(true); setTimeout(() => handleSend(`Jelaskan detail layanan "${nama}" dong!`, false), 300); };

  const handleChip = (text) => {
    if (text.includes('Hubungi Admin')) {
      setChatOpen(true); setMessages(prev => [...prev, { role: 'user', text: 'Cara hubungi admin gimana?' }]); setIsTyping(true);
      setTimeout(() => { setIsTyping(false); setMessages(prev => [...prev, { role: 'ai', text: 'Tentu! Silakan klik tombol di bawah ini untuk ngobrol langsung dengan admin ya 👇', showWaCard: true }]); }, 800);
    } else { handleSend(text); }
  };

  return (
    <div className={`min-h-screen bg-surface-50 font-sans text-dark-900/80 ${totalItems > 0 ? 'pb-24' : 'pb-0'}`}>
      <Navbar scrolled={isScrolled} />
      <Hero onChat={() => setChatOpen(true)} productCount={prods.length} />

      {/* ── KATALOG ── */}
      <section id="katalog" className="bg-white py-24 px-6 rounded-t-[3rem] shadow-[0_-10px_40px_rgba(0,0,0,0.03)] relative z-20 -mt-8">
        <div className="max-w-6xl mx-auto">
          <div className="mb-10 flex flex-col md:flex-row justify-between items-center md:items-end gap-6 text-center md:text-left">
            <div className="flex-1 w-full">
              <span className="text-primary-500 font-black text-xs tracking-[0.2em] uppercase mb-2 block">Katalog Layanan</span>
              <h2 className="text-3xl md:text-4xl font-black text-dark-900 tracking-tight">Pilih Layanan Kami</h2>
            </div>
            <div className="w-full md:w-80 relative group">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-surface-300 group-focus-within:text-primary-500 transition-colors" size={18} />
              <input type="text" placeholder="Cari produk atau layanan..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-surface-50 border-2 border-surface-200/50 rounded-2xl py-3.5 pl-11 pr-4 text-sm font-medium outline-none focus:border-primary-500 focus:bg-white transition-all shadow-sm" />
              {searchQuery && <span className="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-dark-900/30">{filtered.length} hasil</span>}
            </div>
          </div>

          {cats.length > 0 && (
            <div className="flex gap-2 overflow-x-auto pb-8 no-scrollbar sticky top-[68px] z-30 bg-white/90 backdrop-blur-md pt-2">
              <button onClick={() => setSelectedCat('all')} className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap border-2 ${selectedCat === 'all' ? 'gradient-primary text-white border-transparent shadow-md' : 'bg-surface-50 text-dark-900/40 border-transparent hover:border-surface-200'}`}>Semua</button>
              {cats.map(c => (
                <button key={c.id_kategori} onClick={() => setSelectedCat(c.id_kategori)} className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap border-2 ${selectedCat == c.id_kategori ? 'gradient-primary text-white border-transparent shadow-md' : 'bg-surface-50 text-dark-900/40 border-transparent hover:border-surface-200'}`}>{c.nama_kategori}</button>
              ))}
            </div>
          )}

          <motion.div layout className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <AnimatePresence mode="popLayout">
              {filtered.length === 0 ? (
                <motion.div layout initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="col-span-full text-center py-20 glass-card rounded-3xl">
                  <Filter size={48} className="mx-auto text-surface-300 mb-4" />
                  <div className="text-dark-900/50 font-bold text-lg">Tidak ditemukan.</div>
                  <p className="text-dark-900/30 text-sm mt-1">Coba kata kunci lain.</p>
                </motion.div>
              ) : filtered.map(p => (
                <ProductCard key={p.id_produk} prod={p} cartItem={cart.find(c => c.id_produk === p.id_produk)}
                  onAsk={tanyaProduk} onAdd={addToCart} onQty={updateQty}
                  imgLoaded={!!imgLoaded[p.id_produk]} onImgLoad={() => setImgLoaded(prev => ({ ...prev, [p.id_produk]: true }))} />
              ))}
            </AnimatePresence>
          </motion.div>
        </div>
      </section>

      <FaqSection items={faqs} openIdx={openFaq} onToggle={(i) => setOpenFaq(openFaq === i ? null : i)} />

      {/* ── FOOTER ── */}
      <footer className="bg-dark-900 text-white py-16 px-6 text-center relative z-10">
        <div className="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto mb-6 text-xl font-black shadow-lg overflow-hidden">
          {S.logo ? <img src={`/assets/img/produk/${S.logo}`} className="w-full h-full object-cover" alt="" /> : S.nama_toko.charAt(0).toUpperCase()}
        </div>
        <h4 className="font-extrabold text-xl mb-2">{S.nama_toko}</h4>
        <p className="text-white/40 text-sm font-medium mb-10">Pilihan terbaik untuk kebutuhan Anda.</p>
        <p className="text-white/20 text-[10px] font-black uppercase tracking-[0.3em]">&copy; {new Date().getFullYear()} Pasek SaaS Engine</p>
      </footer>

      {/* ── CART SHEET ── */}
      <AnimatePresence>
        {cartOpen && <CartSheet cart={cart} open={cartOpen} onClose={() => setCartOpen(false)} onQty={updateQty} total={totalPrice} onCheckout={checkoutWA} />}
      </AnimatePresence>

      {/* ── STICKY CART BAR ── */}
      <AnimatePresence>
        {totalItems > 0 && !cartOpen && (
          <motion.div initial={{ y: 150, opacity: 0 }} animate={{ y: 0, opacity: 1 }} exit={{ y: 150, opacity: 0 }} transition={{ type: 'spring', damping: 25 }}
            onClick={() => setCartOpen(true)}
            className="fixed bottom-4 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-[560px] gradient-primary text-white p-4 rounded-2xl shadow-2xl z-[60] flex justify-between items-center cursor-pointer active:scale-[0.98] transition-transform">
            <div className="flex flex-col">
              <span className="text-[10px] font-black bg-white/20 px-2.5 py-1 rounded-lg w-fit uppercase tracking-widest mb-1">{totalItems} Item</span>
              <span className="font-black text-lg">Rp {fmt(totalPrice)}</span>
            </div>
            <div className="flex items-center gap-2 font-black text-sm bg-white text-primary-600 px-5 py-3 rounded-xl shadow-md">
              Lihat Keranjang <ShoppingBag size={16} strokeWidth={2.5} />
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ── CHAT WIDGET ── */}
      <AnimatePresence>
        {chatOpen && <ChatWidget open={chatOpen} onClose={() => setChatOpen(false)} messages={messages} input={inputText} setInput={setInputText}
          onSend={() => handleSend()} typing={isTyping} onChip={handleChip} totalItems={totalItems} />}
      </AnimatePresence>

      {!chatOpen && (
        <motion.button whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }} onClick={() => setChatOpen(true)}
          className={`fixed right-6 md:right-8 w-14 h-14 gradient-primary text-white rounded-2xl shadow-2xl flex items-center justify-center z-50 transition-all ${totalItems > 0 ? 'bottom-24 md:bottom-[100px]' : 'bottom-6 md:bottom-8'}`}
          aria-label="Buka chat AI">
          {messages.length > 0 && <span className="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 border-2 border-white rounded-full animate-pulse" />}
          <MessageSquare size={22} />
        </motion.button>
      )}
    </div>
  );
}
