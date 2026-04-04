import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  LayoutDashboard, Package, MessageSquare, Sparkles, 
  LogOut, Menu, X, Trash2, Edit3, Image as ImageIcon, 
  Save, FolderOpen, ExternalLink, CheckCircle2, Activity, Settings, HelpCircle, Plus 
} from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const adminData = window.ADMIN_DATA || { 
  toko: { nama_toko: 'Admin', subdomain: '', deskripsi_landing: '', kontak_wa: '', logo: '' }, 
  total_nilai: 0, produk: [], kategori: [], log: [], faq: [], grafik: [], tab_aktif: 'dashboard' 
};

const formatRp = (angka) => Number(angka).toLocaleString('id-ID');

export default function AdminApp() {
  const [activeTab, setActiveTab] = useState(adminData.tab_aktif);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [editForm, setEditForm] = useState({ id_produk: '', nama_produk: '', harga: '', deskripsi: '', id_kategori: '', foto_produk: '' });
  const [toast, setToast] = useState(null);
  const [showFaqModal, setShowFaqModal] = useState(false);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
      const msg = params.get('msg') || 'Operasi Berhasil';
      showToast(msg);
      window.history.replaceState({}, document.title, `admin.php?tab=${activeTab}`);
    }
  }, [activeTab]);

  const showToast = (message) => {
    setToast(message);
    setTimeout(() => setToast(null), 4000);
  };

  const startEdit = (p) => {
    setEditForm(p);
    setIsEditing(true);
    setActiveTab('form_layanan');
    setSidebarOpen(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const cancelEdit = () => {
    setIsEditing(false);
    setEditForm({ id_produk: '', nama_produk: '', harga: '', deskripsi: '', id_kategori: '', foto_produk: '' });
  };

  const menuItems = [
    { id: 'dashboard', label: 'Overview', icon: LayoutDashboard },
    { id: 'form_layanan', label: 'Tambah/Edit', icon: Save },
    { id: 'produk', label: 'Etalase', icon: Package, count: adminData.produk.length },
    { id: 'kategori', label: 'Kategori', icon: FolderOpen, count: adminData.kategori.length },
    { id: 'log', label: 'Histori AI', icon: MessageSquare, count: adminData.log.length },
    { id: 'persona', label: 'Otak AI & FAQ', icon: Sparkles },
    { id: 'pengaturan', label: 'Profil Toko', icon: Settings },
  ];

  return (
    <div className="flex h-screen bg-slate-50 font-sans text-slate-800 overflow-hidden">
      
      {/* ── TOAST NOTIFICATION ── */}
      <AnimatePresence>
        {toast && (
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} className="fixed top-6 right-6 z-[100] flex items-center gap-3 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 min-w-[280px]">
            <CheckCircle2 className="text-emerald-500" size={24} />
            <div>
              <h4 className="font-bold text-slate-900 text-sm">Berhasil!</h4>
              <p className="text-xs text-slate-500 font-medium">{toast}</p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ── SIDEBAR ── */}
      <AnimatePresence>
        {(sidebarOpen || window.innerWidth >= 768) && (
          <>
            {sidebarOpen && <motion.div initial={{opacity:0}} animate={{opacity:1}} exit={{opacity:0}} onClick={()=>setSidebarOpen(false)} className="md:hidden fixed inset-0 bg-slate-900/50 z-40" />}
            <motion.aside initial={{x:-300}} animate={{x:0}} exit={{x:-300}} transition={{type:'spring', damping:25}} className="fixed md:static inset-y-0 left-0 w-[260px] bg-white border-r border-slate-200 z-50 flex flex-col shadow-[4px_0_24px_rgba(0,0,0,0.02)] md:shadow-none">
              
              <div className="p-6 flex items-center justify-between border-b border-slate-100">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-md overflow-hidden">
                    {adminData.toko.logo ? <img src={`/assets/img/produk/${adminData.toko.logo}`} className="w-full h-full object-cover"/> : adminData.toko.nama_toko.substring(0,1).toUpperCase()}
                  </div>
                  <span className="font-extrabold text-lg truncate text-slate-900">{adminData.toko.nama_toko}</span>
                </div>
                <button className="md:hidden text-slate-400" onClick={()=>setSidebarOpen(false)}><X size={24}/></button>
              </div>

              <div className="flex-1 overflow-y-auto p-4 flex flex-col gap-1.5">
                <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-2 mt-2">Menu Navigasi</div>
                {menuItems.map(item => (
                  <button 
                    key={item.id} 
                    onClick={() => { setActiveTab(item.id); setSidebarOpen(false); if(item.id !== 'form_layanan') cancelEdit(); }}
                    className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all ${activeTab === item.id ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'}`}
                  >
                    <item.icon size={18} className={activeTab === item.id ? 'text-sky-400' : ''} />
                    {item.label}
                    {item.count !== undefined && <span className={`ml-auto px-2 py-0.5 rounded-md text-[10px] ${activeTab === item.id ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'}`}>{item.count}</span>}
                  </button>
                ))}
              </div>

              <div className="p-4 border-t border-slate-100">
                <a href="logout.php" className="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold text-sm text-rose-500 hover:bg-rose-50 transition-colors">
                  <LogOut size={18} /> Keluar
                </a>
              </div>
            </motion.aside>
          </>
        )}
      </AnimatePresence>

      {/* ── MAIN CONTENT ── */}
      <main className="flex-1 flex flex-col overflow-hidden h-screen relative">
        
        <header className="bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
          <div className="flex items-center gap-4">
            <button className="md:hidden text-slate-500" onClick={()=>setSidebarOpen(true)}><Menu size={24}/></button>
            <h2 className="font-extrabold text-xl text-slate-900">{menuItems.find(m => m.id === activeTab)?.label}</h2>
          </div>
          {adminData.toko.subdomain && (
            <a href={`https://${adminData.toko.subdomain}.websitewayan.my.id`} target="_blank" rel="noreferrer" className="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-colors">
              <ExternalLink size={14} /> <span className="hidden sm:inline">Kunjungi Web</span>
            </a>
          )}
        </header>

        <div className="flex-1 overflow-y-auto p-6 md:p-8">
          <div className="max-w-5xl mx-auto">

            {/* ── TAB: DASHBOARD (STATISTIK & GRAFIK) ── */}
            {activeTab === 'dashboard' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                
                {/* Stats Cards */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                  {[
                    { label: 'Total Layanan', val: adminData.produk.length, icon: Package, color: 'text-sky-500', bg: 'bg-sky-50' },
                    { label: 'Nilai Etalase', val: `Rp ${formatRp(adminData.total_nilai)}`, icon: Activity, color: 'text-emerald-500', bg: 'bg-emerald-50' },
                    { label: 'Chat Masuk', val: adminData.log.length, icon: MessageSquare, color: 'text-amber-500', bg: 'bg-amber-50' },
                    { label: 'Kategori', val: adminData.kategori.length, icon: FolderOpen, color: 'text-purple-500', bg: 'bg-purple-50' },
                  ].map((stat, i) => (
                    <div key={i} className="bg-white p-5 rounded-[2rem] border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col md:flex-row md:items-center gap-4 hover:border-slate-200 transition-colors">
                      <div className={`w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${stat.bg} ${stat.color}`}><stat.icon size={24} /></div>
                      <div className="min-w-0">
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{stat.label}</div>
                        <div className={`font-extrabold text-slate-900 truncate ${stat.label === 'Nilai Etalase' ? 'text-lg' : 'text-2xl'}`}>{stat.val}</div>
                      </div>
                    </div>
                  ))}
                </div>
                
                {/* Grafik Interaksi AI */}
                <div className="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)]">
                  <h3 className="font-extrabold text-lg text-slate-900 mb-6 flex items-center gap-2"><Activity className="text-sky-500"/> Tren Obrolan AI (7 Hari Terakhir)</h3>
                  <div className="h-[300px] w-full">
                    <ResponsiveContainer width="100%" height="100%">
                      <LineChart data={adminData.grafik} margin={{ top: 5, right: 20, bottom: 5, left: -20 }}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                        <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fontSize: 12, fill: '#64748b', fontWeight: 'bold'}} dy={10} />
                        <YAxis axisLine={false} tickLine={false} tick={{fontSize: 12, fill: '#64748b', fontWeight: 'bold'}} />
                        <Tooltip contentStyle={{ borderRadius: '1rem', border: 'none', boxShadow: '0 10px 25px rgba(0,0,0,0.1)', fontWeight: 'bold' }} />
                        <Line type="monotone" dataKey="interaksi" stroke="#0ea5e9" strokeWidth={4} dot={{ r: 5, strokeWidth: 2, fill: '#fff' }} activeDot={{ r: 8, strokeWidth: 0 }} />
                      </LineChart>
                    </ResponsiveContainer>
                  </div>
                </div>
              </motion.div>
            )}

            {/* ── TAB: FORM LAYANAN ── */}
            {activeTab === 'form_layanan' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}>
                <div className="bg-white border border-slate-100 p-8 rounded-[2.5rem] shadow-xl relative overflow-hidden">
                  {isEditing && <div className="absolute top-0 left-0 right-0 h-1.5 bg-amber-500"></div>}
                  <div className="flex justify-between items-center mb-8">
                    <div>
                      <h2 className="text-2xl font-black text-slate-900">{isEditing ? 'Edit Layanan' : 'Form Layanan Baru'}</h2>
                      <p className="text-slate-500 text-xs font-medium mt-1">{isEditing ? 'Perbarui info layanan.' : 'Masukkan layanan baru ke etalase.'}</p>
                    </div>
                    {isEditing && <button onClick={cancelEdit} className="px-4 py-2 bg-rose-50 text-rose-500 rounded-lg text-xs font-bold hover:bg-rose-500 hover:text-white transition-colors">Batal Edit</button>}
                  </div>

                  <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-6">
                    <input type="hidden" name="save_product" value="1"/>
                    {isEditing && <input type="hidden" name="id_produk" value={editForm.id_produk}/>}
                    {isEditing && <input type="hidden" name="foto_lama" value={editForm.foto_produk}/>}

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                      <div className="space-y-2">
                        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Layanan</label>
                        <input type="text" name="nama_produk" defaultValue={editForm.nama_produk} required placeholder="Misal: Cuci Helm" className="w-full bg-slate-50 p-4 rounded-2xl outline-none border border-transparent focus:border-sky-500 transition-all font-bold text-sm"/>
                      </div>
                      <div className="space-y-2">
                        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Harga (Rp)</label>
                        <input type="number" name="harga" defaultValue={editForm.harga} required placeholder="50000" className="w-full bg-slate-50 p-4 rounded-2xl outline-none border border-transparent focus:border-sky-500 transition-all font-bold text-sm"/>
                      </div>
                    </div>

                    <div className="space-y-2">
                      <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Kategori</label>
                      <select name="id_kategori" required className="w-full bg-slate-50 p-4 rounded-2xl outline-none border border-transparent focus:border-sky-500 transition-all cursor-pointer font-bold text-sm appearance-none">
                        <option value="">-- Pilih Kategori --</option>
                        {adminData.kategori.map(c => <option key={c.id_kategori} value={c.id_kategori} selected={editForm.id_kategori == c.id_kategori}>{c.nama_kategori}</option>)}
                      </select>
                    </div>

                    <div className="space-y-2">
                      <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Deskripsi (Untuk Dibaca AI)</label>
                      <textarea name="deskripsi" defaultValue={editForm.deskripsi} required rows="3" placeholder="Jelaskan detailnya agar AI pintar..." className="w-full bg-slate-50 p-4 rounded-2xl outline-none border border-transparent focus:border-sky-500 transition-all resize-none text-sm font-medium"></textarea>
                    </div>

                    <div className="space-y-2">
                      <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Foto {isEditing && '(Kosongkan jika tidak diganti)'}</label>
                      <input type="file" name="foto" accept="image/*" className="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-600 bg-slate-50 rounded-2xl p-1.5"/>
                    </div>

                    <button type="submit" className={`w-full py-4 rounded-2xl font-black text-white shadow-lg flex justify-center gap-2 transition-transform active:scale-95 ${isEditing ? 'bg-amber-500 shadow-amber-200' : 'bg-slate-900 shadow-slate-200'}`}>
                      <Save size={18}/> {isEditing ? 'Simpan Perubahan' : 'Terbitkan Layanan'}
                    </button>
                  </form>
                </div>
              </motion.div>
            )}

            {/* ── TAB: DAFTAR PRODUK ── */}
            {activeTab === 'produk' && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-4">
                {adminData.produk.length === 0 ? (
                  <div className="text-center py-20 bg-white rounded-[2rem] border border-slate-100 text-slate-400"><Package size={48} className="mx-auto mb-4 opacity-30"/><p className="font-bold">Etalase kosong.</p></div>
                ) : (
                  adminData.produk.map(p => (
                    <div key={p.id_produk} className="bg-white p-5 rounded-3xl border border-slate-100 flex items-center justify-between hover:border-sky-200 hover:shadow-xl transition-all">
                      <div className="flex items-center gap-4 md:gap-6">
                        <div className="w-16 h-16 rounded-2xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                          {p.foto_produk ? <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-full object-cover"/> : <ImageIcon className="m-auto mt-5 text-slate-300"/>}
                        </div>
                        <div className="min-w-0 pr-2">
                          <div className="text-[10px] font-bold text-sky-500 uppercase mb-0.5">{adminData.kategori.find(c => c.id_kategori == p.id_kategori)?.nama_kategori || 'Uncategorized'}</div>
                          <h4 className="font-black text-slate-900 truncate">{p.nama_produk}</h4>
                          <p className="text-sm font-bold text-slate-500 mt-1">Rp {formatRp(p.harga)}</p>
                        </div>
                      </div>
                      <div className="flex gap-2 shrink-0">
                        <button onClick={()=>startEdit(p)} className="p-3 bg-amber-50 text-amber-500 rounded-xl hover:bg-amber-500 hover:text-white transition-colors" title="Edit"><Edit3 size={18}/></button>
                        <a href={`admin.php?hapus_prod=${p.id_produk}`} onClick={(e)=>!confirm('Hapus permanen?') && e.preventDefault()} className="p-3 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-colors"><Trash2 size={18}/></a>
                      </div>
                    </div>
                  ))
                )}
              </motion.div>
            )}

            {/* ── TAB: KATEGORI ── */}
            {activeTab === 'kategori' && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-8">
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl">
                  <h3 className="font-black text-xl mb-1 text-slate-900">Buat Kategori</h3>
                  <form method="POST" action="admin.php" className="flex flex-col sm:flex-row gap-3 mt-6">
                    <input type="hidden" name="add_category" value="1"/>
                    <input type="text" name="nama_kategori" required placeholder="Misal: Promo" className="flex-1 bg-slate-50 px-5 py-4 rounded-2xl outline-none border border-transparent focus:border-sky-500 text-sm font-bold"/>
                    <button type="submit" className="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black shadow-lg hover:bg-sky-500 active:scale-95 transition-all">Tambah</button>
                  </form>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {adminData.kategori.map(c => (
                    <div key={c.id_kategori} className="bg-white px-6 py-5 rounded-2xl border border-slate-100 flex justify-between items-center hover:border-sky-200 transition-colors">
                      <div className="flex items-center gap-3"><FolderOpen size={18} className="text-sky-500"/><span className="font-bold text-slate-800">{c.nama_kategori}</span></div>
                      <a href={`admin.php?del_cat=${c.id_kategori}`} onClick={(e)=>!confirm(`Hapus ${c.nama_kategori}?`) && e.preventDefault()} className="text-rose-500 hover:bg-rose-50 p-2 rounded-lg font-bold text-xs transition-colors">Hapus</a>
                    </div>
                  ))}
                </div>
              </motion.div>
            )}

            {/* ── TAB: PENGATURAN PROFIL TOKO (FITUR BARU) ── */}
            {activeTab === 'pengaturan' && (
              <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="max-w-2xl mx-auto space-y-6">
                 <div className="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-xl">
                   <h3 className="font-extrabold text-2xl flex items-center gap-3 mb-6 text-slate-900"><Settings className="text-sky-500"/> Profil Toko</h3>
                   
                   <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-6">
                     <input type="hidden" name="update_profil" value="1"/>
                     
                     <div className="space-y-2">
                       <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Logo Toko (Abaikan jika tidak diganti)</label>
                       <div className="flex items-center gap-4">
                         <div className="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                           {adminData.toko.logo ? <img src={`/assets/img/produk/${adminData.toko.logo}`} className="w-full h-full object-cover"/> : <ImageIcon className="text-slate-300"/>}
                         </div>
                         <input type="file" name="logo_toko" accept="image/*" className="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-600 bg-slate-50 rounded-2xl p-1.5"/>
                       </div>
                     </div>

                     <div className="space-y-2">
                       <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Toko / Bisnis</label>
                       <input type="text" name="nama_toko" defaultValue={adminData.toko.nama_toko} required className="w-full bg-slate-50 border border-transparent focus:border-sky-500 rounded-2xl px-5 py-4 text-sm font-bold outline-none transition-all"/>
                     </div>

                     <div className="space-y-2">
                       <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Nomor WhatsApp (Penerima Pesanan)</label>
                       <input type="text" name="kontak_wa" defaultValue={adminData.toko.kontak_wa} required className="w-full bg-slate-50 border border-transparent focus:border-sky-500 rounded-2xl px-5 py-4 text-sm font-bold outline-none transition-all"/>
                     </div>

                     <div className="space-y-2">
                       <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">Deskripsi Utama (Untuk Landing Page)</label>
                       <textarea name="deskripsi_landing" defaultValue={adminData.toko.deskripsi_landing} rows="3" placeholder="Teks sambutan marketing untuk pengunjung..." className="w-full bg-slate-50 border border-transparent focus:border-sky-500 rounded-2xl p-5 text-sm font-medium outline-none transition-all resize-none"></textarea>
                     </div>

                     <button type="submit" className="w-full py-4 bg-slate-900 text-white rounded-2xl font-black shadow-lg hover:bg-sky-500 transition-all active:scale-95 flex items-center justify-center gap-2">
                       <Save size={18}/> Simpan Pengaturan
                     </button>
                   </form>
                 </div>
              </motion.div>
            )}

            {/* ── TAB: PERSONA & FAQ AI (OTAK AI) ── */}
            {activeTab === 'persona' && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-8">
                
                {/* Setting Karakter */}
                <div className="bg-gradient-to-r from-slate-900 to-slate-800 rounded-[2.5rem] p-8 shadow-xl text-white">
                  <h3 className="font-extrabold text-2xl flex items-center gap-3 mb-2"><Sparkles className="text-sky-400"/> Otak AI Anda</h3>
                  <p className="text-slate-300 text-sm font-medium mb-6">Atur gaya bicara AI dan tambahkan FAQ agar AI pintar menjawab.</p>
                  
                  <form method="POST" action="admin.php" className="space-y-6">
                    <input type="hidden" name="update_persona" value="1"/>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                      <div>
                        <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Gaya Bahasa</label>
                        <select name="ai_gaya_bahasa" defaultValue={adminData.toko.ai_gaya_bahasa} className="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-sm font-bold text-white outline-none cursor-pointer appearance-none">
                          <option value="formal" className="text-slate-900">👔 Formal & Sopan</option>
                          <option value="santai" className="text-slate-900">🤙 Santai & Akrab</option>
                          <option value="profesional" className="text-slate-900">💼 Profesional Bisnis</option>
                          <option value="ramah" className="text-slate-900">😊 Ramah & Hangat</option>
                        </select>
                      </div>
                      <div>
                        <label className="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Instruksi Karakter</label>
                        <textarea name="ai_persona_prompt" defaultValue={adminData.toko.ai_persona_prompt} rows="2" placeholder="Contoh: Panggil pelanggan dengan Kakak..." className="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-3 text-sm font-medium text-white outline-none resize-none"></textarea>
                      </div>
                    </div>
                    <button type="submit" className="px-8 py-3 bg-sky-500 text-white rounded-xl font-bold shadow-lg hover:bg-sky-400 transition-all active:scale-95 text-sm">Update Karakter</button>
                  </form>
                </div>

                {/* FAQ List */}
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-lg">
                  <div className="flex justify-between items-center mb-6">
                    <h3 className="font-extrabold text-xl text-slate-900 flex items-center gap-2"><HelpCircle className="text-sky-500"/> FAQ Toko</h3>
                    <button onClick={() => setShowFaqModal(true)} className="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-sky-500 transition-all">+ Tambah Tanya Jawab</button>
                  </div>
                  
                  <div className="grid gap-3">
                    {adminData.faq.length === 0 ? (
                      <p className="text-slate-400 text-sm font-bold text-center py-6">Belum ada FAQ khusus.</p>
                    ) : (
                      adminData.faq.map(f => (
                        <div key={f.id_faq} className="bg-slate-50 p-5 rounded-2xl border border-slate-100 relative group">
                          <p className="font-bold text-slate-900 text-sm mb-1">Q: {f.pertanyaan}</p>
                          <p className="text-sm font-medium text-slate-600">A: {f.jawaban}</p>
                          <a href={`admin.php?del_faq=${f.id_faq}`} onClick={(e)=>!confirm('Hapus FAQ?') && e.preventDefault()} className="absolute top-4 right-4 text-rose-400 hover:text-rose-600"><Trash2 size={16}/></a>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              </motion.div>
            )}

            {/* TAB: LOG CHAT */}
            {activeTab === 'log' && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-4">
                {adminData.log.map((l, i) => {
                  const resp = JSON.parse(l.ai_response || '{}');
                  return (
                    <div key={i} className="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
                      <div className="flex justify-between items-start mb-4 gap-4">
                        <div className="text-sm font-bold text-slate-900 bg-slate-100 px-4 py-3 rounded-2xl rounded-tr-sm max-w-[85%]">"{l.user_query}"</div>
                        <span className="text-[10px] font-bold text-slate-400 border border-slate-200 px-2 py-1 rounded-lg shrink-0">{new Date(l.created_at).toLocaleString('id-ID', {hour:'2-digit', minute:'2-digit'})}</span>
                      </div>
                      <div className="flex gap-3 text-sm font-medium text-slate-700 bg-sky-50 border border-sky-100 px-4 py-3 rounded-2xl rounded-tl-sm max-w-[90%] ml-4">
                        <Sparkles size={16} className="text-sky-500 shrink-0 mt-0.5" />
                        <div dangerouslySetInnerHTML={{ __html: (resp.reply || '—').replace(/\n/g, '<br/>') }} />
                      </div>
                    </div>
                  )
                })}
              </motion.div>
            )}

          </div>
        </div>

        {/* Modal Tambah FAQ */}
        <AnimatePresence>
          {showFaqModal && (
            <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
              <motion.div initial={{ scale: 0.9, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} exit={{ scale: 0.9, opacity: 0 }} className="bg-white w-full max-w-lg rounded-[2.5rem] p-8 shadow-2xl relative">
                <button onClick={() => setShowFaqModal(false)} className="absolute top-6 right-6 text-slate-400 hover:text-slate-900"><X size={20}/></button>
                <h3 className="font-extrabold text-xl text-slate-900 mb-6">Tambah Tanya Jawab AI</h3>
                <form method="POST" action="admin.php" className="space-y-4">
                  <input type="hidden" name="add_faq" value="1"/>
                  <div>
                    <label className="block text-[10px] font-black uppercase text-slate-400 mb-1.5">Pertanyaan (Q)</label>
                    <input type="text" name="pertanyaan" required placeholder="Contoh: Buka jam berapa?" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:border-sky-500"/>
                  </div>
                  <div>
                    <label className="block text-[10px] font-black uppercase text-slate-400 mb-1.5">Jawaban AI (A)</label>
                    <textarea name="jawaban" required rows="3" placeholder="Kita buka jam 08:00 - 20:00 kak." className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium outline-none focus:border-sky-500 resize-none"></textarea>
                  </div>
                  <button type="submit" className="w-full py-4 bg-slate-900 text-white rounded-xl font-black shadow-lg hover:bg-sky-500 transition-all mt-2">Simpan FAQ</button>
                </form>
              </motion.div>
            </div>
          )}
        </AnimatePresence>

      </main>
    </div>
  );
}
