import React from 'react';
import { Sparkles } from 'lucide-react';

export default function TypingDots() {
  return (
    <div className="flex gap-2 max-w-[85%]">
      <div className="w-6 h-6 rounded-md bg-white text-black flex items-center justify-center">
        <Sparkles size={11} />
      </div>
      <div className="bg-neutral-900 px-3.5 py-2.5 rounded-2xl rounded-tl-sm border border-neutral-800 flex items-center gap-1.5">
        {[0, 0.15, 0.3].map((delay, i) => (
          <span key={i} className="w-1.5 h-1.5 rounded-full bg-neutral-600 animate-bounce" style={{ animationDelay: `${delay}s` }} />
        ))}
      </div>
    </div>
  );
}
