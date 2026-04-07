/* ═══════════════════════════════════════════════════
   COMPONENT — src/components/Navbar.jsx
   Premium minimalist navigation bar
   ═══════════════════════════════════════════════════ */

import React from 'react';
import { StoreData } from '@/lib/store';

export default function Navbar({ isScrolled }) {
    return (
        <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${isScrolled ? 'glass-nav py-3' : 'bg-transparent py-5'}`}>
            <div className="max-w-6xl mx-auto px-4 sm:px-6 flex justify-between items-center">
                {/* Logo + Name */}
                <div className="flex items-center gap-2 sm:gap-3">
                    <div className="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white text-black flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.1)] overflow-hidden shrink-0 text-xs font-bold">
                        {StoreData.logo
                            ? <img src={`/assets/img/produk/${StoreData.logo}`} alt={StoreData.storeName} className="w-full h-full object-cover" />
                            : StoreData.storeName.charAt(0).toUpperCase()}
                    </div>
                    <span className="font-semibold text-sm sm:text-[15px] text-white tracking-tight truncate max-w-[120px] xs:max-w-[180px] sm:max-w-none">
                        {StoreData.storeName}
                    </span>
                </div>

                {/* Nav Links */}
                <div className="flex items-center gap-2">
                    <a href="#catalog" className="hidden sm:flex px-4 py-2 rounded-lg text-sm font-medium text-neutral-400 hover:text-white transition-colors">
                        Catalog
                    </a>
                    <a href="/login.php" aria-label="Login to Admin Area"
                        className="hidden md:flex px-5 py-2.5 rounded-full bg-white text-black text-sm font-semibold hover:bg-neutral-200 transition-colors shadow-[0_0_15px_rgba(255,255,255,0.2)]">
                        Admin Area
                    </a>
                </div>
            </div>
        </nav>
    );
}