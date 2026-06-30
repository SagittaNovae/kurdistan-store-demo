import React, { useEffect, useState } from 'react';
import { Package, ChevronRight, Loader2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../../context/I18nContext';
import api from '../../services/api';

const STATUS_COLORS = {
  pending:    'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
  processing: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
  completed:  'bg-green-500/10 text-green-400 border-green-500/20',
  canceled:   'bg-red-500/10 text-red-400 border-red-500/20',
  shipped:    'bg-purple-500/10 text-purple-400 border-purple-500/20',
};

const StatusBadge = ({ status }) => (
  <span className={`rounded-full border px-2.5 py-0.5 text-xs capitalize ${STATUS_COLORS[status] || 'bg-neutral-800 text-neutral-400 border-neutral-700'}`}>
    {status}
  </span>
);

const OrderRow = ({ order }) => {
  const { t } = useI18n();
  const navigate = useNavigate();

  return (
    <button
      className="w-full rounded-2xl border border-neutral-800 hover:border-neutral-700 hover:bg-neutral-900/50 transition-all text-start overflow-hidden"
      onClick={() => navigate(`/account/orders/${order.id}`)}
    >
      <div className="flex items-center gap-4 px-5 py-4">
        <div className="rounded-xl bg-neutral-800 p-2.5 shrink-0">
          <Package size={16} className="text-neutral-400" />
        </div>
        <div className="flex-1 min-w-0">
          <p className="text-sm font-semibold">#{order.increment_id || order.id}</p>
          <p className="text-xs text-neutral-500 mt-0.5">
            {new Date(order.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}
          </p>
          {order.items && order.items.length > 0 && (
            <p className="text-xs text-neutral-600 mt-1 truncate">
              {order.items.map(i => i.name).join(', ')}
            </p>
          )}
        </div>
        <div className="flex flex-col items-end gap-2 shrink-0">
          <StatusBadge status={order.status} />
          <span className="text-sm font-semibold">{order.grand_total_formatted}</span>
        </div>
        <ChevronRight size={16} className="text-neutral-600 shrink-0 rtl:rotate-180" />
      </div>
    </button>
  );
};

const OrdersTab = () => {
  const { t } = useI18n();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    api.orders.list()
      .then(res => setOrders(Array.isArray(res.data) ? res.data : []))
      .catch(err => setError(err.message || t('orders.loadFailed')))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return <div className="flex items-center gap-2 text-neutral-500 py-10"><Loader2 size={18} className="animate-spin" /> {t('common.loading')}</div>;
  }

  if (error) {
    return <div className="rounded-2xl border border-red-500/20 bg-red-500/5 p-6 text-sm text-red-400">{error}</div>;
  }

  if (orders.length === 0) {
    return (
      <div className="rounded-2xl border border-dashed border-neutral-800 p-10 text-center">
        <Package size={28} className="mx-auto mb-3 text-neutral-600" />
        <p className="text-neutral-500 text-sm">{t('orders.empty')}</p>
        <button
          onClick={() => navigate('/browse')}
          className="mt-4 text-sm text-neutral-300 hover:text-white underline underline-offset-4 transition-colors"
        >
          {t('orders.startShopping')}
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-lg font-semibold tracking-wide mb-1">{t('orders.title')}</h2>
        <p className="text-sm text-neutral-500">
          {orders.length === 1 ? t('orders.count', { count: 1 }) : t('orders.countPlural', { count: orders.length })}
        </p>
      </div>
      <div className="space-y-3">
        {orders.map(order => <OrderRow key={order.id} order={order} />)}
      </div>
    </div>
  );
};

export default OrdersTab;
