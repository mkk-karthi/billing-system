@extends('layout')

@section('content')
    <div class="container-fluid p-3">
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('user.orders', ['id' => $order['user_id']]) }}" class="btn btn-primary m-2">
                        <i class="bi bi-arrow-left"></i>
                        Back</a>
                    <button class="btn btn-primary m-2" onclick="printData()">
                        <i class="bi bi-printer"></i>
                        Print</button>
                </div>
                <div id="print">
                    <div class="row">
                        <div class="col-12 mt-3 text-center">
                            <p class="fw-bold fs-5">Inv. No: {{ $order['invoice_no'] }}</p>
                        </div>
                        <div class="col-12 col-sm-6 mt-3">
                            <p class="fw-bold fs-5">Customer:</p>
                            <table class="w-100">
                                <tr>
                                    <td class="w-25">
                                        <p class="fw-bold my-0 me-2">Name</p>
                                    </td>
                                    <td style="width: 30px">{{ $order['user']['name'] }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="fw-bold my-0 me-2">Email</p>
                                    </td>
                                    <td style="width: 30px">{{ $order['user']['email'] }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 col-sm-3">
                        </div>
                        <div class="col-12 col-sm-3 mt-3">
                            <table class="w-100">
                                <tr>
                                    <td class="w-25">
                                        <p class="fw-bold my-0 me-2">Date</p>
                                    </td>
                                    <td style="width: 30px">{{ $order['date'] }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="fw-bold my-0 me-2">Time</p>
                                    </td>
                                    <td style="width: 30px">{{ $order['time'] }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 mt-3">
                            <p class="fw-bold fs-5">Bill section:</p>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product</th>
                                            <th>Unit Price</th>
                                            <th>Tax %</th>
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
                                                    <td>
                                                        <p class="fw-medium m-0">{{ $orderDetail['product_name'] }}</p>
                                                        <p class="text-secondary m-0">{{ $orderDetail['varient_name'] }}</p>
                                                    </td>
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
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row flex-row-reverse">
                                <div class="col-12 col-sm-6 mb-3">
                                    <table class="w-100 text-end">
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
                                @if (!empty($order['order_denominations']))
                                    <div class="col-12 col-sm-6 text-end d-flex mb-3">
                                        <p class="fw-bold me-3">Payment Denominations:</p>
                                        <table class="table table-bordered text-end" style="width: fit-content">
                                            <tr>
                                                <th>Amount</th>
                                                <th>Count</th>
                                            </tr>
                                            @foreach ($order['order_denominations'] as $denomination => $count)
                                                @if (!empty($count))
                                                    <tr>
                                                        <td>{{ $denomination }}</td>
                                                        <td>{{ $count }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printData() {
            let printWindow = window.open();
            let printContent = document.getElementById('print').innerHTML;

            printWindow.document.write();
            printWindow.document.write(`<html>
				<head>
					<title>Receipt - {{ $order['invoice_no'] }}</title>
					<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
				</head>
				<body> ${printContent} </body></html>`);
            printWindow.print();
            printWindow.close();
        }
    </script>

@endsection
