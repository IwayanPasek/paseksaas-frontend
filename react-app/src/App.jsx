import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Sparkles, MessageSquare, X, Send, Image as ImageIcon, Menu, ShoppingBag, ShieldCheck, Zap } from 'lucide-react';

// ── MENGAMBIL DATA DARI JEMBATAN PHP ──
// Fallback aman jika dibuka di localhost Vite (saat development)
const storeData = window.STORE_DATA || {
  id_toko: 1,
  nama_toko: 'Toko AI',
  desc_toko: 'Layanan cerdas berbasis AI.',
  wa_num: '6281234567890',
  logo: null,
  products: []
};

// Fungsi Format Rupiah
const formatRp = (angka) => Number(angka).toLocaleString('id-ID');

// Fungsi Redirect WhatsApp
const beliViaWA = (nama, harga) => {
  const text = `Halo Admin *${storeData.nama_toko}*! Saya mau pesan/tanya soal:\n\n🛍️ *${nama}*\n💰 ${harga}\n\nApakah bisa dibantu kak?`;
  window.open(`https://wa.me/${storeData.wa_num}?text=${encodeURIComponent(text)}`, '_blank');
};

export default function App() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [chatOpen, setChatOpen] = useState(false);
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const chatEndRef = useRef(null);

  // Inisialisasi Session ID AI
  useEffect(() => {
    if (!localStorage.getItem('ai_session')) {
      localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));
    }
  }, []);

  // Efek Glassmorphism Navbar
  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 50);
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Auto-scroll ke pesan terbaru
  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, isTyping]);

  // Lock scroll layar utama saat chat terbuka (Khusus Mobile)
  useEffect(() => {
    if (chatOpen && window.innerWidth <= 480) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'auto';
    }
  }, [chatOpen]);

  // ── LOGIKA ENGINE CHAT AI ──
  const handleSendMessage = async (text = inputText, skipUserBubble = false) => {
    const userMsg = text.trim();
    if (!userMsg || isTyping) return;
    
    if (!chatOpen) setChatOpen(true);
    
    // Tambahkan bubble user (kecuali jika disembunyikan via chip)
    if (!skipUserBubble) {
      setMessages(prev => [...prev, { role: 'user', text: userMsg }]);
    }
    
    setInputText('');
    setIsTyping(true);

    try {
      const res = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          id_toko: storeData.id_toko, 
          session_id: localStorage.getItem('ai_session'), 
          user_message: userMsg 
        }),
      });

      if (!res.ok) throw new Error('HTTP Error');
      const data = await res.json();
      
      const newAiMsg = { 
        role: 'ai', 
        text: data.reply || 'Maaf, saya tidak mengerti.',
        products: data.db_result || [],
        showWaCard: false
      };

      // Auto-trigger WA Card jika AI menyuruh menghubungi admin
      if (data.reply && (data.reply.toLowerCase().includes('whatsapp') || data.reply.toLowerCase().includes('pesan') || data.reply.toLowerCase().includes('hubungi'))) {
        newAiMsg.showWaCard = true;
      }

      setMessages(prev => [...prev, newAiMsg]);
      
    } catch (err) {
      setMessages(prev => [...prev, { role: 'ai', text: '⚠️ Aduh, koneksi ke server AI terputus. Coba lagi ya!' }]);
    } finally {
      setIsTyping(false);
    }
  };

  const tanyaProduk = (nama) => {
    setChatOpen(true);
    setTimeout(() => handleSendMessage(`Jelaskan lebih detail tentang layanan "${nama}" dong!`), 300);
  };

  const handleChip = (text) => {
    if (text.includes('Hubungi Admin')) {
      setChatOpen(true);
      setMessages(prev => [...prev, { role: 'user', text: 'Cara hubungi admin gimana?' }]);
      setIsTyping(true);
      setTimeout(() => {
        setIsTyping(false);
        setMessages(prev => [...prev, { role: 'ai', text: 'Tentu! Silakan klik tombol di bawah ini untuk ngobrol langsung dengan admin via WhatsApp ya 👇', showWaCard: true }]);
      }, 800);
    } else {
      handleSendMessage(text);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 font-sans text-slate-700 pb-20 md:pb-0">
      
      {/* ── NAVBAR ── */}
      <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        isScrolled ? 'bg-white/95 backdrop-blur-md shadow-sm py-3 border-b border-slate-200' : 'bg-transparent py-5'
      }`}>
        <div className="max-w-6xl mx-auto px-6 flex justify-between items-center">
          <a href="#" className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-extrabold text-lg shadow-md overflow-hidden">
              {storeData.logo ? (
                <img src={`/assets/img/produk/${storeData.logo}`} alt="Logo" className="w-full h-full object-cover" onError={(e) => { e.target.style.display='none'; e.target.nextSibling.style.display='flex'; }} />
              ) : null}
              <span style={{ display: storeData.logo ? 'none' : 'flex' }}>
                {storeData.nama_toko.substring(0, 1).toUpperCase()}
              </span>
            </div>
            <span className="font-extrabold text-xl text-slate-900 tracking-tight">{storeData.nama_toko}</span>
          </a>
          <div className="hidden md:flex items-center gap-4">
            <a href="#katalog" className="px-5 py-2.5 rounded-full font-bold text-sm bg-transparent text-slate-700 border-2 border-transparent hover:bg-white hover:border-slate-200 transition-all">
              Lihat Layanan
            </a>
            <a href="/login.php" className="px-5 py-2.5 rounded-full font-bold text-sm bg-slate-900 text-white hover:bg-sky-500 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
              Admin
            </a>
          </div>
          <button className="md:hidden text-slate-900"><Menu size={28} /></button>
        </div>
      </nav>

      {/* ── HERO SECTION ── */}
      <section className="pt-32 pb-20 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center min-h-[90vh]">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.5 }} className="text-center md:text-left relative z-10">
          <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider mb-6 shadow-sm">
            <span className="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
            Layanan Pintar AI
          </div>
          <h1 className="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-[1.15] mb-6 tracking-tight">
            Kami ahlinya beresin masalahmu.<br/>
            <span className="text-sky-500">Tinggal duduk manis!</span>
          </h1>
          <p className="text-lg text-slate-500 mb-8 md:pr-12 font-medium leading-relaxed">{storeData.desc_toko}</p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
            <a href="#katalog" className="px-8 py-4 rounded-full bg-slate-900 text-white font-bold hover:bg-sky-500 transition-all shadow-lg hover:shadow-sky-500/30 flex items-center justify-center gap-2 hover:-translate-y-1">
              <ShoppingBag size={18} /> Lihat Layanan
            </a>
            <button onClick={() => setChatOpen(true)} className="px-8 py-4 rounded-full bg-transparent border-2 border-slate-200 text-slate-900 font-bold hover:bg-white hover:border-slate-900 transition-all flex items-center justify-center gap-2 hover:-translate-y-1">
              <Sparkles size={18} className="text-sky-500" /> Tanya AI Gratis
            </button>
          </div>
        </motion.div>

        {/* Skeleton Image Placeholder */}
        <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ duration: 0.5, delay: 0.2 }} className="relative hidden md:block">
          <div className="aspect-[4/3] w-full bg-gradient-to-tr from-slate-100 to-slate-200 rounded-[2.5rem] border-4 border-white shadow-2xl flex flex-col items-center justify-center text-slate-400 relative overflow-hidden">
            <div className="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
            <ImageIcon size={64} className="mb-4 opacity-50" />
            <span className="font-bold tracking-widest uppercase text-sm opacity-60">Ruang Foto Utama</span>
          </div>
          <div className="absolute -bottom-6 -left-6 bg-white p-5 rounded-3xl shadow-xl flex items-center gap-4 animate-[bounce_4s_infinite]">
            <div className="w-12 h-12 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center"><Zap size={24} /></div>
            <div>
              <h4 className="font-extrabold text-slate-900 text-sm">Respon Cepat</h4>
              <p className="text-xs text-slate-500 font-semibold">Siaga 24 Jam</p>
            </div>
          </div>
        </motion.div>
      </section>

      {/* ── KATALOG PRODUK ── */}
      <section id="katalog" className="bg-white py-24 px-6 rounded-t-[3rem] shadow-[0_-10px_40px_rgba(0,0,0,0.02)]">
        <div className="max-w-6xl mx-auto">
          <div className="mb-12 flex flex-col md:flex-row justify-between items-center md:items-end gap-4 text-center md:text-left">
            <div>
              <span className="text-sky-500 font-bold text-sm tracking-widest uppercase mb-2 flex items-center justify-center md:justify-start gap-2">
                <ShoppingBag size={16}/> Katalog Kami
              </span>
              <h2 className="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Pilih Layanan Terbaik</h2>
            </div>
            <div className="px-5 py-2 bg-slate-50 rounded-full border border-slate-100 text-sm font-bold text-slate-500 shadow-sm">
              {storeData.products.length} Layanan Tersedia
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {storeData.products.length === 0 ? (
              <div className="col-span-full text-center py-24 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                <ImageIcon size={48} className="mx-auto text-slate-300 mb-4" />
                <div className="text-slate-500 font-bold text-lg">Belum ada layanan yang ditambahkan.</div>
                <p className="text-sm text-slate-400 mt-2">Pemilik toko belum mengisi etalase.</p>
              </div>
            ) : (
              storeData.products.map((prod) => (
                <motion.div whileHover={{ y: -8 }} key={prod.id_produk} className="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-lg shadow-slate-200/50 flex flex-col transition-colors hover:border-sky-200">
                  <div className="aspect-[4/3] bg-slate-100 relative">
                    {prod.foto_produk ? (
                      <img src={`/assets/img/produk/${prod.foto_produk}`} alt={prod.nama_produk} className="w-full h-full object-cover transition-transform duration-500 hover:scale-105" onError={(e) => { e.target.style.display='none'; e.target.nextSibling.style.display='flex'; }} />
                    ) : null}
                    <div className="w-full h-full flex flex-col items-center justify-center text-slate-300 absolute inset-0 bg-slate-50" style={{ display: prod.foto_produk ? 'none' : 'flex' }}>
                      <ImageIcon size={40} className="mb-2"/>
                    </div>
                  </div>
                  <div className="p-6 flex flex-col flex-1">
                    <h3 className="font-extrabold text-lg text-slate-900 mb-2 leading-snug">{prod.nama_produk}</h3>
                    <p className="text-sm text-slate-500 mb-4 line-clamp-2 flex-1 font-medium leading-relaxed">{prod.deskripsi}</p>
                    <div className="border-t border-slate-100 pt-4 mb-4">
                      <div className="font-extrabold text-xl text-slate-900">
                        <span className="text-xs text-slate-400 font-semibold mr-1">Rp</span> 
                        {formatRp(prod.harga)}
                      </div>
                    </div>
                    <div className="flex flex-col gap-2">
                      <button onClick={() => tanyaProduk(prod.nama_produk)} className="w-full py-3 rounded-xl bg-slate-900 text-white font-bold text-sm flex justify-center items-center gap-2 hover:bg-sky-500 transition-colors shadow-md">
                        <Sparkles size={16} /> Tanya AI
                      </button>
                      <button onClick={() => beliViaWA(prod.nama_produk, `Rp ${formatRp(prod.harga)}`)} className="w-full py-3 rounded-xl bg-white border-[1.5px] border-slate-200 text-emerald-500 font-bold text-sm flex justify-center items-center gap-2 hover:bg-emerald-50 hover:border-emerald-500 transition-colors">
                        Pesan Sekarang
                      </button>
                    </div>
                  </div>
                </motion.div>
              ))
            )}
          </div>
        </div>
      </section>

      {/* ── FOOTER ── */}
      <footer className="bg-slate-50 py-12 px-6 border-t border-slate-200 text-center">
        <div className="font-extrabold text-slate-900 text-lg mb-2 flex items-center justify-center gap-2">
          <span className="w-2 h-2 rounded-full bg-sky-500"></span> {storeData.nama_toko}
        </div>
        <p className="text-slate-500 text-sm font-medium">
          &copy; {new Date().getFullYear()} Ditenagai oleh <span className="font-bold text-slate-900">Pasek AI Engine</span>
        </p>
      </footer>

      {/* ── FLOATING AI CHATBOX ── */}
      <AnimatePresence>
        {chatOpen && (
          <motion.div 
            initial={{ opacity: 0, y: 50, scale: 0.95 }} animate={{ opacity: 1, y: 0, scale: 1 }} exit={{ opacity: 0, y: 20, scale: 0.95 }}
            transition={{ type: 'spring', damping: 28, stiffness: 300 }}
            className="fixed bottom-0 right-0 w-[100vw] h-[100dvh] md:w-[400px] md:bottom-24 md:right-8 md:h-[620px] bg-white md:rounded-[2rem] shadow-[0_20px_60px_-10px_rgba(0,0,0,0.2)] flex flex-col z-[100] border border-slate-100 overflow-hidden"
          >
            {/* Header */}
            <div className="bg-white px-5 py-4 flex items-center justify-between border-b border-slate-100 shrink-0">
              <div className="flex items-center gap-3">
                <div className="w-11 h-11 bg-sky-50 rounded-xl flex items-center justify-center text-sky-500 border border-sky-100 shadow-sm"><Sparkles size={20} /></div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-base">Asisten Toko</h3>
                  <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Siaga 24 Jam
                  </div>
                </div>
              </div>
              <button onClick={() => setChatOpen(false)} className="w-9 h-9 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-slate-100 hover:text-slate-900 transition-colors"><X size={18} /></button>
            </div>

            {/* Quick Chips */}
            <div className="flex gap-2 px-4 pt-3 pb-2 overflow-x-auto no-scrollbar shrink-0 bg-slate-50/50">
              {['Tampilkan semua layanan', 'Layanan termurah', 'Hubungi Admin'].map((chip, i) => (
                <button key={i} onClick={() => handleChip(chip)} className="px-4 py-1.5 bg-white border border-slate-200 rounded-full text-xs font-bold text-slate-600 hover:bg-slate-900 hover:text-white hover:border-slate-900 whitespace-nowrap transition-colors shadow-sm">
                  {chip === 'Hubungi Admin' ? '💬 ' : '✨ '} {chip}
                </button>
              ))}
            </div>

            {/* Messages */}
            <div className="flex-1 bg-slate-50 p-5 overflow-y-auto flex flex-col gap-5">
              <div className="flex gap-3 max-w-[85%]">
                <div className="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-900 flex items-center justify-center flex-shrink-0 shadow-sm text-xs"><Sparkles size={14} /></div>
                <div className="bg-white p-3.5 rounded-2xl rounded-tl-sm shadow-sm border border-slate-100 text-[13px] font-medium text-slate-700 leading-relaxed">
                  Halo Kak! 👋 Ada yang bisa aku bantu untuk layanan hari ini?
                </div>
              </div>

              {messages.map((msg, idx) => (
                <div key={idx} className={`flex flex-col gap-2 w-full ${msg.role === 'user' ? 'items-end' : 'items-start'}`}>
                  <div className={`flex gap-3 max-w-[85%] ${msg.role === 'user' ? 'flex-row-reverse' : ''}`}>
                    <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm text-xs ${msg.role === 'user' ? 'bg-slate-200 text-slate-600' : 'bg-white border border-slate-200 text-slate-900'}`}>
                      {msg.role === 'user' ? 'U' : <Sparkles size={14} />}
                    </div>
                    <div className={`p-3.5 text-[13px] font-medium leading-relaxed shadow-sm ${msg.role === 'user' ? 'bg-slate-900 text-white rounded-2xl rounded-tr-sm' : 'bg-white text-slate-700 rounded-2xl rounded-tl-sm border border-slate-100'}`} dangerouslySetInnerHTML={{ __html: msg.text.replace(/\n/g, '<br/>') }} />
                  </div>
                  
                  {/* Kartu Produk AI */}
                  {msg.products && msg.products.length > 0 && (
                    <div className="flex gap-3 max-w-[90%] pl-11">
                       <div className="flex flex-col gap-3 w-full">
                         {msg.products.map(p => (
                           <div key={p.id_produk} className="bg-white border border-slate-200 p-3 rounded-2xl shadow-sm w-full max-w-[220px]">
                             {p.foto_produk && <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-24 object-cover rounded-xl mb-3 bg-slate-100" />}
                             <div className="font-extrabold text-sm text-slate-900 line-clamp-2 leading-snug mb-1">{p.nama_produk}</div>
                             <div className="font-extrabold text-sky-500 text-sm mb-3">Rp {formatRp(p.harga)}</div>
                             <button onClick={() => beliViaWA(p.nama_produk, `Rp ${formatRp(p.harga)}`)} className="w-full py-2 bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-600 transition-colors">Pesan via WA</button>
                           </div>
                         ))}
                       </div>
                    </div>
                  )}

                  {/* Kartu WhatsApp AI */}
                  {msg.showWaCard && (
                    <div className="flex gap-3 max-w-[90%] pl-11">
                      <div className="bg-white border border-emerald-100 p-4 rounded-2xl shadow-md w-full max-w-[220px]">
                        <div className="w-12 h-12 bg-emerald-500 text-white rounded-xl flex items-center justify-center mb-3 shadow-inner"><MessageSquare size={24} /></div>
                        <div className="font-extrabold text-sm text-slate-900 mb-1">WhatsApp Admin</div>
                        <div className="text-xs text-slate-500 font-medium mb-3">Online, respon cepat!</div>
                        <button onClick={() => window.open(`https://wa.me/${storeData.wa_num}`, '_blank')} className="w-full py-2.5 bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2">
                          Chat Sekarang
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              ))}
              
              {isTyping && (
                <div className="flex gap-3 max-w-[85%]">
                  <div className="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-900 flex items-center justify-center flex-shrink-0 shadow-sm"><Sparkles size={14} /></div>
                  <div className="bg-white px-4 py-3.5 rounded-2xl rounded-tl-sm shadow-sm border border-slate-100 flex items-center gap-1.5">
                    <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce"></span>
                    <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce" style={{animationDelay:'0.2s'}}></span>
                    <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce" style={{animationDelay:'0.4s'}}></span>
                  </div>
                </div>
              )}
              <div ref={chatEndRef} />
            </div>

            {/* Chat Input */}
            <div className="p-4 bg-white border-t border-slate-100 shrink-0">
              <div className="flex items-end gap-2 bg-slate-50 border-2 border-slate-100 rounded-2xl p-1.5 focus-within:border-slate-900 transition-all">
                <textarea 
                  value={inputText} onChange={(e) => setInputText(e.target.value)} onKeyDown={(e) => { if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); handleSendMessage(); } }}
                  placeholder="Ketik pesan..." disabled={isTyping}
                  className="flex-1 bg-transparent border-none outline-none px-3 py-2 text-[13px] font-medium text-slate-900 resize-none max-h-[80px] min-h-[40px]"
                />
                <button onClick={() => handleSendMessage()} disabled={isTyping || !inputText.trim()} className="w-10 h-10 shrink-0 rounded-xl bg-slate-900 disabled:bg-slate-300 text-white flex items-center justify-center transition-transform hover:scale-105 active:scale-95">
                  <Send size={16} className="ml-0.5" />
                </button>
              </div>
              <div className="flex justify-between items-center mt-3 px-1">
                <div className="text-[9px] text-slate-400 font-bold uppercase tracking-wider flex items-center gap-1"><ShieldCheck size={12} className="text-emerald-500"/> Sesi Aman</div>
                <button onClick={() => { if(confirm('Hapus chat?')) setMessages([]); }} className="text-[10px] font-bold text-slate-400 hover:text-rose-500 uppercase tracking-wider">Clear</button>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* FAB */}
      {!chatOpen && (
        <motion.button 
          whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }} onClick={() => setChatOpen(true)}
          className="fixed bottom-6 right-6 md:bottom-8 md:right-8 w-16 h-16 bg-slate-900 text-white rounded-2xl shadow-2xl flex items-center justify-center z-50 hover:bg-sky-500 transition-colors group"
        >
          <span className="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 border-[3px] border-white rounded-full flex items-center justify-center text-[9px] font-bold">1</span>
          <MessageSquare size={26} className="group-hover:animate-bounce" />
        </motion.button>
      )}

    </div>
  );
}
