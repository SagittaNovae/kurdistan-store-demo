@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.orders.created.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #0C1F0C; border: 1px solid #1D3A1D; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #4ADE80;">
                    @lang('admin::app.emails.orders.created.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    New order received.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => core()->getAdminEmailDetails()['name']]) — {!! trans('admin::app.emails.orders.created.greeting', [
                        'order_id'   => '<a href="' . route('admin.sales.orders.view', $order->id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $order->increment_id . '</a>',
                        'created_at' => core()->formatDate($order->created_at, 'Y-m-d H:i'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
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

    {{-- ─── Section label ───────────────────────────────────────────────────── --}}
    <p style="margin: 0 0 14px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        @lang('admin::app.emails.orders.created.summary')
    </p>

    {{-- ─── Address cards ──────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            @if ($order->shipping_address)
            <td class="addr-col" valign="top" width="50%" style="padding-right: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.shipping-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $order->shipping_address->company_name ?? '' }} {{ $order->shipping_address->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $order->shipping_address->address }}<br/>
                                {{ $order->shipping_address->city }}, {{ $order->shipping_address->state }}<br/>
                                {{ $order->shipping_address->country }}
                            </p>
                            @if ($order->billing_address->phone ?? false)
                            <p style="margin: 6px 0 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #666666;">{{ $order->billing_address->phone }}</p>
                            @endif
                            @if ($order->shipping_title)
                            <p style="margin: 12px 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.shipping')</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888;">{{ $order->shipping_title }}</p>
                            @endif
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
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $order->billing_address->company_name ?? '' }} {{ $order->billing_address->name }}</p>
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $order->billing_address->address }}<br/>
                                {{ $order->billing_address->city }}, {{ $order->billing_address->state }}<br/>
                                {{ $order->billing_address->country }}
                            </p>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.payment')</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888;">{{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}</p>
                            @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($order->payment->method); @endphp
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

    {{-- ─── Items ────────────────────────────────────────────────────────────── --}}
    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Items</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden; margin-bottom: 0;">
        @foreach ($order->items as $item)
        <tr>
            <td style="padding: 16px 18px; border-bottom: {{ !$loop->last ? '1px solid #141414' : 'none' }}; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td valign="top">
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; color: #FFFFFF;">{{ $item->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">
                                @lang('admin::app.emails.orders.sku'): {{ $item->getTypeInstance()->getOrderedItem($item)->sku ?? $item->sku ?? '—' }}
                                &nbsp;&middot;&nbsp; @lang('admin::app.emails.orders.qty'): {{ $item->qty_ordered }}
                            </p>
                            @if (isset($item->additional['attributes']))
                            <div style="margin-top: 4px;">
                                @foreach ($item->additional['attributes'] as $attribute)
                                    @if (! isset($attribute['attribute_type']) || $attribute['attribute_type'] !== 'file')
                                    <span style="font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">{{ $attribute['attribute_name'] }}: {{ $attribute['option_label'] }}</span><br>
                                    @else
                                    <span style="font-family: 'Inter', sans-serif; font-size: 11px; color: #555555;">{{ $attribute['attribute_name'] }}: <a href="{{ Storage::url($attribute['option_label']) }}" style="color: #888888;">{{ File::basename($attribute['option_label']) }}</a></span><br>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td valign="top" align="right" style="padding-left: 16px; white-space: nowrap;">
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #FFFFFF;">
                                @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                    {{ core()->formatPrice($item->sub_total_incl_tax, $order->order_currency_code) }}
                                @else
                                    {{ core()->formatPrice($item->sub_total, $order->order_currency_code) }}
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

    {{-- ─── Totals ─────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 24px;">
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.subtotal')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ core()->formatPrice($order->sub_total, $order->order_currency_code) }}</td>
        </tr>
        @if ($order->shipping_amount > 0)
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.shipping-handling')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ core()->formatPrice($order->shipping_amount, $order->order_currency_code) }}</td>
        </tr>
        @endif
        @if ($order->tax_amount > 0)
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.tax')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ core()->formatPrice($order->tax_amount, $order->order_currency_code) }}</td>
        </tr>
        @endif
        @if ($order->discount_amount > 0)
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.discount')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #4ADE80;">-{{ core()->formatPrice($order->discount_amount, $order->order_currency_code) }}</td>
        </tr>
        @endif
        <tr><td colspan="2" style="padding: 10px 0 0; border-top: 1px solid #1A1A1A;">&nbsp;</td></tr>
        <tr>
            <td style="padding-top: 10px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; color: #FFFFFF;">@lang('admin::app.emails.orders.grand-total')</td>
            <td align="right" style="padding-top: 10px; font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">{{ core()->formatPrice($order->grand_total, $order->order_currency_code) }}</td>
        </tr>
    </table>

@endcomponent
