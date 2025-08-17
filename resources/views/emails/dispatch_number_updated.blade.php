<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Number Updated</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 650px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            color: #555555;
            font-size: 16px;
            line-height: 1.5;
        }

        .order-details {
            background-color: #f9fafb;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .order-details h3 {
            margin-top: 0;
        }

        .order-details ul {
            padding-left: 20px;
        }

        .button {
            display: inline-block;
            background-color: #1d72b8;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            color: #888888;
            font-size: 12px;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1>Dispatch Number Updated - Order #{{ $order->id }}</h1>

        @if ($recipientType === 'user')
            <p>Hello {{ $order->user->name }},</p>
            <p>The dispatch number for your order <strong>#{{ $order->id }}</strong> has been updated.</p>
        @elseif($recipientType === 'customer')
            <p>Hello {{ $order->customer->name }},</p>
            <p>The dispatch number for your order <strong>#{{ $order->id }}</strong> has been updated.</p>
        @elseif($recipientType === 'customer_user')
            <p>Hello {{ $order->user->name }},</p>
            <p>The dispatch number for your customer's order <strong>#{{ $order->id }}</strong> has been updated.</p>
        @else
            <p>Hello Admin,</p>
            <p>The dispatch number for order <strong>#{{ $order->id }}</strong> has been updated.</p>
        @endif

        <div class="order-details">
            <h3>Order Summary</h3>
            <p><strong>New Dispatch Number:</strong> {{ $order->dispatch_number ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Amount:</strong> {{ number_format($order->amount, 2) }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
            @if ($order->note)
                <p><strong>Note:</strong> {{ $order->note }}</p>
            @endif

            @if ($order->products->isNotEmpty())
                <h4>Products:</h4>
                <ul>
                    @foreach ($order->products as $product)
                        <li>{{ $product->name }} - Quantity: {{ $product->pivot->quantity }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="footer">
            Thanks,<br>
            {{ config('app.name') }}
        </div>
    </div>
</body>

</html>
