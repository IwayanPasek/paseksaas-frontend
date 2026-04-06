/* ═══════════════════════════════════════════════════
   HOOK — src/hooks/useChat.js
   Semua logika state & request AI Chat.
   ═══════════════════════════════════════════════════ */

import { useState, useCallback } from 'react';
import { S } from '../lib/store';

const WA_TRIGGER = /whatsapp|pesan|hubungi/i;

export function useChat() {
    const [chatOpen, setChatOpen] = useState(false);
    const [messages, setMessages] = useState([]);
    const [inputText, setInputText] = useState('');
    const [isTyping, setIsTyping] = useState(false);

    const pushMsg = (msg) => setMessages(prev => [...prev, msg]);

    /**
     * Kirim pesan ke API dan tampung respons AI.
     * @param {string}  text        - Teks yang dikirim (default: inputText)
     * @param {boolean} skipBubble  - Jika true, bubble user tidak ditampilkan
     */
    const handleSend = useCallback(async (text = inputText, skipBubble = false) => {
        const msg = text.trim();
        if (!msg || isTyping) return;

        if (!skipBubble) pushMsg({ role: 'user', text: msg });
        setInputText('');
        setIsTyping(true);
        if (!chatOpen) setChatOpen(true);

        try {
            const res = await fetch('/api/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_toko: S.id_toko,
                    session_id: localStorage.getItem('ai_session'),
                    user_message: msg,
                }),
            });
            const data = await res.json();
            const reply = {
                role: 'ai',
                text: data.reply || 'Maaf, saya tidak mengerti.',
                products: data.db_result || [],
                showWaCard: false,
            };
            if (data.reply && WA_TRIGGER.test(data.reply)) reply.showWaCard = true;
            pushMsg(reply);
        } catch {
            pushMsg({ role: 'ai', text: '⚠️ Gagal terhubung ke server AI.' });
        } finally {
            setIsTyping(false);
        }
    }, [inputText, isTyping, chatOpen]);

    /** Klik chip quick-reply */
    const handleChip = (text) => {
        if (text.includes('Hubungi Admin')) {
            setChatOpen(true);
            pushMsg({ role: 'user', text: 'Cara hubungi admin gimana?' });
            setIsTyping(true);
            setTimeout(() => {
                setIsTyping(false);
                pushMsg({ role: 'ai', text: 'Tentu! Silakan klik tombol di bawah ini untuk ngobrol langsung dengan admin ya 👇', showWaCard: true });
            }, 800);
        } else {
            handleSend(text);
        }
    };

    /** Tanya AI dari tombol pada ProductCard */
    const tanyaProduk = (nama) => {
        setChatOpen(true);
        setTimeout(() => handleSend(`Jelaskan detail layanan "${nama}" dong!`, false), 300);
    };

    return {
        chatOpen, setChatOpen,
        messages,
        inputText, setInputText,
        isTyping,
        handleSend,
        handleChip,
        tanyaProduk,
    };
}