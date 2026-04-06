/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/CartSheet.jsx
   Bottom sheet keranjang belanja ala GoFood.
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { ShoppingCart, X, Minus, Plus, MessageSquare } from 'lucide-react';
import { fmt } from '../lib/store';

export default function CartSheet({ cart, open, onClose, onQty, total, onCheckout }) {
    if (!open || !cart.length) return null;

    return (
        <>
            {/* Backdrop */}
            <motion.div
                initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                onClick={onClose}
                className="fixed inset-0 bg-dark-900/50 backdrop-blur-sm z-[70]" />

            {/* Sheet */}
            <motion.div
                initial={{ y: '100%' }} animate={{ y: 0 }} exit={{ y: '100%' }}
                transition={{ type: 'spring', damping: 25, stiffness: 200 }}
                className="fixed bottom-0 left-0 right-0 md:left-1/2 md:-translate-x-1/2 md:w-[480px] md:bottom-8 bg-white rounded-t-[2rem] md:rounded-[2rem] shadow-2xl z-[80] overflow-hidden flex flex-col max-h-[85vh]">

                {/* Header */}
                <div className="p-5 border-b border-surface-200/50 flex justify-between items-center shrink-0">
                    <h3 className="font-black text-lg text-dark-900 flex items-center gap-2">
                        <ShoppingCart className="text-emerald-500" size={20} /> Keranjang
                    </h3>
                    <button onClick={onClose} aria-label="Tutup keranjang"
                        className="w-8 h-8 flex items-center justify-center bg-surface-100 text-dark-900/50 rounded-full hover:bg-surface-200 transition-colors">
                        <X size={16} />
                    </button>
                </div>

                {/* Item List */}
                <div className="flex-1 overflow-y-auto p-5 space-y-3 bg-surface-50">
                    {cart.map(item => (
                        <div key={item.id_produk}
                            className="bg-white p-4 rounded-2xl border border-surface-200/50 shadow-sm flex justify-between items-center gap-4">
                            <div className="flex items-center gap-3 flex-1 min-w-0">
                                {item.foto_produk && (
                                    <img src={`/assets/img/produk/${item.foto_produk}`}
                                        className="w-11 h-11 rounded-xl object-cover bg-surface-100 shrink-0" alt="" />
                                )}
                                <div className="min-w-0">
                                    <h4 className="font-bold text-dark-900 text-sm line-clamp-1">{item.nama_produk}</h4>
                                    <p className="text-xs font-black text-primary-500 mt-0.5">Rp {fmt(item.harga)}</p>
                                </div>
                            </div>

                            {/* Qty Stepper */}
                            <div className="flex items-center bg-emerald-50 border border-emerald-200 rounded-xl p-1 h-9 shrink-0">
                                <button onClick={() => onQty(item.id_produk, -1)}
                                    className="w-7 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all">
                                    <Minus size={13} strokeWidth={3} />
                                </button>
                                <span className="font-black text-emerald-600 text-xs w-6 text-center">{item.qty}</span>
                                <button onClick={() => onQty(item.id_produk, 1)}
                                    className="w-7 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all">
                                    <Plus size={13} strokeWidth={3} />
                                </button>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Footer Checkout */}
                <div className="p-5 bg-white border-t border-surface-200/50 shrink-0">
                    <div className="flex justify-between items-center mb-4">
                        <span className="text-sm font-bold text-dark-900/50">Total</span>
                        <span className="text-2xl font-black text-dark-900">Rp {fmt(total)}</span>
                    </div>
                    <button onClick={onCheckout}
                        className="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all active:scale-95">
                        <MessageSquare size={16} /> Checkout via WhatsApp
                    </button>
                </div>
            </motion.div>
        </>
    );
}