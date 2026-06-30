import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useProducts } from '../context/ProductContext';
import { useI18n } from '../context/I18nContext';
import LanguageSwitcher from '../components/LanguageSwitcher';
import api from '../services/api';
import { ArrowLeft, Heart, Minus, Plus, ShieldCheck, ShoppingBag, Star, UserRound } from 'lucide-react';

const ProductDetailsPage = () => {
  const { t } = useI18n();
  const { id } = useParams();
  const navigate = useNavigate();
  const { addToCart, setIsCartOpen, cartCount } = useCart();
  const { currentUser } = useAuth();
  const { getProductById, fetchProductById } = useProducts();
  const [quantity, setQuantity] = React.useState(1);
  const [directProduct, setDirectProduct] = React.useState(null);
  const [loadError, setLoadError] = React.useState(false);
  const [reviews, setReviews] = React.useState([]);
  const [wishlisted, setWishlisted] = React.useState(false);
  const [reviewForm, setReviewForm] = React.useState({ title: '', comment: '', rating: 5 });
  const [reviewSubmitting, setReviewSubmitting] = React.useState(false);
  const [reviewMessage, setReviewMessage] = React.useState('');

  const getStockBadgeConfig = (stockValue) => {
    const stock = Number(stockValue) || 0;
    if (stock <= 0) return { label: t('product.stock.outOfStockAlt'), className: 'border-red-500/30 bg-red-500/10 text-red-300' };
    if (stock <= 5) return { label: t('product.stock.lowStock'), className: 'border-amber-500/30 bg-amber-500/10 text-amber-300' };
    return { label: t('product.stock.inStock'), className: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' };
  };

  const product = getProductById(id) ?? directProduct;

  React.useEffect(() => {
    if (!getProductById(id)) {
      setDirectProduct(null);
      setLoadError(false);
      fetchProductById(id)
        .then((p) => setDirectProduct(p))
        .catch(() => setLoadError(true));
    }
  }, [id]); // eslint-disable-line

  React.useEffect(() => {
    if (!id) return;
    api.reviews.list(id).then((res) => setReviews(res.data || [])).catch(() => {});
  }, [id]);

  const handleWishlistToggle = async () => {
    if (!currentUser) { navigate('/login'); return; }
    try {
      if (wishlisted) {
        await api.wishlist.remove(product.id);
      } else {
        await api.wishlist.add(product.id);
      }
      setWishlisted((prev) => !prev);
    } catch {
      // ignore
    }
  };

  const handleReviewSubmit = async (e) => {
    e.preventDefault();
    if (!currentUser) { navigate('/login'); return; }
    setReviewSubmitting(true);
    setReviewMessage('');
    try {
      await api.reviews.create(id, reviewForm);
      setReviewMessage(t('product.reviewSubmitted'));
      setReviewForm({ title: '', comment: '', rating: 5 });
    } catch (err) {
      setReviewMessage(err.data?.message || err.message || t('product.reviewFailed'));
    } finally {
      setReviewSubmitting(false);
    }
  };

  const avgRating = reviews.length
    ? (reviews.reduce((s, r) => s + r.rating, 0) / reviews.length).toFixed(1)
    : null;

  if (!product) {
    if (loadError) {
      return (
        <div className="min-h-screen bg-black text-white flex flex-col items-center justify-center p-6 text-center">
          <h1 className="text-3xl font-bold mb-4">{t('product.notFound')}</h1>
          <p className="text-neutral-400 mb-8">{t('product.notFoundDesc')}</p>
          <button
            onClick={() => navigate('/browse')}
            className="px-8 py-3 bg-white text-black rounded-full font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors"
          >
            {t('product.returnToShop')}
          </button>
        </div>
      );
    }
    return (
      <div className="min-h-screen bg-black text-white flex items-center justify-center">
        <div className="text-neutral-400 text-sm uppercase tracking-widest">{t('common.loading')}</div>
      </div>
    );
  }

  const handleAddToCart = () => {
    if (Number(product.stock) <= 0) return;
    addToCart({ ...product, quantity });
    setIsCartOpen(true);
  };

  const stockBadge = getStockBadgeConfig(product.stock);

  return (
    <div className="bg-black text-white min-h-screen font-sans selection:bg-neutral-800">
      {/* Header */}
      <header className="fixed top-0 left-0 w-full z-40 bg-black/80 backdrop-blur-md border-b border-neutral-900">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <button
            onClick={() => navigate(-1)}
            className="flex items-center gap-2 text-neutral-400 hover:text-white transition-colors group"
          >
            <ArrowLeft size={20} className="transition-transform rtl:rotate-180 group-hover:-translate-x-1 rtl:group-hover:translate-x-1" />
            <span className="text-sm uppercase tracking-wider hidden sm:inline">{t('common.nav.back')}</span>
          </button>

          <h1 className="text-xl font-bold tracking-widest text-white">{t('product.pageTitle')}</h1>

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

      <main className="max-w-7xl mx-auto px-6 pt-32 pb-20">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-start">
          {/* Image Section */}
          <div className="relative aspect-square lg:aspect-[4/5] bg-neutral-900 overflow-hidden rounded-lg">
            <img
              src={product.image}
              alt={product.name}
              className="w-full h-full object-cover hover:scale-105 transition-transform duration-700 ease-out"
            />
          </div>

          {/* Details Section */}
          <div className="flex flex-col space-y-8 lg:sticky lg:top-32">
            <div>
              <div className="mb-3 flex flex-wrap items-center gap-3">
                <p className="text-sm text-neutral-500 uppercase tracking-widest">{product.sku}</p>
                <span className={`rounded-full border px-3 py-1 text-[10px] uppercase tracking-[0.2em] ${stockBadge.className}`}>
                  {stockBadge.label}
                </span>
              </div>
              <h1 className="text-4xl md:text-5xl font-bold tracking-tight text-white mb-4">{product.name}</h1>
              <div className="flex items-center gap-4">
                <p className="text-2xl font-light text-neutral-300">{product.price}</p>
                {avgRating && (
                  <div className="flex items-center gap-1 text-yellow-500">
                    {[1,2,3,4,5].map((s) => (
                      <Star key={s} size={16} fill={s <= Math.round(Number(avgRating)) ? 'currentColor' : 'none'} />
                    ))}
                    <span className="text-sm text-neutral-500 ms-1">({avgRating} · {reviews.length})</span>
                  </div>
                )}
              </div>
            </div>

            <div className="h-px bg-neutral-800 w-full" />

            <div className="prose prose-invert text-neutral-400 leading-relaxed">
              <p>{product.description}</p>
            </div>

            <div className="space-y-6 pt-4">
              {/* Quantity Selector */}
              <div className="flex items-center gap-4">
                <span className="text-sm uppercase tracking-widest text-neutral-500">{t('product.quantity')}</span>
                <div className="flex items-center border border-neutral-800 rounded-full px-4 py-2 bg-neutral-900">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    disabled={Number(product.stock) <= 0}
                    className="p-1 text-neutral-400 hover:text-white transition-colors"
                  >
                    <Minus size={16} />
                  </button>
                  <span className="w-8 text-center font-medium">{quantity}</span>
                  <button
                    onClick={() => setQuantity((q) => Math.min(Number(product.stock) || 1, q + 1))}
                    disabled={Number(product.stock) <= 0}
                    className="p-1 text-neutral-400 hover:text-white transition-colors"
                  >
                    <Plus size={16} />
                  </button>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-4">
                <button
                  onClick={handleAddToCart}
                  disabled={Number(product.stock) <= 0}
                  className="flex-1 py-4 bg-white text-black font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors rounded-full flex items-center justify-center gap-2 disabled:cursor-not-allowed disabled:bg-neutral-500 disabled:text-neutral-900"
                >
                  <ShoppingBag size={18} />
                  {Number(product.stock) <= 0 ? t('product.unavailable') : t('common.ui.addToCart')}
                </button>
                <button
                  onClick={handleWishlistToggle}
                  className={`p-4 border rounded-full transition-colors ${wishlisted ? 'border-red-500 text-red-400 hover:bg-red-500/10' : 'border-neutral-800 text-neutral-400 hover:bg-neutral-800 hover:text-white'}`}
                  aria-label={wishlisted ? t('product.removeFromWishlist') : t('product.addToWishlist')}
                >
                  <Heart size={20} fill={wishlisted ? 'currentColor' : 'none'} />
                </button>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4 pt-8 text-sm text-neutral-500">
              <div className="flex flex-col gap-1">
                <span className="text-white font-medium">{t('product.freeShipping')}</span>
                <span>{t('product.freeShippingDesc')}</span>
              </div>
              <div className="flex flex-col gap-1">
                <span className="text-white font-medium">{t('product.returns')}</span>
                <span>{t('product.returnsDesc')}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Reviews Section */}
        <div className="mt-20 border-t border-neutral-800 pt-16">
          <h2 className="text-2xl font-bold tracking-wide mb-10">
            {t('product.reviews')} {reviews.length > 0 && <span className="text-neutral-500 font-light text-lg">({reviews.length})</span>}
          </h2>

          {reviews.length === 0 ? (
            <p className="text-neutral-500 mb-12">{t('product.noReviews')}</p>
          ) : (
            <div className="space-y-6 mb-14">
              {reviews.map((r) => (
                <div key={r.id} className="border border-neutral-800 rounded-xl p-6 bg-neutral-950">
                  <div className="flex items-center justify-between mb-3">
                    <div>
                      <span className="font-semibold text-white">{r.name}</span>
                      <span className="text-neutral-600 text-xs ms-3">{r.created_at}</span>
                    </div>
                    <div className="flex items-center gap-0.5 text-yellow-500">
                      {[1,2,3,4,5].map((s) => (
                        <Star key={s} size={14} fill={s <= r.rating ? 'currentColor' : 'none'} />
                      ))}
                    </div>
                  </div>
                  <p className="font-medium text-neutral-200 mb-1">{r.title}</p>
                  <p className="text-neutral-400 text-sm leading-relaxed">{r.comment}</p>
                </div>
              ))}
            </div>
          )}

          {/* Write a Review */}
          <div className="border border-neutral-800 rounded-2xl p-8 bg-neutral-950">
            <h3 className="text-lg font-bold tracking-wide mb-6">{t('product.writeReview')}</h3>
            {!currentUser ? (
              <p className="text-neutral-500">
                <button onClick={() => navigate('/login')} className="text-white underline">{t('product.signInLink')}</button> {t('product.signInPrompt')}
              </p>
            ) : (
              <form onSubmit={handleReviewSubmit} className="space-y-5">
                <div className="space-y-1">
                  <label className="text-xs uppercase tracking-widest text-neutral-500">{t('product.ratingLabel')}</label>
                  <div className="flex items-center gap-2">
                    {[1,2,3,4,5].map((s) => (
                      <button
                        key={s}
                        type="button"
                        onClick={() => setReviewForm((prev) => ({ ...prev, rating: s }))}
                        className={`transition-colors ${s <= reviewForm.rating ? 'text-yellow-500' : 'text-neutral-700'}`}
                      >
                        <Star size={24} fill={s <= reviewForm.rating ? 'currentColor' : 'none'} />
                      </button>
                    ))}
                  </div>
                </div>
                <div className="space-y-1">
                  <label className="text-xs uppercase tracking-widest text-neutral-500">{t('product.titleLabel')}</label>
                  <input
                    type="text"
                    value={reviewForm.title}
                    onChange={(e) => setReviewForm((prev) => ({ ...prev, title: e.target.value }))}
                    placeholder={t('product.titlePlaceholder')}
                    className="w-full bg-neutral-900 border border-neutral-800 rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors"
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="text-xs uppercase tracking-widest text-neutral-500">{t('product.commentLabel')}</label>
                  <textarea
                    value={reviewForm.comment}
                    onChange={(e) => setReviewForm((prev) => ({ ...prev, comment: e.target.value }))}
                    placeholder={t('product.commentPlaceholder')}
                    rows={4}
                    className="w-full bg-neutral-900 border border-neutral-800 rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors resize-none"
                    required
                  />
                </div>
                {reviewMessage && (
                  <p className={`text-sm ${reviewMessage === t('product.reviewSubmitted') ? 'text-green-400' : 'text-red-400'}`}>
                    {reviewMessage}
                  </p>
                )}
                <button
                  type="submit"
                  disabled={reviewSubmitting}
                  className="px-8 py-3 bg-white text-black font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors rounded-full disabled:opacity-50"
                >
                  {reviewSubmitting ? t('product.submitting') : t('product.submitReview')}
                </button>
              </form>
            )}
          </div>
        </div>
      </main>

    </div>
  );
};

export default ProductDetailsPage;
