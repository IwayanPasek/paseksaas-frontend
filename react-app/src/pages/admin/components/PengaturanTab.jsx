import React from 'react';
import { motion } from 'framer-motion';
import { Settings, Save, Image as ImageIcon } from 'lucide-react';
import { adminData } from '../adminData';

export default function PengaturanTab({ csrfToken }) {
  return (
    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} className="max-w-2xl mx-auto">
      <div className="card rounded-xl p-6 md:p-8 shadow-sm border border-neutral-100">
        <h3 className="font-semibold text-xl flex items-center gap-2.5 mb-5 text-neutral-900">
          <Settings size={18} className="text-neutral-400" /> Store Profile
        </h3>
        <p className="text-sm text-neutral-500 mb-8 font-light">
          Configure your business identity and contact information for the storefront.
        </p>

        <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-6">
          <input type="hidden" name="update_profil" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />

          {/* Logo Upload */}
          <div className="space-y-2">
            <label className="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Business Logo</label>
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 rounded-2xl bg-neutral-50 border border-dashed border-neutral-200 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                {adminData.store.logo ? <img src={`/assets/img/produk/${adminData.store.logo}`} className="w-full h-full object-cover" alt="Logo" /> : <ImageIcon className="text-neutral-300" size={24} />}
              </div>
              <div className="flex-1">
                <input type="file" name="logo_toko" accept="image/*" 
                  className="w-full text-xs text-neutral-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border file:border-neutral-200 file:text-xs file:font-semibold file:bg-white file:text-neutral-700 file:hover:bg-neutral-50 file:transition-all cursor-pointer" />
                <p className="text-[10px] text-neutral-400 mt-2 italic">Recommended: Square WebP or PNG (Max 5MB)</p>
              </div>
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Business Name</label>
            <input type="text" name="nama_toko" defaultValue={adminData.store?.name || ''} required 
              placeholder="e.g., Zenith Premium Coffee"
              className="w-full bg-white border border-neutral-200 focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/5 rounded-xl px-4 py-3.5 text-sm outline-none transition-all shadow-sm" />
          </div>

          <div className="space-y-2">
            <label className="text-[10px] font-bold uppercase tracking-widest text-neutral-500">WhatsApp Contact (Order Receiver)</label>
            <input type="text" name="kontak_wa" defaultValue={adminData.store?.whatsapp || ''} required 
              placeholder="e.g., 6281234567890"
              className="w-full bg-white border border-neutral-200 focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/5 rounded-xl px-4 py-3.5 text-sm outline-none transition-all shadow-sm font-mono" />
              <p className="text-[10px] text-neutral-400 mt-1">Include country code without "+" (e.g., 62 for Indonesia)</p>
          </div>

          <div className="space-y-2">
            <label className="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Primary Description (Hero Section)</label>
            <textarea name="deskripsi_landing" defaultValue={adminData.store?.description || ''} rows="3" 
              placeholder="Enter a compelling welcome message for your customers..." 
              className="w-full bg-white border border-neutral-200 focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/5 rounded-xl p-4 text-sm outline-none transition-all resize-none shadow-sm" />
          </div>

          <div className="pt-4">
            <button type="submit" className="w-full py-4 bg-neutral-900 text-white rounded-xl font-bold text-sm hover:bg-black transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 shadow-lg shadow-neutral-900/10">
              <Save size={18} /> Update Store Profile
            </button>
          </div>
        </form>
      </div>
    </motion.div>
  );
}
