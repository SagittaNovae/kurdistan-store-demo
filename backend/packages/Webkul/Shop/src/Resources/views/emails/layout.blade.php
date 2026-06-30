<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @if (in_array(app()->getLocale(), ['ar', 'ku', 'fa', 'he', 'ur'])) dir="rtl" @endif>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="color-scheme" content="dark" />
        <meta name="supported-color-schemes" content="dark" />
        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <style type="text/css">
            body {
                margin: 0;
                padding: 0;
                background-color: #080808;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            table {
                border-collapse: collapse;
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
            }
            img {
                border: 0;
                outline: none;
                text-decoration: none;
                -ms-interpolation-mode: bicubic;
            }
            a { color: #FFFFFF; }
            p { margin: 0; }

            /* Dark mode is the default — reinforce for clients that override */
            @media (prefers-color-scheme: dark) {
                body, .body-bg { background-color: #080808 !important; }
                .email-bg { background-color: #000000 !important; }
            }

            @media only screen and (max-width: 640px) {
                .email-wrapper { width: 100% !important; min-width: 100% !important; }
                .content-pad  { padding: 36px 24px !important; }
                .header-pad   { padding: 32px 24px !important; }
                .footer-pad   { padding: 28px 24px 36px !important; }
                .addr-col     {
                    display: block !important;
                    width: 100% !important;
                    padding-right: 0 !important;
                    padding-bottom: 28px !important;
                }
                .btn-wrap { width: 100% !important; text-align: center !important; }
                .btn-full { display: block !important; text-align: center !important; }
            }
        </style>
    </head>

    <body class="body-bg" style="margin: 0; padding: 0; background-color: #080808; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;">

        @isset($preheader)
        <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all;">{{ trim($preheader) }}&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;</div>
        @endisset

        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center"><![endif]-->

        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #080808;">
            <tr>
                <td align="center" style="padding: 48px 16px 56px;">

                    <table class="email-wrapper email-bg" width="600" cellpadding="0" cellspacing="0" role="presentation"
                        style="max-width: 600px; width: 100%; background-color: #000000; border: 1px solid #1A1A1A; border-radius: 12px;">

                        {{-- ─── Header ──────────────────────────────────────────────── --}}
                        <tr>
                            <td class="header-pad" align="center" style="padding: 44px 52px 40px;">
                                <a href="{{ route('shop.home.index') }}" style="text-decoration: none; display: inline-block; line-height: 1;">
                                    @if ($logo = core()->getCurrentChannel()->logo_url)
                                        <img src="{{ $logo }}" alt="{{ config('app.name') }}" style="height: 28px; max-width: 140px; display: block;" />
                                    @else
                                        <span style="font-family: 'Inter', -apple-system, sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 5px; color: #FFFFFF; text-transform: uppercase; line-height: 1; text-decoration: none;">KURDISTAN STORE</span>
                                    @endif
                                </a>
                            </td>
                        </tr>

                        {{-- Header hairline --}}
                        <tr>
                            <td style="font-size: 0; line-height: 0; border-top: 1px solid #1A1A1A;">&nbsp;</td>
                        </tr>

                        {{-- ─── Body ────────────────────────────────────────────────── --}}
                        <tr>
                            <td class="content-pad" style="padding: 48px 52px;">
                                {{ $slot }}
                            </td>
                        </tr>

                        {{-- Footer hairline --}}
                        <tr>
                            <td style="font-size: 0; line-height: 0; border-top: 1px solid #1A1A1A;">&nbsp;</td>
                        </tr>

                        {{-- ─── Footer ──────────────────────────────────────────────── --}}
                        <tr>
                            <td class="footer-pad" style="padding: 32px 52px 44px;">

                                {{-- Help line --}}
                                <p style="margin: 0 0 6px; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #444444;">
                                    Need help?
                                </p>

                                @php $contact = core()->getContactEmailDetails(); @endphp

                                <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666; line-height: 20px;">
                                    <a href="mailto:{{ $contact['email'] }}" style="color: #888888; text-decoration: none;">{{ $contact['email'] }}</a>
                                </p>

                                @php $phone = core()->getConfigData('general.general.contact.phone'); @endphp
                                @if ($phone)
                                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #666666; line-height: 20px;">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" style="color: #888888; text-decoration: none;">WhatsApp</a>
                                    &nbsp;&middot;&nbsp;
                                    <span style="color: #666666;">{{ $phone }}</span>
                                </p>
                                @endif

                                {{-- Footer rule --}}
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 20px 0 16px;">
                                    <tr>
                                        <td style="border-top: 1px solid #141414; font-size: 0; line-height: 0;">&nbsp;</td>
                                    </tr>
                                </table>

                                <p style="margin: 0 0 4px; font-family: 'Inter', sans-serif; font-size: 12px; color: #3A3A3A; line-height: 18px;">
                                    &copy; {{ date('Y') }} Kurdistan Store. All rights reserved.
                                </p>
                                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; color: #2E2E2E; line-height: 18px;">
                                    You received this because you have an account or placed an order at <a href="{{ route('shop.home.index') }}" style="color: #3A3A3A; text-decoration: none;">Kurdistan Store</a>.
                                </p>

                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>

        <!--[if mso]></td></tr></table><![endif]-->

    </body>
</html>
