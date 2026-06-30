@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.orders.inventory-source.preheader')@endslot

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #111111; border: 1px solid #222222; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #888888;">
                    @lang('admin::app.emails.orders.inventory-source.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    New order to fulfill.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => $inventorySource->name]) — {!! trans('admin::app.emails.orders.inventory-source.greeting', [
                        'order_id'   => '<a href="' . route('admin.sales.orders.view', $order->id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $order->increment_id . '</a>',
                        'created_at' => core()->formatDate($order->created_at, 'Y-m-d H:i'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 40px;">
        <tr>
            <td>
                <a href="{{ route('admin.sales.orders.view', $order->id) }}"
                   style="display: inline-block; padding: 14px 32px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Order
                </a>
            </td>
        </tr>
    </table>

    {{-- Inventory source info --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td style="border: 1px solid #1A1A1A; border-radius: 10px; padding: 18px 20px;">
                <p style="margin: 0 0 5px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Inventory Source</p>
                <p style="margin: 0 0 3px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500; color: #FFFFFF;">{{ $inventorySource->name }}</p>
                @if ($inventorySource->contact_email)
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888;">{{ $inventorySource->contact_email }}</p>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Items to Fulfill</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden;">
        @foreach ($order->items as $item)
        @if (! $item->isVirtual() && $item->qty_ordered > $item->qty_shipped)
        <tr>
            <td style="padding: 16px 18px; border-bottom: {{ !$loop->last ? '1px solid #141414' : 'none' }}; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; color: #FFFFFF;">{{ $item->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">
                                @lang('admin::app.emails.orders.sku'): {{ $item->sku ?? '—' }}
                                &middot; @lang('admin::app.emails.orders.qty'): {{ $item->qty_ordered - $item->qty_shipped }}
                            </p>
                        </td>
                        <td align="right" style="padding-left: 16px; white-space: nowrap;">
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #FFFFFF;">{{ core()->formatPrice($item->sub_total, $order->order_currency_code) }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
        @endforeach
    </table>

    {{-- Shipping address --}}
    @if ($order->shipping_address)
    <p style="margin: 28px 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Ship To</p>
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="border: 1px solid #1A1A1A; border-radius: 10px; padding: 18px;">
                <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $order->shipping_address->name }}</p>
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                    {{ $order->shipping_address->address }}<br/>
                    {{ $order->shipping_address->city }}, {{ $order->shipping_address->state }}<br/>
                    {{ $order->shipping_address->country }}
                </p>
                @if ($order->billing_address->phone ?? false)
                <p style="margin: 6px 0 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #666666;">{{ $order->billing_address->phone }}</p>
                @endif
            </td>
        </tr>
    </table>
    @endif

@endcomponent
