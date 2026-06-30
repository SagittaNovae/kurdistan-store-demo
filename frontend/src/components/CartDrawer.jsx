import React, { useRef, useEffect } from 'react';
import { useCart } from '../context/CartContext';
import { useI18n } from '../context/I18nContext';
import { useNavigate } from 'react-router-dom';
import { X, Minus, Plus, ShoppingBag, Trash2 } from 'lucide-react';


const CartDrawer = () => {
  const { t, dir } = useI18n();
  const { cart, isCartOpen, setIsCartOpen, removeFromCart, updateQuantity, cartMeta } = useCart();
  const drawerRef = useRef(null);
  const navigate = useNavigate();

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (drawerRef.current && !drawerRef.current.contains(event.target) && isCartOpen) {
        setIsCartOpen(false);
      }
    };

    if (isCartOpen) {
      document.body.style.overflow = 'hidden';
      document.addEventListener('mousedown', handleClickOutside);
    } else {
      document.body.style.overflow = 'unset';
      document.removeEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.body.style.overflow = 'unset';
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isCartOpen, setIsCartOpen]);

  const handleCheckout = () => {
    setIsCartOpen(false);
    navigate('/checkout');
  };

  return (
    <>
      <div
        className={`fixed inset-0 bg-black/50 backdrop-blur-sm z-50 transition-opacity duration-300 ${
          isCartOpen ? 'opacity-100 visible' : 'opacity-0 invisible'
        }`}
      />

      <div
        ref={drawerRef}
        className={`fixed top-0 h-full w-full sm:w-[400px] bg-neutral-900 z-50 transform transition-transform duration-300 ease-out shadow-2xl ${
          dir === 'rtl'
            ? 'left-0 border-r border-neutral-800'
            : 'right-0 border-l border-neutral-800'
        } ${
          isCartOpen ? 'translate-x-0' : dir === 'rtl' ? '-translate-x-full' : 'translate-x-full'
        }`}
      >
        <div className="flex flex-col h-full text-white">
          <div className="flex items-center justify-between p-6 border-b border-neutral-800">
            <div className="flex items-center gap-3">
              <ShoppingBag size={20} />
              <h2 className="text-xl font-bold tracking-widest">{t('cart.title')}</h2>
            </div>
            <button
              onClick={() => setIsCartOpen(false)}
              className="p-2 hover:bg-neutral-800 rounded-full transition-colors"
            >
              <X size={24} />
            </button>
          </div>

          <div className="flex-1 overflow-y-auto p-6 space-y-6">
            {cart.length === 0 ? (
              <div className="flex flex-col items-center justify-center h-full text-neutral-500 space-y-4">
                <ShoppingBag size={48} strokeWidth={1} />
                <p className="text-lg">{t('cart.empty')}</p>
                <button
                  onClick={() => setIsCartOpen(false)}
                  className="px-6 py-2 bg-white text-black text-sm font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors rounded-full"
                >
                  {t('cart.startShopping')}
                </button>
              </div>
            ) : (
              cart.map((item) => (
                <div key={item.id} className="flex gap-4">
                  <div className="w-20 h-24 bg-neutral-800 rounded-md overflow-hidden flex-shrink-0">
                    <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                  </div>
                  <div className="flex-1 flex flex-col justify-between">
                    <div>
                      <h3 className="font-medium text-white">{item.name}</h3>
                      <p className="text-sm text-neutral-400">{item.price}</p>
                    </div>
                    <div className="flex items-center justify-between mt-2">
                      <div className="flex items-center gap-3 bg-neutral-800 rounded-full px-3 py-1">
                        <button onClick={() => updateQuantity(item.id, item.quantity - 1)} className="text-neutral-400 hover:text-white transition-colors">
                          <Minus size={14} />
                        </button>
                        <span className="text-sm font-medium w-4 text-center">{item.quantity}</span>
                        <button onClick={() => updateQuantity(item.id, item.quantity + 1)} className="text-neutral-400 hover:text-white transition-colors">
                          <Plus size={14} />
                        </button>
                      </div>
                      <button onClick={() => removeFromCart(item.id)} className="text-neutral-500 hover:text-red-500 transition-colors p-1">
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>

          {cart.length > 0 && (
            <div className="p-6 border-t border-neutral-800 bg-neutral-900">
              <div className="flex items-center justify-between mb-4 text-lg font-medium">
                <span className="text-neutral-400">{t('cart.subtotal')}</span>
                <span>{cartMeta.subtotalFormatted}</span>
              </div>
              <p className="text-xs text-neutral-500 mb-6 text-center">
                {t('cart.shippingNote')}
              </p>
              <button
                onClick={handleCheckout}
                className="w-full py-4 bg-white text-black font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors rounded-full"
              >
                {t('cart.checkout')}
              </button>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default CartDrawer;
