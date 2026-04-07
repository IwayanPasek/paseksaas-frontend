import { LayoutDashboard, Package, MessageSquare, Sparkles, Save, FolderOpen, Settings } from 'lucide-react';

/** Admin sidebar menu configuration */
export const ADMIN_MENU = [
  { id: 'dashboard',     label: 'Overview',       icon: LayoutDashboard },
  { id: 'service_form',  label: 'Add/Edit',       icon: Save },
  { id: 'products',      label: 'Showcase',       icon: Package,    countKey: 'products' },
  { id: 'categories',    label: 'Categories',     icon: FolderOpen, countKey: 'categories' },
  { id: 'recent_logs',   label: 'Chat History',   icon: MessageSquare, countKey: 'recentLogs' },
  { id: 'persona',       label: 'AI Brain & FAQ', icon: Sparkles },
  { id: 'settings',      label: 'Store Profile',  icon: Settings },
];

/** Chat widget quick-reply chips */
export const CHAT_CHIPS = ['Show all services', 'Cheapest service', 'Contact Admin'];

/** Default empty product form */
export const EMPTY_PRODUCT_FORM = {
  id: '', name: '', price: '', description: '', categoryId: '', image: ''
};
