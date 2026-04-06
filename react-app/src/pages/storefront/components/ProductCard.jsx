/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/ProductCard.jsx
   Premium minimalist product card
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { Image as ImageIcon, ShoppingCart, Sparkles, Plus, Minus } from 'lucide-react';
import { cats, fmt } from '../lib/store';

export default function ProductCard({ prod, cartItem, onAsk, onAdd, onQty, imgLoaded, onImgLoad }) {
    const catName = cats.find(c => c.id_kategori == prod.id_kategori)?.nama_kategori;

    return (
        <motion.div layout
            initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.97 }} transition={{ duration: 0.25 }}
            whileHover={{ y: -4 }}
            className="card card-hover rounded-2xl overflow-hidden flex flex-col group">

            {/* ── Product Image ── */}
            <div className="aspect-[4/3] bg-neutral-100 relative overflow-hidden">
                {!imgLoaded && prod.foto_produk && <div className="skeleton absolute inset-0" />}

                {prod.foto_produk ? (
                    <img
                        src={`/assets/img/produk/${prod.foto_produk}`}
                        alt={prod.nama_produk}
                        loading="lazy"
                        onLoad={onImgLoad}
                        onError={(e) => { e.target.style.display = 'none'; }}
                        className={`w-full h-full object-cover transition-all duration-500 ${imgLoaded ? 'opacity-100 group-hover:scale-105' : 'opacity-0'}`}
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-neutral-300">
                        <ImageIcon size={32} />
                    </div>
                )}

                {catName && (
                    <span className="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-white/90 backdrop-blur-sm text-[10px] font-medium text-neutral-600 border border-neutral-100">
                        {catName}
                    </span>
                )}
            </div>

            {/* ── Info & Actions ── */}
            <div className="p-4 flex flex-col flex-1">
                <h3 className="font-semibold text-sm text-neutral-900 mb-1 leading-snug line-clamp-1">
                    {prod.nama_produk}
                </h3>
                <p className="text-xs text-neutral-400 mb-3 line-clamp-2 flex-1 leading-relaxed">
                    {prod.deskripsi}
                </p>

                {/* Price */}
                <div className="border-t border-neutral-100 pt-3 mb-3 flex items-baseline gap-1">
                    <span className="text-[10px] text-neutral-400 font-medium">Rp</span>
                    <span className="font-bold text-lg text-neutral-900">{fmt(prod.harga)}</span>
                </div>

                {/* Action Buttons */}
                <div className="flex flex-col gap-2">
                    <button
                        onClick={() => onAsk(prod.nama_produk)}
                        aria-label={`Tanya AI tentang ${prod.nama_produk}`}
                        className="w-full py-2 rounded-lg border border-neutral-200 text-neutral-600 font-medium text-xs flex justify-center items-center gap-2 hover:bg-neutral-50 hover:border-neutral-300 transition-all active:scale-[0.98]">
                        <Sparkles size={12} /> Tanya AI
                    </button>

                    {cartItem ? (
                        /* Qty stepper */
                        <div className="flex items-center justify-between bg-neutral-50 border border-neutral-200 rounded-lg p-1 h-[38px]">
                            <button onClick={() => onQty(prod.id_produk, -1)} aria-label="Kurangi jumlah"
                                className="w-8 h-full flex items-center justify-center text-neutral-600 hover:bg-white rounded-md transition-all">
                                <Minus size={14} strokeWidth={2.5} />
                            </button>
                            <span className="font-bold text-neutral-900 text-sm">{cartItem.qty}</span>
                            <button onClick={() => onQty(prod.id_produk, 1)} aria-label="Tambah jumlah"
                                className="w-8 h-full flex items-center justify-center text-neutral-600 hover:bg-white rounded-md transition-all">
                                <Plus size={14} strokeWidth={2.5} />
                            </button>
                        </div>
                    ) : (
                        /* Add to cart */
                        <button
                            onClick={() => onAdd(prod)}
                            aria-label={`Tambah ${prod.nama_produk} ke keranjang`}
                            className="w-full h-[38px] rounded-lg bg-neutral-900 text-white font-medium text-xs flex justify-center items-center gap-2 hover:bg-neutral-800 active:scale-[0.98] transition-all">
                            <ShoppingCart size={12} /> Tambah
                        </button>
                    )}
                </div>
            </div>
        </motion.div>
    );
}