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

    <!-- Student Balance Card (Show if logged in user is a student) -->
    @if(Auth::check() && Auth::user()->role === 'Students')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title fw-bold">Your Balance</h5>
                            <p class="text-muted mb-0">Student ID: {{ Auth::user()->student_id }}</p>
                            <input type="hidden" name="student_id" value="{{ Auth::user()->student_id }}" id="student_id_input">
                        </div>
                        <div class="col-md-6 text-end">
                            <h2 class="text-primary fw-bold mb-0" id="student-balance-header">
                                ₱{{ number_format(App\Models\CashDeposit::where('student_id', Auth::user()->student_id)->whereNull('deleted_at')->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END')), 2) }}
                            </h2>
                            <p class="text-muted mb-0">Available Balance</p>
                            <a href="{{ route('pos.deposits.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-wallet2 me-1"></i> Manage Deposits
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('pos.store') }}" method="POST" id="orderForm">
        @csrf
        <div class="row g-4">
            <!-- Menu Items Section -->
            <div class="col-lg-8">
                <!-- View Controls -->
                <div class="card shadow-sm mb-4 border-0 rounded-3">
                    <div class="card-body d-flex justify-content-end">
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

                <!-- Menu Categories -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body pb-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="categories-scroll">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary category-btn active" data-category="all">
                                        All Items
                                    </button>
                                    @foreach($categories as $category)
                                        <button type="button" class="btn btn-outline-primary category-btn" 
                                                data-category="{{ $category->ClassificationId }}">
                                            {{ $category->ClassificationName }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <!-- Add Item Button (only visible for Admin and Cashier roles) -->
                            @if(Auth::check() && (Auth::user()->role === 'Admin' || Auth::user()->role === 'Cashier'))
                            <div>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                    <i class="bi bi-plus-circle me-1"></i> Add Item
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Menu Items Grid -->
                <div class="menu-items-grid">
                    <div class="row g-4">
                        @foreach($menuItems as $item)
                            <div class="col-md-4 menu-item" data-category="{{ $item->ClassificationId }}">
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
                                    <input class="form-check-input" type="radio" name="payment_type" 
                                           id="cashPayment" value="cash" checked>
                                    <label class="form-check-label" for="cashPayment">
                                        <i class="bi bi-cash me-1"></i> Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_type" 
                                           id="depositPayment" value="deposit" {{ Auth::check() && Auth::user()->role === 'Students' ? '' : 'disabled' }}>
                                    <label class="form-check-label" for="depositPayment">
                                        <i class="bi bi-wallet2 me-1"></i> Student Deposit
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Cash Payment Details - Only shown when cash payment is selected -->
                        <div id="cashPaymentDetails" class="mb-3 p-3 border rounded">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label for="cashAmount" class="form-label">Cash Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="cashAmount" name="amount_tendered" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="changeAmount" class="form-label">Change</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="text" class="form-control" id="changeAmount" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between gap-1">
                                <button type="button" class="quick-cash btn btn-sm btn-outline-secondary" data-amount="100">₱100</button>
                                <button type="button" class="quick-cash btn btn-sm btn-outline-secondary" data-amount="200">₱200</button>
                                <button type="button" class="quick-cash btn btn-sm btn-outline-secondary" data-amount="500">₱500</button>
                                <button type="button" class="quick-cash btn btn-sm btn-outline-secondary" data-amount="1000">₱1000</button>
                                <button type="button" class="quick-cash btn btn-sm btn-outline-primary" data-amount="exact">Exact</button>
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
.quantity-controls {
    max-width: 150px;
}
.quantity-controls .form-control {
    text-align: center;
    background-color: #fff;
    cursor: default;
}
.quantity-controls .btn {
    padding: 0.375rem 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.quantity-controls .btn:hover {
    background-color: #e9ecef;
}
.quantity-controls .btn i {
    font-size: 1rem;
}
.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.quantity-input {
    -moz-appearance: textfield;
}
.item-subtotal {
    font-size: 1.1rem;
    color: var(--bs-primary);
    min-width: 100px;
    text-align: right;
}
.cart-item .quantity-controls {
    width: 120px;
    margin-right: auto;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Enable deposit payment option if student is logged in
    if ($('#student_id_input').length) {
        $('#depositPayment').prop('disabled', false);
    }
    
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
                            <span class="text-primary item-price" data-price="${price}">₱${price.toFixed(2)}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <div class="input-group quantity-controls">
                            <button type="button" class="btn btn-outline-secondary quantity-decrease">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control quantity-input" 
                                   value="1" 
                                   min="1" 
                                   max="${stock}"
                                   readonly>
                            <button type="button" class="btn btn-outline-secondary quantity-increase">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <span class="ms-3 fw-bold item-subtotal">₱${price.toFixed(2)}</span>
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

    // Handle quantity decrease button
    $(document).on('click', '.quantity-decrease', function() {
        const input = $(this).siblings('.quantity-input');
        const currentVal = parseInt(input.val());
        const min = parseInt(input.attr('min')) || 1;
        
        if (currentVal > min) {
            input.val(currentVal - 1);
            updateItemPrice(input);
            input.trigger('change'); // Trigger change event to update totals
        }
    });

    // Handle quantity increase button
    $(document).on('click', '.quantity-increase', function() {
        const input = $(this).siblings('.quantity-input');
        const currentVal = parseInt(input.val());
        const max = parseInt(input.attr('max'));
        
        if (currentVal < max) {
            input.val(currentVal + 1);
            updateItemPrice(input);
            input.trigger('change'); // Trigger change event to update totals
        }
    });

    // Add function to update individual item price
    function updateItemPrice(input) {
        const cartItem = input.closest('.cart-item');
        const quantity = parseInt(input.val());
        const unitPrice = parseFloat(cartItem.find('.item-price').data('price'));
        const subtotal = quantity * unitPrice;
        
        // Update the item's subtotal display
        cartItem.find('.item-subtotal').text(`₱${subtotal.toFixed(2)}`);
    }

    // Handle direct input changes
    $(document).on('change', '.quantity-input', function() {
        const min = parseInt($(this).attr('min')) || 1;
        const max = parseInt($(this).attr('max'));
        let value = parseInt($(this).val()) || min;

        // Enforce min/max constraints
        if (value < min) value = min;
        if (value > max) value = max;
        
        $(this).val(value);

        // Update this item's price and cart totals
        updateItemPrice($(this));
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
            const price = parseFloat($(this).find('.item-price').data('price'));
            const itemTotal = quantity * price;
            subtotal += itemTotal;
        });
        
        const total = subtotal;

        // Update the displays with animation
        animateNumber($('#subtotal'), subtotal);
        animateNumber($('#total'), total);

        // If cash amount is entered, update the change
        calculateChange(total);
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
    $('input[name="payment_type"]').change(function() {
        const paymentType = $(this).val();
        
        // Show/hide cash payment details
        if (paymentType === 'cash') {
            $('#cashPaymentDetails').show();
        } else {
            $('#cashPaymentDetails').hide();
        }
        
        if (paymentType === 'deposit') {
            // Check if student is logged in
            if (!$('#student_id_input').length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Student Required',
                    text: 'Only students can use deposit payment.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#cashPayment').prop('checked', true);
                    $('#cashPaymentDetails').show();
                });
                return;
            }
            
            // Check if student has sufficient balance
            const total = parseFloat($('#total').text());
            const balance = parseFloat($('#student-balance-header').text().replace(/[₱,]/g, ''));
            if (balance < total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    text: `Current balance (₱${balance.toFixed(2)}) is less than the total amount (₱${total.toFixed(2)})`,
                    confirmButtonText: 'OK'
                });
                return;
            }
        }
    });

    // Form submission
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        
        const paymentType = $('input[name="payment_type"]:checked').val();
        if (paymentType === 'deposit') {
            const studentId = $('#student_id_input').val();
            if (!studentId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Student Required',
                    text: 'Only students can use deposit payment.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Check if student has sufficient balance
            const total = parseFloat($('#total').text());
            const balance = parseFloat($('#student-balance-header').text().replace(/[₱,]/g, ''));
            if (balance < total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    text: `Current balance (₱${balance.toFixed(2)}) is less than the total amount (₱${total.toFixed(2)})`,
                    confirmButtonText: 'OK'
                });
                return;
            }
        }

        // Prepare cart items data
        const cartItems = [];
        $('.cart-item').each(function() {
            cartItems.push({
                id: parseInt($(this).data('item-id')),
                quantity: parseInt($(this).find('.quantity-input').val()),
                price: parseFloat($(this).find('.item-price').data('price')),
                name: $(this).find('h6').text()
            });
        });

        // Add cart items to form
        $('#cartData').html(`<input type="hidden" name="cart_items" value='${JSON.stringify(cartItems)}'>`);

        // Calculate total amount
        const total = parseFloat($('#total').text());
        $('#cartData').append(`<input type="hidden" name="total_amount" value="${total}">`);
        
        // Add payment type
        $('#cartData').append(`<input type="hidden" name="payment_type" value="${paymentType}">`);

        // If cash payment, add cash amount and change
        if (paymentType === 'cash') {
            const cashAmount = parseFloat($('#cashAmount').val()) || 0;
            const changeAmount = cashAmount - total;
            
            // Validate cash amount
            if (cashAmount < total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Cash Amount',
                    text: `Cash amount (₱${cashAmount.toFixed(2)}) is less than the total (₱${total.toFixed(2)})`,
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            $('#cartData').append(`<input type="hidden" name="amount_tendered" value="${cashAmount}">`);
            $('#cartData').append(`<input type="hidden" name="change_amount" value="${changeAmount}">`);
        }

        // Add student_id if provided via hidden input
        if ($('#student_id_input').length) {
            const studentId = $('#student_id_input').val();
            $('#cartData').append(`<input type="hidden" name="student_id" value="${studentId}">`);
        }

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
                            // Prepare success message content
                            let successContent = `<div class="text-center">
                                <h6 class="mb-3">Order #${response.order_number || ''} completed</h6>
                                <p class="mb-2">Total: ₱${total.toFixed(2)}</p>`;
                            
                            // Add payment-specific details
                            if (paymentType === 'cash') {
                                const cashAmount = parseFloat($('#cashAmount').val()) || 0;
                                const changeAmount = cashAmount - total;
                                successContent += `
                                    <p class="mb-2">Cash: ₱${cashAmount.toFixed(2)}</p>
                                    <p class="mb-4">Change: ₱${changeAmount.toFixed(2)}</p>`;
                            } else {
                                successContent += `
                                    <p class="mb-4">Paid from student deposit</p>`;
                            }
                            
                            successContent += `</div>`;
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: response.alert.title || 'Order Completed',
                                html: successContent,
                                showConfirmButton: true,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                // Reset form and cart
                                $('.cart-items').empty();
                                updateCartCount();
                                updateTotals();
                                updateSubmitButton();
                                $('#student_id_input').val(null).trigger('change');
                                $('#cashPayment').prop('checked', true);
                                $('#cashAmount').val('');
                                $('#changeAmount').val('');
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

    // Add Item form submission
    $('#addItemForm').submit(function(e) {
        e.preventDefault();
        
        // Show loading
        Swal.fire({
            title: 'Adding Item',
            text: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
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
                Swal.fire({
                    icon: 'success',
                    title: 'Item Added Successfully',
                    text: 'The menu item has been added to the inventory.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Close modal and reset form
                    $('#addItemModal').modal('hide');
                    $('#addItemForm')[0].reset();
                    
                    // Reload page to show new item
                    location.reload();
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'Failed to add menu item.';
                
                if (response && response.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                } else if (response && response.message) {
                    errorMessage = response.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage,
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Update the cash amount handler
    $('#cashAmount').on('input', function() {
        const total = parseFloat($('#total').text());
        calculateChange(total);
    });
    
    // Update the calculateChange function
    function calculateChange(total) {
        const cashAmount = parseFloat($('#cashAmount').val()) || 0;
        const changeAmount = cashAmount - total;
        
        if (cashAmount >= total) {
            $('#changeAmount').val(changeAmount.toFixed(2));
            $('button[type="submit"]').prop('disabled', false);
        } else {
            $('#changeAmount').val('Insufficient amount');
            $('button[type="submit"]').prop('disabled', true);
        }
    }
    
    // Update the quick cash buttons
    $('.quick-cash').click(function() {
        const amount = $(this).data('amount');
        const total = parseFloat($('#total').text());
        
        if (amount === 'exact') {
            $('#cashAmount').val(total.toFixed(2));
        } else {
            $('#cashAmount').val(amount.toFixed(2));
        }
        
        calculateChange(total);
    });
    
    // Show cash details by default (since cash is the default payment method)
    $('#cashPaymentDetails').show();
});
</script>
@endpush

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addItemForm" action="{{ route('pos.add-menu-item') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stocks_available" class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" id="stocks_available" name="stocks_available" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="classification_id" class="form-label">Category</label>
                        <select class="form-select" id="classification_id" name="classification_id" required>
                            <option value="" selected disabled>Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->ClassificationId }}">{{ $category->ClassificationName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection 