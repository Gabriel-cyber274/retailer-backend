<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->id }}</title>
</head>

<body style="font-family: Arial, sans-serif; color:#333; line-height:1.6;">

    <h2 style="color:#2d3748; margin-bottom:8px;">
        @if ($recipientType === 'admin')
            New Order Notification
        @elseif($recipientType === 'customer')
            Thank you for your purchase!
        @elseif($recipientType === 'customer_user')
            Customer Order Alert
        @else
            Order Confirmation
        @endif
    </h2>

    <p style="margin-top:0;">
        @if ($recipientType === 'admin')
            Hello Admin,<br>A new order has been placed. Details below:
        @elseif($recipientType === 'customer')
            Hello {{ $order->customer->name ?? 'Valued Customer' }},<br>Here’s your order summary:
        @elseif($recipientType === 'customer_user')
            Hello {{ $order->user->name ?? 'User' }},<br>
            Your customer <strong>{{ $order->customer->name ?? 'A customer' }}</strong> just placed an order.
            Here are the details:
        @else
            Hello {{ $order->user->name ?? 'Customer' }},<br>Your order has been placed successfully:
        @endif
    </p>


    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;">
        <tr>
            <td style="padding:12px 0;">
                <strong>Order ID:</strong> #{{ $order->id }}<br>
                <strong>Date:</strong> {{ optional($order->created_at)->format('M d, Y H:i') }}<br>
                <strong>Status:</strong> {{ ucfirst($order->status) }}<br>
                <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->type)) }}<br>
                <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}<br>
                @if (!empty($order->reference))
                    <strong>Reference:</strong> {{ $order->reference }}<br>
                @endif
            </td>
        </tr>

        <tr>
            <td style="padding:12px 0; border-top:1px solid #e2e8f0;">
                <strong>Delivery Address:</strong><br>
                {{ $order->address }}<br>
                @if (optional($order->state)->name)
                    {{ $order->state->name }}
                @endif
            </td>
        </tr>

        @if ($order->products && $order->products->count())
            <tr>
                <td style="padding:12px 0; border-top:1px solid #e2e8f0;">
                    <strong>Products Ordered</strong>
                    <ul style="margin:8px 0 0 18px; padding:0;">
                        @foreach ($order->products as $product)
                            <li>{{ $product->name ?? 'N/A' }} — Qty: {{ $product->pivot->quantity ?? 1 }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endif

        {{-- COST BREAKDOWN --}}
        <tr>
            <td style="padding:12px 0; border-top:1px solid #e2e8f0;">
                <strong>Cost Breakdown</strong>
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top:6px;">
                    <tr>
                        <td style="padding:4px 0;">Subtotal (Original Price)</td>
                        <td style="padding:4px 0; text-align:right;">
                            ₦{{ number_format((float) $order->original_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;">Dispatch Fee</td>
                        <td style="padding:4px 0; text-align:right;">
                            ₦{{ number_format((float) $order->dispatch_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; border-top:1px solid #e2e8f0;"><strong>Total</strong></td>
                        <td style="padding:6px 0; text-align:right; border-top:1px solid #e2e8f0;">
                            <strong>₦{{ number_format((float) $order->amount, 2) }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        @if (!empty($order->note))
            <tr>
                <td style="padding:12px 0; border-top:1px solid #e2e8f0;">
                    <strong>Note</strong>
                    <div style="margin-top:6px; white-space:pre-wrap;">
                        {{ $order->note }}
                    </div>
                </td>
            </tr>
        @endif
    </table>



    <p style="margin-top:16px;">
        @if ($recipientType === 'admin')
            Please review and process this order in the dashboard.<br><br>
            Regards,<br>{{ config('app.name') }} Team
        @elseif($recipientType === 'customer')
            We’ll notify you once your order is dispatched.<br><br>
            Thank you for choosing {{ config('app.name') }}.<br>
            Best regards,<br>{{ config('app.name') }} Team
        @elseif($recipientType === 'customer_user')
            Keep track of this order in your dashboard.<br><br>
            Regards,<br>{{ config('app.name') }} Team
        @else
            Your order is being processed. You’ll receive updates shortly.<br><br>
            Thanks for shopping with {{ config('app.name') }}.<br>
            Warm regards,<br>{{ config('app.name') }} Team
        @endif
    </p>


</body>

</html>
