import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, HelpCircle, Trash2 } from 'lucide-react';
import Modal from '@/components/ui/Modal';
import { adminData } from '../adminData';

export default function PersonaTab({ csrfToken }) {
  const [showFaqModal, setShowFaqModal] = useState(false);

  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-6">
      {/* AI Character */}
      <div className="rounded-xl p-6 md:p-8 bg-neutral-900 text-white border border-neutral-800 shadow-sm">
        <h3 className="font-semibold text-xl flex items-center gap-2.5 mb-1.5"><Sparkles size={18} className="text-neutral-400" /> Otak AI Anda</h3>
        <p className="text-neutral-400 text-sm mb-5">Atur gaya bicara AI dan tambahkan FAQ agar AI pintar menjawab.</p>
        <form method="POST" action="admin.php" className="space-y-4">
          <input type="hidden" name="update_persona" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-[10px] font-medium uppercase tracking-widest text-neutral-500 mb-1.5">Gaya Bahasa</label>
              <select name="ai_gaya_bahasa" defaultValue={adminData.toko?.ai_gaya_bahasa || 'formal'} className="w-full bg-neutral-800 border border-neutral-700 rounded-xl px-4 py-3 text-sm text-white outline-none cursor-pointer">
                <option value="formal" className="bg-neutral-800 text-white">👔 Formal &amp; Sopan</option>
                <option value="santai" className="bg-neutral-800 text-white">🤙 Santai &amp; Akrab</option>
                <option value="profesional" className="bg-neutral-800 text-white">💼 Profesional Bisnis</option>
                <option value="ramah" className="bg-neutral-800 text-white">😊 Ramah &amp; Hangat</option>
              </select>
            </div>
            <div>
              <label className="block text-[10px] font-medium uppercase tracking-widest text-neutral-500 mb-1.5">Instruksi Karakter</label>
              <textarea name="ai_persona_prompt" defaultValue={adminData.toko?.ai_persona_prompt || ''} rows="2" placeholder="Contoh: Panggil pelanggan dengan Kakak..." className="w-full bg-neutral-800 border border-neutral-700 rounded-xl px-4 py-3 text-sm text-white placeholder-neutral-500 outline-none resize-none" />
            </div>
          </div>
          <button type="submit" className="px-6 py-2.5 bg-white text-neutral-900 rounded-lg font-semibold text-sm hover:bg-neutral-100 transition-all active:scale-[0.98]">Update Karakter</button>
        </form>
      </div>

      {/* FAQ List */}
      <div className="card rounded-xl p-6">
        <div className="flex justify-between items-center mb-5">
          <h3 className="font-semibold text-lg text-neutral-900 flex items-center gap-2"><HelpCircle size={16} className="text-neutral-400" /> FAQ Toko</h3>
          <button onClick={() => setShowFaqModal(true)} className="px-3 py-1.5 bg-neutral-900 text-white rounded-lg text-xs font-medium hover:bg-neutral-800 transition-all">+ Tambah</button>
        </div>
        <div className="grid gap-2.5">
          {adminData.faq.length === 0
            ? <p className="text-neutral-400 text-sm text-center py-6">Belum ada FAQ khusus.</p>
            : adminData.faq.map(f => (
              <div key={f.id_faq} className="bg-neutral-50 p-4 rounded-xl border border-neutral-100 relative group">
                <p className="font-medium text-neutral-900 text-sm mb-1">Q: {f.pertanyaan || '(Kosong)'}</p>
                <p className="text-sm text-neutral-500">A: {f.jawaban || '(Kosong)'}</p>
                <form method="POST" action="admin.php" onSubmit={(e) => !confirm('Hapus FAQ?') && e.preventDefault()} className="absolute top-3 right-3">
                  <input type="hidden" name="del_faq" value={f.id_faq} />
                  <input type="hidden" name="_csrf_token" value={csrfToken} />
                  <button type="submit" className="text-neutral-300 hover:text-danger-500 transition-colors"><Trash2 size={14} /></button>
                </form>
              </div>
            ))}
        </div>
      </div>

      {/* FAQ Modal */}
      <Modal open={showFaqModal} onClose={() => setShowFaqModal(false)} title="Tambah Tanya Jawab AI">
        <form method="POST" action="admin.php" className="space-y-3.5">
          <input type="hidden" name="add_faq" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          <div>
            <label className="block text-[10px] font-medium uppercase text-neutral-400 mb-1">Pertanyaan (Q)</label>
            <input type="text" name="pertanyaan" required placeholder="Contoh: Buka jam berapa?" className="w-full bg-neutral-50 border border-neutral-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-neutral-400" />
          </div>
          <div>
            <label className="block text-[10px] font-medium uppercase text-neutral-400 mb-1">Jawaban AI (A)</label>
            <textarea name="jawaban" required rows="3" placeholder="Kita buka jam 08:00 - 20:00 kak." className="w-full bg-neutral-50 border border-neutral-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-neutral-400 resize-none" />
          </div>
          <button type="submit" className="w-full py-3 bg-neutral-900 text-white rounded-lg font-semibold hover:bg-neutral-800 transition-all mt-1">Simpan FAQ</button>
        </form>
      </Modal>
    </motion.div>
  );
}
