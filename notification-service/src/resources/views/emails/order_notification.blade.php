<h2>Информация о вашем заказе</h2>

<p><strong>Адрес:</strong> {{ $orderData['address'] }}</p>
<p><strong>Телефон:</strong> {{ $orderData['phone'] }}</p>
<p><strong>Статус:</strong> {{ $orderData['status'] }}</p>

<h3>Товары в заказе:</h3>
<table style="border-collapse: collapse; border: 1px solid #ccc;">
    <thead>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Количество</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($orderData['products'] as $product)
        <tr>
            <td>{{ $product['id'] }}</td>
            <td>{{ $product['name'] }}</td>
            <td>{{ number_format($product['price'], 2) }} ₽</td>
            <td>{{ $product['quantity'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
