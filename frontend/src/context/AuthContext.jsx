import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import api, { clearAccessToken, setAccessToken } from '../services/api';

const AuthContext = createContext(null);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used inside AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [currentUser, setCurrentUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const refreshUser = useCallback(async () => {
    try {
      // Restore session: exchange the HttpOnly refresh cookie for a new access token.
      // On first visit (no cookie yet) this throws and we fall through to /auth/me.
      try {
        const refreshData = await api.auth.refresh();
        setAccessToken(refreshData.access_token);
      } catch {
        // No refresh cookie — fall back to checking the session cookie.
      }

      const response = await api.auth.me();
      setCurrentUser({
        ...response.data,
        role: response.data.is_admin ? 'admin' : 'user',
      });
    } catch {
      clearAccessToken();
      setCurrentUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    refreshUser();
  }, [refreshUser]);

  // When the reactive 401 → refresh → retry cycle exhausts, api.js fires auth:expired.
  useEffect(() => {
    const handleExpired = () => {
      clearAccessToken();
      setCurrentUser(null);
    };
    window.addEventListener('auth:expired', handleExpired);
    return () => window.removeEventListener('auth:expired', handleExpired);
  }, []);

  const signIn = async ({ phone, password, remember = false }) => {
    const response = await api.auth.login(phone, password, remember);
    setAccessToken(response.access_token);
    const user = {
      ...response.data,
      role: response.data.is_admin ? 'admin' : 'user',
    };
    setCurrentUser(user);
    return user;
  };

  const signUp = async ({ firstName, lastName, phone, email, password }) => {
    const response = await api.auth.register({
      first_name: firstName.trim(),
      last_name: lastName.trim(),
      phone,
      email: email || undefined,
      password,
      password_confirmation: password,
    });
    if (response.access_token) {
      setAccessToken(response.access_token);
    }
    setCurrentUser({ ...response.data, role: 'user' });
  };

  const signOut = async () => {
    try {
      await api.auth.logout();
    } catch {
      // Session may already be cleared server-side
    }
    clearAccessToken();
    setCurrentUser(null);
  };

  const value = useMemo(
    () => ({
      currentUser,
      isAuthenticated: Boolean(currentUser),
      loading,
      signIn,
      signUp,
      signOut,
      refreshUser,
    }),
    [currentUser, loading, refreshUser]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
