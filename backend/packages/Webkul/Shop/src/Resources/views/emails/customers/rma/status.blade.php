@component('shop::emails.layout')

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <p class="text-dark" style="margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #0F172A; line-height: 28px;">
                    @lang('shop::app.rma.mail.status.title')
                </p>
                <p class="text-muted" style="margin: 0 0 8px; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.rma.mail.status.heading', ['name' => $rma->order->customer->name])
                </p>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.rma.mail.status.your-rma-id')
                    {!! trans('shop::app.rma.mail.status.status-change', [
                        'id' => '<a href="' . route('shop.customers.account.rma.view', $rma->id) . '" style="color: #2969FF; font-weight: 600; text-decoration: none;">#' . $rma->id . '</a>',
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="background-color: #F8FAFC; border-left: 3px solid #2969FF; border-radius: 4px; padding: 14px 20px;">
                <p class="text-dark" style="margin: 0 0 4px; font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.6px;">
                    @lang('shop::app.rma.mail.status.status')
                </p>
                <p class="text-mid" style="margin: 0; font-size: 16px; font-weight: 600; color: #0F172A;">
                    {{ $rma->status->title }}
                </p>
            </td>
        </tr>
    </table>

@endcomponent
