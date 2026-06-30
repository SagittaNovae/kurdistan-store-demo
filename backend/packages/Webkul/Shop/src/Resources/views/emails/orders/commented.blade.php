@component('shop::emails.layout')

    @slot('preheader')@lang('shop::app.emails.orders.commented.preheader')@endslot

    {{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td>
                <span style="display: inline-block; padding: 3px 10px; background-color: #111111; border: 1px solid #222222; border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #888888;">
                    Order Update
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.5px; color: #FFFFFF; line-height: 36px;">
                    New note on your order.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 12px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #888888; line-height: 26px;">
                    {!! trans('shop::app.emails.orders.commented.title', [
                        'order_id'   => '<a href="' . route('shop.customers.account.orders.view', $comment->order_id) . '" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">#' . $comment->order->increment_id . '</a>',
                        'created_at' => core()->formatDate($comment->order->created_at, 'Y-m-d'),
                    ]) !!}
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── Note card ──────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 36px;">
        <tr>
            <td style="border: 1px solid #1A1A1A; border-left: 2px solid #555555; border-radius: 0 10px 10px 0; padding: 20px 24px;">
                <p style="margin: 0; font-family: 'Inter', sans-serif; font-size: 15px; color: #CCCCCC; line-height: 26px;">{{ $comment->comment }}</p>
            </td>
        </tr>
    </table>

    {{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td>
                <a href="{{ route('shop.customers.account.orders.view', $comment->order_id) }}"
                   style="display: inline-block; padding: 16px 36px; background-color: #FFFFFF; color: #000000; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; border-radius: 8px; line-height: 1;">
                    View Order
                </a>
            </td>
        </tr>
    </table>

@endcomponent
