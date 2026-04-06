import React from 'react';
import { motion } from 'framer-motion';
import { Package, MessageSquare, FolderOpen, Activity } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { adminData } from '../adminData';
import { formatRp } from '@/lib/utils';

export default function DashboardTab() {
  const stats = [
    { label: 'Total Layanan', val: adminData.produk.length, icon: Package },
    { label: 'Nilai Etalase', val: `Rp ${formatRp(adminData.total_nilai)}`, icon: Activity },
    { label: 'Chat Masuk', val: adminData.log.length, icon: MessageSquare },
    { label: 'Kategori', val: adminData.kategori.length, icon: FolderOpen },
  ];

  return (
    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} className="space-y-5">
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {stats.map((s, i) => (
          <div key={i} className="card rounded-xl p-4 flex flex-col gap-3">
            <div className="w-9 h-9 rounded-lg bg-neutral-100 flex items-center justify-center text-neutral-500"><s.icon size={18} /></div>
            <div>
              <div className="text-[10px] font-medium text-neutral-400 uppercase tracking-wider mb-0.5">{s.label}</div>
              <div className={`font-bold text-neutral-900 ${s.label === 'Nilai Etalase' ? 'text-base' : 'text-xl'}`}>{s.val}</div>
            </div>
          </div>
        ))}
      </div>

      <div className="card rounded-xl p-5 md:p-6">
        <h3 className="font-semibold text-base text-neutral-900 mb-5 flex items-center gap-2"><Activity size={16} className="text-neutral-400"/> Tren Obrolan AI (7 Hari)</h3>
        <div className="h-[280px] w-full">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={adminData.grafik} margin={{ top: 5, right: 20, bottom: 5, left: -20 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e5e5e5" />
              <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fontSize: 11, fill: '#a3a3a3', fontWeight: 500}} dy={10} />
              <YAxis axisLine={false} tickLine={false} tick={{fontSize: 11, fill: '#a3a3a3', fontWeight: 500}} />
              <Tooltip contentStyle={{ borderRadius: '0.75rem', border: '1px solid #e5e5e5', boxShadow: '0 4px 12px rgba(0,0,0,0.06)', fontWeight: 500, fontSize: '13px' }} />
              <Line type="monotone" dataKey="interaksi" stroke="#171717" strokeWidth={2.5} dot={{ r: 4, strokeWidth: 2, fill: '#fff', stroke: '#171717' }} activeDot={{ r: 6, strokeWidth: 0, fill: '#171717' }} />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>
    </motion.div>
  );
}
