import React, { useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import {
  ArrowLeft, User, MapPin, Package, Shield,
  Bell, Settings, HelpCircle, LogOut,
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useI18n } from '../context/I18nContext';
import LanguageSwitcher from '../components/LanguageSwitcher';
import ProfileTab from './account/ProfileTab';
import AddressTab from './account/AddressTab';
import OrdersTab from './account/OrdersTab';
import SecurityTab from './account/SecurityTab';
import NotificationsTab from './account/NotificationsTab';
import PreferencesTab from './account/PreferencesTab';
import SupportTab from './account/SupportTab';

const TAB_DEFS = [
  { id: 'profile',       key: 'account.profile',       Icon: User,        Component: ProfileTab },
  { id: 'addresses',     key: 'account.addresses',     Icon: MapPin,      Component: AddressTab },
  { id: 'orders',        key: 'account.orders',        Icon: Package,     Component: OrdersTab },
  { id: 'security',      key: 'account.security',      Icon: Shield,      Component: SecurityTab },
  { id: 'notifications', key: 'account.notifications', Icon: Bell,        Component: NotificationsTab },
  { id: 'preferences',   key: 'account.preferences',   Icon: Settings,    Component: PreferencesTab },
  { id: 'support',       key: 'account.support',       Icon: HelpCircle,  Component: SupportTab },
];

const AccountPage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();
  const { currentUser, signOut } = useAuth();
  const [searchParams] = useSearchParams();
  const [activeTab, setActiveTab] = useState(
    TAB_DEFS.find(tab => tab.id === searchParams.get('tab'))?.id || 'profile'
  );

  if (!currentUser) {
    navigate('/login', { replace: true });
    return null;
  }

  const active = TAB_DEFS.find(tab => tab.id === activeTab) || TAB_DEFS[0];
  const ActiveComponent = active.Component;

  return (
    <div className="min-h-screen bg-black text-white font-sans">
      <header className="fixed top-0 left-0 w-full z-40 bg-black/90 backdrop-blur-md border-b border-neutral-900">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center gap-4">
          <button
            onClick={() => navigate('/')}
            className="flex items-center gap-2 text-neutral-400 hover:text-white transition-colors"
          >
            <ArrowLeft size={18} className="rtl:rotate-180" />
            <span className="text-sm hidden sm:inline">{t('common.nav.home')}</span>
          </button>
          <div className="flex-1">
            <p className="text-xs uppercase tracking-[0.3em] text-neutral-600">{t('common.nav.myAccount')}</p>
            <h1 className="text-base font-bold tracking-wide leading-tight">{currentUser.name}</h1>
          </div>
          <LanguageSwitcher />
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 pt-28 pb-20">
        <div className="flex gap-8">

          {/* Desktop sidebar */}
          <aside className="hidden lg:flex flex-col w-52 shrink-0 pt-2">
            <div className="flex flex-col gap-1 flex-1">
              {TAB_DEFS.map(({ id, key, Icon }) => (
                <button
                  key={id}
                  onClick={() => setActiveTab(id)}
                  className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-start transition-colors ${
                    activeTab === id
                      ? 'bg-neutral-900 text-white font-medium'
                      : 'text-neutral-500 hover:text-neutral-200 hover:bg-neutral-900/50'
                  }`}
                >
                  <Icon size={16} className={activeTab === id ? 'text-white' : 'text-neutral-600'} />
                  {t(key)}
                </button>
              ))}
            </div>
            <div className="mt-6 pt-4 border-t border-neutral-800/60">
              <button
                onClick={async () => { await signOut(); navigate('/login'); }}
                className="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm text-start text-red-500/80 hover:text-red-400 hover:bg-red-500/5 transition-colors"
              >
                <LogOut size={16} className="text-red-500/70" />
                {t('account.logOut')}
              </button>
            </div>
          </aside>

          {/* Main content */}
          <div className="flex-1 min-w-0">
            {/* Mobile horizontal tab bar */}
            <div className="lg:hidden mb-6 overflow-x-auto">
              <div className="flex gap-1 min-w-max border-b border-neutral-900 pb-0">
                {TAB_DEFS.map(({ id, key, Icon }) => (
                  <button
                    key={id}
                    onClick={() => setActiveTab(id)}
                    className={`flex items-center gap-1.5 px-3 py-3 text-xs whitespace-nowrap border-b-2 transition-colors ${
                      activeTab === id
                        ? 'border-white text-white font-semibold'
                        : 'border-transparent text-neutral-500 hover:text-neutral-300'
                    }`}
                  >
                    <Icon size={13} />
                    {t(key)}
                  </button>
                ))}
              </div>
            </div>

            <ActiveComponent />

            {/* Mobile logout section */}
            <div className="lg:hidden mt-10 pt-6 border-t border-neutral-800/60">
              <button
                onClick={async () => { await signOut(); navigate('/login'); }}
                className="flex items-center gap-2.5 text-sm text-red-500/80 hover:text-red-400 transition-colors"
              >
                <LogOut size={15} />
                {t('account.logOut')}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AccountPage;
