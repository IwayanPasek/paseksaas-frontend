import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Filter, Search, ShoppingBag, MessageSquare } from 'lucide-react';
import { S, cats, prods, faqs, fmt } from '@/lib/store';
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

  const filtered = prods.filter(p => {
    const matchCat = selectedCat === 'all' || p.id_kategori == selectedCat;
    const matchSearch = p.nama_produk.toLowerCase().includes(searchQuery.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <div className={`min-h-screen bg-neutral-50 font-sans text-neutral-700 ${totalItems > 0 ? 'pb-24' : 'pb-0'}`}>
      <Navbar scrolled={isScrolled} />
      <Hero onChat={() => setChatOpen(true)} productCount={prods.length} />

      <section id="katalog" className="bg-white py-20 px-6 relative z-20">
        <div className="max-w-6xl mx-auto">
          <div className="mb-8 flex flex-col md:flex-row justify-between items-center md:items-end gap-6 text-center md:text-left">
            <div className="flex-1 w-full">
              <span className="text-neutral-400 font-medium text-xs tracking-widest uppercase mb-1.5 block">Katalog Layanan</span>
              <h2 className="text-2xl md:text-3xl font-bold text-neutral-900 tracking-tight">Pilih Layanan Kami</h2>
            </div>
            <div className="w-full md:w-72 relative group">
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-300 group-focus-within:text-neutral-500 transition-colors" size={16} />
              <input type="text" placeholder="Cari produk atau layanan..."
                value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-neutral-50 border border-neutral-200 rounded-xl py-3 pl-10 pr-4 text-sm outline-none focus:border-neutral-400 focus:bg-white transition-all" />
              {searchQuery && <span className="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-neutral-400">{filtered.length} hasil</span>}
            </div>
          </div>

          {cats.length > 0 && (
            <div className="flex gap-1.5 overflow-x-auto pb-6 no-scrollbar sticky top-[60px] z-30 bg-white/95 backdrop-blur-sm pt-2">
              <button onClick={() => setSelectedCat('all')}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap ${selectedCat === 'all' ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-500 hover:bg-neutral-200'}`}>
                Semua
              </button>
              {cats.map(c => (
                <button key={c.id_kategori} onClick={() => setSelectedCat(c.id_kategori)}
                  className={`px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap ${selectedCat == c.id_kategori ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-500 hover:bg-neutral-200'}`}>
                  {c.nama_kategori}
                </button>
              ))}
            </div>
          )}

          <motion.div layout className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <AnimatePresence mode="popLayout">
              {filtered.length === 0 ? (
                <motion.div layout initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                  className="col-span-full text-center py-16 card rounded-2xl">
                  <Filter size={40} className="mx-auto text-neutral-300 mb-3" />
                  <div className="text-neutral-500 font-medium text-base">Tidak ditemukan.</div>
                  <p className="text-neutral-400 text-sm mt-1">Coba kata kunci lain.</p>
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

      <FaqSection items={faqs} openIdx={openFaq} onToggle={(i) => setOpenFaq(openFaq === i ? null : i)} />

      <footer className="bg-neutral-950 text-white py-14 px-6 text-center relative z-10">
        <LogoAvatar logo={S.logo} name={S.nama_toko} size="lg" className="bg-white text-neutral-900 mx-auto mb-4" />
        <h4 className="font-semibold text-base mb-1.5">{S.nama_toko}</h4>
        <p className="text-white/40 text-sm mb-8">Pilihan terbaik untuk kebutuhan Anda.</p>
        <p className="text-white/20 text-[10px] font-medium uppercase tracking-[0.25em]">&copy; {new Date().getFullYear()} Pasek SaaS Engine</p>
      </footer>

      <AnimatePresence>{cartOpen && <CartSheet cart={cart} open={cartOpen} onClose={() => setCartOpen(false)} onQty={updateQty} total={totalPrice} onCheckout={checkoutWA} />}</AnimatePresence>

      <AnimatePresence>
        {totalItems > 0 && !cartOpen && (
          <motion.div initial={{ y: 100, opacity: 0 }} animate={{ y: 0, opacity: 1 }} exit={{ y: 100, opacity: 0 }}
            transition={{ type: 'spring', damping: 25 }}
            onClick={() => setCartOpen(true)}
            className="fixed bottom-4 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-[500px] bg-neutral-900 text-white p-3.5 rounded-xl shadow-2xl z-[60] flex justify-between items-center cursor-pointer active:scale-[0.99] transition-transform">
            <div className="flex flex-col">
              <span className="text-[10px] font-medium bg-white/15 px-2 py-0.5 rounded-md w-fit uppercase tracking-wider mb-0.5">{totalItems} Item</span>
              <span className="font-bold text-base">Rp {fmt(totalPrice)}</span>
            </div>
            <div className="flex items-center gap-2 font-medium text-sm bg-white text-neutral-900 px-4 py-2.5 rounded-lg">
              Lihat Keranjang <ShoppingBag size={14} />
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      <AnimatePresence>
        {chatOpen && <ChatWidget open={chatOpen} onClose={() => setChatOpen(false)} messages={messages}
          input={inputText} setInput={setInputText} onSend={() => handleSend()} typing={isTyping}
          onChip={handleChip} totalItems={totalItems} />}
      </AnimatePresence>

      {!chatOpen && (
        <motion.button whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}
          onClick={() => setChatOpen(true)} aria-label="Buka chat AI"
          className={`fixed right-5 md:right-8 w-12 h-12 bg-neutral-900 text-white rounded-xl shadow-lg flex items-center justify-center z-50 transition-all hover:bg-neutral-800 ${totalItems > 0 ? 'bottom-24 md:bottom-[96px]' : 'bottom-5 md:bottom-8'}`}>
          {messages.length > 0 && <span className="absolute -top-0.5 -right-0.5 w-3 h-3 bg-danger-500 border-2 border-white rounded-full" />}
          <MessageSquare size={18} />
        </motion.button>
      )}
    </div>
  );
}
