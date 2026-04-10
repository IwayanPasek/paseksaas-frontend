/* ═══════════════════════════════════════════════════
   HOOK — src/hooks/useCart.js
   Semua logika state keranjang belanja.
   ═══════════════════════════════════════════════════ */

import { useState, useEffect } from 'react';
import { StoreData, formatCurrency } from '../lib/store';

export function useCart() {
    const [cart, setCart] = useState([]);
    const [cartOpen, setCartOpen] = useState(false);

    // Auto-close sheet if cart is empty
    useEffect(() => {
        if (cart.length === 0) setCartOpen(false);
    }, [cart]);

    const addToCart = (product) => {
        setCart(prev => {
            const exists = prev.find(i => i.id === product.id);
            if (exists) {
                return prev.map(i => i.id === product.id ? { ...i, qty: i.qty + 1 } : i);
            }
            return [...prev, { ...product, qty: 1 }];
        });
    };

    const updateQty = (id, delta) =>
        setCart(prev =>
            prev
                .map(i => i.id === id ? { ...i, qty: i.qty + delta } : i)
                .filter(i => i.qty > 0)
        );

    const totalItems = cart.reduce((acc, item) => acc + item.qty, 0);
    const totalPrice = cart.reduce((acc, item) => acc + item.price * item.qty, 0);

    const checkoutWA = () => {
        let message = `Hello ${StoreData.name} Admin! I would like to place an order:\n\n`;
        cart.forEach(item => {
            message += `▪️ ${item.qty}x ${item.name} - IDR ${formatCurrency(item.price * item.qty)}\n`;
        });
        message += `\n*Grand Total: IDR ${formatCurrency(totalPrice)}*\n\nPlease process my order. Thank you!`;
        window.open(`https://wa.me/${StoreData.whatsappNumber}?text=${encodeURIComponent(message)}`, '_blank');
    };

    return { cart, cartOpen, setCartOpen, addToCart, updateQty, totalItems, totalPrice, checkoutWA };
}