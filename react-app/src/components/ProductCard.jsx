/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/ProductCard.jsx
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { Image as ImageIcon, ShoppingCart, Sparkles, Plus, Minus } from 'lucide-react';
import { cats, fmt } from '../lib/store';

export default function ProductCard({ prod, cartItem, onAsk, onAdd, onQty, imgLoaded, onImgLoad }) {
    const catName = cats.find(c => c.id_kategori == prod.id_kategori)?.nama_kategori;

    return (
        <motion.div layout
            initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.95 }} transition={{ duration: 0.3 }}
            whileHover={{ y: -6 }}
            className="glass-card rounded-[1.8rem] overflow-hidden flex flex-col group">

            {/* ── Gambar Produk ── */}
            <div className="aspect-[4/3] bg-surface-100 relative overflow-hidden">
                {!imgLoaded && prod.foto_produk && <div className="skeleton absolute inset-0" />}

                {prod.foto_produk ? (
                    <img
                        src={`/assets/img/produk/${prod.foto_produk}`}
                        alt={prod.nama_produk}
                        loading="lazy"
                        onLoad={onImgLoad}
                        onError={(e) => { e.target.style.display = 'none'; }}
                        className={`w-full h-full object-cover transition-all duration-700 ${imgLoaded ? 'opacity-100 group-hover:scale-110' : 'opacity-0'}`}
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-surface-300">
                        <ImageIcon size={36} />
                    </div>
                )}

                {catName && (
                    <span className="absolute top-4 left-4 px-3 py-1.5 rounded-full glass text-[10px] font-black text-primary-600 uppercase tracking-wider">
                        {catName}
                    </span>
                )}
            </div>

            {/* ── Info & Aksi ── */}
            <div className="p-5 flex flex-col flex-1">
                <h3 className="font-extrabold text-base text-dark-900 mb-1.5 leading-snug line-clamp-1">
                    {prod.nama_produk}
                </h3>
                <p className="text-xs text-dark-900/50 mb-4 line-clamp-2 flex-1 font-medium leading-relaxed">
                    {prod.deskripsi}
                </p>

                {/* Harga */}
                <div className="border-t border-surface-200/60 pt-4 mb-4 flex items-baseline gap-1">
                    <span className="text-[10px] text-dark-900/40 font-bold">Rp</span>
                    <span className="font-black text-xl text-dark-900">{fmt(prod.harga)}</span>
                </div>

                {/* Tombol aksi */}
                <div className="flex flex-col gap-2">
                    <button
                        onClick={() => onAsk(prod.nama_produk)}
                        aria-label={`Tanya AI tentang ${prod.nama_produk}`}
                        className="w-full py-2.5 rounded-xl bg-primary-50 text-primary-600 font-bold text-xs flex justify-center items-center gap-2 hover:bg-primary-500 hover:text-white transition-all active:scale-95">
                        <Sparkles size={13} /> Tanya AI
                    </button>

                    {cartItem ? (
                        /* Qty stepper */
                        <div className="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl p-1.5 h-[42px]">
                            <button onClick={() => onQty(prod.id_produk, -1)} aria-label="Kurangi jumlah"
                                className="w-8 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all">
                                <Minus size={15} strokeWidth={3} />
                            </button>
                            <span className="font-black text-emerald-600 text-sm">{cartItem.qty}</span>
                            <button onClick={() => onQty(prod.id_produk, 1)} aria-label="Tambah jumlah"
                                className="w-8 h-full flex items-center justify-center text-emerald-600 hover:bg-white rounded-lg transition-all">
                                <Plus size={15} strokeWidth={3} />
                            </button>
                        </div>
                    ) : (
                        /* Tambah ke keranjang */
                        <button
                            onClick={() => onAdd(prod)}
                            aria-label={`Tambah ${prod.nama_produk} ke keranjang`}
                            className="w-full h-[42px] rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 font-bold text-xs flex justify-center items-center gap-2 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 active:scale-95 transition-all">
                            <ShoppingCart size={13} /> Tambah
                        </button>
                    )}
                </div>
            </div>
        </motion.div>
    );
}