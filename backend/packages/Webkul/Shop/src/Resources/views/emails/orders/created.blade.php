@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.orders.created.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                {{-- Status badge --}}
                <span style="display: inline-block; padding: 3px 10px; background-color: #0C1F0C; border: 1px solid #1D3A1D; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #4ADE80;">
                    @lang('shop::app.emails.orders.created.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    Order confirmed.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $order->customer_full_name]) — {!! trans('shop::app.emails.orders.created.greeting', [
                        'order_id'   => '<a href="' . route('shop.customers.account.orders.view', $order->id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $order->increment_id . '</a>',
                        'created_at' => core()->formatDate($order->created_at, 'Y-m-d'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── CTA Button ─────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 44px;">
        <tr>
            <td>
                <a href="{{ route('shop.customers.account.orders.view', $order->id) }}"
                   style="display: inline-block; padding: 16px 36px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Order
                </a>
            </td>
        </tr>
    </table>

    {{-- ─── Order meta ─────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 40px; border: 1px solid #1A1A1A; border-radius: 10px;">
        <tr>
            <td style="padding: 24px 24px 20px;">
                <p style="margin: 0 0 16px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
                    Order Details
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="padding-bottom: 12px; width: 50%;">
                            <p style="margin: 0 0 3px; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555; text-transform: uppercase; letter-spacing: 0.8px;">Order</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500;">#{{ $order->increment_id }}</p>
                        </td>
                        <td style="padding-bottom: 12px; width: 50%;">
                            <p style="margin: 0 0 3px; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555; text-transform: uppercase; letter-spacing: 0.8px;">Date</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500;">{{ core()->formatDate($order->created_at, 'd M Y') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin: 0 0 3px; font-family: 'Inter', sans-serif; font-size: 11px; color: #555555; text-transform: uppercase; letter-spacing: 0.8px;">Status</p>
                            <span style="display: inline-block; padding: 3px 10px; background-color: #0C1F0C; border: 1px solid #1D3A1D; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #4ADE80;">
                                {{ $order->status_label ?? ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ─── Section divider ────────────────────────────────────────────────────── --}}
    <p style="margin: 0 0 24px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        @lang('shop::app.emails.orders.created.summary')
    </p>

    {{-- ─── Address cards ──────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 40px;">
        <tr>
            {{-- Shipping / Delivery --}}
            @if ($order->shipping_address)
            <td class="addr-col" valign="top" width="50%" style="padding-right: 12px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; height: 100%;">
                    <tr>
                        <td style="padding: 20px 20px 20px;">
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
                                @lang('shop::app.emails.orders.shipping-address')
                            </p>
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500; line-height: 22px;">
                                @if ($order->shipping_address->company_name){{ $order->shipping_address->company_name }}<br/>@endif
                                {{ $order->shipping_address->name }}
                            </p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888; line-height: 22px;">
                                {{ $order->shipping_address->address }}<br/>
                                {{ $order->shipping_address->city }}@if ($order->shipping_address->state), {{ $order->shipping_address->state }}@endif<br/>
                                {{ $order->shipping_address->country }}
                            </p>
                            @if ($order->billing_address && $order->billing_address->phone)
                            <p style="margin: 8px 0 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">
                                {{ $order->billing_address->phone }}
                            </p>
                            @endif
                            @if ($order->shipping_title)
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #141414;">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">
                                            @lang('shop::app.emails.orders.shipping')
                                        </p>
                                        <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888;">{{ $order->shipping_title }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            @endif

            {{-- Billing / Payment --}}
            @if ($order->billing_address)
            <td class="addr-col" valign="top" width="50%" style="padding-left: 12px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; height: 100%;">
                    <tr>
                        <td style="padding: 20px 20px 20px;">
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
                                @lang('shop::app.emails.orders.billing-address')
                            </p>
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500; line-height: 22px;">
                                @if ($order->billing_address->company_name){{ $order->billing_address->company_name }}<br/>@endif
                                {{ $order->billing_address->name }}
                            </p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888; line-height: 22px;">
                                {{ $order->billing_address->address }}<br/>
                                {{ $order->billing_address->city }}@if ($order->billing_address->state), {{ $order->billing_address->state }}@endif<br/>
                                {{ $order->billing_address->country }}
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #141414;">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">
                                            @lang('shop::app.emails.orders.payment')
                                        </p>
                                        <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #888888;">
                                            {{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}
                                        </p>
                                        @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($order->payment->method); @endphp
                                        @if (! empty($additionalDetails))
                                        <p style="margin: 4px 0 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">
                                            {{ $additionalDetails['title'] }}: {{ $additionalDetails['value'] }}
                                        </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            @endif
        </tr>
    </table>

    {{-- ─── Items ──────────────────────────────────────────────────────────────── --}}
    <p style="margin: 0 0 16px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        Items
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 0; border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden;">
        @foreach ($order->items as $item)
        @php $thumbnail = $item->product?->base_image_url ?? null; @endphp
        <tr>
            <td style="padding: 16px 20px; border-bottom: {{ !$loop->last ? '1px solid #141414' : 'none' }}; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        {{-- Thumbnail --}}
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
                        {{-- Info --}}
                        <td valign="top" style="vertical-align: top;">
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500; color: #FFFFFF; line-height: 20px;">{{ $item->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #555555;">
                                @lang('shop::app.emails.orders.sku'): {{ $item->getTypeInstance()->getOrderedItem($item)->sku }}
                                &nbsp;&middot;&nbsp;
                                @lang('shop::app.emails.orders.qty'): {{ $item->qty_ordered }}
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
                        {{-- Price --}}
                        <td valign="top" align="right" style="vertical-align: top; white-space: nowrap; padding-left: 16px;">
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600; color: #FFFFFF;">
                                @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                    {{ core()->formatPrice($item->sub_total_incl_tax, $order->order_currency_code) }}
                                @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                                    {{ core()->formatPrice($item->sub_total_incl_tax, $order->order_currency_code) }}
                                @else
                                    {{ core()->formatPrice($item->sub_total, $order->order_currency_code) }}
                                @endif
                            </p>
                            <p style="margin: 4px 0 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #555555;">
                                @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                    {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }} each
                                @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                                    {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }} each
                                    <br><span style="font-size: 11px;">@lang('shop::app.emails.orders.excl-tax') {{ core()->formatPrice($item->price, $order->order_currency_code) }}</span>
                                @else
                                    {{ core()->formatPrice($item->price, $order->order_currency_code) }} each
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

    {{-- ─── Totals ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 28px;">

        {{-- Subtotal --}}
        @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
        <tr>
            <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal')</td>
            <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->sub_total_incl_tax, $order->order_currency_code) }}</td>
        </tr>
        @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
        <tr>
            <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal-excl-tax')</td>
            <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->sub_total, $order->order_currency_code) }}</td>
        </tr>
        <tr>
            <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal-incl-tax')</td>
            <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->sub_total_incl_tax, $order->order_currency_code) }}</td>
        </tr>
        @else
        <tr>
            <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.subtotal')</td>
            <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->sub_total, $order->order_currency_code) }}</td>
        </tr>
        @endif

        {{-- Shipping --}}
        @if ($order->shipping_address)
            @if (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'including_tax')
            <tr>
                <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.shipping-handling')</td>
                <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->shipping_amount_incl_tax, $order->order_currency_code) }}</td>
            </tr>
            @elseif (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'both')
            <tr>
                <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.shipping-handling-excl-tax')</td>
                <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->shipping_amount, $order->order_currency_code) }}</td>
            </tr>
            <tr>
                <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.shipping-handling-incl-tax')</td>
                <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->shipping_amount_incl_tax, $order->order_currency_code) }}</td>
            </tr>
            @else
            <tr>
                <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.shipping-handling')</td>
                <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->shipping_amount, $order->order_currency_code) }}</td>
            </tr>
            @endif
        @endif

        {{-- Tax --}}
        @if ($order->tax_amount > 0)
        <tr>
            <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.tax')</td>
            <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF;">{{ core()->formatPrice($order->tax_amount, $order->order_currency_code) }}</td>
        </tr>
        @endif

        {{-- Discount --}}
        @if ($order->discount_amount > 0)
        <tr>
            <td style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #666666;">@lang('shop::app.emails.orders.discount')</td>
            <td align="right" style="padding: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 14px; color: #4ADE80;">-{{ core()->formatPrice($order->discount_amount, $order->order_currency_code) }}</td>
        </tr>
        @endif

        {{-- Grand Total --}}
        <tr>
            <td colspan="2" style="padding: 12px 0 0; border-top: 1px solid #1A1A1A; font-size: 0; line-height: 0;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding-top: 12px; font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">
                @lang('shop::app.emails.orders.grand-total')
            </td>
            <td align="right" style="padding-top: 12px; font-family: 'Inter', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF;">
                {{ core()->formatPrice($order->grand_total, $order->order_currency_code) }}
            </td>
        </tr>

    </table>

@endcomponent
