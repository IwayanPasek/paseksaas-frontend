import React from 'react';
import { motion } from 'framer-motion';
import { Save } from 'lucide-react';
import { adminData } from '../adminData';

export default function ServiceFormTab({ isEditing, editForm, onCancelEdit, csrfToken }) {
  return (
    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}>
      <div className="card rounded-xl p-6 md:p-8 relative overflow-hidden">
        {isEditing && <div className="absolute top-0 left-0 right-0 h-1 bg-yellow-500" />}
        <div className="flex justify-between items-center mb-6">
          <div>
            <h2 className="text-xl font-bold text-neutral-900">{isEditing ? 'Edit Service' : 'New Service'}</h2>
            <p className="text-neutral-400 text-xs mt-0.5">{isEditing ? 'Update service information.' : 'Add a new service to your showcase.'}</p>
          </div>
          {isEditing && <button onClick={onCancelEdit} className="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-medium hover:bg-red-500 hover:text-white transition-colors">Cancel</button>}
        </div>

        <form method="POST" action="admin.php" encType="multipart/form-data" className="space-y-5">
          <input type="hidden" name="save_product" value="1" />
          <input type="hidden" name="_csrf_token" value={csrfToken} />
          {isEditing && <input type="hidden" name="productId" value={editForm.id} />}
          {isEditing && <input type="hidden" name="oldImage" value={editForm.image} />}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Service Name</label>
              <input type="text" name="productName" defaultValue={editForm.name} required placeholder="e.g., Premium Wash" className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all text-sm" />
            </div>
            <div className="space-y-1.5">
              <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Price (IDR)</label>
              <input type="number" name="price" defaultValue={editForm.price} required placeholder="50000" className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all text-sm" />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Category</label>
            <select name="categoryId" required defaultValue={editForm.categoryId || ''} className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all cursor-pointer text-sm appearance-none">
              <option value="">-- Select Category --</option>
              {adminData.categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
            </select>
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Description (AI Knowledge)</label>
            <textarea name="description" defaultValue={editForm.description} required rows="3" placeholder="Explain details for the AI to learn..." className="w-full bg-neutral-50 p-3.5 rounded-xl outline-none border border-neutral-200 focus:border-neutral-400 transition-all resize-none text-sm" />
          </div>

          <div className="space-y-1.5">
            <label className="text-[10px] font-medium uppercase tracking-widest text-neutral-400">Image {isEditing && '(Leave empty to keep current)'}</label>
            <input type="file" name="image" accept="image/*" className="w-full text-sm text-neutral-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-neutral-100 file:text-neutral-600 bg-neutral-50 rounded-xl p-1.5 border border-neutral-200" />
          </div>

          <button type="submit" className={`w-full py-3.5 rounded-xl font-semibold text-white flex justify-center gap-2 transition-all active:scale-[0.98] ${isEditing ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-neutral-900 hover:bg-neutral-800'}`}>
            <Save size={16} /> {isEditing ? 'Keep Changes' : 'Publish Service'}
          </button>
        </form>
      </div>
    </motion.div>
  );
}
