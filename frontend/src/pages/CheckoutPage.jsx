import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useI18n } from '../context/I18nContext';
import api from '../services/api';
import { ArrowLeft, Check, CreditCard, MapPin, Truck, Home, Briefcase, Star } from 'lucide-react';
import LanguageSwitcher from '../components/LanguageSwitcher';
import MapPickerField, { isInsideIraq } from '../components/MapPicker';
import { formatIQD } from '../utils/format';

const LABEL_KEYS = { Home: 'address.labelHome', Work: 'address.labelWork', Other: 'address.labelOther' };

const CheckoutPage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();
  const { cart, cartTotal, cartMeta, clearCart, placeOrder } = useCart();
  const { currentUser } = useAuth();

  const [formData, setFormData] = useState(() => ({
    firstName: currentUser?.name?.split(' ')[0] || '',
    lastName: currentUser?.name?.split(' ').slice(1).join(' ') || '',
    phone: currentUser?.phone || '',
    governorate: '',
    city: '',
    address: '',
    notes: '',
  }));

  const [zones, setZones] = useState({});
  const [savedAddresses, setSavedAddresses] = useState([]);
  const [selectedSavedId, setSelectedSavedId] = useState(null);
  const [mapLocation, setMapLocation] = useState(null);
  const [mapFlyTo, setMapFlyTo] = useState(null);

  useEffect(() => {
    api.shipping.zones().then(res => setZones(res.data || {})).catch(() => {});
  }, []);

  const applySavedAddress = (addr) => {
    setSelectedSavedId(addr.id);
    setFormData(prev => ({
      ...prev,
      governorate: addr.governorate,
      city: addr.city,
      address: addr.address_line,
    }));
    const loc = { lat: addr.latitude, lng: addr.longitude, address: addr.address_text || null };
    setMapLocation(loc);
    setMapFlyTo({ lat: addr.latitude, lng: addr.longitude, _t: Date.now() });
  };

  useEffect(() => {
    if (!currentUser) return;
    setFormData(prev => ({
      ...prev,
      firstName: prev.firstName || currentUser.name?.split(' ')[0] || '',
      lastName:  prev.lastName  || currentUser.name?.split(' ').slice(1).join(' ') || '',
      phone:     prev.phone     || currentUser.phone || '',
    }));
    api.account.addresses.list()
      .then(res => {
        const addrs = res.data || [];
        setSavedAddresses(addrs);
        const def = addrs.find(a => a.is_default) || addrs[0];
        if (def) applySavedAddress(def);
      })
      .catch(() => {});
  }, [currentUser?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const selectedZone = (() => {
    if (!formData.governorate || !zones[formData.governorate]) return null;
    const govZones = zones[formData.governorate];
    return (formData.city ? govZones.find(z => z.district === formData.city) : null)
      || govZones.find(z => z.district === null)
      || null;
  })();

  const [paymentMethod, setPaymentMethod] = useState('cod');
  const [errors, setErrors] = useState({});
  const [orderError, setOrderError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  if (cart.length === 0) {
    return (
      <div className="min-h-screen bg-black text-white flex flex-col items-center justify-center p-6 text-center">
        <h1 className="text-3xl font-bold mb-4">{t('checkout.emptyTitle')}</h1>
        <p className="text-neutral-400 mb-8">{t('checkout.emptyDesc')}</p>
        <button
          onClick={() => navigate('/browse')}
          className="px-8 py-3 bg-white text-black rounded-full font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors"
        >
          {t('checkout.returnToShop')}
        </button>
      </div>
    );
  }

  const shippingCost = selectedZone?.rate ?? cartMeta?.shipping ?? 0;
  const totalAmount = cartMeta?.grandTotal || cartTotal + shippingCost;

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (errors[name]) setErrors(prev => ({ ...prev, [name]: '' }));
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.firstName.trim()) newErrors.firstName = t('checkout.errors.firstName');
    if (!formData.lastName.trim()) newErrors.lastName = t('checkout.errors.lastName');
    if (!formData.phone.trim()) newErrors.phone = t('checkout.errors.phone');
    if (!formData.governorate) newErrors.governorate = t('checkout.errors.governorate');
    if (!formData.city) newErrors.city = t('checkout.errors.city');
    if (!formData.address.trim()) newErrors.address = t('checkout.errors.address');
    if (!mapLocation) {
      newErrors.mapLocation = t('checkout.errors.mapRequired');
    } else if (!isInsideIraq(mapLocation.lat, mapLocation.lng)) {
      newErrors.mapLocation = t('checkout.errors.mapOutOfIraq');
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!validateForm()) return;
    processOrder();
  };

  const processOrder = async () => {
    setSubmitting(true);
    setOrderError('');
    try {
      const payload = {
        first_name: formData.firstName,
        last_name: formData.lastName,
        phone: formData.phone,
        governorate: formData.governorate,
        city: formData.city,
        address: formData.address,
        notes: formData.notes,
        payment_method: 'cashondelivery',
        delivery_latitude: mapLocation?.lat,
        delivery_longitude: mapLocation?.lng,
        delivery_address_text: mapLocation?.address || null,
      };
      await placeOrder(payload);
      clearCart();
      navigate('/success');
    } catch (err) {
      const status = err.status ? ` (${err.status})` : '';
      setOrderError((err.message || t('checkout.errors.orderFailed')) + status);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="bg-black text-white min-h-screen font-sans selection:bg-neutral-800">
      <header className="fixed top-0 left-0 w-full z-40 bg-black/80 backdrop-blur-md border-b border-neutral-900">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <button
            onClick={() => navigate(-1)}
            className="flex items-center gap-2 text-neutral-400 hover:text-white transition-colors group"
          >
            <ArrowLeft size={20} className="transition-transform rtl:rotate-180 group-hover:-translate-x-1 rtl:group-hover:translate-x-1" />
            <span className="text-sm uppercase tracking-wider hidden sm:inline">{t('common.nav.backToCart')}</span>
          </button>

          <h1 className="text-xl font-bold tracking-widest text-white">{t('checkout.title')}</h1>

          <LanguageSwitcher />
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-6 pt-32 pb-20">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:gap-24">

          {/* Checkout Form */}
          <div className="lg:col-span-2 space-y-12">
            <form id="checkout-form" onSubmit={handleSubmit} className="space-y-12">

              {/* Shipping Information */}
              <section className="space-y-6">
                <div className="flex items-center gap-3 border-b border-neutral-800 pb-4">
                  <MapPin size={20} className="text-neutral-400" />
                  <h2 className="text-xl font-bold tracking-wide">{t('checkout.shippingSection')}</h2>
                </div>

                {currentUser && (
                  <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 px-4 py-4 text-sm text-neutral-400">
                    {t('checkout.orderingAs', { name: `${currentUser.name}${currentUser.email ? ` (${currentUser.email})` : ''}` })}
                  </div>
                )}

                {/* Saved address cards */}
                {currentUser && savedAddresses.length > 0 && (
                  <div className="space-y-3">
                    <p className="text-xs uppercase tracking-widest text-neutral-500">{t('checkout.savedAddresses')}</p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      {savedAddresses.map(addr => {
                        const LabelIcon = addr.label === 'Work' ? Briefcase : addr.label === 'Other' ? MapPin : Home;
                        const selected = selectedSavedId === addr.id;
                        return (
                          <button
                            key={addr.id}
                            type="button"
                            onClick={() => applySavedAddress(addr)}
                            className={`rounded-xl border px-4 py-3 text-start transition-colors ${
                              selected ? 'border-white bg-neutral-900' : 'border-neutral-800 hover:border-neutral-600'
                            }`}
                          >
                            <div className="flex items-center gap-2 mb-1">
                              <LabelIcon size={13} className={selected ? 'text-white' : 'text-neutral-500'} />
                              <span className="text-xs font-semibold">{t(LABEL_KEYS[addr.label] || 'address.labelOther')}</span>
                              {addr.nickname && <span className="text-xs text-neutral-600">· {addr.nickname}</span>}
                              {addr.is_default && <Star size={10} className="text-yellow-500 fill-yellow-500" />}
                            </div>
                            <p className="text-xs text-neutral-400 line-clamp-1">{addr.city}, {addr.governorate}</p>
                            <p className="text-xs text-neutral-600 line-clamp-1">{addr.address_line}</p>
                          </button>
                        );
                      })}
                      <button
                        type="button"
                        onClick={() => { setSelectedSavedId(null); setFormData(prev => ({ ...prev, governorate: '', city: '', address: '' })); setMapLocation(null); }}
                        className={`rounded-xl border border-dashed px-4 py-3 text-start transition-colors ${
                          !selectedSavedId
                            ? 'border-neutral-500 text-neutral-300'
                            : 'border-neutral-800 text-neutral-600 hover:border-neutral-600 hover:text-neutral-400'
                        }`}
                      >
                        <p className="text-xs font-semibold">{t('checkout.enterManually')}</p>
                        <p className="text-xs mt-0.5 text-neutral-600">{t('checkout.enterManuallyDesc')}</p>
                      </button>
                    </div>
                  </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest text-neutral-500">{t('common.ui.firstName')}</label>
                    <input
                      type="text"
                      name="firstName"
                      value={formData.firstName}
                      onChange={handleInputChange}
                      className={`w-full bg-neutral-900 border ${errors.firstName ? 'border-red-500' : 'border-neutral-800'} rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors`}
                    />
                    {errors.firstName && <p className="text-red-500 text-xs">{errors.firstName}</p>}
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest text-neutral-500">{t('common.ui.lastName')}</label>
                    <input
                      type="text"
                      name="lastName"
                      value={formData.lastName}
                      onChange={handleInputChange}
                      className={`w-full bg-neutral-900 border ${errors.lastName ? 'border-red-500' : 'border-neutral-800'} rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors`}
                    />
                    {errors.lastName && <p className="text-red-500 text-xs">{errors.lastName}</p>}
                  </div>
                </div>

                <div className="space-y-2">
                  <label className="text-xs uppercase tracking-widest text-neutral-500">{t('common.ui.phoneNumber')}</label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleInputChange}
                    placeholder={t('checkout.phonePlaceholder')}
                    className={`w-full bg-neutral-900 border ${errors.phone ? 'border-red-500' : 'border-neutral-800'} rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors`}
                  />
                  {errors.phone && <p className="text-red-500 text-xs">{errors.phone}</p>}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest text-neutral-500">{t('checkout.governorate')}</label>
                    <select
                      name="governorate"
                      value={formData.governorate}
                      onChange={(e) => {
                        handleInputChange(e);
                        setFormData(prev => ({ ...prev, city: '' }));
                      }}
                      className={`w-full bg-neutral-900 border ${errors.governorate ? 'border-red-500' : 'border-neutral-800'} rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors appearance-none`}
                    >
                      <option value="">{t('checkout.selectGovernorate')}</option>
                      {Object.keys(zones).map(gov => (
                        <option key={gov} value={gov}>{gov}</option>
                      ))}
                    </select>
                    {errors.governorate && <p className="text-red-500 text-xs">{errors.governorate}</p>}
                  </div>

                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest text-neutral-500">{t('checkout.city')}</label>
                    <select
                      name="city"
                      value={formData.city}
                      onChange={handleInputChange}
                      disabled={!formData.governorate}
                      className={`w-full bg-neutral-900 border ${errors.city ? 'border-red-500' : 'border-neutral-800'} rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors appearance-none disabled:opacity-50`}
                    >
                      <option value="">{t('checkout.selectCity')}</option>
                      {formData.governorate && (zones[formData.governorate] || [])
                        .map(z => z.district).filter(Boolean)
                        .map(district => (
                          <option key={district} value={district}>{district}</option>
                        ))}
                    </select>
                    {errors.city && <p className="text-red-500 text-xs">{errors.city}</p>}
                  </div>
                </div>

                <div className="space-y-2">
                  <label className="text-xs uppercase tracking-widest text-neutral-500">{t('checkout.addressField')}</label>
                  <textarea
                    name="address"
                    value={formData.address}
                    onChange={handleInputChange}
                    placeholder={t('checkout.addressPlaceholder')}
                    rows="3"
                    className={`w-full bg-neutral-900 border ${errors.address ? 'border-red-500' : 'border-neutral-800'} rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors resize-none`}
                  />
                  {errors.address && <p className="text-red-500 text-xs">{errors.address}</p>}
                </div>

                <div className="space-y-2">
                  <label className="text-xs uppercase tracking-widest text-neutral-500">
                    {t('checkout.notes')} <span className="normal-case tracking-normal text-neutral-600">({t('checkout.notesOptional')})</span>
                  </label>
                  <textarea
                    name="notes"
                    value={formData.notes}
                    onChange={handleInputChange}
                    placeholder={t('checkout.notesPlaceholder')}
                    rows="2"
                    className="w-full bg-neutral-900 border border-neutral-800 rounded-md px-4 py-3 focus:outline-none focus:border-white transition-colors resize-none"
                  />
                </div>
              </section>

              {/* Delivery Location */}
              <section className="space-y-6">
                <div className="flex items-center gap-3 border-b border-neutral-800 pb-4">
                  <MapPin size={20} className="text-neutral-400" />
                  <h2 className="text-xl font-bold tracking-wide">{t('checkout.deliverySection')}</h2>
                </div>
                <p className="text-sm text-neutral-400">{t('checkout.deliveryDesc')}</p>
                <MapPickerField
                  value={mapLocation}
                  onChange={(loc) => {
                    setMapLocation(loc);
                    if (errors.mapLocation) setErrors(prev => ({ ...prev, mapLocation: '' }));
                  }}
                  error={errors.mapLocation}
                  flyTo={mapFlyTo}
                />
              </section>

              {/* Payment Method */}
              <section className="space-y-6">
                <div className="flex items-center gap-3 border-b border-neutral-800 pb-4">
                  <CreditCard size={20} className="text-neutral-400" />
                  <h2 className="text-xl font-bold tracking-wide">{t('checkout.paymentSection')}</h2>
                </div>

                <div className="space-y-4">
                  <label className={`flex items-center gap-4 p-4 border rounded-lg cursor-pointer transition-all ${paymentMethod === 'cod' ? 'border-white bg-neutral-900' : 'border-neutral-800 hover:border-neutral-600'}`}>
                    <input type="radio" name="payment" value="cod" checked={paymentMethod === 'cod'} onChange={() => setPaymentMethod('cod')} className="w-5 h-5 accent-black" />
                    <div className="flex items-center gap-3">
                      <Truck size={24} className="text-neutral-300" />
                      <div>
                        <p className="font-bold text-white">{t('checkout.cod')}</p>
                        <p className="text-sm text-neutral-400">{t('checkout.codDesc')}</p>
                      </div>
                    </div>
                  </label>

                  <label className="flex items-center gap-4 p-4 border border-neutral-800 rounded-lg opacity-50 cursor-not-allowed">
                    <input type="radio" name="payment" disabled className="w-5 h-5" />
                    <div className="flex items-center gap-3">
                      <img src="/logos/fib.webp" alt="First Iraqi Bank (FIB)" className="w-10 h-10 rounded-xl object-cover flex-shrink-0" />
                      <div>
                        <p className="font-bold text-neutral-500">{t('checkout.fibLabel')}</p>
                        <p className="text-sm text-neutral-600">{t('checkout.fibDesc')}</p>
                      </div>
                    </div>
                  </label>

                  <label className="flex items-center gap-4 p-4 border border-neutral-800 rounded-lg opacity-50 cursor-not-allowed">
                    <input type="radio" name="payment" disabled className="w-5 h-5" />
                    <div className="flex items-center gap-3">
                      <img src="/logos/fastpay.webp" alt="FastPay" className="w-10 h-10 rounded-xl object-cover flex-shrink-0" />
                      <div>
                        <p className="font-bold text-neutral-500">{t('checkout.fastpayLabel')}</p>
                        <p className="text-sm text-neutral-600">{t('checkout.fastpayDesc')}</p>
                      </div>
                    </div>
                  </label>

                  <label className="flex items-center gap-4 p-4 border border-neutral-800 rounded-lg opacity-50 cursor-not-allowed">
                    <input type="radio" name="payment" disabled className="w-5 h-5" />
                    <div className="flex items-center gap-3">
                      <CreditCard size={24} className="text-neutral-500" />
                      <div>
                        <p className="font-bold text-neutral-500">{t('checkout.cardLabel')}</p>
                        <p className="text-sm text-neutral-600">{t('checkout.cardDesc')}</p>
                      </div>
                    </div>
                  </label>
                </div>
              </section>

            </form>
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-neutral-900 rounded-lg p-6 lg:sticky lg:top-32 space-y-6">
              <h2 className="text-xl font-bold tracking-wide mb-6">{t('checkout.orderSummary')}</h2>

              <div className="space-y-4 max-h-80 overflow-y-auto pe-2">
                {cart.map((item) => (
                  <div key={item.id} className="flex gap-4">
                    <div className="w-16 h-20 bg-neutral-800 rounded overflow-hidden flex-shrink-0">
                      <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                    </div>
                    <div className="flex-1">
                      <p className="text-sm font-medium text-white line-clamp-1">{item.name}</p>
                      <p className="text-xs text-neutral-400">{t('checkout.qty')} {item.quantity}</p>
                      <p className="text-sm text-neutral-300">{item.price}</p>
                    </div>
                  </div>
                ))}
              </div>

              <div className="border-t border-neutral-800 pt-4 space-y-2">
                <div className="flex justify-between text-neutral-400">
                  <span>{t('checkout.subtotal')}</span>
                  <span>{cartMeta.subtotalFormatted}</span>
                </div>
                <div className="flex justify-between text-neutral-400">
                  <span>{t('checkout.shipping')}</span>
                  <span>{selectedZone?.rate_formatted ?? (formData.governorate ? '...' : '—')}</span>
                </div>
                <div className="flex justify-between text-white font-bold text-lg pt-2 border-t border-neutral-800 mt-2">
                  <span>{t('checkout.total')}</span>
                  <span>{formatIQD(totalAmount)}</span>
                </div>
              </div>

              {orderError && <p className="text-sm text-red-400">{orderError}</p>}

              <button
                type="submit"
                form="checkout-form"
                disabled={submitting}
                className="w-full py-4 bg-white text-black font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors rounded-full flex items-center justify-center gap-2 disabled:opacity-50"
              >
                {submitting ? t('checkout.placingOrder') : t('checkout.placeOrder')}
                <Check size={18} />
              </button>
            </div>
          </div>

        </div>
      </main>

    </div>
  );
};

export default CheckoutPage;
