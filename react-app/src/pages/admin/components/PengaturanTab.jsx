import React from 'react';
import { motion } from 'framer-motion';
import { Settings, Save, Image as ImageIcon } from 'lucide-react';
import { adminData } from '../adminData';

export default function PengaturanTab({ csrfToken }) {
  return (
    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} className="max-w-2xl mx-auto">
      <div className="card rounded-xl p-6 md:p-8">
        <h3 className="font-semibold text-xl flex items-center gap-2.5 mb-5 text-neutral-900"><Settings size={18} className="text-neutral-400" /> Profil Toko</h3>
        <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-5">
          <input type="hidden" name="update_profil" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Logo Toko</label>
            <div className="flex items-center gap-3">
              <div className="w-14 h-14 rounded-xl bg-neutral-100 border border-neutral-200 overflow-hidden flex items-center justify-center shrink-0">
                {adminData.toko.logo ? <img src={`/assets/img/produk/${adminData.toko.logo}`} className="w-full h-full object-cover" alt="" /> : <ImageIcon className="text-neutral-300" size={20} />}
              </div>
              <input type="file" name="logo_toko" accept="image/*" className="w-full text-sm text-neutral-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-neutral-100 file:text-neutral-600 bg-neutral-50 rounded-xl p-1.5 border border-neutral-200" />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Nama Toko / Bisnis</label>
            <input type="text" name="nama_toko" defaultValue={adminData.toko?.nama_toko || ''} required className="w-full bg-neutral-50 border border-neutral-200 focus:border-neutral-400 rounded-xl px-4 py-3.5 text-sm outline-none transition-all" />
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Nomor WhatsApp (Penerima Pesanan)</label>
            <input type="text" name="kontak_wa" defaultValue={adminData.toko?.kontak_wa || ''} required className="w-full bg-neutral-50 border border-neutral-200 focus:border-neutral-400 rounded-xl px-4 py-3.5 text-sm outline-none transition-all" />
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Deskripsi Utama (Landing Page)</label>
            <textarea name="deskripsi_landing" defaultValue={adminData.toko?.deskripsi_landing || ''} rows="3" placeholder="Teks sambutan marketing..." className="w-full bg-neutral-50 border border-neutral-200 focus:border-neutral-400 rounded-xl p-4 text-sm outline-none transition-all resize-none" />
          </div>

          <button type="submit" className="w-full py-3.5 bg-neutral-900 text-white rounded-xl font-semibold hover:bg-neutral-800 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
            <Save size={16} /> Simpan Pengaturan
          </button>
        </form>
      </div>
    </motion.div>
  );
}
