import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import api, { normalizeProduct } from '../services/api';

const ProductContext = createContext(null);

export const useProducts = () => {
  const context = useContext(ProductContext);
  if (!context) {
    throw new Error('useProducts must be used inside ProductProvider');
  }
  return context;
};

export const ProductProvider = ({ children }) => {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadProducts = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const response = await api.products.list(params);
      const normalized = (response.data || []).map(normalizeProduct);
      setProducts(normalized);
      return normalized;
    } catch (err) {
      setError(err.message);
      return [];
    } finally {
      setLoading(false);
    }
  }, []);

  const loadCategories = useCallback(async () => {
    try {
      const response = await api.categories.list();
      setCategories((response.data || []).map((c) => c.name));
    } catch {
      setCategories([]);
    }
  }, []);

  const aiSearch = useCallback(async (query, filters = {}) => {
    setLoading(true);
    try {
      const params = { ...(query ? { search: query } : {}), ...filters };
      const response = await api.products.list(params);
      const normalized = (response.data || []).map(normalizeProduct);
      setProducts(normalized);
      return {
        products: normalized,
        meta: response.meta,
      };
    } finally {
      setLoading(false);
    }
  }, []);

  const getProductById = useCallback(
    (productId) => products.find((p) => Number(p.id) === Number(productId)),
    [products]
  );

  const fetchProductById = useCallback(async (productId) => {
    const cached = products.find((p) => Number(p.id) === Number(productId));
    if (cached) return cached;

    const response = await api.products.get(productId);
    return normalizeProduct(response.data);
  }, [products]);

  useEffect(() => {
    loadProducts();
    loadCategories();
  }, [loadProducts, loadCategories]);

  const value = useMemo(
    () => ({
      products,
      categories,
      loading,
      error,
      loadProducts,
      aiSearch,
      getProductById,
      fetchProductById,
    }),
    [products, categories, loading, error, loadProducts, aiSearch, getProductById, fetchProductById]
  );

  return <ProductContext.Provider value={value}>{children}</ProductContext.Provider>;
};
