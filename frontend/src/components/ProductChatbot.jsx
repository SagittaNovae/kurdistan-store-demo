import React, { useState, useRef, useEffect } from 'react';
import { MessageCircle, Send, X } from 'lucide-react';
import api, { normalizeProduct } from '../services/api';
import { useI18n } from '../context/I18nContext';

// Set to true when AI backend is activated
const AI_ACTIVE = false;

const ProductChatbot = () => {
  const { t } = useI18n();
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState(() => [
    { role: 'assistant', content: t('chatbot.greeting') },
  ]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [sessionId, setSessionId] = useState(null);
  const bottomRef = useRef(null);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const sendMessage = async (e) => {
    e?.preventDefault();
    const text = input.trim();
    if (!text || loading) return;

    setInput('');
    setMessages((prev) => [...prev, { role: 'user', content: text }]);
    setLoading(true);

    try {
      const response = await api.ai.chat(text, sessionId);
      const data = response.data;

      if (data.session_id) setSessionId(data.session_id);

      setMessages((prev) => [
        ...prev,
        {
          role: 'assistant',
          content: data.message,
          products: (data.products || []).map(normalizeProduct),
        },
      ]);
    } catch {
      setMessages((prev) => [
        ...prev,
        { role: 'assistant', content: t('chatbot.error') },
      ]);
    } finally {
      setLoading(false);
    }
  };

  if (!AI_ACTIVE) return null;

  return (
    <>
      <button
        type="button"
        onClick={() => setIsOpen(true)}
        className="fixed bottom-24 end-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-white text-black shadow-lg transition-transform hover:scale-105"
        aria-label={t('chatbot.openAriaLabel')}
      >
        <MessageCircle size={24} />
      </button>

      {isOpen && (
        <div className="fixed bottom-6 end-6 z-50 flex h-[min(32rem,80vh)] w-[min(24rem,92vw)] flex-col overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950 shadow-2xl">
          <div className="flex items-center justify-between border-b border-neutral-800 px-4 py-3">
            <div>
              <p className="text-sm font-semibold text-white">{t('chatbot.title')}</p>
              <p className="text-xs text-neutral-500">{t('chatbot.langs')}</p>
            </div>
            <button type="button" onClick={() => setIsOpen(false)} className="text-neutral-400 hover:text-white">
              <X size={20} />
            </button>
          </div>

          <div className="flex-1 space-y-3 overflow-y-auto p-4">
            {messages.map((msg, index) => (
              <div
                key={index}
                className={`max-w-[90%] rounded-2xl px-4 py-2 text-sm whitespace-pre-wrap ${
                  msg.role === 'user'
                    ? 'ms-auto bg-white text-black'
                    : 'bg-neutral-900 text-neutral-200'
                }`}
              >
                {msg.content}
                {msg.products?.length > 0 && (
                  <ul className="mt-2 space-y-1 border-t border-neutral-800 pt-2 text-xs text-neutral-400">
                    {msg.products.map((p) => (
                      <li key={p.id}>
                        {p.name} — {p.price}
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            ))}
            {loading && (
              <div className="text-xs text-neutral-500">{t('chatbot.thinking')}</div>
            )}
            <div ref={bottomRef} />
          </div>

          <form onSubmit={sendMessage} className="border-t border-neutral-800 p-3">
            <div className="flex gap-2">
              <input
                type="text"
                value={input}
                onChange={(e) => setInput(e.target.value)}
                placeholder={t('chatbot.placeholder')}
                className="flex-1 rounded-full border border-neutral-800 bg-black px-4 py-2 text-sm text-white outline-none focus:border-neutral-600"
              />
              <button
                type="submit"
                disabled={loading}
                className="rounded-full bg-white p-2 text-black disabled:opacity-50"
              >
                <Send size={18} />
              </button>
            </div>
          </form>
        </div>
      )}
    </>
  );
};

export default ProductChatbot;
