import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';

export default function Modal({ open, onClose, title, children }) {
  if (!open) return null;
  return (
    <AnimatePresence>
      <div className="fixed inset-0 bg-neutral-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <motion.div
          initial={{ scale: 0.95, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} exit={{ scale: 0.95, opacity: 0 }}
          className="bg-white w-full max-w-md rounded-xl p-6 shadow-2xl relative border border-neutral-200"
        >
          <button onClick={onClose} className="absolute top-4 right-4 text-neutral-400 hover:text-neutral-900 transition-colors">
            <X size={18} />
          </button>
          {title && <h3 className="font-semibold text-lg text-neutral-900 mb-5">{title}</h3>}
          {children}
        </motion.div>
      </div>
    </AnimatePresence>
  );
}
