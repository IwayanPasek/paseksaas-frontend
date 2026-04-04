import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Sparkles, MessageSquare, X, Send, Image as ImageIcon, 
  ShoppingBag, ShieldCheck, Filter, Search, ShoppingCart, 
  Plus, Minus, ChevronRight, ChevronDown, HelpCircle
} from 'lucide-react';

const storeData = window.STORE_DATA || { id_toko: 1, nama_toko: 'Toko AI', desc_toko: '', wa_num: '6281234567890', logo: null, products: [], categories: [], faq: [] };
const safeCategories = Array.isArray(storeData.categories) ? storeData.categories : [];
const safeProducts = Array.isArray(storeData.products) ? storeData.products : [];
const safeFaq = Array.isArray(storeData.faq) ? storeData.faq : [];

const formatRp = (angka) => Number(angka).toLocaleString('id-ID');

export default function App() {
  const [selectedCat, setSelectedCat] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [isScrolled, setIsScrolled] = useState(false);
  const [imgLoaded, setImgLoaded] = useState({});
  const [openFaq, setOpenFaq] = useState(null); // State untuk Accordion FAQ
  
  const [cart, setCart] = useState([]);
  const [cartOpen, setCartOpen] = useState(false);

  const [chatOpen, setChatOpen] = useState(false);
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const chatEndRef = useRef(null);

  useEffect(() => {
    if (!localStorage.getItem('ai_session')) {
      localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));
    }
    const handleScroll = () => setIsScrolled(window.scrollY > 50);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => { chatEndRef.current?.scrollIntoView({ behavior: 'smooth' }); }, [messages, isTyping]);
  useEffect(() => { if (cart.length === 0) setCartOpen(false); }, [cart]);

  // ── FILTER ──
  const filteredProducts = safeProducts.filter(p => {
    const matchCat = selectedCat === 'all' || p.id_kategori == selectedCat;
    const matchSearch = p.nama_produk.toLowerCase().includes(searchQuery.toLowerCase());
    return matchCat && matchSearch;
  });

  // ── LOGIKA KERANJANG ──
  const addToCart = (prod) => setCart(prev => [...prev, { ...prod, qty: 1 }]);
  const updateQty = (id_produk, delta) => {
    setCart(prev => prev.map(item => item.id_produk === id_produk ? { ...item, qty: item.qty + delta } : item).filter(item => item.qty > 0));
  };

  const totalItems = cart.reduce((acc, item) => acc + item.qty, 0);
  const totalPrice = cart.reduce((acc, item) => acc + (item.harga * item.qty), 0);

  const checkoutWA = () => {
    let text = `Halo Admin *${storeData.nama_toko}*! Saya ingin memesan:\n\n`;
    cart.forEach(item => { text += `▪️ ${item.qty}x ${item.nama_produk} - Rp ${formatRp(item.harga * item.qty)}\n`; });
    text += `\n*Total Tagihan: Rp ${formatRp(totalPrice)}*\n\nMohon dibantu prosesnya ya kak!`;
    window.open(`https://wa.me/${storeData.wa_num}?text=${encodeURIComponent(text)}`, '_blank');
  };

  // ── LOGIKA CHAT AI ──
  const handleSendMessage = async (text = inputText, skipUserBubble = false) => {
    const msg = text.trim();
    if (!msg || isTyping) return;
    
    if (!skipUserBubble) setMessages(prev => [...prev, { role: 'user', text: msg }]);
    setInputText('');
    setIsTyping(true);
    if (!chatOpen) setChatOpen(true);

    try {
      const res = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_toko: storeData.id_toko, session_id: localStorage.getItem('ai_session'), user_message: msg }),
      });
      const data = await res.json();
      const newAiMsg = { role: 'ai', text: data.reply || 'Maaf, saya tidak mengerti.', products: data.db_result || [], showWaCard: false };
      
      if (data.reply && (data.reply.toLowerCase().includes('whatsapp') || data.reply.toLowerCase().includes('pesan') || data.reply.toLowerCase().includes('hubungi'))) {
        newAiMsg.showWaCard = true;
      }
      setMessages(prev => [...prev, newAiMsg]);
    } catch (err) {
      setMessages(prev => [...prev, { role: 'ai', text: '⚠️ Gagal terhubung ke server AI.' }]);
    } finally { setIsTyping(false); }
  };

  const tanyaProduk = (nama) => {
    setChatOpen(true);
    setTimeout(() => handleSendMessage(`Jelaskan detail layanan "${nama}" dong!`, false), 300);
  };

  const handleChip = (text) => {
    if (text.includes('Hubungi Admin')) {
      setChatOpen(true); setMessages(prev => [...prev, { role: 'user', text: 'Cara hubungi admin gimana?' }]); setIsTyping(true);
      setTimeout(() => {
        setIsTyping(false);
        setMessages(prev => [...prev, { role: 'ai', text: 'Tentu! Silakan klik tombol di bawah ini untuk ngobrol langsung dengan admin ya 👇', showWaCard: true }]);
      }, 800);
    } else { handleSendMessage(text); }
  };

  return (
    <div className={`min-h-screen bg-slate-50 font-sans text-slate-700 pb-20 ${totalItems > 0 ? 'md:pb-32' : 'md:pb-0'}`}>
      
      {/* ── NAVBAR ── */}
      <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${isScrolled ? 'bg-white/95 backdrop-blur-md shadow-sm py-3 border-b border-slate-200' : 'bg-transparent py-5'}`}>
        <div className="max-w-6xl mx-auto px-6 flex justify-between items-center">
          <div className="flex items-center gap-3 font-extrabold text-xl text-slate-900 tracking-tight">
            {/* LOGO DINAMIS */}
            <div className="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg overflow-hidden shrink-0 border border-slate-200">
              {storeData.logo ? (
                <img src={`/assets/img/produk/${storeData.logo}`} alt="Logo" className="w-full h-full object-cover" />
              ) : (
                storeData.nama_toko.substring(0, 1).toUpperCase()
              )}
            </div>
            <span className="truncate max-w-[200px] sm:max-w-none">{storeData.nama_toko}</span>
          </div>
          <a href="/login.php" className="hidden md:flex px-5 py-2 rounded-full bg-slate-900 text-white text-sm font-bold shadow-md hover:bg-sky-500 transition-all">Admin Area</a>
        </div>
      </nav>

      {/* ── HERO SECTION ── */}
      <section className="pt-32 pb-20 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center min-h-[90vh]">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="text-center md:text-left">
          <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 text-[10px] font-bold text-sky-500 uppercase tracking-widest mb-6 shadow-sm">
            <span className="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span> Smart AI Shop
          </div>
          <h1 className="text-4xl md:text-6xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tighter">
            Kami ahlinya beresin masalahmu.<br/><span className="text-sky-500">Tinggal duduk manis!</span>
          </h1>
          <p className="text-lg text-slate-500 mb-8 font-medium leading-relaxed max-w-lg mx-auto md:mx-0">{storeData.desc_toko}</p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
            <a href="#katalog" className="px-8 py-4 rounded-2xl bg-slate-900 text-white font-bold shadow-lg hover:bg-sky-500 flex items-center justify-center gap-2 hover:-translate-y-1 transition-all active:scale-95">
              <ShoppingBag size={18} /> Lihat Layanan
            </a>
            <button onClick={() => setChatOpen(true)} className="px-8 py-4 rounded-2xl bg-white border-2 border-slate-200 text-slate-900 font-bold hover:border-slate-900 flex items-center justify-center gap-2 hover:-translate-y-1 transition-all active:scale-95">
              <Sparkles size={18} className="text-sky-500" /> Tanya AI Gratis
            </button>
          </div>
        </motion.div>
        
        <div className="hidden md:block relative">
          <div className="aspect-[4/3] bg-slate-200 rounded-[2.5rem] border-4 border-white shadow-2xl overflow-hidden flex flex-col items-center justify-center text-slate-400 relative">
             <div className="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
             {storeData.logo ? <img src={`/assets/img/produk/${storeData.logo}`} className="w-full h-full object-cover opacity-50 blur-sm mix-blend-multiply" /> : <ImageIcon size={64} className="opacity-30" />}
             
             <motion.div animate={{ y: [0, -10, 0] }} transition={{ duration: 4, repeat: Infinity }} className="absolute -bottom-6 -left-10 bg-white p-4 rounded-2xl shadow-xl flex items-start gap-3 w-64 border border-slate-100 z-10">
               <div className="w-8 h-8 rounded-full bg-sky-100 text-sky-500 flex items-center justify-center shrink-0"><Sparkles size={14}/></div>
               <div>
                 <p className="text-xs font-bold text-slate-900">AI Asisten</p>
                 <p className="text-[10px] text-slate-500 mt-0.5 line-clamp-2">"Baik kak, pesanan segera kami proses. Ada tambahan lagi?"</p>
               </div>
             </motion.div>
          </div>
        </div>
      </section>

      {/* ── KATALOG & LIVE SEARCH ── */}
      <section id="katalog" className="bg-white py-24 px-6 rounded-t-[3.5rem] shadow-[0_-10px_40px_rgba(0,0,0,0.02)] relative z-20">
        <div className="max-w-6xl mx-auto">
          
          <div className="mb-8 flex flex-col md:flex-row justify-between items-center md:items-end gap-6 text-center md:text-left">
            <div className="flex-1 w-full">
              <span className="text-sky-500 font-black text-xs tracking-[0.2em] uppercase mb-2 block">Katalog Layanan</span>
              <h2 className="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Pilih Layanan Kami</h2>
            </div>
            
            <div className="w-full md:w-80 relative group">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-500 transition-colors" size={18} />
              <input type="text" placeholder="Cari produk atau layanan..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3.5 pl-11 pr-4 text-sm font-medium outline-none focus:border-sky-500 focus:bg-white transition-all shadow-sm"/>
            </div>
          </div>

          {/* Kategori Filter */}
          {safeCategories.length > 0 && (
            <div className="flex gap-2 overflow-x-auto pb-8 no-scrollbar sticky top-[72px] z-30 bg-white/90 backdrop-blur-md">
              <button onClick={() => setSelectedCat('all')} className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap border-2 ${selectedCat === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200'}`}>Semua Kategori</button>
              {safeCategories.map(cat => (
                <button key={cat.id_kategori} onClick={() => setSelectedCat(cat.id_kategori)} className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap border-2 ${selectedCat == cat.id_kategori ? 'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-500/20' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200'}`}>{cat.nama_kategori}</button>
              ))}
            </div>
          )}

          {/* Grid Produk */}
          <motion.div layout className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <AnimatePresence mode="popLayout">
              {filteredProducts.length === 0 ? (
                 <motion.div layout initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="col-span-full text-center py-20 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                    <Filter size={48} className="mx-auto text-slate-300 mb-4" />
                    <div className="text-slate-500 font-bold text-lg">Pencarian tidak ditemukan.</div>
                 </motion.div>
              ) : (
                filteredProducts.map((prod) => {
                  const cartItem = cart.find(c => c.id_produk === prod.id_produk);
                  
                  return (
                  <motion.div 
                    layout initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} exit={{ opacity: 0, scale: 0.9 }} transition={{ duration: 0.2 }}
                    whileHover={{ y: -8, scale: 1.01 }} 
                    key={prod.id_produk} 
                    className="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-[0_10px_20px_rgba(0,0,0,0.04)] flex flex-col hover:border-sky-200 transition-colors hover:shadow-sky-500/10"
                  >
                    <div className="aspect-[4/3] bg-slate-100 relative overflow-hidden group">
                      {!imgLoaded[prod.id_produk] && prod.foto_produk && <div className="absolute inset-0 bg-slate-200 animate-pulse z-0"></div>}
                      {prod.foto_produk ? (
                        <img 
                          src={`/assets/img/produk/${prod.foto_produk}`} 
                          onLoad={() => setImgLoaded(prev => ({...prev, [prod.id_produk]: true}))}
                          className={`w-full h-full object-cover transition-all duration-700 z-10 relative ${imgLoaded[prod.id_produk] ? 'opacity-100 group-hover:scale-110' : 'opacity-0'}`} 
                          onError={(e) => { e.target.style.display='none'; }} 
                        />
                      ) : (
                        <div className="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50 relative z-10"><ImageIcon size={40} /></div>
                      )}
                    </div>

                    <div className="p-6 flex flex-col flex-1">
                      {prod.id_kategori && (
                        <div className="text-[10px] font-black text-sky-500 uppercase tracking-widest mb-2">
                          {safeCategories.find(c => c.id_kategori == prod.id_kategori)?.nama_kategori || 'Layanan'}
                        </div>
                      )}
                      <h3 className="font-extrabold text-lg text-slate-900 mb-2 leading-snug line-clamp-1">{prod.nama_produk}</h3>
                      <p className="text-xs text-slate-500 mb-4 line-clamp-2 flex-1 font-medium leading-relaxed">{prod.deskripsi}</p>
                      <div className="border-t border-slate-100 pt-4 mb-4 flex items-baseline gap-1 font-black text-xl text-slate-900">
                        <span className="text-xs text-slate-400 font-bold">Rp</span> {formatRp(prod.harga)}
                      </div>
                      
                      <div className="flex flex-col gap-2">
                        <button onClick={() => tanyaProduk(prod.nama_produk)} className="w-full py-3 rounded-xl bg-slate-50 text-slate-600 font-bold text-xs flex justify-center items-center gap-2 hover:bg-slate-900 hover:text-white transition-all">
                          <Sparkles size={14} /> Tanya AI
                        </button>
                        
                        {cartItem ? (
                          <div className="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl p-1.5 h-[44px]">
                            <button onClick={() => updateQty(prod.id_produk, -1)} className="w-8 h-full flex items-center justify-center text-emerald-600 hover:bg-white hover:shadow-sm rounded-lg transition-all"><Minus size={16} strokeWidth={3}/></button>
                            <span className="font-black text-emerald-600 text-sm">{cartItem.qty}</span>
                            <button onClick={() => updateQty(prod.id_produk, 1)} className="w-8 h-full flex items-center justify-center text-emerald-600 hover:bg-white hover:shadow-sm rounded-lg transition-all"><Plus size={16} strokeWidth={3}/></button>
                          </div>
                        ) : (
                          <button onClick={() => addToCart(prod)} className="w-full h-[44px] rounded-xl bg-white border-2 border-emerald-100 text-emerald-500 font-bold text-xs flex justify-center items-center gap-2 hover:bg-emerald-50 hover:border-emerald-500 active:scale-95 transition-all">
                            <ShoppingCart size={14}/> Tambah
                          </button>
                        )}
                      </div>
                    </div>
                  </motion.div>
                  );
                })
              )}
            </AnimatePresence>
          </motion.div>
        </div>
      </section>

      {/* ── FAQ SECTION (FITUR BARU) ── */}
      {safeFaq.length > 0 && (
        <section className="bg-slate-50 py-24 px-6 relative z-10 border-t border-slate-200/50">
          <div className="max-w-3xl mx-auto">
            <div className="text-center mb-12">
              <span className="w-12 h-12 bg-sky-100 text-sky-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm"><HelpCircle size={24}/></span>
              <h2 className="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Tanya Jawab</h2>
              <p className="text-slate-500 font-medium mt-3">Jawaban cepat untuk pertanyaan yang sering ditanyakan.</p>
            </div>
            
            <div className="space-y-4">
              {safeFaq.map((f, i) => (
                <div key={f.id_faq} className="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                  <button 
                    onClick={() => setOpenFaq(openFaq === i ? null : i)} 
                    className="w-full flex items-center justify-between p-5 text-left font-extrabold text-slate-900 focus:outline-none"
                  >
                    {f.pertanyaan}
                    <ChevronDown size={18} className={`text-slate-400 transition-transform duration-300 ${openFaq === i ? 'rotate-180 text-sky-500' : ''}`}/>
                  </button>
                  <AnimatePresence>
                    {openFaq === i && (
                      <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: 'auto', opacity: 1 }} exit={{ height: 0, opacity: 0 }} className="px-5 pb-5 text-sm text-slate-600 font-medium leading-relaxed">
                        {f.jawaban}
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ── FOOTER ── */}
      <footer className="bg-slate-900 text-white py-16 px-6 text-center relative z-10">
        <div className="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-6 text-xl font-black border border-white/20">
          {storeData.logo ? <img src={`/assets/img/produk/${storeData.logo}`} className="w-full h-full object-cover rounded-2xl" /> : storeData.nama_toko.substring(0, 1).toUpperCase()}
        </div>
        <h4 className="font-extrabold text-xl mb-2">{storeData.nama_toko}</h4>
        <p className="text-slate-400 text-sm font-medium mb-8">Pilihan terbaik untuk kebutuhan Anda.</p>
        <p className="text-slate-600 text-[10px] font-black uppercase tracking-[0.3em]">&copy; {new Date().getFullYear()} Pasek SaaS Engine</p>
      </footer>

      {/* ── BOTTOM SHEET KERANJANG (MODAL) ── */}
      <AnimatePresence>
        {cartOpen && totalItems > 0 && (
          <>
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} onClick={() => setCartOpen(false)} className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[70]"/>
            <motion.div initial={{ y: '100%' }} animate={{ y: 0 }} exit={{ y: '100%' }} transition={{ type: 'spring', damping: 25, stiffness: 200 }} className="fixed bottom-0 left-0 right-0 md:left-1/2 md:-translate-x-1/2 md:w-[500px] md:bottom-8 bg-white rounded-t-[2rem] md:rounded-[2rem] shadow-2xl z-[80] overflow-hidden flex flex-col max-h-[85vh]">
              <div className="p-5 md:p-6 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
                <h3 className="font-black text-lg text-slate-900 flex items-center gap-2"><ShoppingCart className="text-emerald-500" /> Keranjang Belanja</h3>
                <button onClick={() => setCartOpen(false)} className="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-500 rounded-full hover:bg-slate-200 transition-colors"><X size={18} /></button>
              </div>
              <div className="flex-1 overflow-y-auto p-5 md:p-6 space-y-4 bg-slate-50">
                {cart.map(item => (
                  <div key={item.id_produk} className="bg-white p-4 rounded-2xl border border-slate-100 shadow-[0_4px_12px_rgba(0,0,0,0.02)] flex justify-between items-center gap-4">
                    <div className="flex-1 min-w-0">
                      <h4 className="font-bold text-slate-900 text-sm line-clamp-1">{item.nama_produk}</h4>
                      <p className="text-xs font-black text-sky-500 mt-1">Rp {formatRp(item.harga)}</p>
                    </div>
                    <div className="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl p-1 h-10 w-[90px] shrink-0">
                      <button onClick={() => updateQty(item.id_produk, -1)} className="w-7 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all shadow-sm"><Minus size={14} strokeWidth={3}/></button>
                      <span className="font-black text-emerald-600 text-xs">{item.qty}</span>
                      <button onClick={() => updateQty(item.id_produk, 1)} className="w-7 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all shadow-sm"><Plus size={14} strokeWidth={3}/></button>
                    </div>
                  </div>
                ))}
              </div>
              <div className="p-5 md:p-6 bg-white border-t border-slate-100 shrink-0">
                <div className="flex justify-between items-center mb-5">
                  <span className="text-sm font-bold text-slate-500">Total Tagihan</span>
                  <span className="text-2xl font-black text-slate-900">Rp {formatRp(totalPrice)}</span>
                </div>
                <button onClick={checkoutWA} className="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-sm flex items-center justify-center gap-2 shadow-[0_10px_20px_rgba(16,185,129,0.3)] transition-transform active:scale-95">
                  <MessageSquare size={18} /> Checkout via WhatsApp
                </button>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      {/* ── STICKY CART BAR (GOFOOD STYLE) ── */}
      <AnimatePresence>
        {totalItems > 0 && !cartOpen && (
          <motion.div 
            initial={{ y: 150, opacity: 0 }} animate={{ y: 0, opacity: 1 }} exit={{ y: 150, opacity: 0 }} transition={{ type: 'spring', damping: 25 }}
            onClick={() => setCartOpen(true)}
            className="fixed bottom-4 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-[600px] bg-emerald-500 hover:bg-emerald-600 text-white p-4 rounded-2xl shadow-[0_15px_30px_rgba(16,185,129,0.4)] z-[60] flex justify-between items-center cursor-pointer transform active:scale-[0.98] transition-transform"
          >
            <div className="flex flex-col">
              <span className="text-[10px] font-black bg-white/20 px-2.5 py-1 rounded-lg w-fit uppercase tracking-widest mb-1 shadow-inner">{totalItems} Item</span>
              <span className="font-black text-lg md:text-xl">Rp {formatRp(totalPrice)}</span>
            </div>
            <div className="flex items-center gap-2 font-black text-sm bg-white text-emerald-600 px-5 py-3 rounded-xl shadow-md">
              Lihat Keranjang <ShoppingBag size={18} strokeWidth={2.5}/>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ── FLOATING CHAT AI ── */}
      <AnimatePresence>
        {chatOpen && (
          <motion.div initial={{ opacity: 0, y: 50, scale: 0.95 }} animate={{ opacity: 1, y: 0, scale: 1 }} exit={{ opacity: 0, y: 20, scale: 0.95 }} transition={{ type: 'spring', damping: 28 }} className={`fixed bottom-0 right-0 w-[100vw] h-[100dvh] md:w-[400px] ${totalItems > 0 ? 'md:bottom-28' : 'md:bottom-24'} md:right-8 md:h-[620px] bg-white md:rounded-[2rem] shadow-2xl flex flex-col z-[100] border border-slate-100 overflow-hidden`}>
             <div className="bg-white px-5 py-4 flex items-center justify-between border-b border-slate-100 shrink-0">
               <div className="flex items-center gap-3">
                 <div className="w-11 h-11 bg-sky-50 rounded-xl flex items-center justify-center text-sky-500 shadow-sm"><Sparkles size={20} /></div>
                 <div>
                   <h3 className="font-extrabold text-slate-900 text-base">Asisten Toko</h3>
                   <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5"><span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online</div>
                 </div>
               </div>
               <button onClick={() => setChatOpen(false)} className="w-9 h-9 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-slate-100 hover:text-slate-900"><X size={18} /></button>
             </div>

             <div className="flex gap-2 px-4 pt-3 pb-2 overflow-x-auto no-scrollbar shrink-0 bg-slate-50/50">
               {['Tampilkan semua layanan', 'Layanan termurah', 'Hubungi Admin'].map((chip, i) => (
                 <button key={i} onClick={() => handleChip(chip)} className="px-4 py-1.5 bg-white border border-slate-200 rounded-full text-xs font-bold text-slate-600 hover:bg-slate-900 hover:text-white shadow-sm whitespace-nowrap">{chip === 'Hubungi Admin' ? '💬 ' : '✨ '} {chip}</button>
               ))}
             </div>

             <div className="flex-1 bg-slate-50 p-5 overflow-y-auto flex flex-col gap-5">
               {messages.map((msg, idx) => (
                 <div key={idx} className={`flex flex-col gap-2 w-full ${msg.role === 'user' ? 'items-end' : 'items-start'}`}>
                   <div className={`flex gap-3 max-w-[85%] ${msg.role === 'user' ? 'flex-row-reverse' : ''}`}>
                     <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm text-xs ${msg.role === 'user' ? 'bg-slate-200 text-slate-600' : 'bg-white border border-slate-200 text-slate-900'}`}>{msg.role === 'user' ? 'U' : <Sparkles size={14} />}</div>
                     <div className={`p-3.5 text-[13px] font-medium leading-relaxed shadow-sm ${msg.role === 'user' ? 'bg-slate-900 text-white rounded-2xl rounded-tr-sm' : 'bg-white text-slate-700 rounded-2xl rounded-tl-sm border border-slate-100'}`} dangerouslySetInnerHTML={{ __html: msg.text.replace(/\n/g, '<br/>') }} />
                   </div>
                   {msg.products && msg.products.length > 0 && (
                     <div className="flex gap-3 max-w-[90%] pl-11">
                        <div className="flex flex-col gap-3 w-full">
                          {msg.products.map(p => (
                            <div key={p.id_produk} className="bg-white border border-slate-200 p-3 rounded-2xl shadow-sm w-full max-w-[220px]">
                              {p.foto_produk && <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-24 object-cover rounded-xl mb-3 bg-slate-100" />}
                              <div className="font-extrabold text-sm text-slate-900 line-clamp-2 leading-snug mb-1">{p.nama_produk}</div>
                              <div className="font-extrabold text-sky-500 text-sm mb-3">Rp {formatRp(p.harga)}</div>
                              <button onClick={() => addToCart(p)} className="w-full py-2 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-500 hover:text-white transition-colors">Tambah ke Keranjang</button>
                            </div>
                          ))}
                        </div>
                     </div>
                   )}
                 </div>
               ))}
               
               {isTyping && (
                 <div className="flex gap-3 max-w-[85%]">
                   <div className="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-900 flex items-center justify-center shadow-sm"><Sparkles size={14} /></div>
                   <div className="bg-white px-4 py-3.5 rounded-2xl rounded-tl-sm shadow-sm border border-slate-100 flex items-center gap-1.5">
                     <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce"></span>
                     <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce" style={{animationDelay:'0.2s'}}></span>
                     <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce" style={{animationDelay:'0.4s'}}></span>
                   </div>
                 </div>
               )}
               <div ref={chatEndRef} />
             </div>

             <div className="p-4 bg-white border-t border-slate-100 shrink-0">
               <div className="flex items-end gap-2 bg-slate-50 border-2 border-slate-100 rounded-2xl p-1.5 focus-within:border-slate-900 transition-all">
                 <textarea value={inputText} onChange={(e) => setInputText(e.target.value)} onKeyDown={(e) => { if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); handleSendMessage(); } }} placeholder="Ketik pesan..." disabled={isTyping} className="flex-1 bg-transparent border-none outline-none px-3 py-2 text-[13px] font-medium text-slate-900 resize-none max-h-[80px] min-h-[40px]"/>
                 <button onClick={() => handleSendMessage()} disabled={isTyping || !inputText.trim()} className="w-10 h-10 shrink-0 rounded-xl bg-slate-900 text-white flex items-center justify-center active:scale-95 transition-transform"><Send size={16} className="ml-0.5" /></button>
               </div>
             </div>
          </motion.div>
        )}
      </AnimatePresence>

      {!chatOpen && (
        <motion.button whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }} onClick={() => setChatOpen(true)} className={`fixed right-6 md:right-8 w-16 h-16 bg-slate-900 text-white rounded-2xl shadow-2xl flex items-center justify-center z-50 hover:bg-sky-500 transition-all ${totalItems > 0 ? 'bottom-24 md:bottom-[104px]' : 'bottom-6 md:bottom-8'}`}>
          <span className="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 border-[3px] border-white rounded-full flex items-center justify-center text-[9px] font-bold">1</span>
          <MessageSquare size={26} />
        </motion.button>
      )}
    </div>
  );
}
