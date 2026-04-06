/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/Navbar.jsx
   Premium minimalist navigation bar
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { S } from '@/lib/store';

export default function Navbar({ scrolled }) {
    return (
        <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${scrolled ? 'glass-nav py-3' : 'bg-transparent py-5'}`}>
            <div className="max-w-6xl mx-auto px-6 flex justify-between items-center">
                {/* Logo + Nama */}
                <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-white text-black flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.1)] overflow-hidden shrink-0 text-xs font-bold">
                        {S.logo
                            ? <img src={`/assets/img/produk/${S.logo}`} alt={S.nama_toko} className="w-full h-full object-cover" />
                            : S.nama_toko.charAt(0).toUpperCase()}
                    </div>
                    <span className="font-semibold text-[15px] text-white tracking-tight truncate max-w-[180px] sm:max-w-none">
                        {S.nama_toko}
                    </span>
                </div>

                {/* Nav Links */}
                <div className="flex items-center gap-2">
                    <a href="#katalog" className="hidden sm:flex px-4 py-2 rounded-lg text-sm font-medium text-neutral-400 hover:text-white transition-colors">
                        Katalog
                    </a>
                    <a href="/login.php" aria-label="Login ke Admin Area"
                        className="hidden md:flex px-5 py-2.5 rounded-full bg-white text-black text-sm font-semibold hover:bg-neutral-200 transition-colors shadow-[0_0_15px_rgba(255,255,255,0.2)]">
                        Admin Area
                    </a>
                </div>
            </div>
        </nav>
    );
}