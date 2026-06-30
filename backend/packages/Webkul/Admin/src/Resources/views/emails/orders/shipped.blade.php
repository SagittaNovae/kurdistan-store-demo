@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.orders.shipped.preheader')@endslot

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #0A0C1F; border: 1px solid #1A1D3A; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #818CF8;">
                    @lang('admin::app.emails.orders.shipped.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    Order shipped.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => core()->getAdminEmailDetails()['name']]) — {!! trans('admin::app.emails.orders.shipped.greeting', [
                        'invoice_id' => $shipment->increment_id,
                        'order_id'   => '<a href="' . route('admin.sales.orders.view', $shipment->order_id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $shipment->order->increment_id . '</a>',
                        'created_at' => core()->formatDate($shipment->order->created_at, 'Y-m-d H:i'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <a href="{{ route('admin.sales.orders.view', $shipment->order_id) }}"
                   style="display: inline-block; padding: 14px 32px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Order
                </a>
            </td>
        </tr>
    </table>

    {{-- Tracking info --}}
    @if ($shipment->track_number)
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td style="border: 1px solid #1A1D3A; border-radius: 10px; padding: 18px 20px;">
                <p style="margin: 0 0 5px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
                    @lang('admin::app.emails.orders.carrier'): {{ $shipment->carrier_title }}
                </p>
                <p style="margin: 0; font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; font-size: 14px; font-weight: 600; color: #FFFFFF;">
                    @lang('admin::app.emails.orders.tracking-number'): {{ $shipment->track_number }}
                </p>
            </td>
        </tr>
    </table>
    @endif

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        @lang('admin::app.emails.orders.shipped.summary')
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            @if ($shipment->order->shipping_address)
            <td class="addr-col" valign="top" width="50%" style="padding-right: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.shipping-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $shipment->order->shipping_address->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $shipment->order->shipping_address->address }}<br/>
                                {{ $shipment->order->shipping_address->city }}, {{ $shipment->order->shipping_address->state }}<br/>
                                {{ $shipment->order->shipping_address->country }}
                            </p>
                            @if ($shipment->order->billing_address->phone ?? false)
                            <p style="margin: 6px 0 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #666666;">{{ $shipment->order->billing_address->phone }}</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            @endif

            @if ($shipment->order->billing_address)
            <td class="addr-col" valign="top" width="50%" style="padding-left: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.billing-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $shipment->order->billing_address->name }}</p>
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $shipment->order->billing_address->address }}<br/>
                                {{ $shipment->order->billing_address->city }}, {{ $shipment->order->billing_address->state }}
                            </p>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.payment')</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888;">{{ core()->getConfigData('sales.payment_methods.' . $shipment->order->payment->method . '.title') }}</p>
                            @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($shipment->order->payment->method); @endphp
                            @if (! empty($additionalDetails))
                            <p style="margin: 3px 0 0; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">{{ $additionalDetails['title'] }}: {{ $additionalDetails['value'] }}</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            @endif
        </tr>
    </table>

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Items Shipped</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden;">
        @foreach ($shipment->items as $item)
        <tr>
            <td style="padding: 16px 18px; border-bottom: {{ !$loop->last ? '1px solid #141414' : 'none' }}; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; color: #FFFFFF;">{{ $item->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">
                                @lang('admin::app.emails.orders.sku'): {{ $item->sku }} &middot; @lang('admin::app.emails.orders.qty'): {{ $item->qty }}
                            </p>
                        </td>
                        <td align="right" style="padding-left: 16px; white-space: nowrap;">
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #FFFFFF;">
                                @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                    {{ core()->formatBasePrice($item->base_price_incl_tax) }}
                                @else
                                    {{ core()->formatBasePrice($item->base_price) }}
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

@endcomponent
