/* ═══════════════════════════════════════════════════
   MAIN APP — src/App.jsx
   Pure orchestrator: state + render. Zero business logic.
   ═══════════════════════════════════════════════════ */

import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Filter, Search, ShoppingBag, MessageSquare } from 'lucide-react';

// Data
import { S, cats, prods, faqs, fmt } from './lib/store';

// Hooks
import { useCart } from './hooks/useCart';
import { useChat } from './hooks/useChat';

// Components
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import ProductCard from './components/ProductCard';
import FaqSection from './components/FaqSection';
import CartSheet from './components/CartSheet';
import ChatWidget from './components/ChatWidget';

export default function App() {
  /* ── UI State ── */
  const [selectedCat, setSelectedCat] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [isScrolled, setIsScrolled] = useState(false);
  const [imgLoaded, setImgLoaded] = useState({});
  const [openFaq, setOpenFaq] = useState(null);

  /* ── Custom hooks ── */
  const { cart, cartOpen, setCartOpen, addToCart, updateQty, totalItems, totalPrice, checkoutWA } = useCart();
  const { chatOpen, setChatOpen, messages, inputText, setInputText, isTyping, handleSend, handleChip, tanyaProduk } = useChat();

  /* ── Side-effects ── */
  useEffect(() => {
    if (!localStorage.getItem('ai_session'))
      localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));

    const onScroll = () => setIsScrolled(window.scrollY > 50);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  /* ── Filter produk ── */
  const filtered = prods.filter(p => {
    const matchCat = selectedCat === 'all' || p.id_kategori == selectedCat;
    const matchSearch = p.nama_produk.toLowerCase().includes(searchQuery.toLowerCase());
    return matchCat && matchSearch;
  });

  /* ════════════════════════════════════════════════ */
  return (
    <div className={`min-h-screen bg-surface-50 font-sans text-dark-900/80 ${totalItems > 0 ? 'pb-24' : 'pb-0'}`}>
      <Navbar scrolled={isScrolled} />
      <Hero onChat={() => setChatOpen(true)} productCount={prods.length} />

      {/* ══ KATALOG ══ */}
      <section id="katalog" className="bg-white py-24 px-6 rounded-t-[3rem] shadow-[0_-10px_40px_rgba(0,0,0,0.03)] relative z-20 -mt-8">
        <div className="max-w-6xl mx-auto">

          {/* Header katalog + search */}
          <div className="mb-10 flex flex-col md:flex-row justify-between items-center md:items-end gap-6 text-center md:text-left">
            <div className="flex-1 w-full">
              <span className="text-primary-500 font-black text-xs tracking-[0.2em] uppercase mb-2 block">Katalog Layanan</span>
              <h2 className="text-3xl md:text-4xl font-black text-dark-900 tracking-tight">Pilih Layanan Kami</h2>
            </div>
            <div className="w-full md:w-80 relative group">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-surface-300 group-focus-within:text-primary-500 transition-colors" size={18} />
              <input type="text" placeholder="Cari produk atau layanan..."
                value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-surface-50 border-2 border-surface-200/50 rounded-2xl py-3.5 pl-11 pr-4 text-sm font-medium outline-none focus:border-primary-500 focus:bg-white transition-all shadow-sm" />
              {searchQuery && (
                <span className="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-dark-900/30">
                  {filtered.length} hasil
                </span>
              )}
            </div>
          </div>

          {/* Filter kategori (sticky horizontal scroll) */}
          {cats.length > 0 && (
            <div className="flex gap-2 overflow-x-auto pb-8 no-scrollbar sticky top-[68px] z-30 bg-white/90 backdrop-blur-md pt-2">
              <button onClick={() => setSelectedCat('all')}
                className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap border-2 ${selectedCat === 'all' ? 'gradient-primary text-white border-transparent shadow-md' : 'bg-surface-50 text-dark-900/40 border-transparent hover:border-surface-200'}`}>
                Semua
              </button>
              {cats.map(c => (
                <button key={c.id_kategori} onClick={() => setSelectedCat(c.id_kategori)}
                  className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap border-2 ${selectedCat == c.id_kategori ? 'gradient-primary text-white border-transparent shadow-md' : 'bg-surface-50 text-dark-900/40 border-transparent hover:border-surface-200'}`}>
                  {c.nama_kategori}
                </button>
              ))}
            </div>
          )}

          {/* Grid produk */}
          <motion.div layout className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <AnimatePresence mode="popLayout">
              {filtered.length === 0 ? (
                <motion.div layout initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                  className="col-span-full text-center py-20 glass-card rounded-3xl">
                  <Filter size={48} className="mx-auto text-surface-300 mb-4" />
                  <div className="text-dark-900/50 font-bold text-lg">Tidak ditemukan.</div>
                  <p className="text-dark-900/30 text-sm mt-1">Coba kata kunci lain.</p>
                </motion.div>
              ) : (
                filtered.map(p => (
                  <ProductCard
                    key={p.id_produk}
                    prod={p}
                    cartItem={cart.find(c => c.id_produk === p.id_produk)}
                    onAsk={tanyaProduk}
                    onAdd={addToCart}
                    onQty={updateQty}
                    imgLoaded={!!imgLoaded[p.id_produk]}
                    onImgLoad={() => setImgLoaded(prev => ({ ...prev, [p.id_produk]: true }))}
                  />
                ))
              )}
            </AnimatePresence>
          </motion.div>
        </div>
      </section>

      {/* ══ FAQ ══ */}
      <FaqSection items={faqs} openIdx={openFaq} onToggle={(i) => setOpenFaq(openFaq === i ? null : i)} />

      {/* ══ FOOTER ══ */}
      <footer className="bg-dark-900 text-white py-16 px-6 text-center relative z-10">
        <div className="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto mb-6 text-xl font-black shadow-lg overflow-hidden">
          {S.logo ? <img src={`/assets/img/produk/${S.logo}`} className="w-full h-full object-cover" alt="" /> : S.nama_toko.charAt(0).toUpperCase()}
        </div>
        <h4 className="font-extrabold text-xl mb-2">{S.nama_toko}</h4>
        <p className="text-white/40 text-sm font-medium mb-10">Pilihan terbaik untuk kebutuhan Anda.</p>
        <p className="text-white/20 text-[10px] font-black uppercase tracking-[0.3em]">
          &copy; {new Date().getFullYear()} Pasek SaaS Engine
        </p>
      </footer>

      {/* ══ OVERLAYS ══ */}
      <AnimatePresence>
        {cartOpen && (
          <CartSheet
            cart={cart} open={cartOpen}
            onClose={() => setCartOpen(false)}
            onQty={updateQty} total={totalPrice}
            onCheckout={checkoutWA}
          />
        )}
      </AnimatePresence>

      {/* Sticky cart bar */}
      <AnimatePresence>
        {totalItems > 0 && !cartOpen && (
          <motion.div
            initial={{ y: 150, opacity: 0 }} animate={{ y: 0, opacity: 1 }} exit={{ y: 150, opacity: 0 }}
            transition={{ type: 'spring', damping: 25 }}
            onClick={() => setCartOpen(true)}
            className="fixed bottom-4 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-[560px] gradient-primary text-white p-4 rounded-2xl shadow-2xl z-[60] flex justify-between items-center cursor-pointer active:scale-[0.98] transition-transform">
            <div className="flex flex-col">
              <span className="text-[10px] font-black bg-white/20 px-2.5 py-1 rounded-lg w-fit uppercase tracking-widest mb-1">
                {totalItems} Item
              </span>
              <span className="font-black text-lg">Rp {fmt(totalPrice)}</span>
            </div>
            <div className="flex items-center gap-2 font-black text-sm bg-white text-primary-600 px-5 py-3 rounded-xl shadow-md">
              Lihat Keranjang <ShoppingBag size={16} strokeWidth={2.5} />
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Chat widget */}
      <AnimatePresence>
        {chatOpen && (
          <ChatWidget
            open={chatOpen}
            onClose={() => setChatOpen(false)}
            messages={messages}
            input={inputText}
            setInput={setInputText}
            onSend={() => handleSend()}
            typing={isTyping}
            onChip={handleChip}
            totalItems={totalItems}
          />
        )}
      </AnimatePresence>

      {/* Floating chat button */}
      {!chatOpen && (
        <motion.button
          whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}
          onClick={() => setChatOpen(true)}
          aria-label="Buka chat AI"
          className={`fixed right-6 md:right-8 w-14 h-14 gradient-primary text-white rounded-2xl shadow-2xl flex items-center justify-center z-50 transition-all ${totalItems > 0 ? 'bottom-24 md:bottom-[100px]' : 'bottom-6 md:bottom-8'}`}>
          {messages.length > 0 && (
            <span className="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 border-2 border-white rounded-full animate-pulse" />
          )}
          <MessageSquare size={22} />
        </motion.button>
      )}
    </div>
  );
}