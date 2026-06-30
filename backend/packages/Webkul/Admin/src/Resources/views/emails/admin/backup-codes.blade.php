@component('admin::emails.layout')

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <p class="text-dark" style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 30px;">
                    @lang('admin::app.account.emails.common.dear', ['admin_name' => $admin->name])
                </p>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('admin::app.account.emails.backup-codes.greeting')
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        <tr>
            <td>
                <p class="text-muted" style="margin: 0; font-size: 15px; color: #475569; line-height: 24px;">
                    @lang('admin::app.account.emails.backup-codes.description')
                </p>
            </td>
        </tr>
    </table>

    {{-- Backup codes heading --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 16px;">
        <tr>
            <td>
                <p class="text-dark" style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #0F172A; line-height: 24px;">
                    @lang('admin::app.account.emails.backup-codes.codes-title')
                </p>
                <p class="text-muted" style="margin: 0; font-size: 14px; color: #64748B; line-height: 20px;">
                    @lang('admin::app.account.emails.backup-codes.codes-subtitle')
                </p>
            </td>
        </tr>
    </table>

    {{-- Backup codes grid (table-based for email compatibility) --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
        @foreach ($admin->two_factor_backup_codes->chunk(2) as $pair)
        <tr>
            @foreach ($pair as $code)
            <td width="48%" style="padding: 0 4px 8px 0; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="background-color: #F8FAFC; border: 2px solid #060C3B; border-radius: 6px; padding: 12px 16px; text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: 700; color: #060C3B; letter-spacing: 2px;">
                            {{ $code }}
                        </td>
                    </tr>
                </table>
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>

    {{-- Warning box --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="background-color: #FFF3CD; border: 1px solid #F59E0B; border-radius: 6px; padding: 16px 20px;">
                <p style="margin: 0 0 6px; font-size: 14px; font-weight: 700; color: #92400E; line-height: 22px;">
                    @lang('admin::app.account.emails.backup-codes.warning-title')
                </p>
                <p style="margin: 0; font-size: 14px; color: #92400E; line-height: 20px;">
                    @lang('admin::app.account.emails.backup-codes.warning-description')
                </p>
            </td>
        </tr>
    </table>

@endcomponent
