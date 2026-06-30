import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useProducts } from '../context/ProductContext';
import { useI18n } from '../context/I18nContext';
import api from '../services/api';
import LanguageSwitcher from '../components/LanguageSwitcher';
import { ArrowLeft, ShieldCheck, ShoppingBag, UserRound, X } from 'lucide-react';

const SELECT_CLASS =
  'bg-neutral-900 border border-neutral-800 rounded-md px-3 py-2 text-sm text-neutral-300 focus:outline-none focus:border-neutral-600 appearance-none cursor-pointer';

const INPUT_CLASS =
  'bg-neutral-900 border border-neutral-800 rounded-md px-3 py-2 text-sm text-neutral-300 focus:outline-none focus:border-neutral-600 w-28 placeholder-neutral-600';

const BrowsePage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const searchQuery = searchParams.get('search') || '';
  const { addToCart, setIsCartOpen, cartCount } = useCart();
  const { currentUser } = useAuth();
  const { loadProducts, loading } = useProducts();

  const [filteredProducts, setFilteredProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [sort, setSort] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [minPrice, setMinPrice] = useState('');
  const [maxPrice, setMaxPrice] = useState('');
  const [inStock, setInStock] = useState(false);

  const getStockBadgeConfig = (stockValue) => {
    const stock = Number(stockValue) || 0;
    if (stock <= 0) return { label: t('product.stock.outOfStockAlt'), className: 'border-red-500/30 bg-red-500/10 text-red-300' };
    if (stock <= 5) return { label: t('product.stock.lowStock'), className: 'border-amber-500/30 bg-amber-500/10 text-amber-300' };
    return { label: t('product.stock.inStock'), className: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' };
  };

  useEffect(() => {
    api.categories.list().then(res => {
      const flatten = (items) =>
        (items || []).flatMap(c => [{ id: c.id, name: c.name }, ...flatten(c.children)]);
      setCategories(flatten(res.data));
    }).catch(() => {});
  }, []);

  useEffect(() => {
    const params = {};
    if (searchQuery) params.search = searchQuery;
    if (categoryId)  params.category_id = categoryId;
    if (sort)        params.sort = sort;
    if (minPrice)    params.min_price = minPrice;
    if (maxPrice)    params.max_price = maxPrice;
    if (inStock)     params.in_stock = 1;
    loadProducts(params).then(data => setFilteredProducts(data ?? []));
  }, [searchQuery, categoryId, sort, minPrice, maxPrice, inStock, loadProducts]);

  const hasActiveFilters = sort || categoryId || minPrice || maxPrice || inStock;
  const clearFilters = () => { setSort(''); setCategoryId(''); setMinPrice(''); setMaxPrice(''); setInStock(false); };
  const clearSearch = () => setSearchParams({});
  const handleProductClick = (productId) => navigate(`/product/${productId}`);
  const handleAddToCart = async (e, product) => { e.stopPropagation(); await addToCart(product); };

  return (
    <div className="bg-black text-white min-h-screen font-sans selection:bg-neutral-800">
      <header className="fixed top-0 left-0 w-full z-40 bg-black/80 backdrop-blur-md border-b border-neutral-900">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <button
            onClick={() => navigate('/')}
            className="flex items-center gap-2 text-neutral-400 hover:text-white transition-colors group"
          >
            <ArrowLeft size={20} className="transition-transform rtl:rotate-180 group-hover:-translate-x-1 rtl:group-hover:translate-x-1" />
            <span className="text-sm uppercase tracking-wider hidden sm:inline">{t('common.nav.back')}</span>
          </button>

          <h1 className="text-xl font-bold tracking-widest text-white">{t('browse.title')}</h1>

          <div className="flex items-center gap-3">
            {currentUser ? (
              <>
                {currentUser.role === 'admin' && (
                  <button
                    onClick={() => navigate('/admin')}
                    className="hidden items-center gap-2 rounded-full border border-neutral-800 px-4 py-2 text-xs uppercase tracking-[0.2em] text-neutral-300 transition-colors hover:border-neutral-700 hover:text-white md:flex"
                  >
                    <ShieldCheck size={14} />
                    <span>{t('common.nav.admin')}</span>
                  </button>
                )}
                <div className="hidden items-center gap-2 rounded-full border border-neutral-800 px-4 py-2 text-sm text-neutral-400 md:flex">
                  <UserRound size={14} />
                  <span>{currentUser.name}</span>
                </div>
              </>
            ) : (
              <button
                onClick={() => navigate('/login')}
                className="rounded-full border border-neutral-800 px-4 py-2 text-xs uppercase tracking-[0.2em] text-neutral-300 transition-colors hover:border-neutral-700 hover:text-white"
              >
                {t('common.nav.signIn')}
              </button>
            )}

            <LanguageSwitcher />

            <button
              onClick={() => setIsCartOpen(true)}
              aria-label={t('common.aria.openCart')}
              className="text-neutral-400 hover:text-white transition-colors relative"
            >
              <ShoppingBag size={20} />
              {cartCount > 0 && (
                <span className="absolute -top-2 -end-2 bg-white text-black text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                  {cartCount}
                </span>
              )}
            </button>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-3 sm:px-6 pt-32 pb-20">
        {searchQuery && (
          <div className="mb-6 flex items-center justify-between">
            <p className="text-neutral-400">
              {t('browse.showingResults', { query: searchQuery })}
            </p>
            <button
              onClick={clearSearch}
              className="text-sm text-neutral-500 hover:text-white flex items-center gap-1 transition-colors"
            >
              <X size={16} /> {t('browse.clearSearch')}
            </button>
          </div>
        )}

        <div className="mb-10 flex flex-wrap items-center gap-3">
          <select value={sort} onChange={e => setSort(e.target.value)} className={SELECT_CLASS}>
            <option value="">{t('browse.sortNewest')}</option>
            <option value="price_asc">{t('browse.sortPriceLow')}</option>
            <option value="price_desc">{t('browse.sortPriceHigh')}</option>
          </select>

          {categories.length > 0 && (
            <select value={categoryId} onChange={e => setCategoryId(e.target.value)} className={SELECT_CLASS}>
              <option value="">{t('browse.allCategories')}</option>
              {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
            </select>
          )}

          <input
            type="number"
            placeholder={t('browse.minPrice')}
            value={minPrice}
            onChange={e => setMinPrice(e.target.value)}
            className={INPUT_CLASS}
            min="0"
          />
          <input
            type="number"
            placeholder={t('browse.maxPrice')}
            value={maxPrice}
            onChange={e => setMaxPrice(e.target.value)}
            className={INPUT_CLASS}
            min="0"
          />

          <label className="flex items-center gap-2 text-sm text-neutral-400 cursor-pointer select-none">
            <input type="checkbox" checked={inStock} onChange={e => setInStock(e.target.checked)} className="w-4 h-4 rounded accent-white" />
            {t('browse.inStockOnly')}
          </label>

          {hasActiveFilters && (
            <button onClick={clearFilters} className="flex items-center gap-1 text-xs text-neutral-500 hover:text-white transition-colors">
              <X size={14} /> {t('browse.clearFilters')}
            </button>
          )}
        </div>

        {loading ? (
          <div className="text-center py-20 text-neutral-500">{t('common.loading')}</div>
        ) : filteredProducts.length > 0 ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-10 lg:gap-x-8 lg:gap-y-12">
            {filteredProducts.map((product) => {
              const badge = getStockBadgeConfig(product.stock);
              return (
                <div
                  key={product.id}
                  className="group flex flex-col cursor-pointer transition-transform duration-300 hover:-translate-y-0.5"
                  onClick={() => handleProductClick(product.id)}
                >
                  <div className="relative aspect-square overflow-hidden rounded-xl border border-neutral-800/40 mb-2 sm:mb-3 transition-shadow duration-300 group-hover:shadow-xl group-hover:shadow-black/50 bg-[radial-gradient(ellipse_at_center,_#262626_0%,_#0a0a0a_100%)]">
                    <img
                      src={product.image}
                      alt={product.name}
                      className="w-full h-full object-contain p-1 scale-[1.02] group-hover:scale-[1.05] transition-transform duration-500 ease-out opacity-90 group-hover:opacity-100"
                    />
                    <div className="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-2 sm:p-3 lg:p-4">
                      <button
                        onClick={(e) => handleAddToCart(e, product)}
                        disabled={Number(product.stock) <= 0}
                        className="w-full py-2 sm:py-2.5 bg-white text-black text-[10px] sm:text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors rounded-lg transform translate-y-4 group-hover:translate-y-0 duration-300 disabled:cursor-not-allowed disabled:bg-neutral-500 disabled:text-neutral-900"
                      >
                        {Number(product.stock) <= 0 ? t('common.ui.outOfStock') : t('common.ui.addToCart')}
                      </button>
                    </div>
                  </div>
                  <div className="flex flex-col gap-0.5 px-0.5">
                    <div className="flex justify-between items-baseline gap-1">
                      <h3 className="text-xs sm:text-sm font-medium text-white tracking-tight group-hover:text-neutral-300 transition-colors line-clamp-2 leading-snug">{product.name}</h3>
                      <span className="text-xs sm:text-sm font-light text-neutral-400 shrink-0">{product.price}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <span className="hidden sm:inline text-[10px] text-neutral-600 uppercase tracking-widest">{product.sku}</span>
                      <span className={`rounded-full border px-2 py-0.5 text-[9px] sm:text-[10px] uppercase tracking-[0.15em] ${badge.className}`}>
                        {badge.label}
                      </span>
                    </div>
                    <p className="hidden sm:block text-xs text-neutral-500 line-clamp-1 font-light">{product.description}</p>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="text-center py-20">
            <h2 className="text-2xl font-bold text-white mb-4">{t('browse.noProductsFound')}</h2>
            <p className="text-neutral-500 max-w-md mx-auto">
              {searchQuery ? t('browse.noResultsQuery', { query: searchQuery }) : t('browse.noResultsFilter')}
            </p>
            <button
              onClick={() => { clearSearch(); clearFilters(); }}
              className="mt-8 px-8 py-3 bg-white text-black font-medium rounded-full hover:bg-neutral-200 transition-colors"
            >
              {t('browse.viewAll')}
            </button>
          </div>
        )}
      </main>

    </div>
  );
};

export default BrowsePage;
