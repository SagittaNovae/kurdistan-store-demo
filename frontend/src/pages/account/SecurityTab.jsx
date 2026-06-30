import React, { useState } from 'react';
import { Shield, Loader2, AlertTriangle, Check, LockKeyhole } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useI18n } from '../../context/I18nContext';
import api from '../../services/api';

const SecurityTab = () => {
  const { t } = useI18n();
  const { signOut } = useAuth();
  const navigate = useNavigate();

  const [pwForm, setPwForm] = useState({ current_password: '', new_password: '', new_password_confirmation: '' });
  const [pwSaving, setPwSaving] = useState(false);
  const [pwSuccess, setPwSuccess] = useState(false);
  const [pwError, setPwError] = useState('');

  const pwMismatch =
    pwForm.new_password_confirmation.length > 0 &&
    pwForm.new_password !== pwForm.new_password_confirmation;

  const handleChangePassword = async (e) => {
    e.preventDefault();
    if (pwMismatch) return;
    setPwSaving(true);
    setPwError('');
    setPwSuccess(false);
    try {
      await api.account.changePassword({
        current_password: pwForm.current_password,
        new_password: pwForm.new_password,
        new_password_confirmation: pwForm.new_password_confirmation,
      });
      setPwSuccess(true);
      setPwForm({ current_password: '', new_password: '', new_password_confirmation: '' });
      setTimeout(() => setPwSuccess(false), 4000);
    } catch (err) {
      setPwError(err.data?.errors
        ? Object.values(err.data.errors).flat().join(' ')
        : err.message || t('security.passwordChangeFailed'));
    } finally {
      setPwSaving(false);
    }
  };

  const [loggingOutAll, setLoggingOutAll] = useState(false);
  const [logoutAllSuccess, setLogoutAllSuccess] = useState(false);
  const [logoutAllError, setLogoutAllError] = useState('');
  const [showConfirm, setShowConfirm] = useState(false);

  const handleLogoutAll = async () => {
    setLoggingOutAll(true);
    setLogoutAllError('');
    try {
      await api.account.logoutAll();
      setLogoutAllSuccess(true);
      setShowConfirm(false);
      setTimeout(async () => {
        await signOut();
        navigate('/login');
      }, 1500);
    } catch (err) {
      setLogoutAllError(err.message || t('security.failed'));
    } finally {
      setLoggingOutAll(false);
    }
  };

  const inputClass = 'w-full bg-neutral-900 border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neutral-500 transition-colors';

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-lg font-semibold tracking-wide mb-1">{t('security.title')}</h2>
        <p className="text-sm text-neutral-500">{t('security.desc')}</p>
      </div>

      {/* Change Password */}
      <form onSubmit={handleChangePassword} className="space-y-4 rounded-2xl border border-neutral-800 p-5">
        <div className="flex items-start gap-3 mb-4">
          <div className="rounded-xl bg-neutral-800 p-2.5 mt-0.5">
            <LockKeyhole size={16} className="text-neutral-400" />
          </div>
          <div>
            <p className="font-medium text-sm">{t('security.changePassword')}</p>
            <p className="text-xs text-neutral-500 mt-0.5">{t('security.changePasswordDesc')}</p>
          </div>
        </div>

        <div className="space-y-3">
          <div className="space-y-1.5">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('security.currentPassword')}</label>
            <input
              type="password"
              value={pwForm.current_password}
              onChange={(e) => setPwForm(p => ({ ...p, current_password: e.target.value }))}
              className={inputClass}
              required
              autoComplete="current-password"
            />
          </div>
          <div className="space-y-1.5">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('security.newPassword')}</label>
            <input
              type="password"
              value={pwForm.new_password}
              onChange={(e) => setPwForm(p => ({ ...p, new_password: e.target.value }))}
              className={inputClass}
              required
              minLength={8}
              autoComplete="new-password"
            />
          </div>
          <div className="space-y-1.5">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('security.confirmNewPassword')}</label>
            <input
              type="password"
              value={pwForm.new_password_confirmation}
              onChange={(e) => setPwForm(p => ({ ...p, new_password_confirmation: e.target.value }))}
              className={`${inputClass} ${pwMismatch ? 'border-red-500/60' : ''}`}
              required
              autoComplete="new-password"
            />
            {pwMismatch && <p className="text-xs text-red-400">{t('common.passwordMismatch')}</p>}
          </div>
        </div>

        {pwError && <p className="text-sm text-red-400">{pwError}</p>}
        {pwSuccess && (
          <div className="flex items-center gap-2 text-green-400 text-sm">
            <Check size={14} />
            {t('security.passwordChanged')}
          </div>
        )}

        <button
          type="submit"
          disabled={pwSaving || pwMismatch}
          className="flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold uppercase tracking-widest text-black hover:bg-neutral-200 disabled:opacity-50 transition-colors"
        >
          {pwSaving ? <Loader2 size={14} className="animate-spin" /> : null}
          {pwSaving ? t('security.changingPassword') : t('security.changePassword')}
        </button>
      </form>

      <hr className="border-neutral-800" />

      <div className={`rounded-2xl border p-5 space-y-3 ${showConfirm ? 'border-red-500/30 bg-red-500/5' : 'border-neutral-800'}`}>
        <div className="flex items-start gap-3">
          <div className={`rounded-xl p-2.5 mt-0.5 ${showConfirm ? 'bg-red-500/10' : 'bg-neutral-800'}`}>
            <Shield size={16} className={showConfirm ? 'text-red-400' : 'text-neutral-400'} />
          </div>
          <div>
            <p className="font-medium text-sm">{t('security.signOutAll')}</p>
            <p className="text-xs text-neutral-500 mt-0.5">
              {t('security.signOutAllDesc')}
            </p>
          </div>
        </div>

        {logoutAllSuccess && (
          <div className="flex items-center gap-2 text-green-400 text-sm">
            <Check size={14} />
            {t('security.signedOutAll')}
          </div>
        )}

        {logoutAllError && (
          <p className="text-sm text-red-400">{logoutAllError}</p>
        )}

        {!showConfirm && !logoutAllSuccess && (
          <button
            onClick={() => setShowConfirm(true)}
            className="rounded-full border border-red-500/30 px-5 py-2.5 text-sm text-red-400 hover:border-red-500/60 hover:text-red-300 transition-colors"
          >
            {t('security.signOutBtn')}
          </button>
        )}

        {showConfirm && !logoutAllSuccess && (
          <div className="space-y-3">
            <div className="flex items-center gap-2 text-sm text-yellow-400">
              <AlertTriangle size={14} />
              {t('security.confirmWarning')}
            </div>
            <div className="flex gap-3">
              <button
                onClick={handleLogoutAll}
                disabled={loggingOutAll}
                className="flex items-center gap-2 rounded-full bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-50 transition-colors"
              >
                {loggingOutAll ? <Loader2 size={14} className="animate-spin" /> : null}
                {loggingOutAll ? t('security.signingOut') : t('security.confirmYes')}
              </button>
              <button
                onClick={() => setShowConfirm(false)}
                className="rounded-full border border-neutral-700 px-5 py-2.5 text-sm text-neutral-400 hover:text-white transition-colors"
              >
                {t('security.cancel')}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default SecurityTab;
