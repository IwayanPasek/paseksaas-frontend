import React from 'react';
import { motion } from 'framer-motion';
import { Server, ExternalLink, MessageCircle } from 'lucide-react';

export default function TenantList({ tenants, totalNodes }) {
  return (
    <div>
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-end mb-6 gap-3">
        <h2 className="text-xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
          Cloud <span className="text-neutral-400 font-normal">Instances</span>
        </h2>
        <div className="bg-success-50 border border-success-100 px-3 py-1.5 rounded-lg text-[11px] font-medium text-success-600 tracking-wider flex items-center gap-1.5 w-fit">
          <span className="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse" /> {totalNodes} ACTIVE NODES
        </div>
      </div>

      <div className="space-y-3">
        {tenants.length === 0 ? (
          <div className="text-center py-16 border-2 border-dashed border-neutral-200 rounded-xl bg-white">
            <Server className="mx-auto text-neutral-300 mb-3" size={40} />
            <p className="text-neutral-400 font-medium text-sm">Belum ada tenant (node) yang dideploy di jaringan.</p>
          </div>
        ) : (
          tenants.map((t, idx) => (
            <motion.div initial={{ opacity: 0, x: 16 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: idx * 0.04 }}
              key={t.id_toko} className="card card-hover rounded-xl p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group transition-all">
              <div className="flex items-center gap-3 md:gap-4">
                <div className="w-10 h-10 md:w-11 md:h-11 bg-neutral-100 rounded-xl flex items-center justify-center font-semibold text-base text-neutral-500 group-hover:bg-neutral-900 group-hover:text-white transition-colors shrink-0">
                  {t.nama_toko.substring(0, 1).toUpperCase()}
                </div>
                <div className="min-w-0">
                  <h3 className="font-semibold text-neutral-900 text-sm md:text-base truncate">{t.nama_toko}</h3>
                  <span className="text-xs text-neutral-400 font-mono truncate">{t.subdomain}.websitewayan.my.id</span>
                </div>
              </div>
              <div className="flex gap-2 ml-14 sm:ml-0 shrink-0">
                <a href={`https://wa.me/${t.kontak_wa}`} target="_blank" rel="noreferrer" className="w-9 h-9 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400 hover:text-success-500 hover:bg-success-50 hover:border-success-100 transition-all" title="WhatsApp">
                  <MessageCircle size={15} />
                </a>
                <a href={`https://${t.subdomain}.websitewayan.my.id`} target="_blank" rel="noreferrer" className="w-9 h-9 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400 hover:text-neutral-900 hover:bg-neutral-200 transition-all" title="Visit">
                  <ExternalLink size={15} />
                </a>
              </div>
            </motion.div>
          ))
        )}
      </div>
    </div>
  );
}
