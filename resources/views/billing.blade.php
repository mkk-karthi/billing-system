@extends('layout')

@section('content')
    @includeIf('products.error_model')
    <div class="container-fluid">
        <div class="card my-3">
            <div class="card-body p-3">
                <form id="bill-form">
                    <div class="row">
                        <div class="col-12 col-md-9 mb-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="email" class="col-form-label">Customer email</label>
                                </div>
                                <div class="col-auto">
                                    <input type="email" id="email" name="email" value="" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary m-2" id="add-new">
                                Add new
                            </button>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Tax %</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-list">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12">
                            <table class="w-100 text-end mx-2 my-3">
                                <tr>
                                    <td>Total price without tax:</td>
                                    <td id="total-price">0</td>
                                </tr>
                                <tr>
                                    <td>Total tax payable:</td>
                                    <td id="tax-price">0</td>
                                </tr>
                                <tr>
                                    <td>Discount:</td>
                                    <td class="w-25">
                                        <input type="number" id="discount" name="discount" value=""
                                            class="form-control text-end" max="100" autocomplete="off">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Net price:</td>
                                    <td id="net-price">0</td>
                                </tr>
                                <tr>
                                    <td>Round of net price:</td>
                                    <td id="round-net-price">0</td>
                                </tr>
                            </table>
                        </div>

                        @if (!empty($denominations))
                            <hr>

                            <div class="col-12">
                                <p class="fw-bold fs-5">Denominations</p>
                                <div class="row">
                                    <div class="col-12 col-md-9" id="denominations">
                                        @foreach ($denominations as $denomination)
                                            <div class="row g-3 align-items-center">
                                                <div class="col-4 col-md-3 text-end">
                                                    <label class="col-form-label">{{ $denomination }}</label>
                                                </div>
                                                <div class="col-8 col-md-3 mb-2">
                                                    <input type="number" name="denominations.{{ $denomination }}"
                                                        value="" class="form-control denominations"
                                                        data-value="{{ $denomination }}" pattern="\d+" max="1000">
                                                </div>
                                                <div class="col-8 col-md-4 mb-2">
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="row g-3 align-items-center my-2">
                                            <div class="col-4 text-end">
                                                <label class="col-form-label">Cash paid by customer</label>
                                            </div>
                                            <div class="col-8 col-md-3 mb-2">
                                                <input type="number" name="customer_paid" value=""
                                                    class="form-control" id="customer_paid" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-12 my-3" id="messages"> </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-success m-3" id="submit-btn">
                                Generate Bill
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            var availableTags = @php echo $emails; @endphp;
            $("#email").autocomplete({
                source: availableTags
            });

            $("#bill-form").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    email: {
                        required: "Email is required"
                    }
                },
                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                }
            });

            let products = @php echo $products; @endphp;
            let productOptions = "";
            for (item of products) {
                productOptions +=
                    `<option value="${item.product_id}">${item.product_sku} - ${item.product_name}</option>`;
            }
            const roundNumber = (num, decimalPlaces = 2) => {
                const factor = Math.pow(10, decimalPlaces);
                return num ? Math.trunc(num * factor) / factor : 0;
            }

            const calculatePrice = () => {
                let totalPrice = 0;
                let taxPrice = 0;
                $(`#item-list`).children().each(function() {
                    const id = $(this).data("id")
                    const product = $(`#item-${id}-product`).val();
                    const quantity = $(`#item-${id}-quantity`).val();

                    if (quantity > 0) {
                        const selectedProduct = products.filter(v => v.product_id == product);

                        if (selectedProduct[0]) {
                            let productTax = selectedProduct[0].product_tax;
                            let productPrice = selectedProduct[0].product_price;
                            totalPrice += (productPrice) * quantity;
                            taxPrice += ((productPrice / 100) * productTax) * quantity;
                        }
                    }
                });
                let discount = $("#discount").val();
                discount = discount > 0 ? discount : 0;

                let netPrice = (totalPrice + taxPrice);
                let netPriceDisounted = netPrice - discount;
                netPriceDisounted = netPriceDisounted > 0 ? netPriceDisounted : 0;

                $(`#total-price`).text(roundNumber(totalPrice));
                $(`#tax-price`).text(roundNumber(taxPrice));
                $(`#net-price`).text(roundNumber(netPriceDisounted));
                $(`#round-net-price`).text(Math.floor(netPriceDisounted).toFixed(2));
                $("#discount").attr("max", netPrice > 0 ? Math.floor(netPrice) : 100);
            }
            let discount = $("#discount").change(calculatePrice);

            let itemKey = 0;
            const addNew = () => {

                if ($(`#item-list`).children().length < {{ config('common.maxBillingProducts') }}) {
                    $("#item-list").append(`<tr id="item-${itemKey}" data-id="${itemKey}">
                        <td>
                            <select class="form-select" name="items.${itemKey}.product" id="item-${itemKey}-product" required data-id="${itemKey}">
                                <option value="" hidden>Select Product</option>
                                ${productOptions}
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control" name="items.${itemKey}.quantity" id="item-${itemKey}-quantity" value=""
                                placeholder="Quantity" required pattern="\\d+" min="1" max="{{ config('common.maxBillingProductQty') }}">
                        </td>
                        <td>
                            <input type="number" class="form-control" name="items.${itemKey}.price" id="item-${itemKey}-price" value=""
                                placeholder="Price" disabled>
                        </td>
                        <td>
                            <input type="number" class="form-control" name="items.${itemKey}.tax" id="item-${itemKey}-tax" value=""
                                placeholder="Tax" disabled>
                        </td>
						<td class="text-center">
							<button type="button" class="btn btn-danger btn-sm m-2"  id="delete-${itemKey}-item" data-id="${itemKey}">
								<i class="bi bi-trash3"></i>
							</button>
						</td>
					</tr>`);

                    $(`#item-${itemKey}-product`).change(function() {
                        let id = $(this).data("id")
                        let selectedProduct = products.filter(v => v.product_id == this.value);

                        $(`#item-${id}-price`).val(selectedProduct[0].product_price || '');
                        $(`#item-${id}-tax`).val(selectedProduct[0].product_tax || '');
                        calculatePrice();
                    });
                    $(`#item-${itemKey}-quantity`).change(calculatePrice);

                    $(`#delete-${itemKey}-item`).click(function() {

                        // check at least 1 item is required
                        if ($(`#item-list`).children().length > 1) {

                            let id = $(this).data("id");
                            $(`#item-${id}`).remove();
                        } else {

                            $("#errorModal").modal("show")
                            $(".modal-body").html(
                                "<p class='text-danger'>At least 1 item is required</p>")
                        }
                    })
                    itemKey++;
                } else {
                    $("#errorModal").modal("show")
                    $(".modal-body").html(
                        "<p class='text-danger'>Max {{ config('common.maxBillingProducts') }} items only</p>"
                    )
                }
            }
            $("#add-new").click(() => addNew())
            addNew();

            const setTotalPaid = () => {
                let denomination = 0;
                $(".denominations").each((key, ele) => {
                    let denominationCount = Number.parseInt(ele.value);
                    let denominationValue = Number.parseInt($(ele).attr("data-value"));
                    if (denominationCount)
                        denomination += (denominationCount * denominationValue);
                })
                $("#customer_paid").val(denomination)
            }

            $(".denominations").change(() => setTotalPaid())
            setTotalPaid();

            $("#submit-btn").click(() => {
                $("#submit-btn").prop("disabled", true)
                if ($("#bill-form").valid()) {

                    // get values
                    const email = $("#email").val();
                    const discount = $("#discount").val();

                    // get items
                    let items = [];
                    $(`#item-list`).children().each(function() {
                        const id = $(this).data("id")

                        const product = $(`#item-${id}-product`).val();
                        const quantity = $(`#item-${id}-quantity`).val();

                        items.push({
                            product_id: product,
                            quantity: quantity
                        })
                    })

                    let denominations = {};
                    $(".denominations").each((key, ele) => {
                        let denomination = $(ele).attr("data-value");
                        Object.assign(denominations, {
                            [denomination]: ele.value
                        })
                    })


                    $.ajax({
                        url: "{{ route('order.create') }}",
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            email,
                            items,
                            discount,
                            denominations
                        }),
                        success: (res) => {
                            if (res.code == 1) {
                                $("#submit-btn").prop("disabled", false)

                                for (let [key, value] of Object.entries(res.errors)) {
                                    key = key.replaceAll("length", "lengths").replaceAll(
                                        ".",
                                        "-")

                                    $(`#${key}`).addClass("is-invalid");
                                    $(`#${key}-error`).text(value.join(" "));
                                }
                            } else if (res.code == 2) {
                                $("#submit-btn").prop("disabled", false)

                                // show error message
                                const msgContent =
                                    `<div class="alert alert-danger" role="alert">${res.message}</div>`;
                                $("#messages").append(msgContent)

                                setTimeout(() => {
                                    $("#messages").html($("#messages").html()
                                        .replace(msgContent, ""))
                                }, 5000);
                            } else {
                                // show success message
                                const msgContent =
                                    `<div class="alert alert-success" role="alert">${res.message}</div>`;
                                $("#messages").html(msgContent)

                                setTimeout(() => {
                                    location.href = res.data.redirct;
                                }, 1000);
                            }
                        },
                        error: (err) => {
                            $("#submit-btn").prop("disabled", false)
                            console.log(err)
                        }
                    })
                } else {
                    $("#submit-btn").prop("disabled", false)
                }
            });
        });
    </script>

    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
@endsection
