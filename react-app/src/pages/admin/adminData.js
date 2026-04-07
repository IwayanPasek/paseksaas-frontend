/** Admin page data layer and configuration */

export const adminData = window.ADMIN_DATA || {
  store: { name: 'Admin', subdomain: '', description: '', whatsapp: '', logo: '' },
  total_inventory_value: 0, 
  products: [], 
  categories: [], 
  recent_logs: [], 
  faqs: [], 
  analytics: [], 
  current_tab: 'dashboard', 
  csrf_token: '',
  is_impersonating: false
};
