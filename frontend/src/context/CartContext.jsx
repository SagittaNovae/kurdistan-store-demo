import React, { createContext, useCallback, useContext, useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { useAuth } from './AuthContext';
import { useI18n } from './I18nContext';
import { formatIQD } from '../utils/format';

const CartContext = createContext();

export const useCart = () => useContext(CartContext);

const mapCartItem = (item) => ({
  id: item.product_id,
  cartItemId: item.id,
  name: item.name,
  price: item.price_formatted,
  priceRaw: item.price,
  image: item.image,
  quantity: item.quantity,
  stock: item.stock,
  sku: item.sku,
});

export const CartProvider = ({ children }) => {
  const { t } = useI18n();
  const [cart, setCart] = useState([]);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [cartMeta, setCartMeta] = useState({
    subtotal: 0,
    grandTotal: 0,
    shipping: 5,
    subtotalFormatted: 'IQD 0',
    grandTotalFormatted: 'IQD 0',
  });
  const [cartError, setCartError] = useState(null);
  const errorTimerRef = useRef(null);
  const { isAuthenticated } = useAuth();

  const showCartError = useCallback((msg) => {
    clearTimeout(errorTimerRef.current);
    setCartError(msg);
    errorTimerRef.current = setTimeout(() => setCartError(null), 5000);
  }, []);

  const syncCart = useCallback(async () => {
    try {
      const response = await api.cart.get();
      const data = response.data;
      setCart((data.items || []).map(mapCartItem));
      setCartMeta({
        subtotal: data.sub_total || 0,
        grandTotal: data.grand_total || 0,
        shipping: data.shipping_amount || 5,
        subtotalFormatted: data.sub_total_formatted || formatIQD(data.sub_total),
        grandTotalFormatted: data.grand_total_formatted || formatIQD(data.grand_total),
      });
    } catch {
      setCart([]);
    }
  }, []);

  useEffect(() => {
    syncCart();
  }, [syncCart, isAuthenticated]);

  const addToCart = async (product) => {
    const quantity = Number(product.quantity) > 0 ? Number(product.quantity) : 1;
    try {
      await api.cart.add(product.id, quantity);
      setCartError(null);
      await syncCart();
      setIsCartOpen(true);
    } catch (err) {
      console.error('Add to cart failed:', err?.message || err);
      const msg =
        err?.message && err.message !== 'Request failed'
          ? err.message
          : t('cart.outOfStock');
      showCartError(msg);
    }
  };

  const removeFromCart = async (productId) => {
    const item = cart.find((i) => i.id === productId);
    if (item?.cartItemId) {
      await api.cart.remove(item.cartItemId);
      await syncCart();
    }
  };

  const updateQuantity = async (productId, quantity) => {
    const item = cart.find((i) => i.id === productId);
    if (!item?.cartItemId) return;

    if (quantity < 1) {
      await removeFromCart(productId);
      return;
    }

    await api.cart.update(item.cartItemId, quantity);
    await syncCart();
  };

  const clearCart = () => {
    setCart([]);
    setCartMeta({ subtotal: 0, grandTotal: 0, shipping: 5, subtotalFormatted: 'IQD 0', grandTotalFormatted: 'IQD 0' });
  };

  const cartCount = cart.reduce((total, item) => total + item.quantity, 0);

  const cartTotal = cartMeta.subtotal || cart.reduce((total, item) => {
    const price = parseFloat(String(item.price).replace(/[^0-9.]/g, '')) || item.priceRaw || 0;
    return total + price * item.quantity;
  }, 0);

  return (
    <CartContext.Provider
      value={{
        cart,
        isCartOpen,
        setIsCartOpen,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        syncCart,
        cartCount,
        cartTotal,
        cartMeta,
        cartError,
        setCartError,
        placeOrder: api.cart.checkout,
      }}
    >
      {children}
    </CartContext.Provider>
  );
};
