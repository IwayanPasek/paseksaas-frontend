import { LayoutDashboard, Package, MessageSquare, Sparkles, Save, FolderOpen, Settings } from 'lucide-react';

/** Admin sidebar menu configuration */
export const ADMIN_MENU = [
  { id: 'dashboard',     label: 'Overview',      icon: LayoutDashboard },
  { id: 'form_layanan',  label: 'Tambah/Edit',   icon: Save },
  { id: 'produk',        label: 'Etalase',        icon: Package,    countKey: 'produk' },
  { id: 'kategori',      label: 'Kategori',       icon: FolderOpen, countKey: 'kategori' },
  { id: 'log',           label: 'Histori AI',     icon: MessageSquare, countKey: 'log' },
  { id: 'persona',       label: 'Otak AI & FAQ', icon: Sparkles },
  { id: 'pengaturan',    label: 'Profil Toko',    icon: Settings },
];

/** Chat widget quick-reply chips */
export const CHAT_CHIPS = ['Tampilkan semua layanan', 'Layanan termurah', 'Hubungi Admin'];

/** Default empty product form */
export const EMPTY_PRODUCT_FORM = {
  id_produk: '', nama_produk: '', harga: '', deskripsi: '', id_kategori: '', foto_produk: ''
};
