import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowRight, Search, ShieldCheck, ShoppingBag, UserRound, X } from 'lucide-react';
import LanguageSwitcher from '../components/LanguageSwitcher';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useProducts } from '../context/ProductContext';
import { useI18n } from '../context/I18nContext';
import api, { normalizeProduct } from '../services/api';

const HomePage = () => {
  const { t } = useI18n();
  const [query, setQuery] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const navigate = useNavigate();
  const searchRef = useRef(null);
  const { setIsCartOpen, cartCount } = useCart();
  const { currentUser } = useAuth();
  useProducts();
  const [searchLoading, setSearchLoading] = useState(false);
  const debounceRef = useRef(null);

  const getStockLabel = (stockValue) => {
    const stock = Number(stockValue) || 0;
    if (stock <= 0) return t('common.ui.outOfStock');
    if (stock <= 5) return t('product.stock.lowStock');
    return t('product.stock.inStock');
  };

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (searchRef.current && !searchRef.current.contains(event.target)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleInputChange = (e) => {
    const value = e.target.value;
    setQuery(value);
    if (debounceRef.current) clearTimeout(debounceRef.current);
    if (value.trim().length > 1) {
      setSearchLoading(true);
      debounceRef.current = setTimeout(async () => {
        try {
          const response = await api.products.list({ search: value.trim() });
          setSuggestions((response.data || []).map(normalizeProduct).slice(0, 5));
          setShowSuggestions(true);
        } catch {
          setSuggestions([]);
        } finally {
          setSearchLoading(false);
        }
      }, 300);
    } else {
      setSuggestions([]);
      setShowSuggestions(false);
    }
  };

  const handleSearch = (e) => {
    e && e.preventDefault();
    if (query.trim()) {
      navigate(`/browse?search=${encodeURIComponent(query)}`);
      setShowSuggestions(false);
    }
  };

  const handleSuggestionClick = (product) => {
    navigate(`/product/${product.id}`);
    setShowSuggestions(false);
  };

  const clearSearch = () => {
    setQuery('');
    setSuggestions([]);
    setShowSuggestions(false);
  };

  return (
    <div className="bg-black text-white min-h-screen flex flex-col items-center justify-center relative overflow-hidden font-sans">
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-neutral-900/50 via-black to-black pointer-events-none" />

      <div className="absolute start-6 top-6 z-50 flex items-center gap-3">
        {currentUser ? (
          <>
            <div className="hidden items-center gap-2 rounded-full border border-neutral-800 bg-neutral-950/90 px-4 py-3 text-sm text-neutral-300 md:flex">
              {currentUser.role === 'admin' ? (
                <ShieldCheck size={16} className="text-emerald-400" />
              ) : (
                <UserRound size={16} className="text-neutral-400" />
              )}
              <span>{currentUser.name}</span>
            </div>
            <button
              onClick={() => navigate('/account')}
              className="rounded-full border border-neutral-800 bg-neutral-950/90 px-4 py-3 text-sm uppercase tracking-[0.2em] text-neutral-300 transition-colors hover:border-neutral-700 hover:text-white"
            >
              {t('common.nav.account')}
            </button>
          </>
        ) : (
          <button
            onClick={() => navigate('/login')}
            className="rounded-full border border-neutral-800 bg-neutral-950/90 px-5 py-3 text-sm uppercase tracking-[0.2em] text-neutral-300 transition-colors hover:border-neutral-700 hover:text-white"
          >
            {t('common.nav.signIn')}
          </button>
        )}
      </div>

      <div className="absolute top-6 end-6 z-50 flex items-center gap-3">
        <LanguageSwitcher />
        <button
          onClick={() => setIsCartOpen(true)}
          aria-label={t('common.aria.openCart')}
          className="w-14 h-14 bg-neutral-900 rounded-full flex items-center justify-center shadow-lg hover:bg-neutral-800 transition-colors relative border border-neutral-800"
        >
          <ShoppingBag size={24} className="text-white" />
          {cartCount > 0 && (
            <span className="absolute -top-1 -end-1 bg-white text-black text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-black">
              {cartCount}
            </span>
          )}
        </button>
      </div>

      <div className="text-center space-y-10 z-10 max-w-3xl px-6 w-full">
        <div className="space-y-4">
          <h1 className="text-6xl md:text-8xl font-bold tracking-tighter text-white">KURDISTAN STORE.</h1>
          <p className="text-neutral-400 text-lg md:text-xl font-light tracking-wide max-w-lg mx-auto">
            {t('home.tagline')}
          </p>
        </div>

        <div ref={searchRef} className="w-full max-w-lg mx-auto relative group">
          <form onSubmit={handleSearch} className="relative z-20">
            <input
              type="text"
              value={query}
              onChange={handleInputChange}
              onFocus={() => query.trim().length > 0 && setShowSuggestions(true)}
              placeholder={t('home.searchPlaceholder')}
              className="w-full px-8 py-5 rounded-full bg-neutral-900/90 border border-neutral-800 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-neutral-700 focus:border-transparent transition-all shadow-2xl backdrop-blur-sm text-lg pe-14"
            />
            {query && (
              <button
                type="button"
                onClick={clearSearch}
                className="absolute end-14 top-1/2 -translate-y-1/2 p-2 text-neutral-500 hover:text-white transition-colors"
              >
                <X size={18} />
              </button>
            )}
            <button
              type="submit"
              className="absolute end-3 top-1/2 -translate-y-1/2 p-3 bg-white text-black rounded-full hover:bg-neutral-200 transition-colors duration-200"
              aria-label={t('common.aria.search')}
            >
              <Search size={20} strokeWidth={2.5} />
            </button>
          </form>

          {searchLoading && (
            <div className="absolute top-full left-0 right-0 mt-2 rounded-2xl border border-neutral-800 bg-neutral-900/95 px-6 py-4 text-sm text-neutral-500">
              {t('home.searching')}
            </div>
          )}
          {showSuggestions && suggestions.length > 0 && (
            <div className="absolute top-full left-0 right-0 mt-2 bg-neutral-900/95 backdrop-blur-md border border-neutral-800 rounded-2xl shadow-2xl overflow-hidden z-10">
              <ul className="py-2">
                {suggestions.map((product) => (
                  <li key={product.id}>
                    <button
                      onClick={() => handleSuggestionClick(product)}
                      className="w-full text-start px-6 py-3 hover:bg-neutral-800 transition-colors flex items-center gap-4 group"
                    >
                      <div className="bg-neutral-800 p-2 rounded-md group-hover:bg-neutral-700 transition-colors">
                        <Search size={16} className="text-neutral-400 group-hover:text-white" />
                      </div>
                      <div className="flex flex-col">
                        <span className="text-white font-medium">{product.name}</span>
                        <span className="text-xs text-neutral-500">
                          {product.category || t('home.product')} · {product.sku}
                        </span>
                      </div>
                      <div className="ms-auto">
                        <div className="flex flex-col items-end">
                          <span className="text-sm text-neutral-400 group-hover:text-white transition-colors">
                            {product.price}
                          </span>
                          <span className="text-xs text-neutral-500">
                            {getStockLabel(product.stock)}
                          </span>
                        </div>
                      </div>
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>

        <div className="pt-8 flex flex-col items-center">
          <button
            onClick={() => navigate('/browse')}
            className="group flex items-center gap-3 rounded-full border border-neutral-700 px-8 py-3 text-sm uppercase tracking-[0.2em] text-neutral-300 transition-all duration-300 hover:border-white hover:text-white"
          >
            <span>{t('home.exploreProducts')}</span>
            <ArrowRight size={14} className="transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" />
          </button>
        </div>
      </div>

    </div>
  );
};

export default HomePage;
