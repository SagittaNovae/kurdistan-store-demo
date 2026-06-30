import React, { useEffect, useState } from 'react';
import {
  ArrowLeft, Package, MapPin, CreditCard, Truck,
  AlertCircle, Loader2, ExternalLink, RefreshCw,
} from 'lucide-react';
import { useNavigate, useParams } from 'react-router-dom';
import { useI18n } from '../context/I18nContext';
import api from '../services/api';

const STATUS_STYLES = {
  pending:    'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
  processing: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
  completed:  'bg-green-500/10 text-green-400 border-green-500/20',
  canceled:   'bg-red-500/10 text-red-400 border-red-500/20',
  shipped:    'bg-purple-500/10 text-purple-400 border-purple-500/20',
};

const StatusBadge = ({ status }) => (
  <span
    className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold capitalize tracking-wide ${STATUS_STYLES[status] || 'bg-neutral-800 text-neutral-400 border-neutral-700'}`}
  >
    {status}
  </span>
);

const SectionHeading = ({ icon: Icon, label }) => (
  <div className="flex items-center gap-2.5 mb-4">
    <Icon size={15} className="text-neutral-500 shrink-0" aria-hidden="true" />
    <h2 className="text-xs font-bold uppercase tracking-[0.12em] text-neutral-500">{label}</h2>
  </div>
);

const TotalRow = ({ label, value, accent = false }) => (
  <div className="flex items-center justify-between px-5 py-3">
    <p className="text-sm text-neutral-500">{label}</p>
    <p className={`text-sm font-medium ${accent ? 'text-green-400' : 'text-neutral-300'}`}>{value}</p>
  </div>
);

const AddressCard = ({ address }) => {
  const { t } = useI18n();
  if (!address) return null;
  return (
    <div className="rounded-2xl border border-neutral-800 bg-neutral-950 px-5 py-4 space-y-1">
      {address.name    && <p className="text-sm font-semibold text-neutral-200">{address.name}</p>}
      {address.phone   && <p className="text-sm text-neutral-400">{address.phone}</p>}
      {address.city    && <p className="text-sm text-neutral-400">{address.city}</p>}
      {address.address && <p className="text-sm text-neutral-400 break-words">{address.address}</p>}
      {address.maps_link && (
        <a
          href={address.maps_link}
          target="_blank"
          rel="noopener noreferrer"
          aria-label={`${t('orders.viewOnMaps')} (opens in new tab)`}
          className="mt-1 inline-flex items-center gap-1.5 text-xs text-blue-400 hover:text-blue-300 transition-colors"
        >
          <MapPin size={11} aria-hidden="true" />
          {t('orders.viewOnMaps')}
          <ExternalLink size={10} aria-hidden="true" />
        </a>
      )}
    </div>
  );
};

const OrderDetailPage = () => {
  const { t } = useI18n();
  const navigate = useNavigate();
  const { orderId } = useParams();
  const [order, setOrder]     = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState('');
  const [retryKey, setRetryKey] = useState(0);

  useEffect(() => {
    setOrder(null);
    setError('');
    setLoading(true);

    api.orders.get(orderId)
      .then(res => setOrder(res.data))
      .catch(err => {
        setError(
          (err.status === 404 || err.status === 403)
            ? t('orders.detail.notFound')
            : t('orders.detail.loadFailed')
        );
      })
      .finally(() => setLoading(false));
  }, [orderId, retryKey]);

  const formattedDate = order
    ? new Date(order.created_at).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
      })
    : '';

  const isNotFound = error === t('orders.detail.notFound');

  return (
    <div className="min-h-screen bg-black text-white font-sans">
      <header className="fixed top-0 left-0 w-full z-40 bg-black/90 backdrop-blur-md border-b border-neutral-900">
        <div className="max-w-4xl mx-auto px-6 py-4 flex items-center gap-4">
          <button
            onClick={() => navigate('/account?tab=orders')}
            className="flex items-center gap-2 text-neutral-400 hover:text-white transition-colors"
            aria-label={t('orders.backToOrders')}
          >
            <ArrowLeft size={18} className="rtl:rotate-180" aria-hidden="true" />
            <span className="text-sm hidden sm:inline">{t('orders.backToOrders')}</span>
          </button>
          <div className="flex-1 min-w-0">
            <p className="text-xs uppercase tracking-[0.3em] text-neutral-600 truncate">
              {t('orders.orderNumber')}
              {order ? ` #${order.increment_id || order.id}` : ''}
            </p>
            {order && (
              <p className="text-xs text-neutral-600">
                {t('orders.placedOn')} {formattedDate}
              </p>
            )}
          </div>
          {order && <StatusBadge status={order.status} />}
        </div>
      </header>

      <div className="max-w-4xl mx-auto px-4 sm:px-6 pt-28 pb-20">

        {loading && (
          <div
            className="flex items-center gap-2.5 text-neutral-500 py-20"
            role="status"
            aria-label={t('common.loading')}
          >
            <Loader2 size={18} className="animate-spin" aria-hidden="true" />
            {t('common.loading')}
          </div>
        )}

        {!loading && error && (
          <div
            className="rounded-2xl border border-red-500/20 bg-red-500/5 p-6 flex flex-col gap-4"
            role="alert"
          >
            <div className="flex items-start gap-3">
              <AlertCircle size={18} className="text-red-400 mt-0.5 shrink-0" aria-hidden="true" />
              <p className="text-sm text-red-400">{error}</p>
            </div>
            <div className="flex items-center gap-3">
              <button
                onClick={() => navigate('/account?tab=orders')}
                className="text-sm text-neutral-400 hover:text-white underline underline-offset-4 transition-colors"
              >
                {t('orders.backToOrders')}
              </button>
              {!isNotFound && (
                <button
                  onClick={() => setRetryKey(k => k + 1)}
                  className="flex items-center gap-1.5 text-sm text-blue-400 hover:text-blue-300 transition-colors"
                >
                  <RefreshCw size={13} aria-hidden="true" />
                  Retry
                </button>
              )}
            </div>
          </div>
        )}

        {!loading && order && (
          <div className="space-y-6">

            <div className="rounded-2xl border border-neutral-800 overflow-hidden">
              <div className="px-5 pt-5 pb-2">
                <SectionHeading icon={Package} label={t('orders.detail.items')} />
              </div>
              <div className="divide-y divide-neutral-800/60">
                {(order.items || []).map((item, i) => (
                  <div key={i} className="flex items-center gap-4 px-5 py-4">
                    <div className="w-14 h-14 rounded-xl bg-neutral-900 shrink-0 overflow-hidden flex items-center justify-center">
                      {item.image
                        ? <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                        : <Package size={20} className="text-neutral-700" aria-hidden="true" />
                      }
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-neutral-200 break-words">{item.name}</p>
                      <p className="text-xs text-neutral-600 mt-0.5">SKU: {item.sku}</p>
                    </div>
                    <div className="text-right shrink-0 pl-2">
                      <p className="text-sm font-semibold text-neutral-200">{item.total_formatted}</p>
                      <p className="text-xs text-neutral-600 mt-0.5 whitespace-nowrap">
                        {item.price_formatted} × {item.qty}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

              <div className="space-y-4">
                {order.shipping_address && (
                  <div>
                    <SectionHeading icon={MapPin} label={t('orders.detail.shippingAddress')} />
                    <AddressCard address={order.shipping_address} />
                  </div>
                )}
                {order.billing_address && (
                  <div>
                    <SectionHeading icon={MapPin} label={t('orders.detail.billingAddress')} />
                    <AddressCard address={order.billing_address} />
                  </div>
                )}
              </div>

              <div>
                <SectionHeading icon={CreditCard} label={t('orders.detail.summary')} />
                <div className="rounded-2xl border border-neutral-800 bg-neutral-950 divide-y divide-neutral-800/60">
                  <TotalRow label={t('orders.detail.subtotal')} value={order.sub_total_formatted} />
                  {order.shipping_amount > 0
                    ? <TotalRow label={t('orders.detail.shipping')} value={order.shipping_amount_formatted} />
                    : <TotalRow label={t('orders.detail.shipping')} value={t('orders.detail.free')} accent />
                  }
                  {order.discount_amount > 0 && (
                    <TotalRow label={t('orders.detail.discount')} value={`−${order.discount_amount_formatted}`} accent />
                  )}
                  {order.tax_amount > 0 && (
                    <TotalRow label={t('orders.detail.tax')} value={order.tax_amount_formatted} />
                  )}
                  <div className="flex items-center justify-between px-5 py-4">
                    <p className="text-sm font-bold text-neutral-100">{t('orders.detail.grandTotal')}</p>
                    <p className="text-base font-bold text-white">{order.grand_total_formatted}</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="rounded-2xl border border-neutral-800 bg-neutral-950 px-5 py-4">
                <p className="text-xs font-bold uppercase tracking-[0.12em] text-neutral-600 mb-3 flex items-center gap-2">
                  <CreditCard size={13} aria-hidden="true" />
                  {t('orders.detail.payment')}
                </p>
                <p className="text-sm font-medium text-neutral-200">
                  {order.payment_method_title || order.payment_method || '—'}
                </p>
              </div>

              {order.shipping_title && (
                <div className="rounded-2xl border border-neutral-800 bg-neutral-950 px-5 py-4">
                  <p className="text-xs font-bold uppercase tracking-[0.12em] text-neutral-600 mb-3 flex items-center gap-2">
                    <Truck size={13} aria-hidden="true" />
                    {t('orders.detail.shippingMethod')}
                  </p>
                  <p className="text-sm font-medium text-neutral-200">{order.shipping_title}</p>
                </div>
              )}
            </div>

            {order.shipments && order.shipments.length > 0 ? (
              <div>
                <SectionHeading icon={Truck} label={t('orders.detail.tracking')} />
                <div className="space-y-3">
                  {order.shipments.map((shipment, i) => (
                    <div key={i} className="rounded-2xl border border-neutral-800 bg-neutral-950 px-5 py-4 space-y-3">
                      {shipment.carrier_title && (
                        <div className="flex items-center justify-between gap-4">
                          <p className="text-xs text-neutral-600 uppercase tracking-wider shrink-0">{t('orders.detail.carrier')}</p>
                          <p className="text-sm font-medium text-neutral-200 text-right">{shipment.carrier_title}</p>
                        </div>
                      )}
                      {shipment.track_number && (
                        <div className="flex items-center justify-between gap-4">
                          <p className="text-xs text-neutral-600 uppercase tracking-wider shrink-0">{t('orders.detail.trackNumber')}</p>
                          <p className="text-sm font-mono font-semibold text-blue-400 break-all text-right">{shipment.track_number}</p>
                        </div>
                      )}
                      {shipment.created_at && (
                        <div className="flex items-center justify-between gap-4">
                          <p className="text-xs text-neutral-600 uppercase tracking-wider shrink-0">{t('orders.detail.shippedOn')}</p>
                          <p className="text-sm text-neutral-400">
                            {new Date(shipment.created_at).toLocaleDateString('en-GB', {
                              day: 'numeric', month: 'short', year: 'numeric',
                            })}
                          </p>
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            ) : (
              order.status !== 'pending' && order.status !== 'processing' && (
                <div className="rounded-2xl border border-dashed border-neutral-800 px-5 py-4 flex items-center gap-3 text-neutral-600">
                  <Truck size={16} aria-hidden="true" />
                  <p className="text-sm">{t('orders.detail.noTracking')}</p>
                </div>
              )
            )}

          </div>
        )}
      </div>
    </div>
  );
};

export default OrderDetailPage;
