<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->id }}</title>
</head>

<body>
    <h3>
        @if ($recipientType === 'admin')
            Hello Admin,
        @elseif($recipientType === 'customer')
            Hello {{ $order->customer->name ?? 'Customer' }},
        @else
            Hello {{ $order->user->name ?? 'User' }},
        @endif
    </h3>

    <p>An order has been placed.</p>

    <p><strong>Order ID:</strong> {{ $order->id }}</p>
    <p><strong>Amount:</strong> ₦{{ number_format($order->amount, 2) }}</p>
    <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->type)) }}</p>
    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    <p><strong>Address:</strong> {{ $order->address }}</p>

    @if ($order->products && $order->products->count())
        <h4>Products</h4>
        <ul>
            @foreach ($order->products as $product)
                <li>
                    {{ $product->name ?? 'N/A' }} —
                    qty: {{ $product->pivot->quantity ?? 1 }}
                </li>
            @endforeach
        </ul>
    @endif

    @if ($order->resells && $order->resells->count())
        <h4>Retail items</h4>
        <ul>
            @foreach ($order->resells as $resell)
                <li>
                    {{ $resell->name ?? 'N/A' }} —
                    qty: {{ $resell->pivot->quantity ?? 1 }}
                </li>
            @endforeach
        </ul>
    @endif

    <p>Thank you.</p>
</body>

</html>
