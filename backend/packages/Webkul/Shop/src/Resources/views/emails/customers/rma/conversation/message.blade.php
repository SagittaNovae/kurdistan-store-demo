@component('shop::emails.layout')

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <p class="text-dark" style="margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #0F172A; line-height: 28px;">
                    @lang('shop::app.rma.mail.seller-conversation.title')
                </p>
                <p class="text-muted" style="margin: 0 0 6px; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.rma.mail.customer-conversation.heading', ['name' => $rmaMessage->rma->order->customer->name])
                </p>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.rma.mail.customer-conversation.quotes')
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="background-color: #F8FAFC; border-left: 3px solid #2969FF; border-radius: 4px; padding: 16px 20px;">
                <p class="text-dark" style="margin: 0 0 8px; font-size: 13px; font-weight: 700; color: #0F172A; text-transform: uppercase; letter-spacing: 0.6px;">
                    @lang('shop::app.rma.mail.customer-conversation.message')
                </p>
                <p class="text-mid" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    {{ $rmaMessage->message }}
                </p>
            </td>
        </tr>
    </table>

@endcomponent
