const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

class ApiError extends Error {
  constructor(message, status, data) {
    super(message);
    this.status = status;
    this.data = data;
  }
}

const getCsrfCookie = async () => {
  const base = API_BASE.replace(/\/api\/v1\/?$/, '');
  await fetch(`${base}/sanctum/csrf-cookie`, { credentials: 'include' });
};

const getXsrfToken = () => {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
};

// ── In-memory access token ────────────────────────────────────────────────────
// Never stored in localStorage / sessionStorage — lives only in JS module scope.
// Exposed so AuthContext can set it after login or silent refresh.
let _accessToken = null;

export const setAccessToken = (token) => { _accessToken = token; };
export const clearAccessToken = () => { _accessToken = null; };

// ── 401 silent-refresh state ──────────────────────────────────────────────────
// Collapsing concurrent 401s into one refresh attempt prevents token churn.
let _refreshing = null;

const MUTATING = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

const request = async (path, options = {}, _isRetry = false) => {
  const method = (options.method || 'GET').toUpperCase();

  if (MUTATING.has(method) && !getXsrfToken()) {
    await getCsrfCookie();
  }

  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(_accessToken ? { Authorization: `Bearer ${_accessToken}` } : {}),
    ...(MUTATING.has(method) && getXsrfToken() ? { 'X-XSRF-TOKEN': getXsrfToken() } : {}),
    ...(options.headers || {}),
  };

  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers,
    credentials: 'include',
  });

  const data = await response.json().catch(() => ({}));

  // On 401: attempt a silent token refresh once, then retry the original request.
  // Skip if this is already a retry, or if the failing request IS the refresh endpoint.
  if (response.status === 401 && !_isRetry && path !== '/auth/refresh') {
    if (!_refreshing) {
      _refreshing = request('/auth/refresh', { method: 'POST' }, true)
        .finally(() => { _refreshing = null; });
    }

    try {
      const refreshData = await _refreshing;
      _accessToken = refreshData.access_token;
      return request(path, options, true);
    } catch {
      _accessToken = null;
      window.dispatchEvent(new CustomEvent('auth:expired'));
      throw new ApiError('Session expired. Please sign in again.', 401, {});
    }
  }

  if (!response.ok) {
    throw new ApiError(data.message || data.error || 'Request failed', response.status, data);
  }

  return data;
};

export const api = {
  health: () => request('/health'),

  products: {
    list: (params = {}) => {
      const query = new URLSearchParams(params).toString();
      return request(`/products${query ? `?${query}` : ''}`);
    },
    get: (id) => request(`/products/${id}`),
  },

  categories: {
    list: () => request('/categories'),
  },

  shipping: {
    zones: () => request('/shipping/zones'),
    quote: (governorate, district = null) => {
      const params = new URLSearchParams({ governorate, ...(district ? { district } : {}) });
      return request(`/shipping/quote?${params}`);
    },
  },

  auth: {
    async login(phone, password, remember = false) {
      await getCsrfCookie();
      return request('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ phone, password, remember }),
      });
    },
    async register(payload) {
      await getCsrfCookie();
      return request('/auth/register', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
    },
    // Exchange the HttpOnly refresh_token cookie for a new access token.
    // No auth header needed — the cookie is sent automatically by the browser.
    refresh: () => request('/auth/refresh', { method: 'POST' }),
    logout: () => request('/auth/logout', { method: 'POST' }),
    me: () => request('/auth/me'),

  },

  cart: {
    get: () => request('/cart'),
    add: (productId, quantity = 1) =>
      request('/cart/items', {
        method: 'POST',
        body: JSON.stringify({ product_id: productId, quantity }),
      }),
    update: (itemId, quantity) =>
      request(`/cart/items/${itemId}`, {
        method: 'PATCH',
        body: JSON.stringify({ quantity }),
      }),
    remove: (itemId) => request(`/cart/items/${itemId}`, { method: 'DELETE' }),
    checkout: (payload) =>
      request('/checkout', { method: 'POST', body: JSON.stringify(payload) }),
  },

  orders: {
    list: () => request('/orders'),
    get: (id) => request(`/orders/${id}`),
  },

  wishlist: {
    list: () => request('/wishlist'),
    add: (productId) =>
      request('/wishlist', {
        method: 'POST',
        body: JSON.stringify({ product_id: productId }),
      }),
    remove: (productId) => request(`/wishlist/${productId}`, { method: 'DELETE' }),
  },

  locations: () => request('/locations'),

  reviews: {
    list: (productId) => request(`/products/${productId}/reviews`),
    create: (productId, payload) =>
      request(`/products/${productId}/reviews`, {
        method: 'POST',
        body: JSON.stringify(payload),
      }),
  },

  seo: {
    product: (id) => request(`/seo/products/${id}`),
  },

  account: {
    profile: {
      get: () => request('/account/profile'),
      update: (payload) =>
        request('/account/profile', { method: 'PATCH', body: JSON.stringify(payload) }),
    },
    changePassword: (payload) =>
      request('/account/password', { method: 'POST', body: JSON.stringify(payload) }),
    preferences: {
      get: () => request('/account/preferences'),
      update: (payload) =>
        request('/account/preferences', { method: 'PUT', body: JSON.stringify(payload) }),
    },
    logoutAll: () =>
      request('/account/logout-all', { method: 'POST' }),
    addresses: {
      list: () => request('/account/addresses'),
      create: (payload) =>
        request('/account/addresses', { method: 'POST', body: JSON.stringify(payload) }),
      update: (id, payload) =>
        request(`/account/addresses/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
      delete: (id) => request(`/account/addresses/${id}`, { method: 'DELETE' }),
      setDefault: (id) =>
        request(`/account/addresses/${id}/default`, { method: 'POST' }),
    },
  },
};

export const normalizeProduct = (product) => ({
  id: product.id,
  sku: product.sku,
  name: product.name,
  price: product.price_formatted || `IQD ${Math.round(Number(product.price) || 0).toLocaleString('en-US')}`,
  priceRaw: product.price,
  image: product.image,
  description: product.description,
  category: product.category || null,
  stock: product.stock ?? 0,
  slug: product.slug,
  inStock: product.in_stock,
});

export default api;
