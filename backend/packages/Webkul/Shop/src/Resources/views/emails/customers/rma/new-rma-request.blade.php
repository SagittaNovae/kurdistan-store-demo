@component('shop::emails.layout')

    {{-- Heading --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <p class="text-dark" style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 30px;">
                    @lang('shop::app.rma.mail.customer-rma-create.heading')
                </p>
                <p class="text-muted" style="margin: 0 0 8px; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.rma.mail.customer-rma-create.hello', ['name' => $rma->order->customer->name])
                </p>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    {!! trans('shop::app.rma.mail.customer-rma-create.greeting', [
                        'order_id' => '<a href="' . route('shop.customers.account.orders.view', $rma->order_id) . '" style="color: #2969FF; font-weight: 600; text-decoration: none;">#' . $rma->order_id . '</a>',
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    {{-- Summary card --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td style="background-color: #F8FAFC; border-left: 3px solid #2969FF; border-radius: 6px; padding: 20px 24px;">
                <p class="text-dark" style="margin: 0 0 16px; font-size: 15px; font-weight: 700; color: #0F172A;">
                    @lang('shop::app.rma.mail.customer-rma-create.summary')
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td width="48%" valign="top" style="padding-right: 16px;">
                            <p style="margin: 0 0 4px; font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                                @lang('shop::app.rma.mail.customer-rma-create.rma-id')
                            </p>
                            <p style="margin: 0; font-size: 18px; font-weight: 700; color: #2969FF;">
                                {{ $rma->id }}
                            </p>
                        </td>
                        <td width="48%" valign="top">
                            <p style="margin: 0 0 4px; font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                                @lang('shop::app.rma.mail.customer-rma-create.order-id')
                            </p>
                            <p style="margin: 0; font-size: 18px; font-weight: 700; color: #0F172A;">
                                #{{ $rma->order_id }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Additional information --}}
    @if ($rma->information)
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td style="background-color: #FFF1F2; border-radius: 6px; padding: 16px 20px;">
                <p class="text-dark" style="margin: 0 0 8px; font-size: 14px; font-weight: 700; color: #0F172A;">
                    @lang('shop::app.rma.mail.customer-rma-create.additional-information')
                </p>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    {{ $rma->information }}
                </p>
            </td>
        </tr>
    </table>
    @endif

    {{-- Products heading --}}
    <p class="text-dark" style="margin: 0 0 16px; font-size: 14px; font-weight: 700; color: #0F172A; text-transform: uppercase; letter-spacing: 0.8px;">
        @lang('shop::app.rma.mail.customer-rma-create.requested-rma-product')
    </p>

    {{-- Products table --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden;">
        <thead>
            <tr style="background-color: #F8FAFC;">
                @php($headings = Lang::get('shop::app.rma.mail.customer-data-table-heading'))
                @foreach ($headings as $heading)
                <th style="text-align: left; padding: 11px 14px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.6px; border-bottom: 1px solid #E2E8F0;">
                    {{ $heading }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rma->items as $item)
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 12px 14px; font-size: 14px; color: #0F172A; font-weight: 500; vertical-align: top;">{{ $item->orderItem->name }}</td>
                <td style="padding: 12px 14px; font-size: 14px; color: #475569; vertical-align: top;">{{ $item->quantity }}</td>
                <td style="padding: 12px 14px; font-size: 14px; color: #475569; vertical-align: top;">{{ $item->reason->title }}</td>
                <td style="padding: 12px 14px; font-size: 13px; color: #64748B; vertical-align: top;">{{ $item->orderItem->sku }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endcomponent
