import React from 'react';
import { motion } from 'framer-motion';
import { Sparkles } from 'lucide-react';
import { adminData } from '../adminData';

export default function ChatLogTab() {
  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="space-y-3">
      {(adminData.recentLogs || []).map((log, i) => {
        let reply = '—';
        try {
          const parsed = JSON.parse(log.response || '{}');
          reply = parsed.reply || '—';
        } catch (e) {
          reply = log.response || '—';
        }

        return (
          <div key={i} className="card rounded-xl p-5">
            <div className="flex justify-between items-start mb-3 gap-4">
              <div className="text-sm text-neutral-900 bg-neutral-100 px-3.5 py-2.5 rounded-xl rounded-tr-sm max-w-[85%]">
                "{log.query}"
              </div>
              <span className="text-[10px] font-medium text-neutral-400 border border-neutral-200 px-2 py-0.5 rounded-md shrink-0">
                {new Date(log.date).toLocaleString('en-US', { hour: '2-digit', minute: '2-digit' })}
              </span>
            </div>
            <div className="flex gap-2.5 text-sm text-neutral-600 bg-neutral-50 border border-neutral-100 px-3.5 py-2.5 rounded-xl rounded-tl-sm max-w-[90%] ml-4">
              <Sparkles size={14} className="text-neutral-400 shrink-0 mt-0.5" />
              <div dangerouslySetInnerHTML={{ __html: reply.replace(/\n/g, '<br/>') }} />
            </div>
          </div>
        );
      })}
    </motion.div>
  );
}
