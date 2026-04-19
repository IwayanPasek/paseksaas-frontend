import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, HelpCircle, Trash2, Plus } from 'lucide-react';
import Modal from '@/components/ui/Modal';
import { adminData } from '../adminData';

export default function AIPersonaTab({ csrfToken }) {
  const [showFaqModal, setShowFaqModal] = useState(false);

  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-6">
      {/* AI Character / Brain */}
      <div className="rounded-2xl p-6 md:p-8 bg-neutral-900 text-white border border-neutral-800 shadow-xl overflow-hidden relative group">
        {/* Decorative Glow */}
        <div className="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-[80px] group-hover:bg-indigo-500/20 transition-all duration-700 pointer-events-none" />
        
        <h3 className="font-semibold text-xl flex items-center gap-2.5 mb-2 transition-transform group-hover:translate-x-1 duration-300">
          <Sparkles size={20} className="text-indigo-400" /> AI Knowledge & Persona
        </h3>
        <p className="text-neutral-400 text-sm mb-8 font-light max-w-xl">
          Define how your AI assistant interacts with customers. Provide specific instructions and custom FAQs to improve response accuracy.
        </p>

        <form method="POST" action="admin.php" className="space-y-6 relative z-10">
          <input type="hidden" name="update_persona" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="block text-[10px] font-bold uppercase tracking-widest text-neutral-500">Conversation Tone</label>
              <select name="aiTone" defaultValue={adminData.store?.aiTone || 'formal'} 
                className="w-full bg-neutral-800 border border-neutral-700 rounded-xl px-4 py-3.5 text-sm text-white outline-none cursor-pointer focus:border-indigo-500/50 transition-all appearance-none shadow-sm">
                <option value="formal" className="bg-neutral-800 text-white">👔 Formal & Polished</option>
                <option value="santai" className="bg-neutral-800 text-white">🤙 Casual & Friendly</option>
                <option value="profesional" className="bg-neutral-800 text-white">💼 Business Professional</option>
                <option value="ramah" className="bg-neutral-800 text-white">😊 Warm & Welcoming</option>
                <option value="singkat" className="bg-neutral-800 text-white">⚡ Brief & Concise</option>
              </select>
            </div>
            <div className="space-y-2">
              <label className="block text-[10px] font-bold uppercase tracking-widest text-neutral-500">Character Instructions</label>
              <textarea name="aiPersonaPrompt" defaultValue={adminData.store?.aiPersona || ''} rows="2" 
                placeholder="e.g., Use an upbeat tone and always refer to customers as 'Guest'..." 
                className="w-full bg-neutral-800 border border-neutral-700 rounded-xl px-4 py-3.5 text-sm text-white placeholder-neutral-600 outline-none resize-none focus:border-indigo-500/50 transition-all shadow-sm" />
            </div>
          </div>
          
          <button type="submit" className="px-8 py-3 bg-white text-neutral-900 rounded-xl font-bold text-sm hover:shadow-lg hover:shadow-white/10 transition-all active:scale-[0.98]">
            Update AI Brain
          </button>
        </form>
      </div>

      {/* FAQ Knowledge Base */}
      <div className="card rounded-2xl p-6 md:p-8 shadow-sm border border-neutral-100">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h3 className="font-semibold text-lg text-neutral-900 flex items-center gap-2.5">
              <HelpCircle size={18} className="text-neutral-400" /> Knowledge Base (FAQ)
            </h3>
            <p className="text-[11px] text-neutral-400 mt-1 font-light">Custom Q&A pairs used by the AI to answer visitors.</p>
          </div>
          <button onClick={() => setShowFaqModal(true)} 
            className="flex items-center gap-2 px-4 py-2 bg-neutral-900 text-white rounded-xl text-xs font-bold hover:bg-black transition-all shadow-md active:scale-95">
            <Plus size={14} /> Add Entry
          </button>
        </div>

        <div className="grid gap-4">
          {adminData.faqs.length === 0
            ? <div className="text-center py-12 border-2 border-dashed border-neutral-50 rounded-2xl">
                <p className="text-neutral-300 text-sm italic font-light">No custom FAQ entries found.</p>
              </div>
            : adminData.faqs.map(faq => (
              <div key={faq.id} className="bg-neutral-50/50 p-5 rounded-2xl border border-neutral-100 relative group transition-all hover:bg-neutral-50">
                <div className="pr-10 space-y-2">
                  <p className="font-bold text-neutral-900 text-sm flex items-start gap-2">
                    <span className="text-indigo-500 shrink-0 mt-0.5 font-display text-[10px] uppercase tracking-tighter">Q:</span> 
                    {faq.question || '(Empty Question)'}
                  </p>
                  <p className="text-sm text-neutral-500 flex items-start gap-2 font-light leading-relaxed">
                    <span className="text-neutral-300 shrink-0 mt-0.5 font-display text-[10px] uppercase tracking-tighter">A:</span> 
                    {faq.answer || '(Empty Answer)'}
                  </p>
                </div>
                <form method="POST" action="admin.php" onSubmit={(e) => !confirm('Delete this entry from AI knowledge?') && e.preventDefault()} 
                  className="absolute top-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                  <input type="hidden" name="deleteFaqId" value={faq.id} />
                  <input type="hidden" name="_csrf_token" value={csrfToken} />
                  <button type="submit" className="text-neutral-300 hover:text-red-500 transition-colors p-1">
                    <Trash2 size={16} />
                  </button>
                </form>
              </div>
            ))}
        </div>
      </div>

      {/* Add FAQ Modal */}
      <Modal open={showFaqModal} onClose={() => setShowFaqModal(false)} title="Define AI Knowledge Entry">
        <form method="POST" action="admin.php" className="space-y-5 py-2">
          <input type="hidden" name="add_faq" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          <div className="space-y-2">
            <label className="block text-[10px] font-bold uppercase text-neutral-500 tracking-widest">Visitor Question (Trigger)</label>
            <input type="text" name="question" required placeholder="e.g., What are your opening hours?" 
              className="w-full bg-neutral-50 border border-neutral-200 rounded-xl px-4 py-3.5 text-sm outline-none focus:border-indigo-500/50 transition-all" />
          </div>
          <div className="space-y-2">
            <label className="block text-[10px] font-bold uppercase text-neutral-500 tracking-widest">AI Response (Answer)</label>
            <textarea name="answer" required rows="3" placeholder="e.g., We are open from 9 AM to 9 PM daily!" 
              className="w-full bg-neutral-50 border border-neutral-200 rounded-xl px-4 py-3.5 text-sm outline-none focus:border-indigo-500/50 transition-all resize-none font-light" />
          </div>
          <div className="pt-2">
            <button type="submit" className="w-full py-4 bg-neutral-900 text-white rounded-xl font-bold text-sm hover:bg-black transition-all shadow-lg active:scale-[0.98]">
              Save to Knowledge Base
            </button>
          </div>
        </form>
      </Modal>
    </motion.div>
  );
}
