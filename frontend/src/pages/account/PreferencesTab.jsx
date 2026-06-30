import React, { useEffect, useState } from 'react';
import { Globe, Loader2, Check } from 'lucide-react';
import api from '../../services/api';
import { useI18n } from '../../context/I18nContext';

const PreferencesTab = () => {
  const { t, lang, setLanguage } = useI18n();
  const [prefs, setPrefs] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);
  const [error, setError] = useState('');

  const LANGUAGES = [
    { code: 'en', labelKey: 'preferences.langEn', native: 'English' },
    { code: 'ar', labelKey: 'preferences.langAr', native: 'العربية' },
    { code: 'ku', labelKey: 'preferences.langKu', native: 'کوردی' },
  ];

  useEffect(() => {
    api.account.preferences.get()
      .then(res => setPrefs(res.data?.data ?? res.data))
      .catch(err => setError(err.message || t('preferences.loadFailed')))
      .finally(() => setLoading(false));
  }, []);

  const handleLanguage = async (code) => {
    if (prefs?.preferred_language === code) return;
    const prev = prefs;
    setPrefs(p => ({ ...p, preferred_language: code }));
    setLanguage(code);
    setSaving(true);
    setSaveSuccess(false);
    try {
      const res = await api.account.preferences.update({ preferred_language: code });
      setPrefs(res.data?.data ?? res.data);
      setSaveSuccess(true);
      setTimeout(() => setSaveSuccess(false), 2000);
    } catch (err) {
      setPrefs(prev);
      setLanguage(prev?.preferred_language || lang);
      setError(err.message || t('preferences.saveFailed'));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="flex items-center gap-2 text-neutral-500 py-10"><Loader2 size={18} className="animate-spin" /> {t('common.loading')}</div>;
  }

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold tracking-wide mb-1">{t('preferences.title')}</h2>
          <p className="text-sm text-neutral-500">{t('preferences.desc')}</p>
        </div>
        {saving && <Loader2 size={16} className="animate-spin text-neutral-500" />}
        {saveSuccess && !saving && (
          <div className="flex items-center gap-1 text-green-400 text-xs">
            <Check size={13} />
            {t('common.saved_indicator')}
          </div>
        )}
      </div>

      {error && <p className="text-sm text-red-400">{error}</p>}

      {/* Language */}
      <div className="space-y-3">
        <div className="flex items-center gap-2">
          <Globe size={16} className="text-neutral-500" />
          <h3 className="text-sm font-semibold text-neutral-300">{t('preferences.language')}</h3>
        </div>
        <p className="text-xs text-neutral-500">{t('preferences.languageDesc')}</p>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
          {LANGUAGES.map(({ code, labelKey, native }) => {
            const selected = (prefs?.preferred_language ?? lang) === code;
            return (
              <button
                key={code}
                onClick={() => handleLanguage(code)}
                disabled={saving}
                className={`rounded-2xl border px-4 py-4 text-start transition-colors disabled:opacity-50 ${
                  selected
                    ? 'border-white bg-white/5 text-white'
                    : 'border-neutral-800 text-neutral-400 hover:border-neutral-600 hover:text-neutral-200'
                }`}
              >
                <p className="text-sm font-semibold">{native}</p>
                <p className="text-xs mt-0.5 opacity-70">{t(labelKey)}</p>
                {selected && <div className="mt-2 h-1 w-4 rounded-full bg-white" />}
              </button>
            );
          })}
        </div>
      </div>

      <p className="text-xs text-neutral-600 border-t border-neutral-900 pt-6">
        {t('preferences.moreComingSoon')}
      </p>
    </div>
  );
};

export default PreferencesTab;
