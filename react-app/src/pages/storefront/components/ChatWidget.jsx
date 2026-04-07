import React, { useRef, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Sparkles, X, Send } from 'lucide-react';
import { StoreData } from '@/lib/store';
import { CHAT_CHIPS } from '@/lib/constants';
import ChatBubble from './ChatBubble';
import TypingDots from './TypingDots';

export default function ChatWidget({ onClose, messages, input, setInput, onSend, typing, onChip, totalItems }) {
  const endRef = useRef(null);
  useEffect(() => { endRef.current?.scrollIntoView({ behavior: 'smooth' }); }, [messages, typing]);

  return (
    <motion.div
      initial={{ opacity: 0, y: 40, scale: 0.97 }}
      animate={{ opacity: 1, y: 0, scale: 1 }}
      exit={{ opacity: 0, y: 20, scale: 0.97 }}
      transition={{ type: 'spring', damping: 28 }}
      className={`fixed bottom-0 right-0 w-[100vw] h-[100dvh] md:w-[380px] ${totalItems > 0 ? 'md:bottom-28' : 'md:bottom-24'} md:right-8 md:h-[560px] bg-[#0A0A0A] md:rounded-2xl shadow-[0_0_40px_rgba(0,0,0,0.5)] flex flex-col z-[100] border border-neutral-800 overflow-hidden`}>

      <div className="px-4 py-3.5 flex items-center justify-between border-b border-neutral-800 shrink-0 bg-neutral-950">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-lg bg-white text-black flex items-center justify-center">
            <Sparkles size={15} />
          </div>
          <div>
            <h3 className="font-semibold text-white text-sm">{StoreData.name} Assistant</h3>
            <div className="flex items-center gap-1.5 text-[10px] text-neutral-500 font-medium mt-0.5">
              <span className="relative flex h-1.5 w-1.5">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75" />
                <span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-500" />
              </span>
              Online
            </div>
          </div>
        </div>
        <button onClick={onClose} aria-label="Close chat"
          className="w-7 h-7 rounded-lg bg-transparent border border-neutral-800 text-neutral-500 flex items-center justify-center hover:bg-neutral-800 hover:text-white transition-colors">
          <X size={14} />
        </button>
      </div>

      <div className="flex gap-1.5 px-3 pt-2.5 pb-2 overflow-x-auto no-scrollbar shrink-0 border-b border-neutral-900 bg-neutral-900/50">
        {CHAT_CHIPS.map((c, i) => (
          <button key={i} onClick={() => onChip(c)}
            className="px-3 py-1.5 glass-card border border-neutral-800 rounded-lg text-[11px] font-medium text-neutral-400 hover:text-white hover:border-neutral-700 whitespace-nowrap transition-all active:scale-[0.97]">
            {c}
          </button>
        ))}
      </div>

      <div className="flex-1 bg-[#0A0A0A] p-4 overflow-y-auto flex flex-col gap-3 scrollbar-thin">
        {messages.length === 0 && !typing && (
          <div className="flex-1 flex flex-col items-center justify-center text-center py-8">
            <div className="w-12 h-12 rounded-xl bg-white text-black flex items-center justify-center mb-3">
              <Sparkles size={22} />
            </div>
            <h4 className="font-semibold text-white text-sm mb-1">Welcome! 👋</h4>
            <p className="text-xs text-neutral-500 max-w-[200px]">
              I am {StoreData.name}'s AI assistant. How can I help you today?
            </p>
          </div>
        )}
        {messages.map((msg, idx) => <ChatBubble key={idx} msg={msg} />)}
        {typing && <TypingDots />}
        <div ref={endRef} />
      </div>

      <div className="p-3 bg-neutral-950 border-t border-neutral-800 shrink-0">
        <div className="flex items-end gap-2 bg-[#0A0A0A] border border-neutral-800 rounded-xl p-1 focus-within:border-neutral-600 transition-all">
          <textarea
            value={input}
            onChange={(e) => setInput(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); onSend(); } }}
            placeholder="Type a message..."
            disabled={typing}
            className="flex-1 bg-transparent border-none outline-none px-2.5 py-2 text-[13px] text-white resize-none max-h-[80px] min-h-[36px] placeholder:text-neutral-600"
          />
          <button onClick={onSend} disabled={typing || !input.trim()} aria-label="Send message"
            className="w-8 h-8 shrink-0 rounded-lg bg-white text-black flex items-center justify-center active:scale-95 transition-transform disabled:opacity-30">
            <Send size={13} />
          </button>
        </div>
      </div>
    </motion.div>
  );
}
