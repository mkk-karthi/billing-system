@extends('layout')

@section('content')
    <section class="container-fluid">
        <div class="d-flex justify-content-start">
            <a href="{{ url('/orders') }}" class="btn btn-primary m-2">
                <i class="bi bi-arrow-left"></i>
                Back</a>
        </div>
        <div class="card">
            <div class="card-title text-center border-bottom">
                <p class="fw-bold fs-3">Orders</p>
            </div>
            <div class="card-body mt-3 p-2">
                <div class="my-2"><b>Customer email: </b>{{ $user['email'] }}</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Ref.No</th>
                                <th>Total Price</th>
                                <th>Total Tax</th>
                                <th>Discount</th>
                                <th>Total Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($orders))
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $order['ref_no'] }}</td>
                                        <td>{{ $order['total_price'] }}</td>
                                        <td>{{ $order['tax_price'] }}</td>
                                        <td>{{ $order['discount'] }}
                                        <td>{{ $order['total_items'] }}
                                        </td>
                                        <td>
                                            <a href="{{ route('order.view', ['id' => $order['order_id']]) }}"
                                                class="btn btn-primary">
                                                Receipt
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
