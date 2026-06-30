@component('shop::emails.layout')

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <span style="display: inline-block; background-color: #FFF7ED; color: #92400E; font-size: 12px; font-weight: 600; letter-spacing: 0.6px; text-transform: uppercase; padding: 4px 12px; border-radius: 20px;">
                    Payment Reminder
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p class="text-dark" style="margin: 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 30px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $invoice->order->customer_full_name])
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
        <tr>
            <td>
                <p class="text-muted" style="margin: 0 0 16px; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.emails.customers.reminder.invoice-overdue')
                </p>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('shop::app.emails.customers.reminder.already-paid')
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <a href="{{ route('shop.customers.account.orders.view', $invoice->order_id) }}"
                   style="display: inline-block; padding: 13px 32px; background-color: #2969FF; color: #FFFFFF; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 6px; line-height: 1;">
                    View Invoice
                </a>
            </td>
        </tr>
    </table>

@endcomponent
