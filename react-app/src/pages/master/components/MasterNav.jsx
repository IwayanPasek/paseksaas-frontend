import React from 'react';
import { Cpu, LogOut } from 'lucide-react';

export default function MasterNav({ session }) {
  return (
    <nav className="border-b border-neutral-200 px-6 md:px-8 py-3.5 flex justify-between items-center bg-white/90 backdrop-blur-xl sticky top-0 z-50">
      <div className="flex items-center gap-2.5">
        <div className="bg-neutral-900 text-white w-9 h-9 rounded-lg flex items-center justify-center"><Cpu size={18} strokeWidth={2} /></div>
        <span className="font-semibold text-base tracking-tight text-neutral-900 hidden sm:block">MASTER<span className="text-neutral-400">CENTER</span></span>
      </div>
      <div className="flex items-center gap-4 md:gap-6">
        <div className="hidden md:flex flex-col text-right">
          <span className="text-[10px] text-neutral-400 font-medium uppercase tracking-widest mb-0.5">Admin Session</span>
          <span className="text-xs text-neutral-600 font-mono bg-neutral-100 px-2 py-0.5 rounded-md border border-neutral-200">{session}</span>
        </div>
        <a href="logout.php" className="flex items-center gap-1.5 bg-danger-50 text-danger-500 hover:bg-danger-500 hover:text-white px-4 py-2 rounded-lg text-xs font-medium transition-all border border-danger-100">
          <LogOut size={14} /> <span className="hidden sm:inline">LOGOUT</span>
        </a>
      </div>
    </nav>
  );
}
