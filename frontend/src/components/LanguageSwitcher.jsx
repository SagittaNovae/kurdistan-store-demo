import { useState, useRef, useEffect } from 'react';
import { Globe } from 'lucide-react';
import { useI18n } from '../context/I18nContext';

const LANGS = [
  { code: 'en', label: 'EN', name: 'English' },
  { code: 'ku', label: 'KU', name: 'کوردی' },
  { code: 'ar', label: 'AR', name: 'العربية' },
];

/**
 * Compact language picker — works for guests (localStorage) and logged-in users.
 * Renders a globe-icon button with a small dropdown. Dropdown aligns to `end` so it
 * correctly opens to the left in Arabic RTL mode.
 */
const LanguageSwitcher = ({ className = '' }) => {
  const { lang, setLanguage } = useI18n();
  const [open, setOpen] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    if (!open) return;
    const close = (e) => { if (ref.current && !ref.current.contains(e.target)) setOpen(false); };
    document.addEventListener('mousedown', close);
    return () => document.removeEventListener('mousedown', close);
  }, [open]);

  return (
    <div ref={ref} className={`relative ${className}`}>
      <button
        type="button"
        onClick={() => setOpen(p => !p)}
        className="flex items-center gap-1.5 rounded-full border border-neutral-800 bg-black px-3 py-2 text-xs font-medium uppercase tracking-wider text-neutral-400 transition-colors hover:border-neutral-600 hover:text-white"
        aria-label="Select language"
      >
        <Globe size={13} className="shrink-0" />
        {lang.toUpperCase()}
      </button>

      {open && (
        <div className="absolute end-0 top-full z-50 mt-1.5 w-36 overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 shadow-2xl">
          {LANGS.map(l => (
            <button
              key={l.code}
              type="button"
              onClick={() => { setLanguage(l.code); setOpen(false); }}
              className={`flex w-full items-center gap-2.5 px-4 py-2.5 text-sm transition-colors hover:bg-neutral-800/80 ${
                lang === l.code
                  ? 'bg-neutral-900 text-white font-semibold'
                  : 'text-neutral-400 hover:text-white'
              }`}
            >
              <span className="w-6 shrink-0 font-mono text-[10px] text-neutral-600">{l.label}</span>
              {l.name}
            </button>
          ))}
        </div>
      )}
    </div>
  );
};

export default LanguageSwitcher;
