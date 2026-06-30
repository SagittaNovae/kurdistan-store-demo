@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.orders.canceled.preheader')@endslot

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #1F0C0C; border: 1px solid #3A1A1A; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #F87171;">
                    @lang('admin::app.emails.orders.canceled.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    Order cancelled.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => core()->getAdminEmailDetails()['name']]) — {!! trans('admin::app.emails.orders.canceled.greeting', [
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

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        @lang('admin::app.emails.orders.canceled.summary')
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            @if ($order->shipping_address)
            <td class="addr-col" valign="top" width="50%" style="padding-right: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.shipping-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $order->shipping_address->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $order->shipping_address->address }}<br/>
                                {{ $order->shipping_address->city }}, {{ $order->shipping_address->state }}<br/>
                                {{ $order->shipping_address->country }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            @endif

            @if ($order->billing_address)
            <td class="addr-col" valign="top" width="50%" style="padding-left: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.billing-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $order->billing_address->name }}</p>
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $order->billing_address->address }}<br/>
                                {{ $order->billing_address->city }}, {{ $order->billing_address->state }}
                            </p>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.payment')</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888;">{{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
            @endif
        </tr>
    </table>

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Items</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden;">
        @foreach ($order->items as $item)
        <tr>
            <td style="padding: 16px 18px; border-bottom: {{ !$loop->last ? '1px solid #141414' : 'none' }}; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; color: #FFFFFF;">{{ $item->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">@lang('admin::app.emails.orders.qty'): {{ $item->qty_ordered }}</p>
                        </td>
                        <td align="right" style="padding-left: 16px; white-space: nowrap;">
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #FFFFFF;">{{ core()->formatPrice($item->sub_total, $order->order_currency_code) }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

@endcomponent
