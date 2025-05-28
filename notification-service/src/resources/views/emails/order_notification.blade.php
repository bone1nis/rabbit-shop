<h2 style="font-family: Arial, sans-serif; color: #333;">Your Order Details</h2>

<p style="font-family: Arial, sans-serif;">
    <strong>Shipping Address:</strong> {{ $orderData['address'] }}<br>
    <strong>Phone Number:</strong> {{ $orderData['phone'] }}<br>
    <strong>Status:</strong>
    @php
        $status = $orderData['status'];
        $statusLabels = [
            'processing' => ['label' => 'Processing', 'color' => '#f0ad4e'],
            'completed' => ['label' => 'Completed', 'color' => '#5cb85c'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#d9534f'],
        ];
        $statusInfo = $statusLabels[$status] ?? ['label' => ucfirst($status), 'color' => '#777'];
    @endphp
    <span style="display: inline-block; padding: 4px 10px; color: white; background-color: {{ $statusInfo['color'] }}; border-radius: 4px;">
        {{ $statusInfo['label'] }}
    </span>
</p>

<h3 style="font-family: Arial, sans-serif; color: #333;">Products in your order:</h3>

<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
    <thead>
    <tr style="background-color: #f2f2f2;">
        <th style="border: 1px solid #ccc; padding: 8px;">ID</th>
        <th style="border: 1px solid #ccc; padding: 8px;">Product</th>
        <th style="border: 1px solid #ccc; padding: 8px;">Price</th>
        <th style="border: 1px solid #ccc; padding: 8px;">Quantity</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($orderData['products'] as $product)
        <tr>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ $product['id'] }}</td>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ $product['name'] }}</td>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ number_format($product['price'], 2) }} ₽</td>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ $product['quantity'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
