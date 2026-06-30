@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.customers.forgot-password.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #1F1800; border: 1px solid #3D3000; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #FBBF24;">
                    Password Reset
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    Reset your password.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $userName]) — @lang('shop::app.emails.customers.forgot-password.description')
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 32px;">
        <tr>
            <td>
                <a href="{{ route('shop.customers.reset_password.create', $token) }}"
                   style="display: inline-block; padding: 16px 36px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    @lang('shop::app.emails.customers.forgot-password.reset-password')
                </a>
            </td>
        </tr>
    </table>

    {{-- ─── Security note ──────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #444444; line-height: 20px;">
                    If you didn&rsquo;t request a password reset, no action is needed. This link will expire in 60 minutes.
                </p>
            </td>
        </tr>
    </table>

@endcomponent
