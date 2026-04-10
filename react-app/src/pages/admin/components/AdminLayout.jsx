import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Menu, X, LogOut, ExternalLink } from 'lucide-react';
import { ADMIN_MENU } from '@/lib/constants';
import LogoAvatar from '@/components/ui/LogoAvatar';
import { adminData } from '../adminData';

export function AdminSidebar({ activeTab, setActiveTab, sidebarOpen, setSidebarOpen, onCancelEdit }) {
  const counts = { 
    products: adminData.products.length, 
    categories: adminData.categories.length, 
    recentLogs: adminData.recentLogs.length 
  };

  return (
    <AnimatePresence>
      {(sidebarOpen || window.innerWidth >= 768) && (
        <>
          {sidebarOpen && <motion.div initial={{opacity:0}} animate={{opacity:1}} exit={{opacity:0}} onClick={()=>setSidebarOpen(false)} className="md:hidden fixed inset-0 bg-neutral-900/30 z-40" />}
          <motion.aside initial={{x:-280}} animate={{x:0}} exit={{x:-280}} transition={{type:'spring', damping:25}}
            className="fixed md:static inset-y-0 left-0 w-[240px] bg-white border-r border-neutral-200 z-50 flex flex-col">

            <div className="p-5 flex items-center justify-between border-b border-neutral-100">
              <div className="flex items-center gap-2.5 overflow-hidden">
                <LogoAvatar logo={adminData.store?.logo} name={adminData.store?.name} size="sm" />
                <span className="font-semibold text-sm truncate text-neutral-900">{adminData.store?.name}</span>
              </div>
              <button className="md:hidden text-neutral-400" onClick={()=>setSidebarOpen(false)}><X size={20}/></button>
            </div>

            <div className="flex-1 overflow-y-auto p-3 flex flex-col gap-0.5">
              <div className="text-[10px] font-medium text-neutral-400 uppercase tracking-widest mb-2 px-2.5 mt-2">Main Menu</div>
              {ADMIN_MENU.map(item => (
                <button key={item.id}
                  onClick={() => { setActiveTab(item.id); setSidebarOpen(false); if(item.id !== 'service_form') onCancelEdit(); }}
                  className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-medium text-sm transition-all ${activeTab === item.id ? 'bg-neutral-900 text-white' : 'text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900'}`}>
                  <item.icon size={16} />
                  {item.label}
                  {item.countKey && (
                    <span className={`ml-auto px-1.5 py-0.5 rounded text-[10px] font-medium ${activeTab === item.id ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-500'}`}>
                      {counts[item.countKey] || 0}
                    </span>
                  )}
                </button>
              ))}
            </div>

            <div className="p-3 border-t border-neutral-100">
              <a href="logout.php" className="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg font-medium text-sm text-red-500 hover:bg-red-50 transition-colors">
                <LogOut size={16} /> Logout
              </a>
            </div>
          </motion.aside>
        </>
      )}
    </AnimatePresence>
  );
}

export function AdminHeader({ activeTab, setSidebarOpen }) {
  const current = ADMIN_MENU.find(m => m.id === activeTab);
  return (
    <header className="bg-white/90 backdrop-blur-md border-b border-neutral-200 px-5 py-3.5 flex items-center justify-between sticky top-0 z-30">
      <div className="flex items-center gap-3">
        <button className="md:hidden text-neutral-500" onClick={()=>setSidebarOpen(true)}><Menu size={22}/></button>
        <h2 className="font-semibold text-lg text-neutral-900">{current?.label}</h2>
      </div>
      {adminData.store?.subdomain && (
        <a href={`https://${adminData.store.subdomain}.${adminData.store.siteDomain}`} target="_blank" rel="noreferrer"
          className="flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-600 rounded-lg text-xs font-medium transition-colors">
          <ExternalLink size={13} /> <span className="hidden sm:inline">View Storefront</span>
        </a>
      )}
    </header>
  );
}
