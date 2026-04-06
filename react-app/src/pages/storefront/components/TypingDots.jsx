import React from 'react';
import { Sparkles } from 'lucide-react';

export default function TypingDots() {
  return (
    <div className="flex gap-2 max-w-[85%]">
      <div className="w-6 h-6 rounded-md bg-neutral-900 text-white flex items-center justify-center">
        <Sparkles size={11} />
      </div>
      <div className="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-sm border border-neutral-200 flex items-center gap-1.5">
        {[0, 0.15, 0.3].map((delay, i) => (
          <span key={i} className="w-1.5 h-1.5 rounded-full bg-neutral-400 animate-bounce" style={{ animationDelay: `${delay}s` }} />
        ))}
      </div>
    </div>
  );
}
