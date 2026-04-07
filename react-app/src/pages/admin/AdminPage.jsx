import React, { useState, useEffect } from 'react';
import Toast from '@/components/ui/Toast';
import { AdminSidebar, AdminHeader } from './components/AdminLayout';
import DashboardTab from './components/DashboardTab';
import ServiceFormTab from './components/ServiceFormTab';
import ProductsTab from './components/ProductsTab';
import CategoriesTab from './components/CategoriesTab';
import AIPersonaTab from './components/AIPersonaTab';
import ChatLogTab from './components/ChatLogTab';
import SettingsTab from './components/SettingsTab';
import { adminData } from './adminData';
import { EMPTY_PRODUCT_FORM } from '@/lib/constants';

export default function AdminPage() {
  const [activeTab, setActiveTab] = useState(adminData.currentTab || 'dashboard');
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [editForm, setEditForm] = useState(EMPTY_PRODUCT_FORM);
  const [toast, setToast] = useState(null);

  const csrfToken = adminData.csrfToken || '';

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
      const msg = params.get('msg') || 'Operation Successful';
      const type = params.get('status') === 'error' ? 'error' : 'success';
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setToast({ message: msg, type });
      setTimeout(() => setToast(null), 4000);
      window.history.replaceState({}, document.title, `admin.php?tab=${activeTab}`);
    }
  }, [activeTab]);

  const startEdit = (p) => {
    setEditForm(p);
    setIsEditing(true);
    setActiveTab('service_form');
    setSidebarOpen(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const cancelEdit = () => {
    setIsEditing(false);
    setEditForm(EMPTY_PRODUCT_FORM);
  };

  const TAB_MAP = {
    dashboard:    <DashboardTab />,
    service_form: <ServiceFormTab isEditing={isEditing} editForm={editForm} onCancelEdit={cancelEdit} csrfToken={csrfToken} />,
    products:     <ProductsTab onEdit={startEdit} csrfToken={csrfToken} />,
    categories:   <CategoriesTab csrfToken={csrfToken} />,
    persona:      <AIPersonaTab csrfToken={csrfToken} />,
    recent_logs:  <ChatLogTab />,
    settings:     <SettingsTab csrfToken={csrfToken} />,
  };

  return (
    <div className="flex h-screen bg-neutral-50 font-sans text-neutral-700 overflow-hidden">
      <Toast toast={toast} onClose={() => setToast(null)} />
      <AdminSidebar activeTab={activeTab} setActiveTab={setActiveTab} sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} onCancelEdit={cancelEdit} />
      <main className="flex-1 flex flex-col overflow-hidden h-screen relative">
        <AdminHeader activeTab={activeTab} setSidebarOpen={setSidebarOpen} />
        <div className="flex-1 overflow-y-auto p-5 md:p-7">
          <div className="max-w-5xl mx-auto">
            {TAB_MAP[activeTab] || <DashboardTab />}
          </div>
        </div>
      </main>
    </div>
  );
}
