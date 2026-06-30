@component('admin::emails.layout')

    @slot('preheader')@lang('admin::app.emails.customers.registration.preheader')@endslot

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #0C1F0C; border: 1px solid #1D3A1D; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #4ADE80;">
                    New Customer
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; color: #FFFFFF; line-height: 30px;">
                    A new customer registered.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 14px; color: #888888; line-height: 22px;">
                    @lang('admin::app.emails.dear', ['admin_name' => core()->getAdminEmailDetails()['name']]) — @lang('admin::app.emails.customers.registration.greeting')
                </p>
            </td>
        </tr>
    </table>

    {{-- Customer details card --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td style="border: 1px solid #1A1A1A; border-radius: 10px; padding: 20px 22px;">
                <p style="margin: 0 0 14px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #555555;">Customer Details</p>
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="padding-bottom: 12px; width: 35%;">
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #444444;">Name</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ $customer->first_name . ' ' . $customer->last_name }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px;">
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #444444;">Email</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ $customer->email }}</p>
                        </td>
                    </tr>
                    @if ($customer->phone)
                    <tr>
                        <td>
                            <p style="margin: 0 0 2px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #444444;">Phone</p>
                            <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #FFFFFF;">{{ $customer->phone }}</p>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <a href="{{ route('admin.customers.customers.view', $customer->id) }}"
                   style="display: inline-block; padding: 14px 32px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Customer
                </a>
            </td>
        </tr>
    </table>

@endcomponent
