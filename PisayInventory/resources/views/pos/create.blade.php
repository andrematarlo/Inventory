@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">New Order</h1>
                        <p class="mb-0 opacity-75">Select items to add to your order</p>
                    </div>
                    <a href="{{ route('pos.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
            <ul class="list-unstyled mb-0">
                @foreach($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle me-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('pos.store') }}" method="POST" id="orderForm">
        @csrf
        <div class="row g-4">
            <!-- Menu Items Section -->
            <div class="col-lg-8">
                <!-- Student Selection Card -->
                <div class="card shadow-sm mb-4 border-0 rounded-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="student" class="form-label mb-0">Student ID (Optional)</label>
                                <select class="form-select form-select-lg" id="student" name="student_id">
                                    <option value="">Search student...</option>
                                </select>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-view="grid">
                                        <i class="bi bi-grid"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-view="list">
                                        <i class="bi bi-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Categories -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body pb-0">
                        <div class="categories-scroll">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary category-btn active" data-category="all">
                                    All Items
                                </button>
                                @foreach($categories as $category)
                                    <button type="button" class="btn btn-outline-primary category-btn" 
                                            data-category="{{ $category->ClassificationID }}">
                                        {{ $category->ClassificationName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Grid -->
                <div class="menu-items-grid">
                    <div class="row g-4">
                        @foreach($menuItems as $item)
                            <div class="col-md-4 menu-item" data-category="{{ $item->ClassificationID }}">
                                <div class="card h-100 border-0 shadow-sm menu-item-card">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-2">{{ $item->ItemName }}</h5>
                                        <p class="card-text text-muted small mb-3">{{ $item->Description }}</p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-primary">₱{{ number_format($item->Price, 2) }}</span>
                                                <span class="badge bg-{{ $item->StocksAvailable > 0 ? 'success' : 'danger' }}">
                                                    {{ $item->StocksAvailable > 0 ? 'In Stock: ' . $item->StocksAvailable : 'Out of Stock' }}
                                                </span>
                                            </div>
                                            <button type="button" class="btn btn-primary w-100 add-to-cart"
                                                    {{ $item->StocksAvailable <= 0 ? 'disabled' : '' }}
                                                    data-item-id="{{ $item->MenuItemID }}"
                                                    data-item-name="{{ $item->ItemName }}"
                                                    data-item-price="{{ $item->Price }}"
                                                    data-item-stock="{{ $item->StocksAvailable }}">
                                                <i class="bi bi-plus-circle me-2"></i>Add to Order
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Summary Section -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-cart me-2"></i>Your Order
                            <span class="badge bg-light text-primary ms-2 cart-count">0</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Cart Items -->
                        <div class="cart-items" style="max-height: 400px; overflow-y: auto;">
                            <!-- Cart items will be dynamically added here -->
                        </div>

                        <!-- Empty Cart Message -->
                        <div class="cart-empty text-center py-5">
                            <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Your cart is empty</p>
                            <p class="text-muted small">Add items from the menu to start your order</p>
                        </div>

                        <!-- Order Summary -->
                        <div class="cart-summary p-3 border-top" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span>₱<span id="subtotal">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tax (12%)</span>
                                <span>₱<span id="tax">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                <span class="fw-bold fs-5">Total</span>
                                <span class="fw-bold text-primary fs-5">₱<span id="total">0.00</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white p-3">
                        <div class="mb-3">
                            <label class="d-block mb-2">Payment Method</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cashPayment" value="cash" checked>
                                    <label class="form-check-label" for="cashPayment">
                                        <i class="bi bi-cash me-1"></i> Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="depositPayment" value="deposit">
                                    <label class="form-check-label" for="depositPayment">
                                        <i class="bi bi-wallet2 me-1"></i> Student Deposit
                                    </label>
                                </div>
                            </div>
                        </div>

                        <textarea class="form-control mb-3" id="notes" name="notes" rows="2" 
                                  placeholder="Add any special instructions..."></textarea>

                        <button type="submit" class="btn btn-primary btn-lg w-100" disabled>
                            <i class="bi bi-check-circle me-2"></i>Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs for cart data -->
        <div id="cartData"></div>
    </form>
</div>

@push('styles')
<style>
.card {
    transition: all 0.3s ease;
}
.menu-item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.categories-scroll {
    overflow-x: auto;
    padding-bottom: 1rem;
    margin-bottom: -1rem;
}
.categories-scroll::-webkit-scrollbar {
    height: 6px;
}
.categories-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}
.categories-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}
.cart-item {
    transition: all 0.3s ease;
}
.cart-item:hover {
    background-color: #f8f9fa;
}
.cart-item .quantity-controls {
    width: 120px;
}
.cart-item .quantity-controls .form-control {
    text-align: center;
}
.cart-items:empty + .cart-empty {
    display: block;
}
.cart-items:not(:empty) + .cart-empty {
    display: none;
}
.cart-items:not(:empty) ~ .cart-summary {
    display: block;
}
.category-btn.active {
    background-color: var(--bs-primary);
    color: white;
}
.menu-item-card {
    cursor: pointer;
}
.menu-item-card:hover .btn-primary {
    opacity: 0.9;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for student selection
    $('#student').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '{{ route("pos.students.select2") }}',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        placeholder: 'Search student by ID or name...',
        minimumInputLength: 3
    });

    // Category filtering
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        
        const category = $(this).data('category');
        if (category === 'all') {
            $('.menu-item').show();
        } else {
            $('.menu-item').hide();
            $('.menu-item[data-category="' + category + '"]').show();
        }
    });

    // View switching (grid/list)
    $('[data-view]').click(function() {
        const view = $(this).data('view');
        $('[data-view]').removeClass('active');
        $(this).addClass('active');
        
        if (view === 'list') {
            $('.menu-item').removeClass('col-md-4').addClass('col-12');
            $('.menu-item-card').addClass('flex-row');
        } else {
            $('.menu-item').removeClass('col-12').addClass('col-md-4');
            $('.menu-item-card').removeClass('flex-row');
        }
    });

    // Add to cart functionality
    $('.add-to-cart').click(function() {
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');
        const price = parseFloat($(this).data('item-price'));
        const stock = parseInt($(this).data('item-stock'));
        
        // Check if item already exists in cart
        const existingItem = $(`.cart-item[data-item-id="${itemId}"]`);
        if (existingItem.length) {
            const currentQty = parseInt(existingItem.find('.quantity-input').val());
            if (currentQty < stock) {
                existingItem.find('.quantity-input').val(currentQty + 1).trigger('change');
            }
        } else {
            // Add new item to cart
            const cartItem = `
                <div class="cart-item p-3 border-bottom" data-item-id="${itemId}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${itemName}</h6>
                            <span class="text-primary">₱${price.toFixed(2)}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <div class="input-group quantity-controls">
                            <button type="button" class="btn btn-outline-secondary quantity-decrease">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control quantity-input" value="1" min="1" max="${stock}">
                            <button type="button" class="btn btn-outline-secondary quantity-increase">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('.cart-items').append(cartItem);
        }
        
        updateCartCount();
        updateTotals();
        updateSubmitButton();
        
        // Show success toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Item added to cart'
        });
    });

    // Handle quantity changes
    $(document).on('click', '.quantity-decrease', function() {
        const input = $(this).siblings('.quantity-input');
        const currentVal = parseInt(input.val());
        if (currentVal > 1) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    $(document).on('click', '.quantity-increase', function() {
        const input = $(this).siblings('.quantity-input');
        const currentVal = parseInt(input.val());
        const max = parseInt(input.attr('max'));
        if (currentVal < max) {
            input.val(currentVal + 1).trigger('change');
        }
    });

    $(document).on('change', '.quantity-input', function() {
        const cartItem = $(this).closest('.cart-item');
        const quantity = parseInt($(this).val());
        const price = parseFloat(cartItem.find('.text-primary').text().replace('₱', ''));
        cartItem.find('input[name="quantities[]"]').val(quantity);
        cartItem.find('input[name="amounts[]"]').val((price * quantity).toFixed(2));
        updateTotals();
    });

    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.cart-item').fadeOut(300, function() {
            $(this).remove();
            updateCartCount();
            updateTotals();
            updateSubmitButton();
        });
    });

    // Update cart count
    function updateCartCount() {
        const count = $('.cart-item').length;
        $('.cart-count').text(count);
    }

    // Calculate totals with animation
    function updateTotals() {
        let subtotal = 0;
        $('.cart-item').each(function() {
            const quantity = parseInt($(this).find('.quantity-input').val());
            const price = parseFloat($(this).find('.text-primary').text().replace('₱', ''));
            subtotal += price * quantity;
        });
        
        const tax = subtotal * 0.12;
        const total = subtotal + tax;

        animateNumber($('#subtotal'), subtotal);
        animateNumber($('#tax'), tax);
        animateNumber($('#total'), total);
    }

    function animateNumber(element, value) {
        $({number: parseFloat(element.text())}).animate({
            number: value
        }, {
            duration: 300,
            step: function() {
                element.text(this.number.toFixed(2));
            },
            complete: function() {
                element.text(value.toFixed(2));
            }
        });
    }

    // Update submit button state
    function updateSubmitButton() {
        const hasItems = $('.cart-item').length > 0;
        $('button[type="submit"]').prop('disabled', !hasItems);
    }

    // Handle payment method change
    $('input[name="payment_method"]').change(function() {
        if ($(this).val() === 'deposit' && !$('#student').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Student Required',
                text: 'Please select a student to use deposit payment.',
                confirmButtonText: 'OK'
            });
            $('#cashPayment').prop('checked', true);
        }
    });

    // Form submission
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        
        if ($('#depositPayment').is(':checked') && !$('#student').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Student Required',
                text: 'Please select a student to use deposit payment.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Prepare cart items data
        const cartItems = [];
        $('.cart-item').each(function() {
            cartItems.push({
                id: parseInt($(this).data('item-id')),
                quantity: parseInt($(this).find('.quantity-input').val()),
                price: parseFloat($(this).find('.text-primary').text().replace('₱', '')),
                name: $(this).find('h6').text()
            });
        });

        // Add cart items to form
        $('#cartData').html(`<input type="hidden" name="cart_items" value='${JSON.stringify(cartItems)}'>`);

        // Calculate total amount
        const total = parseFloat($('#total').text());
        $('#cartData').append(`<input type="hidden" name="total_amount" value="${total}">`);
        
        // Add payment type
        const paymentType = $('input[name="payment_method"]:checked').val();
        $('#cartData').append(`<input type="hidden" name="payment_type" value="${paymentType}">`);

        Swal.fire({
            title: 'Confirm Order',
            text: 'Are you sure you want to place this order?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Place Order',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing Order',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: response.alert.title,
                                text: response.alert.text,
                                showConfirmButton: true,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                // Reset form and cart
                                $('.cart-items').empty();
                                updateCartCount();
                                updateTotals();
                                updateSubmitButton();
                                $('#student').val(null).trigger('change');
                                $('#cashPayment').prop('checked', true);
                                $('#notes').val('');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Order Failed',
                                text: response.message || 'Failed to create order',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: response.alert?.title || 'Order Failed',
                            text: response.alert?.text || response.message || 'Failed to create order',
                            footer: response.alert?.footer,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection 