@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.customers.new-customer.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #0C1F0C; border: 1px solid #1D3A1D; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #4ADE80;">
                    Welcome
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    Your account is ready.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    @lang('shop::app.emails.dear', ['customer_name' => $customer->name]) — @lang('shop::app.emails.customers.registration.credentials-description')
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── Credentials card ───────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 40px;">
        <tr>
            <td style="border: 1px solid #1A1A1A; border-radius: 10px; padding: 24px;">
                <p style="margin: 0 0 16px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Account Credentials</p>
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="padding-bottom: 16px;">
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 600; color: #555555; text-transform: uppercase; letter-spacing: 0.8px;">
                                @lang('shop::app.emails.customers.registration.username-email')
                            </p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #FFFFFF; font-weight: 500;">{{ $customer->email }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 600; color: #555555; text-transform: uppercase; letter-spacing: 0.8px;">
                                @lang('shop::app.emails.customers.registration.password')
                            </p>
                            <p style="margin: 0; font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; font-size: 15px; color: #FFFFFF; font-weight: 500; letter-spacing: 0.5px;">{{ $password }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <a href="{{ route('shop.customer.session.index') }}"
                   style="display: inline-block; padding: 16px 36px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    @lang('shop::app.emails.customers.registration.sign-in')
                </a>
            </td>
        </tr>
    </table>

@endcomponent
