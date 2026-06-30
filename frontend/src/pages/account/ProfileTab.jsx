import React, { useEffect, useState } from 'react';
import { Check, Loader2 } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useI18n } from '../../context/I18nContext';
import api from '../../services/api';

const ProfileTab = () => {
  const { t } = useI18n();
  const { currentUser, refreshUser } = useAuth();

  const [form, setForm] = useState({ first_name: '', last_name: '', email: '' });
  const [saving, setSaving] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);
  const [saveError, setSaveError] = useState('');

  useEffect(() => {
    if (currentUser) {
      const parts = (currentUser.name || '').split(' ');
      setForm({
        first_name: currentUser.first_name || parts[0] || '',
        last_name: currentUser.last_name || parts.slice(1).join(' ') || '',
        email: currentUser.email || '',
      });
    }
  }, [currentUser]);

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setSaveError('');
    setSaveSuccess(false);
    try {
      await api.account.profile.update({
        first_name: form.first_name.trim(),
        last_name: form.last_name.trim(),
        email: form.email.trim() || null,
      });
      await refreshUser();
      setSaveSuccess(true);
      setTimeout(() => setSaveSuccess(false), 3000);
    } catch (err) {
      setSaveError(err.data?.errors
        ? Object.values(err.data.errors).flat().join(' ')
        : err.message || t('profile.saveFailed'));
    } finally {
      setSaving(false);
    }
  };

  const inputClass = 'w-full bg-neutral-900 border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neutral-500 transition-colors';

  return (
    <div className="space-y-10">
      <form onSubmit={handleSave} className="space-y-6">
        <div>
          <h2 className="text-lg font-semibold tracking-wide mb-1">{t('profile.title')}</h2>
          <p className="text-sm text-neutral-500">{t('profile.desc')}</p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('common.ui.firstName')}</label>
            <input
              type="text"
              value={form.first_name}
              onChange={(e) => setForm(p => ({ ...p, first_name: e.target.value }))}
              className={inputClass}
              required
            />
          </div>
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('common.ui.lastName')}</label>
            <input
              type="text"
              value={form.last_name}
              onChange={(e) => setForm(p => ({ ...p, last_name: e.target.value }))}
              className={inputClass}
              required
            />
          </div>
        </div>

        <div className="space-y-2">
          <label className="text-xs uppercase tracking-widest text-neutral-500">
            {t('profile.email')} <span className="normal-case tracking-normal text-neutral-600">({t('profile.emailOptional')})</span>
          </label>
          <input
            type="email"
            value={form.email}
            onChange={(e) => setForm(p => ({ ...p, email: e.target.value }))}
            className={inputClass}
            placeholder={t('profile.emailPlaceholder')}
          />
        </div>

        {saveError && <p className="text-sm text-red-400">{saveError}</p>}

        <button
          type="submit"
          disabled={saving}
          className="flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold uppercase tracking-widest text-black hover:bg-neutral-200 disabled:opacity-50 transition-colors"
        >
          {saving ? <Loader2 size={16} className="animate-spin" /> : saveSuccess ? <Check size={16} /> : null}
          {saving ? t('profile.saving') : saveSuccess ? t('profile.saved') : t('profile.saveChanges')}
        </button>
      </form>

      <hr className="border-neutral-800" />

      {/* Phone — read-only (used as login identifier) */}
      <div className="space-y-2">
        <h2 className="text-lg font-semibold tracking-wide">{t('common.ui.phoneNumber')}</h2>
        <p className="text-sm text-neutral-500">
          {t('profile.currentPhone', { phone: currentUser?.phone || '—' })}
        </p>
        <p className="text-xs text-neutral-600">{t('profile.phoneReadOnly')}</p>
      </div>
    </div>
  );
};

export default ProfileTab;
