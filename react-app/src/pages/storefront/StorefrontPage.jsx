import React, { useState, useEffect, useDeferredValue } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Filter, Search, ShoppingBag, MessageSquare, ArrowRight } from 'lucide-react';
import { StoreData, productCategories, productList, faqItems, formatCurrency } from '@/lib/store';
import { useCart } from '@/hooks/useCart';
import { useChat } from '@/hooks/useChat';
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import ProductCard from './components/ProductCard';
import FaqSection from './components/FaqSection';
import CartSheet from './components/CartSheet';
import ChatWidget from './components/ChatWidget';
import LogoAvatar from '@/components/ui/LogoAvatar';

export default function StorefrontPage() {
  const [selectedCat, setSelectedCat] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const deferredSearchQuery = useDeferredValue(searchQuery);
  const [isScrolled, setIsScrolled] = useState(false);
  const [imgLoaded, setImgLoaded] = useState({});
  const [openFaq, setOpenFaq] = useState(null);

  const { cart, cartOpen, setCartOpen, addToCart, updateQty, totalItems, totalPrice, checkoutWA } = useCart();
  const { chatOpen, setChatOpen, messages, inputText, setInputText, isTyping, handleSend, handleChip, tanyaProduk } = useChat();

  useEffect(() => {
    if (!localStorage.getItem('ai_session'))
      localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));
    const onScroll = () => setIsScrolled(window.scrollY > 50);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const filtered = productList.filter(p => {
    const matchCat = selectedCat === 'all' || p.id_kategori == selectedCat;
    const matchSearch = p.nama_produk.toLowerCase().includes(deferredSearchQuery.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <div className={`min-h-screen bg-[#050505] font-sans text-neutral-300 selection:bg-indigo-500/30 selection:text-white pb-20 md:pb-0`}>
      <Navbar scrolled={isScrolled} />
      <Hero onChat={() => setChatOpen(true)} productCount={productList.length} />

      <section id="katalog" className="py-20 px-6 relative z-20 border-t border-neutral-900 border-b">
        <div className="max-w-6xl mx-auto">
          <div className="mb-8 flex flex-col md:flex-row justify-between items-center md:items-end gap-6 text-center md:text-left">
            <div className="flex-1 w-full">
              <span className="text-neutral-500 font-medium text-xs tracking-widest uppercase mb-1.5 block">Katalog Layanan</span>
              <h2 className="text-2xl md:text-3xl font-bold text-white tracking-tight">Pilih Layanan Kami</h2>
            </div>
            <div className="w-full md:w-72 relative group">
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-500 group-focus-within:text-neutral-300 transition-colors" size={16} />
              <input type="text" placeholder="Cari produk atau layanan..."
                value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-[#050505] border border-white/5 rounded-xl py-3 pl-10 pr-4 text-sm text-white outline-none focus:border-white/20 transition-all placeholder-neutral-600" />
              {searchQuery && <span className="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-neutral-500">{filtered.length} hasil</span>}
            </div>
          </div>

          {productCategories.length > 0 && (
            <div className="flex gap-2 overflow-x-auto pb-6 no-scrollbar sticky top-[60px] z-30 bg-[#050505]/90 backdrop-blur-xl pt-2">
              <button onClick={() => setSelectedCat('all')}
                className={`px-4 py-2 text-sm font-medium transition-all whitespace-nowrap border-b-2 ${selectedCat === 'all' ? 'border-white text-white' : 'border-transparent text-neutral-500 hover:text-white'}`}>
                Semua
              </button>
              {productCategories.map(c => (
                <button key={c.id_kategori} onClick={() => setSelectedCat(c.id_kategori)}
                  className={`px-4 py-2 text-sm font-medium transition-all whitespace-nowrap border-b-2 ${selectedCat == c.id_kategori ? 'border-white text-white' : 'border-transparent text-neutral-500 hover:text-white'}`}>
                  {c.nama_kategori}
                </button>
              ))}
            </div>
          )}

          <motion.div layout className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <AnimatePresence mode="popLayout">
              {filtered.length === 0 ? (
                <motion.div layout initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                  className="col-span-full text-center py-16 glass-card rounded-2xl">
                  <Filter size={40} className="mx-auto text-neutral-600 mb-3" />
                  <div className="text-neutral-300 font-medium text-base">Tidak ditemukan.</div>
                  <p className="text-neutral-500 text-sm mt-1">Coba kata kunci lain.</p>
                </motion.div>
              ) : (
                filtered.map(p => (
                  <ProductCard key={p.id_produk} prod={p}
                    cartItem={cart.find(c => c.id_produk === p.id_produk)}
                    onAsk={tanyaProduk} onAdd={addToCart} onQty={updateQty}
                    imgLoaded={!!imgLoaded[p.id_produk]}
                    onImgLoad={() => setImgLoaded(prev => ({ ...prev, [p.id_produk]: true }))} />
                ))
              )}
            </AnimatePresence>
          </motion.div>
        </div>
      </section>

      <FaqSection faqItems={faqItems} activeIndex={openFaq} onToggle={(i) => setOpenFaq(openFaq === i ? null : i)} />

      <footer className="bg-[#050505] border-t border-white/5 py-14 px-6 text-center relative z-10 mb-16 md:mb-0">
        <LogoAvatar logo={StoreData.logo} name={StoreData.name} size="lg" className="bg-white text-neutral-900 mx-auto mb-4" />
        <h4 className="font-semibold text-base mb-1.5 text-white">{StoreData.name}</h4>
        <p className="text-neutral-500 text-sm mb-8">E-Commerce Pintar Didukung PasekSaaS.</p>
        <p className="text-neutral-600 text-[10px] font-medium uppercase tracking-[0.25em]">&copy; {new Date().getFullYear()} Pasek SaaS Engine</p>
      </footer>

      <AnimatePresence>{cartOpen && <CartSheet cart={cart} open={cartOpen} onClose={() => setCartOpen(false)} onQty={updateQty} total={totalPrice} onCheckout={checkoutWA} />}</AnimatePresence>

      <AnimatePresence>
        {chatOpen && <ChatWidget open={chatOpen} onClose={() => setChatOpen(false)} messages={messages}
          input={inputText} setInput={setInputText} onSend={() => handleSend()} typing={isTyping}
          onChip={handleChip} totalItems={totalItems} />}
      </AnimatePresence>

      {/* Desktop Floating Cart & Chat */}
      <div className="hidden md:block">
        <AnimatePresence>
          {totalItems > 0 && !cartOpen && (
            <motion.div initial={{ y: 50, opacity: 0 }} animate={{ y: 0, opacity: 1 }} exit={{ y: 50, opacity: 0 }}
              onClick={() => setCartOpen(true)}
              className="fixed bottom-8 left-1/2 -translate-x-1/2 w-max bg-neutral-900 border border-white/10 text-white p-2.5 pr-4 rounded-full shadow-2xl z-[60] flex items-center gap-4 cursor-pointer hover:scale-105 active:scale-95 transition-transform backdrop-blur-xl">
              <div className="bg-white text-black font-bold h-10 w-10 flex items-center justify-center rounded-full text-sm">
                {totalItems}
              </div>
              <div className="font-semibold text-base">IDR {formatCurrency(totalPrice)}</div>
              <div className="flex items-center gap-2 font-medium text-xs uppercase tracking-wider text-neutral-400 pl-2">
                Checkout <ArrowRight size={14} />
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {!chatOpen && (
          <motion.button whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}
            onClick={() => setChatOpen(true)}
            className="fixed right-8 bottom-8 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-[0_0_20px_rgba(79,70,229,0.4)] flex items-center justify-center z-50 hover:bg-indigo-500 transition-colors">
            {messages.length > 0 && <span className="absolute top-0 right-0 w-3.5 h-3.5 bg-red-500 border-2 border-indigo-600 rounded-full animate-pulse" />}
            <MessageSquare size={22} />
          </motion.button>
        )}
      </div>

      {/* Mobile App-like Bottom Nav */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 bg-[#0A0A0A]/90 backdrop-blur-xl border-t border-white/5 z-[60] flex items-center justify-around p-3 pb-safe-area-inset-bottom">
        <button onClick={() => window.scrollTo(0,0)} className="flex flex-col items-center gap-1 text-neutral-400 hover:text-white">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span className="text-[10px] font-medium">Beranda</span>
        </button>
        <button onClick={() => document.getElementById('katalog')?.scrollIntoView()} className="flex flex-col items-center gap-1 text-neutral-400 hover:text-white">
          <Search size={20} />
          <span className="text-[10px] font-medium">Katalog</span>
        </button>
        <button onClick={() => setChatOpen(!chatOpen)} className="flex flex-col items-center gap-1 text-indigo-400 relative">
          {messages.length > 0 && <span className="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-[#0A0A0A]" />}
          <MessageSquare size={20} />
          <span className="text-[10px] font-medium">Asisten AI</span>
        </button>
        <button onClick={() => setCartOpen(true)} className="flex flex-col items-center gap-1 text-neutral-400 hover:text-white relative">
          {totalItems > 0 && <span className="absolute -top-1 -right-2 w-3.5 h-3.5 bg-white text-black text-[9px] font-bold flex items-center justify-center rounded-full">{totalItems}</span>}
          <ShoppingBag size={20} />
          <span className="text-[10px] font-medium">Cart</span>
        </button>
      </div>
    </div>
  );
}
