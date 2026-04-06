import React from 'react';
import { motion } from 'framer-motion';
import { Save } from 'lucide-react';
import { adminData } from '../adminData';

export default function FormLayananTab({ isEditing, editForm, onCancelEdit, csrfToken }) {
  return (
    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}>
      <div className="card rounded-xl p-6 md:p-8 relative overflow-hidden">
        {isEditing && <div className="absolute top-0 left-0 right-0 h-1 bg-warning-500" />}
        <div className="flex justify-between items-center mb-6">
          <div>
            <h2 className="text-xl font-bold text-neutral-900">{isEditing ? 'Edit Layanan' : 'Layanan Baru'}</h2>
            <p className="text-neutral-400 text-xs mt-0.5">{isEditing ? 'Perbarui info layanan.' : 'Masukkan layanan baru ke etalase.'}</p>
          </div>
          {isEditing && <button onClick={onCancelEdit} className="px-3 py-1.5 bg-danger-50 text-danger-500 rounded-lg text-xs font-medium hover:bg-danger-500 hover:text-white transition-colors">Batal</button>}
        </div>

        <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-5">
          <input type="hidden" name="save_product" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          {isEditing && <input type="hidden" name="id_produk" value={editForm.id_produk} />}
          {isEditing && <input type="hidden" name="foto_lama" value={editForm.foto_produk} />}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Nama Layanan</label>
              <input type="text" name="nama_produk" defaultValue={editForm.nama_produk} required placeholder="Misal: Cuci Helm" className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all text-sm" />
            </div>
            <div className="space-y-1.5">
              <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Harga (Rp)</label>
              <input type="number" name="harga" defaultValue={editForm.harga} required placeholder="50000" className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all text-sm" />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Kategori</label>
            <select name="id_kategori" required className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all cursor-pointer text-sm appearance-none">
              <option value="">-- Pilih Kategori --</option>
              {adminData.kategori.map(c => <option key={c.id_kategori} value={c.id_kategori} selected={editForm.id_kategori == c.id_kategori}>{c.nama_kategori}</option>)}
            </select>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Deskripsi (Untuk Dibaca AI)</label>
            <textarea name="deskripsi" defaultValue={editForm.deskripsi} required rows="3" placeholder="Jelaskan detailnya agar AI pintar..." className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all resize-none text-sm" />
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Foto {isEditing && '(Kosongkan jika tidak diganti)'}</label>
            <input type="file" name="foto" accept="image/*" className="w-full text-sm text-neutral-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-neutral-100 file:text-neutral-600 bg-neutral-50 rounded-xl p-1.5 border border-neutral-200" />
          </div>

          <button type="submit" className={`w-full py-3.5 rounded-xl font-semibold text-white flex justify-center gap-2 transition-all active:scale-[0.98] ${isEditing ? 'bg-warning-500 hover:bg-warning-600' : 'bg-neutral-900 hover:bg-neutral-800'}`}>
            <Save size={16} /> {isEditing ? 'Simpan Perubahan' : 'Terbitkan Layanan'}
          </button>
        </form>
      </div>
    </motion.div>
  );
}
