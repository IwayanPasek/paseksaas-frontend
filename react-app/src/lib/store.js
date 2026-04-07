/* ═══════════════════════════════════════════════════
   DATA LAYER — src/lib/store.js
   Semua data global dari PHP window injection.
   ═══════════════════════════════════════════════════ */

export const StoreData = window.STORE_DATA || {
    storeId: 1,
    storeName: 'Toko AI',
    storeDescription: '',
    whatsappNumber: '6281234567890',
    logo: null,
    products: [],
    categories: [],
    faqItems: [],
};

// Aliases for data layers
export const productCategories = Array.isArray(StoreData.categories) ? StoreData.categories : [];
export const productList = Array.isArray(StoreData.products) ? StoreData.products : [];
export const faqItems = Array.isArray(StoreData.faqItems) ? StoreData.faqItems : [];

/** Format number to Rupiah with thousand separators, e.g. 1.500.000 */
export const formatCurrency = (amount) => Number(amount).toLocaleString('id-ID');