import React from 'react';
import { HelpCircle } from 'lucide-react';
import { useI18n } from '../../context/I18nContext';

const SupportTab = () => {
  const { t } = useI18n();

  const faqs = [
    { qKey: 'support.faq1q', aKey: 'support.faq1a' },
    { qKey: 'support.faq2q', aKey: 'support.faq2a' },
    { qKey: 'support.faq3q', aKey: 'support.faq3a' },
    { qKey: 'support.faq4q', aKey: 'support.faq4a' },
    { qKey: 'support.faq5q', aKey: 'support.faq5a' },
  ];

  return (
    <div className="space-y-10">
      <div>
        <h2 className="text-lg font-semibold tracking-wide mb-1">{t('support.title')}</h2>
        <p className="text-sm text-neutral-500">{t('support.desc')}</p>
      </div>

      <div className="space-y-4">
        <div className="flex items-center gap-2">
          <HelpCircle size={16} className="text-neutral-500" />
          <h3 className="text-sm font-semibold text-neutral-300">{t('support.faqTitle')}</h3>
        </div>
        <div className="space-y-3">
          {faqs.map(({ qKey, aKey }, i) => (
            <details key={i} className="group rounded-2xl border border-neutral-800 overflow-hidden">
              <summary className="flex cursor-pointer items-center justify-between px-5 py-4 text-sm font-medium select-none hover:bg-neutral-900 transition-colors">
                {t(qKey)}
                <span className="ms-2 text-neutral-600 group-open:rotate-180 transition-transform duration-200">▾</span>
              </summary>
              <div className="border-t border-neutral-800 px-5 py-4 text-sm text-neutral-400 leading-relaxed">
                {t(aKey)}
              </div>
            </details>
          ))}
        </div>
      </div>
    </div>
  );
};

export default SupportTab;
