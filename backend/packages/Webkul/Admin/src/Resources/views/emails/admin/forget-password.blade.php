@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.admin.forgot-password.preheader')@endslot

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #1F1800; border: 1px solid #3A2E00; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #FBBF24;">
                    Password Reset
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    Reset your password.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => $admin->name]) — @lang('admin::app.emails.admin.forget-password.description')
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <a href="{{ route('admin.session.forgot-password.create', ['token' => $token]) }}"
                   style="display: inline-block; padding: 14px 32px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    @lang('admin::app.emails.admin.forget-password.reset-password')
                </a>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #333333; line-height: 20px;">
                    If you didn&rsquo;t request this, ignore this email. Your password won&rsquo;t change until you use the link above. This link expires in 60 minutes.
                </p>
            </td>
        </tr>
    </table>

@endcomponent
