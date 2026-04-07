/* ═══════════════════════════════════════════════════
   HOOK — src/hooks/useChat.js
   Semua logika state & request AI Chat.
   ═══════════════════════════════════════════════════ */

import { useState, useCallback } from 'react';
import { StoreData } from '../lib/store';

const WHATSAPP_TRIGGER = /whatsapp|message|contact/i;

export function useChat() {
    const [chatOpen, setChatOpen] = useState(false);
    const [messages, setMessages] = useState([]);
    const [inputText, setInputText] = useState('');
    const [isTyping, setIsTyping] = useState(false);

    const pushMessage = (message) => setMessages(prev => [...prev, message]);

    /**
     * Send message to the AI API and handle response.
     * @param {string}  text        - The message text to send.
     * @param {boolean} skipBubble  - If true, the user's message is not displayed in the chat.
     */
    const handleSend = useCallback(async (text = inputText, skipBubble = false) => {
        const query = text.trim();
        if (!query || isTyping) return;

        if (!skipBubble) pushMessage({ role: 'user', text: query });
        setInputText('');
        setIsTyping(true);
        if (!chatOpen) setChatOpen(true);

        try {
            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: StoreData.storeId,
                    session_id: localStorage.getItem('ai_session'),
                    user_message: query,
                }),
            });
            const chatData = await response.json();
            const aiReply = {
                role: 'ai',
                text: chatData.reply || 'Sorry, I couldn\'t process that request.',
                products: chatData.products || [],
                showWhatsAppCard: false,
            };
            if (chatData.reply && WHATSAPP_TRIGGER.test(chatData.reply)) aiReply.showWhatsAppCard = true;
            pushMessage(aiReply);
        } catch (error) {
            pushMessage({ role: 'ai', text: '⚠️ Failed to connect to the AI server.' });
        } finally {
            setIsTyping(false);
        }
    }, [inputText, isTyping, chatOpen]);

    /** Handle quick-reply chip clicks */
    const handleChip = (text) => {
        if (text.includes('Contact Admin')) {
            setChatOpen(true);
            pushMessage({ role: 'user', text: 'How can I contact the administrator?' });
            setIsTyping(true);
            setTimeout(() => {
                setIsTyping(false);
                pushMessage({ 
                    role: 'ai', 
                    text: 'Certainly! Please click the button below to chat directly with our administrator 👇', 
                    showWhatsAppCard: true 
                });
            }, 800);
        } else {
            handleSend(text);
        }
    };

    /** Ask the AI about a specific product from a ProductCard button */
    const askAboutProduct = (productName) => {
        setChatOpen(true);
        setTimeout(() => handleSend(`Can you tell me more about "${productName}"?`, false), 300);
    };

    return {
        chatOpen, setChatOpen,
        messages,
        inputText, setInputText,
        isTyping,
        handleSend,
        handleChip,
        askAboutProduct,
    };
}