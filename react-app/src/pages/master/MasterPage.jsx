import React, { useState, useEffect } from 'react';
import { Server } from 'lucide-react';
import Toast from '@/components/ui/Toast';
import MasterNav from './components/MasterNav';
import DeployForm from './components/DeployForm';
import TenantList from './components/TenantList';

const masterData = window.MASTER_DATA || { admin_session: 'demo@node', total_nodes: 0, tenants: [], csrf_token: '' };

export default function MasterPage() {
  const [toast, setToast] = useState(null);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
      const status = params.get('status');
      const msg = params.get('msg') || '';
      // eslint-disable-next-line react-hooks/set-state-in-effect
      if (status === 'success') setToast({ title: 'Deploy Berhasil!', message: `Node ${msg} telah aktif.`, type: 'success' });
      else setToast({ title: 'Deploy Gagal', message: msg, type: 'error' });
      setTimeout(() => setToast(null), 5000);
      window.history.replaceState({}, document.title, 'master.php');
    }
  }, []);

  return (
    <div className="min-h-screen bg-neutral-50 font-sans text-neutral-700">
      <Toast toast={toast} onClose={() => setToast(null)} />
      <MasterNav session={masterData.admin_session} />

      <main className="max-w-[1200px] mx-auto px-6 py-8 md:py-10">
        <div className="grid lg:grid-cols-12 gap-8 lg:gap-10">
          <div className="lg:col-span-5">
            <DeployForm csrfToken={masterData.csrf_token} />
          </div>
          <div className="lg:col-span-7 mt-8 lg:mt-0">
            <TenantList tenants={masterData.tenants} totalNodes={masterData.total_nodes} />
          </div>
        </div>
      </main>

      <footer className="mt-8 pb-6 text-center">
        <p className="text-[10px] text-neutral-300 uppercase tracking-[0.3em] font-medium flex items-center justify-center gap-1.5">
          <Server size={10} /> Pasek Cloud Security &copy; {new Date().getFullYear()}
        </p>
      </footer>
    </div>
  );
}
