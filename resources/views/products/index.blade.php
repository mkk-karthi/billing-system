@extends('layout')

@section('content')
    @includeIf('products.error_model')
    <section class="container-fluid">
        <div class="card">
            <div class="card-title text-center border-bottom">
                <p class="fw-bold fs-3">Products</p>
            </div>
            <div class="card-body py-3">
                <div class="d-flex justify-content-end">
                    <a href="{{ url('/product/create') }}" class="btn btn-primary m-2">
                        <i class="bi bi-plus-lg"></i>
                        Create</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Product SKU</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Varient</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="post-list">
                            @if (count($productsData) > 0)
                                @foreach ($productsData as $product)
                                    <tr>
                                        <td>{{ $product['product_sku'] }}</td>
                                        <td>{{ $product['product_name'] }}
                                        </td>
                                        <td>
                                            <img src="{{ url($product['product_image']) }}"
                                                alt="{{ $product['product_name'] }}" width="60" />
                                        </td>
                                        <td>{{ $product['varient_name'] }}</td>
                                        <td>{{ $product['product_quantity'] }}</td>
                                        <td>{{ $product['product_price'] }}</td>
                                        <td>
                                            <a href="{{ url('/product/edit/' . $product['product_id']) }}"
                                                class="btn btn-success btn-sm mx-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm mx-1 delete-product"
                                                data-id="{{ $product['product_id'] }}" data-action="delete">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    @if (count($products) > 0)
                        {{ $products->OnEachSide(1)->links() }}
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteModalLabel">Product Delete</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure to delete this product?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="delete-conform">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(() => {

            let productId = 0;
            $(".delete-product").each(function() {
                $(this).click(function() {
                    productId = $(this).attr("data-id");
                    $("#deleteModal").modal("show")
                })
            })

            $("#delete-conform").click(() => {
                if (productId > 0) {
                    $.ajax({
                        url: "{{ route('product.delete') }}",
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: productId
                        },
                        success: (res) => {
                            productId = 0;
                            $("#deleteModal").modal("hide")
                            if (res.code == 0) {
                                const msgContent =
                                    `<div class="alert alert-success" role="alert">${res.message}</div>`;
                                $("#messages").append(msgContent)

                                setTimeout(() => {
                                    location.reload();
                                }, 1000);

                            } else {
                                const msgContent =
                                    `<div class="alert alert-danger" role="alert">${res.message}</div>`;
                                $("#messages").append(msgContent)

                                setTimeout(() => {
                                    $("#messages").html($("#messages").html()
                                        .replace(msgContent, ""))
                                }, 5000);

                            }
                        },
                        error: (err) => {
                            $("#product-form-submit").prop("disabled", false)
                            console.log(err)
                        }
                    })
                }
            })
        })
    </script>
@endsection
