import React from 'react';
import { Sparkles, MessageSquare, ExternalLink } from 'lucide-react';
import { StoreData, formatCurrency } from '@/lib/store';

export default function ChatBubble({ message }) {
  const isUser = message.role === 'user';
  return (
    <div className={`flex flex-col gap-2 w-full animate-slide-up ${isUser ? 'items-end' : 'items-start'}`}>
      <div className={`flex gap-2 max-w-[85%] ${isUser ? 'flex-row-reverse' : ''}`}>
        <div className={`w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0 text-[10px] font-medium ${isUser ? 'bg-neutral-800 text-neutral-400' : 'bg-white text-black'}`}>
          {isUser ? 'U' : <Sparkles size={11} />}
        </div>
        <div className={`p-3 text-[13px] leading-relaxed ${isUser
          ? 'bg-neutral-800 text-white rounded-2xl rounded-tr-sm border border-neutral-700'
          : 'bg-neutral-900 text-neutral-200 rounded-2xl rounded-tl-sm border border-neutral-800'}`}>
          {message.text.split('\n').map((line, i, arr) => (
            <span key={i}>{line}{i < arr.length - 1 && <br />}</span>
          ))}
        </div>
      </div>

      {message.products?.length > 0 && (
        <div className="flex gap-2 max-w-[90%] pl-8 overflow-x-auto no-scrollbar pb-1">
          {message.products.map(product => (
            <div key={product.id_produk} className="glass-card p-2.5 rounded-lg w-[170px] shrink-0 border-neutral-800 bg-[#0A0A0A]">
              {product.foto_produk && (
                <img src={`/assets/img/produk/${product.foto_produk}`}
                  className="w-full h-[72px] object-cover rounded-md mb-2 bg-neutral-900" alt={product.nama_produk} />
              )}
              <div className="font-medium text-xs text-white line-clamp-1 mb-0.5">{product.nama_produk}</div>
              <div className="font-semibold text-neutral-500 text-xs">IDR {formatCurrency(product.harga)}</div>
            </div>
          ))}
        </div>
      )}

      {message.showWhatsAppCard && (
        <div className="pl-8 max-w-[85%]">
          <a href={`https://wa.me/${StoreData.whatsappNumber}`} target="_blank" rel="noopener noreferrer"
            className="flex items-center gap-3 p-3 rounded-lg bg-success-50 border border-success-100 hover:bg-success-100 transition-colors group">
            <div className="w-8 h-8 rounded-lg bg-success-500 text-white flex items-center justify-center shrink-0">
              <MessageSquare size={14} />
            </div>
            <div className="flex-1 min-w-0">
              <p className="font-medium text-success-600 text-xs">Chat via WhatsApp</p>
              <p className="text-[10px] text-success-500/70">Connect with an administrator</p>
            </div>
            <ExternalLink size={13} className="text-success-500/50 group-hover:text-success-600 transition-colors shrink-0" />
          </a>
        </div>
      )}
    </div>
  );
}
