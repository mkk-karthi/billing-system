<div>
    <p><b>Customer email:</b> {{ $order['email'] }}</p>
</div>
<div><b>Bill section</b></div>
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Purchase Price</th>
            <th>Tax % for item</th>
            <th>Tax payable for item</th>
            <th>Total price of the item</th>
        </tr>
    </thead>
    <tbody>
        @if (count($order['order_details']))
            @foreach ($order['order_details'] as $order_detail)
                <tr>
                    <td>{{ $order_detail['product_name'] }}</td>
                    <td>{{ $order_detail['product_price'] }}</td>
                    <td>{{ $order_detail['product_qty'] }}</td>
                    <td>{{ $order_detail['order_price'] }}</td>
                    <td>{{ $order_detail['order_tax'] }}%</td>
                    <td>{{ $order_detail['tax_price'] }}</td>
                    <td>{{ $order_detail['total_price'] }}</td>
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
            <td>Net price of the purchased item:</td>
            <td>{{ $order['net_price'] }}</td>
        </tr>
        <tr>
            <td>Rounded down value of the purchased items net price:</td>
            <td>{{ $order['round_net_price'] }}</td>
        </tr>
        <tr>
            <td>Balance payable to the customer:</td>
            <td>{{ $order['order_balance_amount'] }}</td>
        </tr>
    </table>
</div>

<hr>

<div style="display: flex; text-align: end; margin-top:20px; justify-content: end; flex-direction: column;">
    <b>Balance denomination</b>

    <table style="width: 100%; text-align: right;">
        @if (count($order['balance_denominations']))
            @foreach ($order['balance_denominations'] as $denomination => $count)
                <tr>
                    <td>{{ $denomination }}: </td>
                    <td style="width: 30px">{{ $count }}</td>
                </tr>
            @endforeach
        @endif
    </table>
</div>
