@component('shop::emails.layout')

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
        <tr>
            <td>
                <p class="text-dark" style="margin: 0 0 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 30px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $customerNote->customer->name])
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="background-color: #F8FAFC; border-left: 3px solid #2969FF; border-radius: 4px; padding: 16px 20px;">
                <p class="text-mid" style="margin: 0; font-size: 15px; color: #334155; line-height: 24px;">
                    @lang('shop::app.emails.customers.commented.description', ['note' => $customerNote->note])
                </p>
            </td>
        </tr>
    </table>

@endcomponent
