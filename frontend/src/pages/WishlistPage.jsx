import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft, Heart, ShoppingBag } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import { useI18n } from '../context/I18nContext';
import LanguageSwitcher from '../components/LanguageSwitcher';
import { normalizeProduct } from '../services/api';
import api from '../services/api';

const WishlistPage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const { addToCart } = useCart();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = () => {
    if (!isAuthenticated) return;
    api.wishlist
      .list()
      .then((res) => setItems(res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login', { replace: true });
      return;
    }
    load();
  }, [isAuthenticated]); // eslint-disable-line

  const handleRemove = async (productId) => {
    await api.wishlist.remove(productId);
    setItems((prev) => prev.filter((i) => i.product_id !== productId));
  };

  const handleAddToCart = async (product) => {
    await addToCart(product);
  };

  return (
    <div className="min-h-screen bg-black text-white font-sans">
      <header className="fixed top-0 left-0 w-full z-40 bg-black/80 backdrop-blur-md border-b border-neutral-900">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="text-neutral-400 hover:text-white">
            <ArrowLeft size={20} className="rtl:rotate-180" />
          </button>
          <h1 className="flex-1 text-xl font-bold tracking-widest">{t('wishlist.title')}</h1>
          <LanguageSwitcher />
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-3 sm:px-6 pt-32 pb-20">
        {loading ? (
          <p className="text-neutral-500 text-center py-20">{t('common.loading')}</p>
        ) : items.length === 0 ? (
          <div className="text-center py-20">
            <Heart size={48} className="mx-auto text-neutral-700 mb-4" />
            <p className="text-neutral-500 mb-6">{t('wishlist.empty')}</p>
            <button
              onClick={() => navigate('/browse')}
              className="px-8 py-3 bg-white text-black rounded-full font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors"
            >
              {t('wishlist.browse')}
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-10 lg:gap-x-8 lg:gap-y-12">
            {items.map((item) => {
              const p = item.product ? normalizeProduct(item.product) : null;
              if (!p) return null;
              return (
                <div key={item.id} className="group flex flex-col transition-transform duration-300 hover:-translate-y-0.5">
                  <div
                    className="relative aspect-square overflow-hidden rounded-xl border border-neutral-800/40 mb-2 sm:mb-3 cursor-pointer transition-shadow duration-300 group-hover:shadow-xl group-hover:shadow-black/50 bg-[radial-gradient(ellipse_at_center,_#262626_0%,_#0a0a0a_100%)]"
                    onClick={() => navigate(`/product/${p.id}`)}
                  >
                    {p.image ? (
                      <img
                        src={p.image}
                        alt={p.name}
                        className="w-full h-full object-contain p-1 scale-[1.02] group-hover:scale-[1.05] transition-transform duration-500"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-neutral-700 text-sm">
                        {t('wishlist.noImage')}
                      </div>
                    )}
                    <button
                      onClick={(e) => { e.stopPropagation(); handleRemove(p.id); }}
                      className="absolute top-2 end-2 p-1.5 bg-black/60 hover:bg-black rounded-full text-red-400 hover:text-red-300 transition-colors"
                      aria-label={t('wishlist.removeAriaLabel')}
                    >
                      <Heart size={14} fill="currentColor" />
                    </button>
                  </div>

                  <div className="flex flex-col gap-1 px-0.5">
                    <div className="flex justify-between items-baseline gap-1">
                      <h3
                        className="text-xs sm:text-sm font-medium text-white tracking-tight cursor-pointer hover:text-neutral-300 transition-colors line-clamp-2 leading-snug"
                        onClick={() => navigate(`/product/${p.id}`)}
                      >
                        {p.name}
                      </h3>
                      <span className="text-xs sm:text-sm text-neutral-400 font-light shrink-0">{p.price}</span>
                    </div>

                    <button
                      onClick={() => handleAddToCart(p)}
                      disabled={Number(p.stock) <= 0}
                      className="w-full py-1.5 sm:py-2 border border-neutral-800 hover:border-white hover:bg-white hover:text-black text-[10px] sm:text-xs font-medium uppercase tracking-wide transition-colors rounded-xl flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                      <ShoppingBag size={12} />
                      {Number(p.stock) <= 0 ? t('common.ui.outOfStock') : t('common.ui.addToCart')}
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </main>
    </div>
  );
};

export default WishlistPage;
