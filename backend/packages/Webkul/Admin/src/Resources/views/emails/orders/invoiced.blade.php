@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.orders.invoiced.preheader')@endslot

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #100C1F; border: 1px solid #1F1A3A; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #C084FC;">
                    @lang('admin::app.emails.orders.invoiced.title')
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    Invoice generated.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => core()->getAdminEmailDetails()['name']]) — {!! trans('admin::app.emails.orders.invoiced.greeting', [
                        'invoice_id' => $invoice->increment_id,
                        'order_id'   => '<a href="' . route('admin.sales.orders.view', $invoice->order_id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $invoice->order->increment_id . '</a>',
                        'created_at' => core()->formatDate($invoice->order->created_at, 'Y-m-d H:i'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 40px;">
        <tr>
            <td>
                <a href="{{ route('admin.sales.invoices.view', $invoice->id) }}"
                   style="display: inline-block; padding: 14px 32px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Invoice
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">
        @lang('admin::app.emails.orders.invoiced.summary')
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            @if ($invoice->order->shipping_address)
            <td class="addr-col" valign="top" width="50%" style="padding-right: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.shipping-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $invoice->order->shipping_address->name }}</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
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
            <td class="addr-col" valign="top" width="50%" style="padding-left: 10px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px;">
                    <tr>
                        <td style="padding: 18px;">
                            <p style="margin: 0 0 10px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.billing-address')</p>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF; font-weight: 500;">{{ $invoice->order->billing_address->name }}</p>
                            <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888; line-height: 20px;">
                                {{ $invoice->order->billing_address->address }}<br/>
                                {{ $invoice->order->billing_address->city }}, {{ $invoice->order->billing_address->state }}
                            </p>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555555;">@lang('admin::app.emails.orders.payment')</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #888888;">{{ core()->getConfigData('sales.payment_methods.' . $invoice->order->payment->method . '.title') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
            @endif
        </tr>
    </table>

    <p style="margin: 0 0 12px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Items Invoiced</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #1A1A1A; border-radius: 10px; overflow: hidden;">
        @foreach ($invoice->items as $item)
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
                                    {{ core()->formatPrice($item->sub_total_incl_tax, $invoice->order_currency_code) }}
                                @else
                                    {{ core()->formatPrice($item->sub_total, $invoice->order_currency_code) }}
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 24px;">
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.subtotal')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ core()->formatPrice($invoice->sub_total, $invoice->order_currency_code) }}</td>
        </tr>
        @if ($invoice->shipping_amount > 0)
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.shipping-handling')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ core()->formatPrice($invoice->shipping_amount, $invoice->order_currency_code) }}</td>
        </tr>
        @endif
        @if ($invoice->tax_amount > 0)
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.tax')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ core()->formatPrice($invoice->tax_amount, $invoice->order_currency_code) }}</td>
        </tr>
        @endif
        @if ($invoice->discount_amount > 0)
        <tr>
            <td style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666;">@lang('admin::app.emails.orders.discount')</td>
            <td align="right" style="padding-bottom: 8px; font-family: 'Inter', sans-serif; font-size: 13px; color: #4ADE80;">-{{ core()->formatPrice($invoice->discount_amount, $invoice->order_currency_code) }}</td>
        </tr>
        @endif
        <tr><td colspan="2" style="padding: 10px 0 0; border-top: 1px solid #1A1A1A;">&nbsp;</td></tr>
        <tr>
            <td style="padding-top: 10px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; color: #FFFFFF;">@lang('admin::app.emails.orders.grand-total')</td>
            <td align="right" style="padding-top: 10px; font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">{{ core()->formatPrice($invoice->grand_total, $invoice->order_currency_code) }}</td>
        </tr>
    </table>

@endcomponent
