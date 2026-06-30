@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.customers.update-password.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #111111; border: 1px solid #222222; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #888888;">
                    Security
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    Password updated.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $customer->name]) — @lang('shop::app.emails.customers.update-password.description')
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── Security note ──────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #444444; line-height: 20px;">
                    If you didn&rsquo;t make this change, contact our support team immediately.
                </p>
            </td>
        </tr>
    </table>

@endcomponent
