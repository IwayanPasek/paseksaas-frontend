import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { CheckCircle2, AlertTriangle, X } from 'lucide-react';

export default function Toast({ toast, onClose }) {
  return (
    <AnimatePresence>
      {toast && (
        <motion.div
          initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }}
          className="fixed top-5 right-5 z-[100] flex items-start gap-3 bg-white border border-neutral-200 p-4 rounded-xl shadow-lg min-w-[260px]"
        >
          {toast.type === 'error'
            ? <AlertTriangle className="text-danger-500 shrink-0 mt-0.5" size={18} />
            : <CheckCircle2 className="text-success-500 shrink-0 mt-0.5" size={18} />}
          <div className="flex-1">
            <h4 className="font-semibold text-neutral-900 text-sm">{toast.title || (toast.type === 'error' ? 'Error' : 'Berhasil!')}</h4>
            <p className="text-xs text-neutral-500">{toast.message}</p>
          </div>
          {onClose && (
            <button onClick={onClose} className="text-neutral-300 hover:text-neutral-900 transition-colors">
              <X size={14} />
            </button>
          )}
        </motion.div>
      )}
    </AnimatePresence>
  );
}
