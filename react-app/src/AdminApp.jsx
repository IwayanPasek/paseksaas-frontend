import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  LayoutDashboard, Package, MessageSquare, Sparkles, 
  LogOut, Menu, X, Plus, Trash2, Image as ImageIcon, 
  CheckCircle2, AlertTriangle, ExternalLink, Activity
} from 'lucide-react';

const adminData = window.ADMIN_DATA || {
  nama_toko: 'Admin Demo', produk: [], log: [], total_nilai: 0, gaya_bahasa: 'formal', persona: ''
};

const formatRp = (angka) => Number(angka).toLocaleString('id-ID');

export default function AdminApp() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [toast, setToast] = useState(null);
  const [fotoPreview, setFotoPreview] = useState(null);

  // Deteksi Status Notifikasi dari URL (Jembatan dari PHP Redirect)
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
      const status = params.get('status');
      const msg = params.get('msg') || '';
      
      if (status === 'success') showToast('Berhasil!', `Produk ${msg} ditambahkan.`, 'success');
      else if (status === 'deleted') showToast('Terhapus', 'Produk berhasil dihapus.', 'info');
      else if (status === 'persona_saved') showToast('Tersimpan', 'Karakter AI berhasil diperbarui.', 'success');
      else if (status === 'error') showToast('Gagal', msg, 'error');
      
      // Bersihkan URL agar tidak muncul terus saat refresh
      window.history.replaceState({}, document.title, 'admin.php');
    }
  }, []);

  const showToast = (title, message, type) => {
    setToast({ title, message, type });
    setTimeout(() => setToast(null), 4000);
  };

  const handleFotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      if (file.size > 2097152) {
        showToast('Terlalu Besar', 'Maksimal ukuran foto 2MB.', 'error');
        e.target.value = '';
        return;
      }
      setFotoPreview(URL.createObjectURL(file));
    }
  };

  const menuItems = [
    { id: 'dashboard', label: 'Dasbor Utama', icon: LayoutDashboard },
    { id: 'produk', label: 'Manajemen Layanan', icon: Package, count: adminData.produk.length },
    { id: 'log', label: 'Histori AI', icon: MessageSquare, count: adminData.log.length },
    { id: 'persona', label: 'Karakter AI', icon: Sparkles },
  ];

  return (
    <div className="flex h-screen bg-slate-50 font-sans text-slate-800 overflow-hidden">
      
      {/* ── TOAST NOTIFICATION ── */}
      <AnimatePresence>
        {toast && (
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} className="fixed top-6 right-6 z-[100] flex items-center gap-3 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 min-w-[280px]">
            {toast.type === 'success' && <CheckCircle2 className="text-emerald-500" size={24} />}
            {toast.type === 'error' && <AlertTriangle className="text-rose-500" size={24} />}
            {toast.type === 'info' && <CheckCircle2 className="text-sky-500" size={24} />}
            <div>
              <h4 className="font-bold text-slate-900 text-sm">{toast.title}</h4>
              <p className="text-xs text-slate-500 font-medium">{toast.message}</p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ── SIDEBAR (DESKTOP & MOBILE) ── */}
      <AnimatePresence>
        {(sidebarOpen || window.innerWidth >= 768) && (
          <>
            {/* Overlay Mobile */}
            {sidebarOpen && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} onClick={() => setSidebarOpen(false)} className="md:hidden fixed inset-0 bg-slate-900/50 z-40" />
            )}
            
            <motion.aside 
              initial={{ x: -300 }} animate={{ x: 0 }} exit={{ x: -300 }} transition={{ type: 'spring', damping: 25, stiffness: 200 }}
              className="fixed md:static inset-y-0 left-0 w-[260px] bg-white border-r border-slate-200 z-50 flex flex-col shadow-[4px_0_24px_rgba(0,0,0,0.02)] md:shadow-none"
            >
              <div className="p-6 flex items-center justify-between border-b border-slate-100">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-md">P</div>
                  <span className="font-bold text-xl text-slate-900 tracking-tight">SaaS<span className="text-sky-500">Admin</span></span>
                </div>
                <button className="md:hidden text-slate-400 hover:text-slate-900" onClick={() => setSidebarOpen(false)}><X size={24}/></button>
              </div>

              <div className="flex-1 overflow-y-auto py-6 px-4 flex flex-col gap-2">
                <div className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 px-2">Menu Navigasi</div>
                {menuItems.map(item => (
                  <button 
                    key={item.id} 
                    onClick={() => { setActiveTab(item.id); setSidebarOpen(false); }}
                    className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all ${activeTab === item.id ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'}`}
                  >
                    <item.icon size={18} className={activeTab === item.id ? 'text-sky-400' : ''} />
                    {item.label}
                    {item.count !== undefined && (
                      <span className={`ml-auto px-2 py-0.5 rounded-md text-[10px] ${activeTab === item.id ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'}`}>{item.count}</span>
                    )}
                  </button>
                ))}
              </div>

              <div className="p-4 border-t border-slate-100">
                <a href="logout.php" className="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold text-sm text-rose-500 hover:bg-rose-50 transition-colors">
                  <LogOut size={18} /> Keluar Akun
                </a>
              </div>
            </motion.aside>
          </>
        )}
      </AnimatePresence>

      {/* ── MAIN CONTENT ── */}
      <main className="flex-1 flex flex-col overflow-hidden h-screen">
        
        {/* TOPBAR */}
        <header className="bg-white/80 backdrop-blur-md border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
          <div className="flex items-center gap-4">
            <button className="md:hidden text-slate-500 hover:text-slate-900" onClick={() => setSidebarOpen(true)}><Menu size={24}/></button>
            <div>
              <h2 className="font-extrabold text-lg text-slate-900">{menuItems.find(m => m.id === activeTab)?.label}</h2>
              <p className="text-xs font-medium text-slate-500 hidden sm:block">Kelola toko {adminData.nama_toko} dengan AI cerdas.</p>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <a href="index.php" target="_blank" className="hidden sm:flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-bold transition-colors">
              <ExternalLink size={16} /> Lihat Web
            </a>
            <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-sky-400 to-sky-500 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
              {adminData.nama_toko.substring(0, 1).toUpperCase()}
            </div>
          </div>
        </header>

        {/* CONTENT AREA */}
        <div className="flex-1 overflow-y-auto p-6 md:p-8">
          <div className="max-w-5xl mx-auto">
            
            {/* TAB: DASHBOARD */}
            {activeTab === 'dashboard' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                
                {/* Stats Row */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                  {[
                    { label: 'Total Layanan', val: adminData.produk.length, icon: Package, color: 'text-sky-500', bg: 'bg-sky-50' },
                    { label: 'Nilai Etalase', val: `Rp ${formatRp(adminData.total_nilai)}`, icon: Activity, color: 'text-emerald-500', bg: 'bg-emerald-50' },
                    { label: 'Interaksi AI', val: adminData.log.length, icon: MessageSquare, color: 'text-amber-500', bg: 'bg-amber-50' },
                    { label: 'Gaya AI', val: adminData.gaya_bahasa, icon: Sparkles, color: 'text-purple-500', bg: 'bg-purple-50', capitalize: true },
                  ].map((stat, i) => (
                    <div key={i} className="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                      <div className={`w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${stat.bg} ${stat.color}`}>
                        <stat.icon size={24} />
                      </div>
                      <div className="min-w-0">
                        <div className="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{stat.label}</div>
                        <div className={`font-extrabold text-slate-900 truncate ${stat.label === 'Nilai Etalase' ? 'text-lg' : 'text-xl'} ${stat.capitalize ? 'capitalize' : ''}`}>
                          {stat.val}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                <div className="grid lg:grid-cols-3 gap-6">
                  {/* Form Tambah Produk (Direct Submit ke PHP) */}
                  <div className="lg:col-span-1 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm h-fit">
                    <h3 className="font-extrabold text-slate-900 mb-6 flex items-center gap-2"><Plus size={18} className="text-sky-500"/> Tambah Layanan</h3>
                    <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-4">
                      <input type="hidden" name="tambah_produk" value="1" />
                      <div>
                        <label className="block text-xs font-bold text-slate-500 mb-1.5">Nama Layanan</label>
                        <input type="text" name="nama_produk" required className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all" placeholder="Misal: Cuci Helm Premium" />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-500 mb-1.5">Harga (Rp)</label>
                        <input type="number" name="harga" required min="0" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all" placeholder="50000" />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-500 mb-1.5">Deskripsi untuk AI</label>
                        <textarea name="deskripsi" required rows="3" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all resize-none" placeholder="Jelaskan detailnya agar AI pintar menjawab..." />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-500 mb-1.5">Foto (Opsional, Maks 2MB)</label>
                        <label className="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-slate-400 transition-all overflow-hidden relative">
                          {fotoPreview ? (
                            <img src={fotoPreview} className="w-full h-full object-cover absolute inset-0" />
                          ) : (
                            <div className="flex flex-col items-center justify-center pt-5 pb-6">
                              <ImageIcon className="w-8 h-8 text-slate-400 mb-2" />
                              <p className="text-xs text-slate-500 font-medium">Klik untuk upload foto</p>
                            </div>
                          )}
                          <input type="file" name="foto" accept="image/*" className="hidden" onChange={handleFotoChange} />
                        </label>
                      </div>
                      <button type="submit" className="w-full py-3 bg-slate-900 hover:bg-sky-500 text-white rounded-xl font-bold text-sm transition-all shadow-md mt-2 flex justify-center items-center gap-2">
                        <Plus size={16} /> Simpan Layanan
                      </button>
                    </form>
                  </div>

                  {/* List Produk Terbaru */}
                  <div className="lg:col-span-2 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm flex flex-col">
                    <div className="flex justify-between items-center mb-6">
                      <h3 className="font-extrabold text-slate-900 flex items-center gap-2"><Package size={18} className="text-sky-500"/> Baru Ditambahkan</h3>
                      <button onClick={() => setActiveTab('produk')} className="text-xs font-bold text-sky-500 hover:text-slate-900">Lihat Semua &rarr;</button>
                    </div>
                    
                    {adminData.produk.length === 0 ? (
                      <div className="flex-1 flex flex-col items-center justify-center text-slate-400 py-10">
                        <Package size={48} className="mb-4 opacity-20" />
                        <p className="font-bold">Etalase kosong.</p>
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {adminData.produk.slice(0, 5).map(p => (
                          <div key={p.id_produk} className="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                            <div className="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                              {p.foto_produk ? <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-full object-cover" /> : <ImageIcon className="text-slate-300" />}
                            </div>
                            <div className="flex-1 min-w-0">
                              <h4 className="font-bold text-slate-900 truncate">{p.nama_produk}</h4>
                              <p className="text-xs font-bold text-sky-500 mt-0.5">Rp {formatRp(p.harga)}</p>
                            </div>
                            <a href={`admin.php?hapus=${p.id_produk}`} onClick={(e) => !confirm('Yakin hapus?') && e.preventDefault()} className="w-8 h-8 rounded-lg bg-white border border-slate-200 text-rose-500 flex items-center justify-center hover:bg-rose-50 hover:border-rose-200 transition-colors shrink-0">
                              <Trash2 size={14} />
                            </a>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </div>
              </motion.div>
            )}

            {/* TAB: PRODUK LENGKAP */}
            {activeTab === 'produk' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="bg-slate-50 border-b border-slate-200">
                        <th className="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Layanan</th>
                        <th className="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Harga</th>
                        <th className="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider hidden md:table-cell">Konteks AI</th>
                        <th className="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {adminData.produk.map(p => (
                        <tr key={p.id_produk} className="hover:bg-slate-50/50 transition-colors">
                          <td className="p-4">
                            <div className="flex items-center gap-4">
                              <div className="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden flex items-center justify-center">
                                {p.foto_produk ? <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-full object-cover" /> : <ImageIcon size={20} className="text-slate-300" />}
                              </div>
                              <div className="font-bold text-slate-900 text-sm">{p.nama_produk}</div>
                            </div>
                          </td>
                          <td className="p-4 font-bold text-sky-500 text-sm whitespace-nowrap">Rp {formatRp(p.harga)}</td>
                          <td className="p-4 hidden md:table-cell">
                            <div className="text-xs font-medium text-slate-500 line-clamp-2 max-w-xs">{p.deskripsi}</div>
                          </td>
                          <td className="p-4 text-center">
                            <a href={`admin.php?hapus=${p.id_produk}`} onClick={(e) => !confirm('Yakin hapus permanen?') && e.preventDefault()} className="inline-flex p-2 bg-rose-50 text-rose-500 rounded-lg hover:bg-rose-500 hover:text-white transition-colors">
                              <Trash2 size={16} />
                            </a>
                          </td>
                        </tr>
                      ))}
                      {adminData.produk.length === 0 && (
                        <tr><td colSpan="4" className="p-10 text-center text-slate-400 font-bold">Belum ada data layanan.</td></tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </motion.div>
            )}

            {/* TAB: LOG CHAT AI */}
            {activeTab === 'log' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-4">
                {adminData.log.length === 0 ? (
                  <div className="bg-white border border-slate-100 rounded-3xl p-10 text-center text-slate-400">
                    <MessageSquare size={48} className="mx-auto mb-4 opacity-20" />
                    <p className="font-bold">Belum ada obrolan terekam.</p>
                  </div>
                ) : (
                  adminData.log.map((l, i) => {
                    const resp = JSON.parse(l.ai_response || '{}');
                    return (
                      <div key={i} className="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div className="flex justify-between items-start mb-4 gap-4">
                          <div className="flex gap-3 text-sm font-bold text-slate-900 bg-slate-100 px-4 py-2.5 rounded-xl rounded-tr-sm w-fit max-w-[80%]">
                            <span className="text-slate-400">User:</span> "{l.user_query}"
                          </div>
                          <span className="text-[10px] font-bold text-slate-400 bg-white border border-slate-200 px-2 py-1 rounded-md shrink-0">
                            {new Date(l.created_at).toLocaleString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'})}
                          </span>
                        </div>
                        <div className="flex gap-3 text-sm font-medium text-slate-700 bg-sky-50 border border-sky-100 px-4 py-3 rounded-xl rounded-tl-sm w-fit max-w-[90%] ml-4">
                          <Sparkles size={16} className="text-sky-500 shrink-0 mt-0.5" />
                          <div dangerouslySetInnerHTML={{ __html: (resp.reply || '—').replace(/\n/g, '<br/>') }} />
                        </div>
                      </div>
                    )
                  })
                )}
              </motion.div>
            )}

            {/* TAB: PERSONA AI */}
            {activeTab === 'persona' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="max-w-2xl mx-auto">
                <div className="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 shadow-lg text-white mb-6">
                  <h3 className="font-extrabold text-2xl flex items-center gap-3 mb-2"><Sparkles className="text-sky-400"/> Otak AI Anda</h3>
                  <p className="text-slate-300 text-sm font-medium">Beri instruksi kepada AI bagaimana ia harus melayani pelanggan Anda. AI akan patuh 100% pada aturan ini.</p>
                </div>

                <form method="POST" action="admin.php" className="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm space-y-6">
                  <input type="hidden" name="update_persona" value="1" />
                  
                  <div>
                    <label className="block text-sm font-bold text-slate-900 mb-2">Pilih Gaya Bahasa</label>
                    <select name="ai_gaya_bahasa" defaultValue={adminData.gaya_bahasa} className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all appearance-none cursor-pointer">
                      <option value="formal">👔 Formal — Sopan dan terstruktur</option>
                      <option value="santai">🤙 Santai — Casual dan akrab</option>
                      <option value="profesional">💼 Profesional — Bisnis dan to-the-point</option>
                      <option value="ramah">😊 Ramah — Hangat dan suportif</option>
                      <option value="singkat">⚡ Singkat — Padat, tanpa basa-basi</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-bold text-slate-900 mb-2">Instruksi Khusus (Custom Prompt)</label>
                    <textarea 
                      name="ai_persona_prompt" 
                      defaultValue={adminData.persona} 
                      rows="6" 
                      className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all resize-none leading-relaxed" 
                      placeholder={`Contoh:\n- Selalu panggil pelanggan dengan sebutan "Kakak".\n- Jika ditanya lokasi, jawab "Kita ada di Jl. Raya Bali No 1".\n- Jika pelanggan minta diskon, tolak dengan halus.`}
                    />
                  </div>

                  <button type="submit" className="w-full py-4 bg-slate-900 hover:bg-sky-500 text-white rounded-xl font-bold transition-all shadow-md flex justify-center items-center gap-2">
                    <Sparkles size={18} /> Update Karakter AI Sekarang
                  </button>
                </form>
              </motion.div>
            )}

          </div>
        </div>
      </main>
    </div>
  );
}
