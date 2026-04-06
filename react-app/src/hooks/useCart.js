/* ═══════════════════════════════════════════════════
   HOOK — src/hooks/useCart.js
   Semua logika state keranjang belanja.
   ═══════════════════════════════════════════════════ */

import { useState, useEffect } from 'react';
import { S, fmt } from '../lib/store';

export function useCart() {
    const [cart, setCart] = useState([]);
    const [cartOpen, setCartOpen] = useState(false);

    // Auto-tutup sheet jika keranjang kosong
    useEffect(() => {
        // eslint-disable-next-line react-hooks/set-state-in-effect
        if (cart.length === 0) setCartOpen(false);
    }, [cart]);

    const addToCart = (prod) =>
        setCart(prev => [...prev, { ...prod, qty: 1 }]);

    const updateQty = (id, delta) =>
        setCart(prev =>
            prev
                .map(i => i.id_produk === id ? { ...i, qty: i.qty + delta } : i)
                .filter(i => i.qty > 0)
        );

    const totalItems = cart.reduce((a, i) => a + i.qty, 0);
    const totalPrice = cart.reduce((a, i) => a + i.harga * i.qty, 0);

    const checkoutWA = () => {
        let t = `Halo Admin *${S.nama_toko}*! Saya ingin memesan:\n\n`;
        cart.forEach(i => {
            t += `▪️ ${i.qty}x ${i.nama_produk} - Rp ${fmt(i.harga * i.qty)}\n`;
        });
        t += `\n*Total: Rp ${fmt(totalPrice)}*\n\nMohon diproses ya kak!`;
        window.open(`https://wa.me/${S.wa_num}?text=${encodeURIComponent(t)}`, '_blank');
    };

    return { cart, cartOpen, setCartOpen, addToCart, updateQty, totalItems, totalPrice, checkoutWA };
}