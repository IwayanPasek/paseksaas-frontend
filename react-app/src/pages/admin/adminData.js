/** Admin page data layer and configuration */

export const adminData = window.ADMIN_DATA || {
  store: { name: 'Admin', subdomain: '', description: '', whatsapp: '', logo: '', aiPersona: '', aiTone: 'formal' },
  totalInventoryValue: 0, 
  products: [], 
  categories: [], 
  recentLogs: [], 
  faqs: [], 
  analytics: [], 
  currentTab: 'dashboard', 
  csrfToken: '',
  isImpersonating: false
};
