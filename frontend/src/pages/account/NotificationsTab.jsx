import React, { useEffect, useState } from 'react';
import { Bell, Loader2, Check } from 'lucide-react';
import api from '../../services/api';
import { useI18n } from '../../context/I18nContext';

const Toggle = ({ checked, onChange, disabled }) => (
  <button
    type="button"
    role="switch"
    aria-checked={checked}
    onClick={() => !disabled && onChange(!checked)}
    disabled={disabled}
    className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none disabled:opacity-50 ${
      checked ? 'bg-white' : 'bg-neutral-700'
    }`}
  >
    <span
      className={`inline-block h-4 w-4 transform rounded-full transition-transform ${
        checked ? 'translate-x-6 bg-black' : 'translate-x-1 bg-neutral-400'
      }`}
    />
  </button>
);

const NotificationsTab = () => {
  const { t } = useI18n();
  const [prefs, setPrefs] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    api.account.preferences.get()
      .then(res => setPrefs(res.data?.data ?? res.data))
      .catch(err => setError(err.message || t('notifications.loadFailed')))
      .finally(() => setLoading(false));
  }, []);

  const handleToggle = async (key, value) => {
    if (!prefs) return;
    const updated = { ...prefs, [key]: value };
    const prev = prefs;
    setPrefs(updated);
    setSaving(true);
    setSaveSuccess(false);
    setError('');
    try {
      const res = await api.account.preferences.update({ [key]: value });
      setPrefs(res.data?.data ?? res.data);
      setSaveSuccess(true);
      setTimeout(() => setSaveSuccess(false), 2000);
    } catch (err) {
      setPrefs(prev);
      setError(err.message || t('notifications.saveFailed'));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="flex items-center gap-2 text-neutral-500 py-10"><Loader2 size={18} className="animate-spin" /> {t('common.loading')}</div>;
  }

  const notifications = [
    {
      key: 'notify_order_updates',
      titleKey: 'notifications.orderUpdates',
      descKey: 'notifications.orderUpdatesDesc',
    },
    {
      key: 'notify_promotions',
      titleKey: 'notifications.promotions',
      descKey: 'notifications.promotionsDesc',
    },
  ];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold tracking-wide mb-1">{t('notifications.title')}</h2>
          <p className="text-sm text-neutral-500">{t('notifications.desc')}</p>
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

      <div className="space-y-3">
        {notifications.map(({ key, titleKey, descKey }) => (
          <div
            key={key}
            className="flex items-center justify-between rounded-2xl border border-neutral-800 px-5 py-4"
          >
            <div className="flex items-start gap-3">
              <div className="mt-0.5 rounded-xl bg-neutral-800 p-2.5">
                <Bell size={15} className="text-neutral-400" />
              </div>
              <div>
                <p className="text-sm font-medium">{t(titleKey)}</p>
                <p className="text-xs text-neutral-500 mt-0.5">{t(descKey)}</p>
              </div>
            </div>
            <Toggle
              checked={prefs?.[key] ?? false}
              onChange={(val) => handleToggle(key, val)}
              disabled={saving}
            />
          </div>
        ))}
      </div>

      <p className="text-xs text-neutral-600">
        {t('notifications.note')}
      </p>
    </div>
  );
};

export default NotificationsTab;
