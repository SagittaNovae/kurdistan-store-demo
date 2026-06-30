import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Check, ShoppingBag } from 'lucide-react';
import { useI18n } from '../context/I18nContext';
import LanguageSwitcher from '../components/LanguageSwitcher';

const OrderSuccessPage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();

  return (
    <div className="min-h-screen bg-black text-white flex flex-col items-center justify-center p-6 text-center relative">
      <LanguageSwitcher className="absolute top-4 end-4" />
      <div className="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mb-6 animate-bounce">
        <Check size={48} className="text-white" />
      </div>

      <h1 className="text-4xl font-bold tracking-tight mb-4">{t('checkout.success.heading')}</h1>
      <p className="text-xl text-neutral-400 mb-2">{t('checkout.success.placed')}</p>
      <p className="text-sm text-neutral-500 mb-8">{t('checkout.success.contact')}</p>

      <button
        onClick={() => navigate('/browse')}
        className="px-8 py-3 bg-white text-black rounded-full font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors flex items-center gap-2"
      >
        <ShoppingBag size={18} />
        {t('checkout.success.continueShopping')}
      </button>
    </div>
  );
};

export default OrderSuccessPage;
