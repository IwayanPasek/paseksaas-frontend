/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/CartSheet.jsx
   Bottom sheet keranjang belanja — premium minimalist
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { ShoppingCart, X, Minus, Plus, MessageSquare } from 'lucide-react';
import { fmt } from '@/lib/store';

export default function CartSheet({ cart, open, onClose, onQty, total, onCheckout }) {
    if (!open || !cart.length) return null;

    return (
        <>
            {/* Backdrop */}
            <motion.div
                initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                onClick={onClose}
                className="fixed inset-0 bg-neutral-900/40 backdrop-blur-sm z-[70]" />

            {/* Sheet */}
            <motion.div
                initial={{ y: '100%' }} animate={{ y: 0 }} exit={{ y: '100%' }}
                transition={{ type: 'spring', damping: 25, stiffness: 200 }}
                className="fixed bottom-0 left-0 right-0 md:left-1/2 md:-translate-x-1/2 md:w-[440px] md:bottom-8 bg-white rounded-t-2xl md:rounded-2xl shadow-2xl z-[80] overflow-hidden flex flex-col max-h-[85vh] border border-neutral-200">

                {/* Header */}
                <div className="p-4 border-b border-neutral-100 flex justify-between items-center shrink-0">
                    <h3 className="font-semibold text-base text-neutral-900 flex items-center gap-2">
                        <ShoppingCart className="text-neutral-400" size={18} /> Keranjang
                    </h3>
                    <button onClick={onClose} aria-label="Tutup keranjang"
                        className="w-7 h-7 flex items-center justify-center bg-neutral-100 text-neutral-400 rounded-lg hover:bg-neutral-200 transition-colors">
                        <X size={14} />
                    </button>
                </div>

                {/* Item List */}
                <div className="flex-1 overflow-y-auto p-4 space-y-2 bg-neutral-50">
                    {cart.map(item => (
                        <div key={item.id_produk}
                            className="bg-white p-3.5 rounded-xl border border-neutral-100 flex justify-between items-center gap-3">
                            <div className="flex items-center gap-3 flex-1 min-w-0">
                                {item.foto_produk && (
                                    <img src={`/assets/img/produk/${item.foto_produk}`}
                                        className="w-10 h-10 rounded-lg object-cover bg-neutral-100 shrink-0" alt="" />
                                )}
                                <div className="min-w-0">
                                    <h4 className="font-medium text-neutral-900 text-sm line-clamp-1">{item.nama_produk}</h4>
                                    <p className="text-xs font-semibold text-neutral-500 mt-0.5">Rp {fmt(item.harga)}</p>
                                </div>
                            </div>

                            {/* Qty Stepper */}
                            <div className="flex items-center bg-neutral-50 border border-neutral-200 rounded-lg p-0.5 h-8 shrink-0">
                                <button onClick={() => onQty(item.id_produk, -1)}
                                    className="w-6 h-full flex items-center justify-center text-neutral-500 hover:bg-white rounded-md transition-all">
                                    <Minus size={12} strokeWidth={2.5} />
                                </button>
                                <span className="font-bold text-neutral-900 text-xs w-5 text-center">{item.qty}</span>
                                <button onClick={() => onQty(item.id_produk, 1)}
                                    className="w-6 h-full flex items-center justify-center text-neutral-500 hover:bg-white rounded-md transition-all">
                                    <Plus size={12} strokeWidth={2.5} />
                                </button>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Footer Checkout */}
                <div className="p-4 bg-white border-t border-neutral-100 shrink-0">
                    <div className="flex justify-between items-center mb-3">
                        <span className="text-sm font-medium text-neutral-400">Total</span>
                        <span className="text-xl font-bold text-neutral-900">Rp {fmt(total)}</span>
                    </div>
                    <button onClick={onCheckout}
                        className="w-full py-3.5 bg-neutral-900 hover:bg-neutral-800 text-white rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                        <MessageSquare size={15} /> Checkout via WhatsApp
                    </button>
                </div>
            </motion.div>
        </>
    );
}