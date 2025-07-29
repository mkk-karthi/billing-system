<div>
    <p><b>Inv. No:</b> {{ $order['invoice_no'] }}</p>
    <p><b>Customer name:</b> {{ $order['user']['name'] }}</p>
    <p><b>Customer email:</b> {{ $order['user']['email'] }}</p>
    <p><b>Date:</b> {{ $order['date'] }}</p>
    <p><b>Time:</b> {{ $order['time'] }}</p>
</div>
<div><b>Bill section</b></div>
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Unit Price</th>
            <th>Tax (%)</th>
            <th>Tax Price</th>
            <th>Quantity</th>
            <th>Total price</th>
        </tr>
    </thead>
    <tbody>
        @if (count($order['order_details']))
            @foreach ($order['order_details'] as $orderDetail)
                <tr>
                    <td>{{ $orderDetail['product_sku'] }}</td>
                    <td>{{ $orderDetail['product_name'] }}</td>
                    <td>{{ $orderDetail['product_price'] }}</td>
                    <td>{{ $orderDetail['order_tax'] }}%</td>
                    <td>{{ $orderDetail['tax_price'] }}</td>
                    <td>{{ $orderDetail['product_qty'] }}</td>
                    <td>{{ $orderDetail['total_price'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div style="display: flex; text-align: end; margin-top:20px; justify-content: end;">
    <table style="text-align: right;">
        <tr>
            <td>Total price without tax:</td>
            <td>{{ $order['order_total_price'] }}</td>
        </tr>
        <tr>
            <td>Total tax payable:</td>
            <td>{{ $order['order_tax_price'] }}</td>
        </tr>
        <tr>
            <td>Discount:</td>
            <td>{{ $order['order_discount'] }}</td>
        </tr>
        <tr>
            <td>Net price:</td>
            <td>{{ $order['net_price'] }}</td>
        </tr>
        <tr>
            <td>Round of net price:</td>
            <td>{{ $order['round_net_price'] }}</td>
        </tr>
        <tr>
            <td>Balance amount:</td>
            <td>{{ $order['order_balance_amount'] }}</td>
        </tr>
    </table>
</div>

<hr>

@if (!empty($order['balance_denominations']))
    <div style="display: flex; text-align: end; margin-top:20px; justify-content: end; flex-direction: column;">
        <b>Balance denomination</b>

        <table style="width: 100%; text-align: right;">
            @foreach ($order['balance_denominations'] as $denomination => $count)
                <tr>
                    <td>{{ $denomination }}: </td>
                    <td style="width: 30px">{{ $count }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
