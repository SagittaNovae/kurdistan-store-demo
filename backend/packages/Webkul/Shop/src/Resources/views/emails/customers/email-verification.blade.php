@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.customers.verification.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #0C1F0C; border: 1px solid #1D3A1D; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #4ADE80;">
                    Verify Email
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    Confirm your email.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $customer->name]) — @lang('shop::app.emails.customers.verification.description')
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <a href="{{ route('shop.customers.verify', $customer->token) }}"
                   style="display: inline-block; padding: 16px 36px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    @lang('shop::app.emails.customers.verification.verify-email')
                </a>
            </td>
        </tr>
    </table>

@endcomponent
