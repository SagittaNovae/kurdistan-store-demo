@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.orders.invoiced.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #100C1F; border: 1px solid #261A3A; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #C084FC;">
                    @lang('shop::app.emails.orders.invoiced.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    Invoice ready.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $invoice->order->customer_full_name]) — {!! trans('shop::app.emails.orders.invoiced.greeting', [
                        'invoice_id' => $invoice->increment_id,
                        'order_id'   => '<a href="' . route('shop.customers.account.orders.view', $invoice->order_id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $invoice->order->increment_id . '</a>',
                        'created_at' => core()->formatDate($invoice->order->created_at, 'Y-m-d'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 44px;">
        <tr>
            <td>
                <a href="{{ route('shop.customers.account.orders.view', $invoice->order_id) }}"
                   style="display: inline-block; padding: 16px 36px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Order
                </a>
            </td>
        </tr>
    </table>

    {{-- ─── Section label ───────────────────────────────────────────────────── --}}
    <p style="margin: 0 0 16px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        @lang('shop::app.emails.orders.invoiced.summary')
    </p>

    {{-- ─── Address cards ──────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 40px;">
        <tr>
            @if ($invoice->order->shipping_address)
            <td class="addr-col" valign="top" width="50%" style="padding-right: 12px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 20px;">
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('shop::app.emails.orders.shipping-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500;">
                                @if ($invoice->order->shipping_address->company_name){{ $invoice->order->shipping_address->company_name }}<br/>@endif
                                {{ $invoice->order->shipping_address->name }}
                            </p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888; line-height: 22px;">
                                {{ $invoice->order->shipping_address->address }}<br/>
                                {{ $invoice->order->shipping_address->city }}, {{ $invoice->order->shipping_address->state }}<br/>
                                {{ $invoice->order->shipping_address->country }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            @endif

            @if ($invoice->order->billing_address)
            <td class="addr-col" valign="top" width="50%" style="padding-left: 12px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 20px;">
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('shop::app.emails.orders.billing-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500;">
                                @if ($invoice->order->billing_address->company_name){{ $invoice->order->billing_address->company_name }}<br/>@endif
                                {{ $invoice->order->billing_address->name }}
                            </p>
                            <p style="margin: 0 0 14px; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888; line-height: 22px;">
                                {{ $invoice->order->billing_address->address }}<br/>
                                {{ $invoice->order->billing_address->city }}, {{ $invoice->order->billing_address->state }}<br/>
                                {{ $invoice->order->billing_address->country }}
                            </p>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">@lang('shop::app.emails.orders.payment')</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888;">
                                {{ core()->getConfigData('sales.payment_methods.' . $invoice->order->payment->method . '.title') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            @endif
        </tr>
    </table>

    {{-- ─── Items ────────────────────────────────────────────────────────────── --}}
    <p style="margin: 0 0 16px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Items</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden; margin-bottom: 0;">
        @foreach ($invoice->items as $item)
        @php $thumbnail = $item->product?->base_image_url ?? null; @endphp
        <tr>
            <td style="padding: 16px 20px; border-bottom: {{ !$loop->last ? '1px solid #141414' : 'none' }}; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td width="76" valign="top" style="width: 76px; vertical-align: top; padding-right: 16px;">
                            @if ($thumbnail)
                                <img src="{{ $thumbnail }}" width="60" alt="{{ $item->name }}"
                                     style="display: block; width: 60px; height: auto; max-width: 60px; border-radius: 6px; border: 1px solid #1A1A1A;" />
                            @else
                                <table width="60" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr><td width="60" height="60" bgcolor="#0D0D0D"
                                        style="width: 60px; height: 60px; background-color: #0D0D0D; border: 1px solid #1A1A1A; border-radius: 6px;">&nbsp;</td></tr>
                                </table>
                            @endif
                        </td>
                        <td valign="top" style="vertical-align: top;">
                            <p style="margin: 0 0 3px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500; color: #FFFFFF;">{{ $item->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #555555;">
                                {{ $item->sku ?? '' }} &middot; @lang('shop::app.emails.orders.qty'): {{ $item->qty }}
                            </p>
                            @if (isset($item->additional['attributes']))
                            <p style="margin: 6px 0 0;">
                                @foreach ($item->additional['attributes'] as $attribute)
                                    @if (! isset($attribute['attribute_type']) || $attribute['attribute_type'] !== 'file')
                                    <span style="font-family: 'Inter', sans-serif; font-size: 12px; color: #555555;">{{ $attribute['attribute_name'] }}: {{ $attribute['option_label'] }}</span><br>
                                    @else
                                    <span style="font-family: 'Inter', sans-serif; font-size: 12px; color: #555555;">{{ $attribute['attribute_name'] }}: <a href="{{ Storage::url($attribute['option_label']) }}" style="color: #888888;">{{ File::basename($attribute['option_label']) }}</a></span><br>
                                    @endif
                                @endforeach
                            </p>
                            @endif
                        </td>
                        <td valign="top" align="right" style="padding-left: 20px; white-space: nowrap; vertical-align: top;">
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600; color: #FFFFFF;">
                                @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                    {{ core()->formatPrice($item->total_incl_tax, $invoice->order_currency_code) }}
                                @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                                    {{ core()->formatPrice($item->total_incl_tax, $invoice->order_currency_code) }}
                                @else
                                    {{ core()->formatPrice($item->total, $invoice->order_currency_code) }}
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
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 28px;">
        @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($invoice->sub_total_incl_tax, $invoice->order_currency_code) }}</td>
        </tr>
        @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal-excl-tax')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($invoice->sub_total, $invoice->order_currency_code) }}</td>
        </tr>
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal-incl-tax')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($invoice->sub_total_incl_tax, $invoice->order_currency_code) }}</td>
        </tr>
        @else
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($invoice->sub_total, $invoice->order_currency_code) }}</td>
        </tr>
        @endif

        @if ($invoice->shipping_amount > 0)
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.shipping-handling')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($invoice->shipping_amount, $invoice->order_currency_code) }}</td>
        </tr>
        @endif

        @if ($invoice->tax_amount > 0)
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.tax')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($invoice->tax_amount, $invoice->order_currency_code) }}</td>
        </tr>
        @endif

        @if ($invoice->discount_amount > 0)
        <tr>
            <td style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.discount')</td>
            <td align="right" style="padding-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #4ADE80;">-{{ core()->formatPrice($invoice->discount_amount, $invoice->order_currency_code) }}</td>
        </tr>
        @endif

        <tr><td colspan="2" style="padding: 12px 0 0; border-top: 1px solid #1A1A1A;">&nbsp;</td></tr>
        <tr>
            <td style="padding-top: 12px; font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">@lang('shop::app.emails.orders.grand-total')</td>
            <td align="right" style="padding-top: 12px; font-family: 'Inter', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF;">{{ core()->formatPrice($invoice->grand_total, $invoice->order_currency_code) }}</td>
        </tr>
    </table>

@endcomponent
