/* ═══════════════════════════════════════════════════
   DATA LAYER — src/lib/store.js
   Semua data global dari PHP window injection.
   ═══════════════════════════════════════════════════ */

export const S = window.STORE_DATA || {
    id_toko: 1,
    nama_toko: 'Toko AI',
    desc_toko: '',
    wa_num: '6281234567890',
    logo: null,
    products: [],
    categories: [],
    faq: [],
};

export const cats = Array.isArray(S.categories) ? S.categories : [];
export const prods = Array.isArray(S.products) ? S.products : [];
export const faqs = Array.isArray(S.faq) ? S.faq : [];

/** Format angka ke Rupiah tanpa simbol, e.g. 1.500.000 */
export const fmt = (n) => Number(n).toLocaleString('id-ID');