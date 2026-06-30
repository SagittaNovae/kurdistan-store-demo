import React, { useEffect, useState } from 'react';
import { Home, Briefcase, MapPin, Plus, Trash2, Star, Loader2, X, Check } from 'lucide-react';
import MapPickerField, { isInsideIraq } from '../../components/MapPicker';
import { useI18n } from '../../context/I18nContext';
import api from '../../services/api';

const LABEL_ICONS = { Home, Work: Briefcase, Other: MapPin };
const LABELS = ['Home', 'Work', 'Other'];
const LABEL_KEYS = { Home: 'address.labelHome', Work: 'address.labelWork', Other: 'address.labelOther' };

const emptyForm = () => ({
  label: 'Home',
  nickname: '',
  governorate: '',
  city: '',
  address_line: '',
  address_text: '',
  latitude: null,
  longitude: null,
  is_default: false,
});

const AddressTab = () => {
  const { t } = useI18n();
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState(emptyForm());
  const [mapLocation, setMapLocation] = useState(null);
  const [zones, setZones] = useState({});
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState('');
  const [deletingId, setDeletingId] = useState(null);

  useEffect(() => {
    Promise.all([
      api.account.addresses.list(),
      api.shipping.zones(),
    ]).then(([addrRes, zonesRes]) => {
      setAddresses(addrRes.data || []);
      setZones(zonesRes.data || {});
    }).finally(() => setLoading(false));
  }, []);

  const cities = form.governorate ? (zones[form.governorate] || []).map(z => z.district).filter(Boolean) : [];

  const openNew = () => {
    setEditingId(null);
    setForm(emptyForm());
    setMapLocation(null);
    setFormError('');
    setShowForm(true);
  };

  const openEdit = (addr) => {
    setEditingId(addr.id);
    setForm({
      label: addr.label,
      nickname: addr.nickname || '',
      governorate: addr.governorate,
      city: addr.city,
      address_line: addr.address_line,
      address_text: addr.address_text || '',
      is_default: addr.is_default,
      latitude: addr.latitude,
      longitude: addr.longitude,
    });
    setMapLocation({ lat: addr.latitude, lng: addr.longitude });
    setFormError('');
    setShowForm(true);
  };

  const closeForm = () => { setShowForm(false); setEditingId(null); setForm(emptyForm()); setMapLocation(null); };

  const handleMapChange = ({ lat, lng, address }) => {
    setMapLocation({ lat, lng });
    setForm(p => ({ ...p, latitude: lat, longitude: lng, address_text: address || p.address_text }));
  };

  const handleSave = async (e) => {
    e.preventDefault();
    if (!mapLocation || !isInsideIraq(mapLocation.lat, mapLocation.lng)) {
      setFormError(t('address.mapRequired'));
      return;
    }
    if (!form.governorate) { setFormError(t('address.governorateRequired')); return; }
    if (!form.city) { setFormError(t('address.cityRequired')); return; }
    if (!form.address_line.trim()) { setFormError(t('address.addressRequired')); return; }

    setSaving(true);
    setFormError('');
    const payload = {
      label: form.label,
      nickname: form.nickname.trim() || null,
      address_text: form.address_text || null,
      governorate: form.governorate,
      city: form.city,
      address_line: form.address_line.trim(),
      latitude: mapLocation.lat,
      longitude: mapLocation.lng,
      is_default: form.is_default,
    };
    try {
      if (editingId) {
        const res = await api.account.addresses.update(editingId, payload);
        setAddresses(prev => prev.map(a => a.id === editingId ? res.data : a));
        if (payload.is_default) {
          setAddresses(prev => prev.map(a => ({ ...a, is_default: a.id === editingId })));
        }
      } else {
        const res = await api.account.addresses.create(payload);
        setAddresses(prev => {
          const updated = payload.is_default ? prev.map(a => ({ ...a, is_default: false })) : prev;
          return [...updated, res.data];
        });
      }
      closeForm();
    } catch (err) {
      setFormError(err.data?.errors
        ? Object.values(err.data.errors).flat().join(' ')
        : err.message || t('address.saveFailed'));
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id) => {
    setDeletingId(id);
    try {
      await api.account.addresses.delete(id);
      setAddresses(prev => {
        const remaining = prev.filter(a => a.id !== id);
        const deleted = prev.find(a => a.id === id);
        if (deleted?.is_default && remaining.length > 0) {
          remaining[0] = { ...remaining[0], is_default: true };
        }
        return remaining;
      });
    } catch (err) {
      console.error(err);
    } finally {
      setDeletingId(null);
    }
  };

  const handleSetDefault = async (id) => {
    try {
      await api.account.addresses.setDefault(id);
      setAddresses(prev => prev.map(a => ({ ...a, is_default: a.id === id })));
    } catch (err) {
      console.error(err);
    }
  };

  const inputClass = 'w-full bg-neutral-900 border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neutral-500 transition-colors';

  if (loading) {
    return <div className="flex items-center gap-2 text-neutral-500 py-10"><Loader2 size={18} className="animate-spin" /> {t('common.loading')}</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold tracking-wide">{t('address.title')}</h2>
          <p className="text-sm text-neutral-500">{t('address.count', { count: addresses.length })}</p>
        </div>
        {!showForm && addresses.length < 10 && (
          <button
            onClick={openNew}
            className="flex items-center gap-2 rounded-full border border-neutral-700 px-4 py-2 text-sm text-neutral-300 hover:border-white hover:text-white transition-colors"
          >
            <Plus size={15} />
            {t('address.addAddress')}
          </button>
        )}
      </div>

      {!showForm && addresses.length === 0 && (
        <div className="rounded-2xl border border-dashed border-neutral-800 p-10 text-center text-neutral-500">
          <MapPin size={28} className="mx-auto mb-3 opacity-40" />
          <p className="text-sm">{t('address.empty')}</p>
          <button onClick={openNew} className="mt-4 text-sm text-neutral-300 hover:text-white underline underline-offset-4 transition-colors">
            {t('address.addFirst')}
          </button>
        </div>
      )}

      {!showForm && (
        <div className="space-y-3">
          {addresses.map(addr => {
            const Icon = LABEL_ICONS[addr.label] || MapPin;
            return (
              <div
                key={addr.id}
                className={`rounded-2xl border p-5 transition-colors ${addr.is_default ? 'border-white/20 bg-neutral-900' : 'border-neutral-800 bg-neutral-950'}`}
              >
                <div className="flex items-start justify-between gap-3">
                  <div className="flex items-start gap-3">
                    <div className={`mt-0.5 rounded-lg p-2 ${addr.is_default ? 'bg-white/10' : 'bg-neutral-800'}`}>
                      <Icon size={15} className={addr.is_default ? 'text-white' : 'text-neutral-400'} />
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="text-sm font-semibold">{t(LABEL_KEYS[addr.label] || 'address.labelOther')}</span>
                        {addr.nickname && <span className="text-xs text-neutral-500">· {addr.nickname}</span>}
                        {addr.is_default && (
                          <span className="rounded-full bg-white/10 px-2 py-0.5 text-xs text-white">{t('address.defaultBadge')}</span>
                        )}
                      </div>
                      <p className="mt-0.5 text-sm text-neutral-400">{addr.city}, {addr.governorate}</p>
                      <p className="text-sm text-neutral-500">{addr.address_line}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2 shrink-0">
                    {!addr.is_default && (
                      <button
                        onClick={() => handleSetDefault(addr.id)}
                        title={t('address.setDefaultTooltip')}
                        className="rounded-lg p-2 text-neutral-500 hover:text-yellow-400 hover:bg-neutral-800 transition-colors"
                      >
                        <Star size={14} />
                      </button>
                    )}
                    <button
                      onClick={() => openEdit(addr)}
                      className="rounded-lg px-3 py-1.5 text-xs text-neutral-400 border border-neutral-800 hover:border-neutral-600 hover:text-white transition-colors"
                    >
                      {t('common.edit')}
                    </button>
                    <button
                      onClick={() => handleDelete(addr.id)}
                      disabled={deletingId === addr.id}
                      className="rounded-lg p-2 text-neutral-600 hover:text-red-400 hover:bg-neutral-800 disabled:opacity-50 transition-colors"
                    >
                      {deletingId === addr.id ? <Loader2 size={14} className="animate-spin" /> : <Trash2 size={14} />}
                    </button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Address form */}
      {showForm && (
        <form onSubmit={handleSave} className="space-y-6 rounded-2xl border border-neutral-800 bg-neutral-950 p-6">
          <div className="flex items-center justify-between">
            <h3 className="font-semibold">{editingId ? t('address.editAddress') : t('address.newAddress')}</h3>
            <button type="button" onClick={closeForm} className="text-neutral-500 hover:text-white transition-colors">
              <X size={18} />
            </button>
          </div>

          {/* Label select */}
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('address.type')}</label>
            <div className="flex gap-2">
              {LABELS.map(lbl => {
                const Icon = LABEL_ICONS[lbl];
                return (
                  <button
                    key={lbl}
                    type="button"
                    onClick={() => setForm(p => ({ ...p, label: lbl }))}
                    className={`flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm transition-colors ${
                      form.label === lbl ? 'border-white bg-white text-black' : 'border-neutral-800 text-neutral-400 hover:border-neutral-600'
                    }`}
                  >
                    <Icon size={14} />
                    {t(LABEL_KEYS[lbl])}
                  </button>
                );
              })}
            </div>
          </div>

          {/* Nickname */}
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest text-neutral-500">
              {t('address.nickname')} <span className="normal-case tracking-normal text-neutral-600">({t('address.nicknameOptional')})</span>
            </label>
            <input
              type="text"
              value={form.nickname}
              onChange={(e) => setForm(p => ({ ...p, nickname: e.target.value }))}
              placeholder={t('address.nicknamePlaceholder')}
              className={inputClass}
            />
          </div>

          {/* Governorate + City */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="space-y-2">
              <label className="text-xs uppercase tracking-widest text-neutral-500">{t('address.governorate')}</label>
              <select
                value={form.governorate}
                onChange={(e) => setForm(p => ({ ...p, governorate: e.target.value, city: '' }))}
                className={inputClass}
              >
                <option value="">{t('address.selectGovernorate')}</option>
                {Object.keys(zones).map(g => <option key={g} value={g}>{g}</option>)}
              </select>
            </div>
            <div className="space-y-2">
              <label className="text-xs uppercase tracking-widest text-neutral-500">{t('address.city')}</label>
              <select
                value={form.city}
                onChange={(e) => setForm(p => ({ ...p, city: e.target.value }))}
                disabled={!form.governorate}
                className={`${inputClass} disabled:opacity-50`}
              >
                <option value="">{t('address.selectCity')}</option>
                {cities.map(c => <option key={c} value={c}>{c}</option>)}
              </select>
            </div>
          </div>

          {/* Address line */}
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('address.addressLine')}</label>
            <input
              type="text"
              value={form.address_line}
              onChange={(e) => setForm(p => ({ ...p, address_line: e.target.value }))}
              placeholder={t('address.addressPlaceholder')}
              className={inputClass}
            />
          </div>

          {/* Map */}
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest text-neutral-500">{t('address.pinOnMap')}</label>
            <MapPickerField
              value={mapLocation}
              onChange={handleMapChange}
            />
          </div>

          {/* Set as default toggle */}
          <label className="flex items-center gap-3 cursor-pointer select-none">
            <input
              type="checkbox"
              checked={form.is_default}
              onChange={(e) => setForm(p => ({ ...p, is_default: e.target.checked }))}
              className="h-4 w-4 rounded border-neutral-700 bg-black accent-white"
            />
            <span className="text-sm text-neutral-400">{t('address.setDefault')}</span>
          </label>

          {formError && <p className="text-sm text-red-400">{formError}</p>}

          <div className="flex gap-3">
            <button
              type="submit"
              disabled={saving}
              className="flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold uppercase tracking-widest text-black hover:bg-neutral-200 disabled:opacity-50 transition-colors"
            >
              {saving ? <Loader2 size={15} className="animate-spin" /> : <Check size={15} />}
              {saving ? t('address.saving') : t('address.save')}
            </button>
            <button
              type="button"
              onClick={closeForm}
              className="rounded-full border border-neutral-700 px-6 py-3 text-sm text-neutral-400 hover:text-white hover:border-neutral-500 transition-colors"
            >
              {t('address.cancel')}
            </button>
          </div>
        </form>
      )}
    </div>
  );
};

export default AddressTab;
