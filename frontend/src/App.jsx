import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useParams } from 'react-router-dom';
import HomePage from './pages/HomePage';
import BrowsePage from './pages/BrowsePage';
import ProductDetailsPage from './pages/ProductDetailsPage';
import CheckoutPage from './pages/CheckoutPage';
import OrderSuccessPage from './pages/OrderSuccessPage';
import LoginPage from './pages/LoginPage';
import AccountPage from './pages/AccountPage';
import OrderDetailPage from './pages/OrderDetailPage';
import WishlistPage from './pages/WishlistPage';
import { CartProvider } from './context/CartContext';
import { AuthProvider } from './context/AuthContext';
import { I18nProvider } from './context/I18nContext';
import { ProductProvider } from './context/ProductContext';
import CartDrawer from './components/CartDrawer';
import CartToast from './components/CartToast';
import ProductChatbot from './components/ProductChatbot';
import ProtectedRoute from './components/ProtectedRoute';

const RedirectToOrderDetail = () => {
  const { id } = useParams();
  return <Navigate to={`/account/orders/${id}`} replace />;
};

function App() {
  return (
    <AuthProvider>
      <I18nProvider>
      <ProductProvider>
        <CartProvider>
          <Router basename="/">
            <Routes>
              <Route path="/" element={<HomePage />} />
              <Route path="/browse" element={<BrowsePage />} />
              <Route path="/product/:id" element={<ProductDetailsPage />} />
              <Route path="/login" element={<LoginPage />} />
              <Route
                path="/account"
                element={
                  <ProtectedRoute>
                    <AccountPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/wishlist"
                element={
                  <ProtectedRoute>
                    <WishlistPage />
                  </ProtectedRoute>
                }
              />
              <Route path="/checkout" element={<CheckoutPage />} />
              <Route path="/success" element={<OrderSuccessPage />} />
              {/* Order detail page */}
              <Route
                path="/account/orders/:orderId"
                element={
                  <ProtectedRoute>
                    <OrderDetailPage />
                  </ProtectedRoute>
                }
              />
              {/* Redirect Bagisto-style URLs to SPA equivalents */}
              <Route path="/customer/account/orders/view/:id" element={<RedirectToOrderDetail />} />
              <Route path="/customer/account/orders" element={<Navigate to="/account?tab=orders" replace />} />
              <Route path="/customer/account/*" element={<Navigate to="/account" replace />} />
            </Routes>
            <CartDrawer />
            <CartToast />
            <ProductChatbot />
          </Router>
        </CartProvider>
      </ProductProvider>
      </I18nProvider>
    </AuthProvider>
  );
}

export default App;
