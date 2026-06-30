@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.customers.subscribed.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #111111; border: 1px solid #222222; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #888888;">
                    Newsletter
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    You&rsquo;re subscribed.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => ! empty($fullName) ? $fullName : $subscribersList->email]) — @lang('shop::app.emails.customers.subscribed.description')
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── Unsubscribe (secondary action) ────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <a href="{{ route('shop.subscription.destroy', $subscribersList->token) }}"
                   style="display: inline-block; padding: 13px 28px; background-color: transparent; color: #555555; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; border: 1px solid #2A2A2A; line-height: 1;">
                    @lang('shop::app.emails.customers.subscribed.unsubscribe')
                </a>
            </td>
        </tr>
    </table>

@endcomponent
