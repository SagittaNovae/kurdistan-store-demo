import React, { useState } from 'react';
import { ArrowLeft, LockKeyhole, Mail, Phone } from 'lucide-react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useI18n } from '../context/I18nContext';
import LanguageSwitcher from '../components/LanguageSwitcher';

const LoginPage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();
  const location = useLocation();
  const { signIn, signUp } = useAuth();
  const [mode, setMode] = useState('login');
  const [formState, setFormState] = useState({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    password: '',
    confirmPassword: '',
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [remember, setRemember] = useState(false);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormState((prev) => ({ ...prev, [name]: value }));
    setError('');
  };

  const passwordMismatch =
    mode === 'signup' &&
    formState.confirmPassword.length > 0 &&
    formState.password !== formState.confirmPassword;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      if (mode === 'login') {
        await signIn({ phone: formState.phone, password: formState.password, remember });
        navigate(location.state?.from || '/', { replace: true });
      } else {
        if (!formState.firstName.trim()) throw new Error(t('auth.firstNameRequired'));
        if (!formState.lastName.trim()) throw new Error(t('auth.lastNameRequired'));
        if (formState.password !== formState.confirmPassword) {
          throw new Error(t('common.passwordMismatch'));
        }
        await signUp({
          firstName: formState.firstName,
          lastName: formState.lastName,
          phone: formState.phone,
          email: formState.email || undefined,
          password: formState.password,
        });
        navigate(location.state?.from || '/', { replace: true });
      }
    } catch (err) {
      setError(err.data?.errors
        ? Object.values(err.data.errors).flat().join(' ')
        : err.message || t('common.somethingWentWrong'));
    } finally {
      setLoading(false);
    }
  };

  const switchMode = (next) => {
    setMode(next);
    setError('');
    setFormState({ firstName: '', lastName: '', phone: '', email: '', password: '', confirmPassword: '' });
  };

  return (
    <div className="min-h-screen bg-black text-white px-6 py-10 font-sans">
      <LanguageSwitcher className="fixed top-4 end-4 z-50" />
      <div className="mx-auto flex min-h-[calc(100vh-5rem)] max-w-6xl items-center">
        <div className="grid w-full gap-10 lg:grid-cols-[1.15fr_0.85fr]">

          {/* Left panel */}
          <section className="flex flex-col justify-between rounded-[2rem] border border-neutral-900 bg-neutral-950 p-8 md:p-12">
            <div className="space-y-8">
              <button
                onClick={() => navigate('/', { replace: true })}
                className="inline-flex items-center gap-2 text-sm text-neutral-400 transition-colors hover:text-white"
              >
                <ArrowLeft size={18} className="rtl:rotate-180" />
                <span>{t('auth.returnHome')}</span>
              </button>

              <div className="space-y-4">
                <p className="text-xs uppercase tracking-[0.35em] text-neutral-500">
                  {t('auth.yourAccount')}
                </p>
                <h1 className="max-w-xl text-4xl font-semibold tracking-tight md:text-6xl">
                  {mode === 'login' ? t('auth.welcomeBack') : t('auth.joinStore')}
                </h1>
                <p className="max-w-xl text-base leading-7 text-neutral-400">
                  {mode === 'login' ? t('auth.signInDesc') : t('auth.signUpDesc')}
                </p>
              </div>
            </div>

            <p className="mt-8 text-sm text-neutral-500">
              {t('auth.passwordSecurity')}
            </p>
          </section>

          {/* Right panel */}
          <section className="rounded-[2rem] border border-neutral-900 bg-neutral-900/80 p-8 shadow-2xl backdrop-blur md:p-10">
              {/* Tab switcher */}
            <div className="mb-8 flex gap-3 rounded-full border border-neutral-800 bg-black p-1">
              <button
                type="button"
                onClick={() => switchMode('login')}
                className={`flex-1 rounded-full px-4 py-3 text-sm font-medium transition-colors ${
                  mode === 'login' ? 'bg-white text-black' : 'text-neutral-400 hover:text-white'
                }`}
              >
                {t('auth.signIn')}
              </button>
              <button
                type="button"
                onClick={() => switchMode('signup')}
                className={`flex-1 rounded-full px-4 py-3 text-sm font-medium transition-colors ${
                  mode === 'signup' ? 'bg-white text-black' : 'text-neutral-400 hover:text-white'
                }`}
              >
                {t('auth.createAccount')}
              </button>
            </div>

            <form className="space-y-5" onSubmit={handleSubmit}>
              {mode === 'signup' && (
                <div className="grid grid-cols-2 gap-3">
                  <label className="block space-y-2">
                    <span className="text-xs uppercase tracking-[0.25em] text-neutral-500">{t('common.ui.firstName')}</span>
                    <div className="flex items-center gap-3 rounded-2xl border border-neutral-800 bg-black px-4 py-3">
                      <input
                        type="text"
                        name="firstName"
                        value={formState.firstName}
                        onChange={handleInputChange}
                        className="w-full bg-transparent text-white outline-none placeholder:text-neutral-600"
                        placeholder={t('auth.firstNamePlaceholder')}
                        required
                      />
                    </div>
                  </label>
                  <label className="block space-y-2">
                    <span className="text-xs uppercase tracking-[0.25em] text-neutral-500">{t('common.ui.lastName')}</span>
                    <div className="flex items-center gap-3 rounded-2xl border border-neutral-800 bg-black px-4 py-3">
                      <input
                        type="text"
                        name="lastName"
                        value={formState.lastName}
                        onChange={handleInputChange}
                        className="w-full bg-transparent text-white outline-none placeholder:text-neutral-600"
                        placeholder={t('auth.lastNamePlaceholder')}
                        required
                      />
                    </div>
                  </label>
                </div>
              )}

              <label className="block space-y-2">
                <span className="text-xs uppercase tracking-[0.25em] text-neutral-500">{t('common.ui.phoneNumber')}</span>
                <div className="flex items-center gap-3 rounded-2xl border border-neutral-800 bg-black px-4 py-3">
                  <Phone size={18} className="text-neutral-500 shrink-0" />
                  <input
                    type="tel"
                    name="phone"
                    value={formState.phone}
                    onChange={handleInputChange}
                    className="w-full bg-transparent text-white outline-none placeholder:text-neutral-600"
                    placeholder={t('auth.phonePlaceholder')}
                    required
                  />
                </div>
              </label>

              {mode === 'signup' && (
                <label className="block space-y-2">
                  <span className="text-xs uppercase tracking-[0.25em] text-neutral-500">
                    {t('auth.email')} <span className="text-neutral-600 normal-case tracking-normal">({t('auth.emailOptional')})</span>
                  </span>
                  <div className="flex items-center gap-3 rounded-2xl border border-neutral-800 bg-black px-4 py-3">
                    <Mail size={18} className="text-neutral-500 shrink-0" />
                    <input
                      type="email"
                      name="email"
                      value={formState.email}
                      onChange={handleInputChange}
                      className="w-full bg-transparent text-white outline-none placeholder:text-neutral-600"
                      placeholder={t('auth.emailPlaceholder')}
                    />
                  </div>
                </label>
              )}

              <label className="block space-y-2">
                <span className="text-xs uppercase tracking-[0.25em] text-neutral-500">{t('auth.password')}</span>
                <div className="flex items-center gap-3 rounded-2xl border border-neutral-800 bg-black px-4 py-3">
                  <LockKeyhole size={18} className="text-neutral-500 shrink-0" />
                  <input
                    type="password"
                    name="password"
                    value={formState.password}
                    onChange={handleInputChange}
                    className="w-full bg-transparent text-white outline-none placeholder:text-neutral-600"
                    placeholder={mode === 'signup' ? t('auth.passwordNewPlaceholder') : t('auth.passwordPlaceholder')}
                    minLength={mode === 'signup' ? 8 : undefined}
                    required
                  />
                </div>
              </label>

              {mode === 'login' && (
                <div className="-mt-2">
                  <label className="flex cursor-pointer items-center gap-2 select-none">
                    <input
                      type="checkbox"
                      checked={remember}
                      onChange={(e) => setRemember(e.target.checked)}
                      className="h-4 w-4 rounded border-neutral-700 bg-black accent-white cursor-pointer"
                    />
                    <span className="text-xs text-neutral-500">{t('auth.rememberMe')}</span>
                  </label>
                </div>
              )}

              {mode === 'signup' && (
                <label className="block space-y-2">
                  <span className="text-xs uppercase tracking-[0.25em] text-neutral-500">{t('auth.confirmPassword')}</span>
                  <div className={`flex items-center gap-3 rounded-2xl border bg-black px-4 py-3 transition-colors ${
                    passwordMismatch ? 'border-red-500/60' : 'border-neutral-800'
                  }`}>
                    <LockKeyhole size={18} className={`shrink-0 transition-colors ${passwordMismatch ? 'text-red-400' : 'text-neutral-500'}`} />
                    <input
                      type="password"
                      name="confirmPassword"
                      value={formState.confirmPassword}
                      onChange={handleInputChange}
                      className="w-full bg-transparent text-white outline-none placeholder:text-neutral-600"
                      placeholder={t('auth.confirmPlaceholder')}
                      required
                    />
                  </div>
                  {passwordMismatch && (
                    <p className="text-xs text-red-400 mt-1">{t('common.passwordMismatch')}</p>
                  )}
                </label>
              )}

              {error && (
                <div className="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                  {error}
                </div>
              )}

              <button
                type="submit"
                disabled={loading || passwordMismatch}
                className="w-full rounded-full bg-white px-5 py-4 text-sm font-semibold uppercase tracking-[0.25em] text-black transition-colors hover:bg-neutral-200 disabled:opacity-50"
              >
                {loading
                  ? (mode === 'login' ? t('auth.signingIn') : t('auth.creatingAccount'))
                  : (mode === 'login' ? t('auth.signIn') : t('auth.createAccount'))}
              </button>
            </form>

            <p className="mt-6 text-center text-sm text-neutral-500">
              {t('auth.browseCta')}{' '}
              <Link className="text-neutral-300 hover:text-white" to="/">
                {t('auth.browseLink')}
              </Link>
              .
            </p>
          </section>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
