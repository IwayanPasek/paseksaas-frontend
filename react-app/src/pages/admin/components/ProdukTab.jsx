import React from 'react';
import { motion } from 'framer-motion';
import { Edit3, Trash2, Image as ImageIcon } from 'lucide-react';
import EmptyState from '@/components/ui/EmptyState';
import { adminData } from '../adminData';
import { formatRp } from '@/lib/utils';
import { Package } from 'lucide-react';

export default function ProdukTab({ onEdit, csrfToken }) {
  if (adminData.produk.length === 0) return <EmptyState icon={Package} title="Etalase kosong." subtitle="Tambahkan layanan pertama Anda." />;

  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-3">
      {adminData.produk.map(p => (
        <div key={p.id_produk} className="card rounded-xl p-4 flex items-center justify-between card-hover transition-all">
          <div className="flex items-center gap-3 md:gap-4">
            <div className="w-12 h-12 rounded-xl bg-neutral-100 overflow-hidden shrink-0 border border-neutral-200">
              {p.foto_produk ? <img src={`/assets/img/produk/${p.foto_produk}`} className="w-full h-full object-cover" alt="" /> : <ImageIcon className="m-auto mt-3.5 text-neutral-300" size={18} />}
            </div>
            <div className="min-w-0 pr-2">
              <div className="text-[10px] font-medium text-neutral-400 uppercase mb-0.5">{adminData.kategori.find(c => c.id_kategori == p.id_kategori)?.nama_kategori || 'Uncategorized'}</div>
              <h4 className="font-semibold text-neutral-900 text-sm truncate">{p.nama_produk}</h4>
              <p className="text-sm text-neutral-500 mt-0.5">Rp {formatRp(p.harga)}</p>
            </div>
          </div>
          <div className="flex gap-1.5 shrink-0">
            <button onClick={() => onEdit(p)} className="p-2.5 bg-neutral-100 text-neutral-500 rounded-lg hover:bg-neutral-900 hover:text-white transition-colors" title="Edit"><Edit3 size={15} /></button>
            <form method="POST" action="admin.php" onSubmit={(e) => !confirm('Hapus permanen?') && e.preventDefault()} className="inline">
              <input type="hidden" name="hapus_prod" value={p.id_produk} />
              <input type="hidden" name="_csrf_token" value={csrfToken} />
              <button type="submit" className="p-2.5 bg-danger-50 text-danger-500 rounded-lg hover:bg-danger-500 hover:text-white transition-colors"><Trash2 size={15} /></button>
            </form>
          </div>
        </div>
      ))}
    </motion.div>
  );
}
