import { useEffect, useState } from 'react';
import { AlertCircle, X } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { useI18n } from '../context/I18nContext';

const CartToast = () => {
  const { cartError, setCartError } = useCart();
  const { t } = useI18n();
  const [visible, setVisible] = useState(false);

  // Trigger enter animation whenever a new error arrives
  useEffect(() => {
    if (cartError) {
      setVisible(true);
    } else {
      setVisible(false);
    }
  }, [cartError]);

  if (!cartError) return null;

  return (
    <div
      role="alert"
      aria-live="assertive"
      style={{ transition: 'opacity 0.2s ease, transform 0.2s ease' }}
      className={`fixed end-4 top-4 z-[9999] flex max-w-sm items-start gap-3 rounded-2xl border border-red-500/30 bg-neutral-900 px-4 py-3 shadow-2xl ${
        visible ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'
      }`}
    >
      <AlertCircle size={17} className="mt-0.5 shrink-0 text-red-400" />
      <p className="flex-1 text-sm leading-snug text-red-300">{cartError}</p>
      <button
        type="button"
        onClick={() => setCartError(null)}
        aria-label={t('common.aria.dismiss')}
        className="shrink-0 text-neutral-500 transition-colors hover:text-white"
      >
        <X size={15} />
      </button>
    </div>
  );
};

export default CartToast;
