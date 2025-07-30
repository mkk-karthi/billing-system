<div style="padding: 20px">
    <div>
        <div style="text-align: center">
            <h3><b>{{ env('APP_NAME') }}</b></h3>
            <p><b>Inv. No:</b> {{ $order['invoice_no'] }}</p>
        </div>
        <div style="display:flex">
            <div style="width:50%">
                <p><b>Customer name:</b> {{ $order['user']['name'] }}</p>
                <p><b>Customer email:</b> {{ $order['user']['email'] }}</p>
            </div>
            <div style="width:50%; text-align:end;">
                <p><b>Date:</b> {{ $order['date'] }}</p>
                <p><b>Time:</b> {{ $order['time'] }}</p>
            </div>
        </div>
    </div>
    <div>
        <h4>Bill section:</h4>
    </div>
    <table style="width: 100%; border-collapse: collapse; border: solid 1px black;">
        <thead>
            <tr>
                <th style="border: solid 1px black;">ID</th>
                <th style="border: solid 1px black;">Product</th>
                <th style="border: solid 1px black;">Unit Price</th>
                <th style="border: solid 1px black;">Tax (%)</th>
                <th style="border: solid 1px black;">Tax Price</th>
                <th style="border: solid 1px black;">Quantity</th>
                <th style="border: solid 1px black;">Total price</th>
            </tr>
        </thead>
        <tbody>
            @if (count($order['order_details']))
                @foreach ($order['order_details'] as $orderDetail)
                    <tr>
                        <td style="border: solid 1px black;">{{ $orderDetail['product_sku'] }}</td>
                        <td style="border: solid 1px black;">{{ $orderDetail['product_name'] }}</td>
                        <td style="border: solid 1px black;">{{ $orderDetail['product_price'] }}</td>
                        <td style="border: solid 1px black;">{{ $orderDetail['order_tax'] }}%</td>
                        <td style="border: solid 1px black;">{{ $orderDetail['tax_price'] }}</td>
                        <td style="border: solid 1px black;">{{ $orderDetail['product_qty'] }}</td>
                        <td style="border: solid 1px black;">{{ $orderDetail['total_price'] }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div style="margin-top:20px; ">

        {{-- @if (!empty($order['order_denominations']))

            <div style="display: flex; min-width: 50%;  float: left;">
                <b style="min-width: 50%">Payment denominations:</b>

                <table style="width: 100%; border-collapse: collapse; text-align: right; float: left;">
                    <tr>
                        <th style="border: solid 1px black;">Amount</th>
                        <th style="border: solid 1px black;">Count</th>
                    </tr>
                    @foreach ($order['order_denominations'] as $denomination => $count)
                        <tr>
                            <td style="border: solid 1px black;">{{ $denomination }}: </td>
                            <td style="border: solid 1px black;">{{ $count }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif --}}

        <div style="min-width: 50%; float: right;">
            <table style="text-align: end; float: right;">
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
    </div>
</div>
