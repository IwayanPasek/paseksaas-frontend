/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/ProductCard.jsx
   Premium minimalist product card
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { motion } from 'framer-motion';
import { Image as ImageIcon, ShoppingBag, Sparkles, Plus, Minus } from 'lucide-react';
import { productCategories, formatCurrency } from '@/lib/store';

export default function ProductCard({ prod: product, cartItem, onAsk, onAdd, onQty: onUpdateQuantity, imgLoaded: isImageLoaded, onImgLoad: onImageLoad }) {
    const categoryName = productCategories.find(cat => cat.id == product.categoryId)?.name;

    return (
        <motion.div layout
            initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.97 }} transition={{ duration: 0.25 }}
            whileHover={{ y: -6 }}
            className="rounded-2xl flex flex-col group relative overflow-hidden bg-transparent border border-white/5 hover:border-white/10 transition-colors shadow-lg">

            {/* ── Product Image ── */}
            <div className="aspect-[4/5] bg-[#0A0A0A] relative overflow-hidden rounded-t-2xl">
                {!isImageLoaded && product.image && <div className="skeleton absolute inset-0 opacity-20" />}

                {product.image ? (
                    <img
                        src={`/assets/img/produk/${product.image}`}
                        alt={product.name}
                        loading="lazy"
                        onLoad={onImageLoad}
                        onError={(e) => { e.target.style.display = 'none'; }}
                        className={`w-full h-full object-cover transition-transform duration-700 ease-out origin-center ${isImageLoaded ? 'opacity-100 group-hover:scale-110' : 'opacity-0'}`}
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-neutral-800">
                        <ImageIcon size={40} strokeWidth={1} />
                    </div>
                )}

                {/* Categories Badge */}
                {categoryName && (
                    <span className="absolute top-3 left-3 px-3 py-1 rounded bg-black/60 backdrop-blur-md text-[9px] font-bold tracking-wider uppercase text-white border border-white/10 z-10">
                        {categoryName}
                    </span>
                )}
                
                {/* Floating "Quick Actions" Overlay on Hover */}
                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10 flex flex-col justify-end p-4 gap-2">
                     <button
                        onClick={() => onAsk(product.name)}
                        aria-label={`Ask AI about ${product.name}`}
                        className="w-full py-2.5 rounded border border-white/20 bg-black/50 backdrop-blur-md text-white font-medium text-xs flex justify-center items-center gap-2 hover:bg-white hover:text-black transition-all transform translate-y-4 group-hover:translate-y-0 duration-300">
                        <Sparkles size={14} className="text-indigo-400 group-hover:text-indigo-600" /> Ask AI
                    </button>
                </div>
            </div>

            {/* ── Info & Actions ── */}
            <div className="p-5 flex flex-col flex-1 bg-[#050505]">
                <h3 className="font-medium text-sm text-white mb-1.5 leading-snug line-clamp-1 group-hover:text-indigo-300 transition-colors">
                    {product.name}
                </h3>
                
                {/* Price */}
                <div className="mb-4 flex items-baseline gap-1">
                    <span className="text-xs text-neutral-500 font-medium tracking-wide">IDR</span>
                    <span className="font-display font-semibold text-base text-white">{formatCurrency(product.price)}</span>
                </div>

                {/* Action Buttons */}
                <div className="mt-auto">
                    {cartItem ? (
                        /* Qty stepper */
                        <div className="flex items-center justify-between border border-white/10 rounded-lg p-1 h-10 bg-[#0A0A0A]">
                            <button onClick={() => onUpdateQuantity(product.id, -1)} aria-label="Decrease"
                                className="w-10 h-full flex items-center justify-center text-neutral-400 hover:text-white transition-colors">
                                <Minus size={14} />
                            </button>
                            <span className="font-bold text-white text-sm w-8 text-center">{cartItem.qty}</span>
                            <button onClick={() => onUpdateQuantity(product.id, 1)} aria-label="Increase"
                                className="w-10 h-full flex items-center justify-center text-neutral-400 hover:text-white transition-colors">
                                <Plus size={14} />
                            </button>
                        </div>
                    ) : (
                        /* Add to cart */
                        <button
                            onClick={() => onAdd(product)}
                            aria-label={`Add ${product.name} to cart`}
                            className="w-full h-10 rounded-lg bg-neutral-900 border border-white/5 text-white font-medium text-xs flex justify-center items-center gap-2 hover:bg-white hover:text-black hover:border-white active:scale-[0.98] transition-all">
                            <ShoppingBag size={14} /> Add to Cart
                        </button>
                    )}
                </div>
            </div>
        </motion.div>
    );
}