import React from 'react';
import { motion } from 'framer-motion';
import { FolderOpen, Trash2 } from 'lucide-react';
import { adminData } from '../adminData';

export default function KategoriTab({ csrfToken }) {
  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-6">
      <div className="card rounded-xl p-6">
        <h3 className="font-semibold text-lg mb-4 text-neutral-900">Buat Kategori</h3>
        <form method="POST" action="admin.php" className="flex flex-col sm:flex-row gap-2.5">
          <input type="hidden" name="add_category" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          <input type="text" name="nama_kategori" required placeholder="Misal: Promo" className="flex-1 bg-neutral-50 px-4 py-3 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 text-sm" />
          <button type="submit" className="px-6 py-3 bg-neutral-900 text-white rounded-xl font-medium text-sm hover:bg-neutral-800 active:scale-[0.98] transition-all">Tambah</button>
        </form>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {adminData.kategori.map(c => (
          <div key={c.id_kategori} className="card rounded-xl px-4 py-3.5 flex justify-between items-center card-hover transition-colors">
            <div className="flex items-center gap-2.5"><FolderOpen size={16} className="text-neutral-400" /><span className="font-medium text-neutral-800 text-sm">{c.nama_kategori}</span></div>
            <form method="POST" action="admin.php" onSubmit={(e) => !confirm(`Hapus ${c.nama_kategori}?`) && e.preventDefault()} className="inline">
              <input type="hidden" name="del_cat" value={c.id_kategori} />
              <input type="hidden" name="_csrf_token" value={csrfToken} />
              <button type="submit" className="text-danger-500 hover:bg-danger-50 p-1.5 rounded-lg text-xs transition-colors"><Trash2 size={14} /></button>
            </form>
          </div>
        ))}
      </div>
    </motion.div>
  );
}
