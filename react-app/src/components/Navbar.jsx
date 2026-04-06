/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/Navbar.jsx
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { S } from '../lib/store';

export default function Navbar({ scrolled }) {
    return (
        <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${scrolled ? 'glass shadow-lg py-3' : 'bg-transparent py-5'}`}>
            <div className="max-w-6xl mx-auto px-6 flex justify-between items-center">
                {/* Logo + Nama */}
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl gradient-primary text-white flex items-center justify-center shadow-lg overflow-hidden shrink-0 text-sm font-black">
                        {S.logo
                            ? <img src={`/assets/img/produk/${S.logo}`} alt={S.nama_toko} className="w-full h-full object-cover" />
                            : S.nama_toko.charAt(0).toUpperCase()}
                    </div>
                    <span className="font-extrabold text-lg text-dark-900 tracking-tight truncate max-w-[180px] sm:max-w-none">
                        {S.nama_toko}
                    </span>
                </div>

                {/* Nav Links */}
                <div className="flex items-center gap-3">
                    <a href="#katalog" className="hidden sm:flex px-5 py-2 rounded-full text-sm font-bold text-dark-900/70 hover:text-dark-900 transition-colors">
                        Katalog
                    </a>
                    <a href="/login.php" aria-label="Login ke Admin Area"
                        className="hidden md:flex px-5 py-2.5 rounded-full gradient-primary text-white text-sm font-bold shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        Admin Area
                    </a>
                </div>
            </div>
        </nav>
    );
}