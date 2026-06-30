import React, { createContext, useContext, useEffect, useState, useCallback, useRef } from 'react';
import { useAuth } from './AuthContext';
import api from '../services/api';

import en from '../i18n/en.json';
import ku from '../i18n/ku.json';
import ar from '../i18n/ar.json';

const DICTIONARIES = { en, ku, ar };
const SUPPORTED = ['en', 'ku', 'ar'];
const STORAGE_KEY = 'ks_lang';
const RTL_LANGS = ['ar'];

const I18nContext = createContext(null);

function resolve(dict, key) {
  return key.split('.').reduce((node, k) => (node && typeof node === 'object' ? node[k] : undefined), dict);
}

function interpolate(str, vars) {
  if (!vars || typeof str !== 'string') return str;
  return str.replace(/\{(\w+)\}/g, (_, k) => (vars[k] !== undefined ? vars[k] : `{${k}}`));
}

export function I18nProvider({ children }) {
  const { currentUser } = useAuth();
  const currentUserIdRef = useRef(currentUser?.id);

  const [lang, setLangState] = useState(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    return SUPPORTED.includes(stored) ? stored : 'en';
  });

  const applyToDOM = useCallback((code) => {
    document.documentElement.lang = code;
    document.documentElement.dir = RTL_LANGS.includes(code) ? 'rtl' : 'ltr';
  }, []);

  useEffect(() => {
    applyToDOM(lang);
  }, [lang, applyToDOM]);

  // Sync from server on login (user ID change from null → value)
  useEffect(() => {
    const prevId = currentUserIdRef.current;
    currentUserIdRef.current = currentUser?.id;

    if (!currentUser?.id) return;
    // Only pull server preference on a fresh login, not on every re-render
    if (prevId === currentUser.id) return;

    api.account.preferences.get()
      .then(res => {
        const serverLang = res?.data?.data?.preferred_language;
        if (serverLang && SUPPORTED.includes(serverLang) && serverLang !== lang) {
          setLangState(serverLang);
          localStorage.setItem(STORAGE_KEY, serverLang);
        }
      })
      .catch(() => {});
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentUser?.id]);

  const setLanguage = useCallback((code) => {
    if (!SUPPORTED.includes(code)) return;
    setLangState(code);
    localStorage.setItem(STORAGE_KEY, code);
    // Persist to server immediately when a logged-in user changes language
    if (currentUserIdRef.current) {
      api.account.preferences.update({ preferred_language: code }).catch(() => {});
    }
  }, []);

  const t = useCallback((key, vars) => {
    const dict = DICTIONARIES[lang];
    const val = resolve(dict, key) ?? resolve(DICTIONARIES.en, key) ?? key;
    return interpolate(val, vars);
  }, [lang]);

  return (
    <I18nContext.Provider value={{ lang, setLanguage, t, dir: RTL_LANGS.includes(lang) ? 'rtl' : 'ltr' }}>
      {children}
    </I18nContext.Provider>
  );
}

export function useI18n() {
  const ctx = useContext(I18nContext);
  if (!ctx) throw new Error('useI18n must be used inside I18nProvider');
  return ctx;
}
