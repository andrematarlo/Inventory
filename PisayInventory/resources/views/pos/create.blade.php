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
    @if(Auth::check() && Auth::user()->role === 'Student')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title fw-bold">Your Balance</h5>
                            <p class="text-muted mb-0">Student ID: {{ Auth::user()->student_id }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h2 class="text-primary fw-bold mb-0" id="student-balance-header">
                                ₱{{ number_format(App\Models\CashDeposit::where('student_id', Auth::user()->student_id)->whereNull('deleted_at')->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END')), 2) }}
                            </h2>
                            <p class="text-muted mb-0">Available Balance</p>
                            <a href="{{ route('pos.cashdeposit') }}" class="btn btn-sm btn-outline-primary mt-2">
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
                                <div id="student-balance-display" style="display: none;">
                                    <h6 class="mb-1">Available Balance</h6>
                                    <h3 class="text-primary mb-0">₱<span id="balance-amount">0.00</span></h3>
                                </div>
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
                        </div>
                    </div>
                </div>

                <!-- Menu Items Grid -->
                <div class="menu-items-grid">
                    <div class="row g-4">
                        @foreach($menuItems as $item)
                            <div class="col-md-4 menu-item" data-category="{{ $item->ClassificationId }}">
                                <div class="card h-100 border-0 shadow-sm menu-item-card">
                                    @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                                             class="card-img-top" 
                                             alt="{{ $item->ItemName }}"
                                             style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    
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
                                           id="depositPayment" value="deposit" disabled>
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize student select2
    $('#student').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search student by ID or name...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("pos.search-students") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return {
                    results: data.results,
                    pagination: data.pagination
                };
            },
            cache: true
        }
    }).on('select2:select', function(e) {
        const studentId = e.params.data.id;
        // Fetch and display student balance
        fetch(`{{ url('pos/student-balance') }}/${studentId}`)
            .then(response => response.json())
            .then(data => {
                $('#balance-amount').text(parseFloat(data.balance).toFixed(2));
                $('#student-balance-display').show();
                
                // Update the balance in the header if it exists (for student users)
                if ($('#student-balance-header').length) {
                    $('#student-balance-header').text('₱' + parseFloat(data.balance).toFixed(2));
                }
                
                // Enable deposit payment option
                $('#depositPayment').prop('disabled', false);
                // Update hidden student_id field
                $('#cartData').append(`<input type="hidden" name="student_id" value="${studentId}">`);
            })
            .catch(error => {
                console.error('Error fetching student balance:', error);
                $('#student-balance-display').hide();
                $('#depositPayment').prop('disabled', true);
            });
    }).on('select2:clear', function() {
        // Hide balance display when student is cleared
        $('#student-balance-display').hide();
        // Disable deposit payment option
        $('#depositPayment').prop('disabled', true);
        // Switch to cash payment if deposit was selected
        if ($('#depositPayment').prop('checked')) {
            $('#cashPayment').prop('checked', true);
        }
        // Remove student_id from form
        $('#cartData input[name="student_id"]').remove();
    });

    // Initially disable deposit payment option
    $('#depositPayment').prop('disabled', true);

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
                <div class="cart-item p-3 border-bottom" data-item-id="${itemId}" data-price="${price.toFixed(2)}">
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
        const price = parseFloat(cartItem.data('price'));
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
            const price = parseFloat($(this).data('price'));
            subtotal += price * quantity;
        });
        
        const total = subtotal;

        animateNumber($('#subtotal'), subtotal);
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
    $('input[name="payment_type"]').change(function() {
        const paymentType = $(this).val();
        
        // Show/hide cash payment details
        if (paymentType === 'cash') {
            $('#cashPaymentDetails').show();
        } else {
            $('#cashPaymentDetails').hide();
        }
        
        if (paymentType === 'deposit') {
            const studentId = $('#student').val();
            if (!studentId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Student Required',
                    text: 'Please select a student to use deposit payment.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#cashPayment').prop('checked', true);
                    $('#cashPaymentDetails').show();
                });
                return;
            }
            
            // Check if student has sufficient balance
            const total = parseFloat($('#total').text());
            const balance = parseFloat($('#balance-amount').text());
            if (balance < total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    text: `Current balance (₱${balance.toFixed(2)}) is less than the total amount (₱${total.toFixed(2)})`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#cashPayment').prop('checked', true);
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
            const studentId = $('#student').val();
            if (!studentId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Student Required',
                    text: 'Please select a student to use deposit payment.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Check if student has sufficient balance
            const total = parseFloat($('#total').text());
            const balance = parseFloat($('#balance-amount').text());
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
                price: parseFloat($(this).data('price')),
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

        // If student is selected, add student_id
        const studentId = $('#student').val();
        if (studentId) {
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
                                $('#student').val(null).trigger('change');
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

    // Calculate change from cash amount
    $('#cashAmount').on('input', function() {
        calculateChange();
    });
    
    // Quick cash buttons
    $('.quick-cash').click(function() {
        const amount = $(this).data('amount');
        
        if (amount === 'exact') {
            // Set exact amount
            const total = parseFloat($('#total').text());
            $('#cashAmount').val(total.toFixed(2));
        } else {
            // Set predefined amount
            $('#cashAmount').val(amount);
        }
        
        calculateChange();
    });
    
    // Function to calculate change
    function calculateChange() {
        const cashAmount = parseFloat($('#cashAmount').val()) || 0;
        const totalAmount = parseFloat($('#total').text()) || 0;
        
        let change = cashAmount - totalAmount;
        
        if (change >= 0) {
            $('#changeAmount').val(change.toFixed(2));
            // If we have valid change, enable the submit button if there are items
            if ($('.cart-item').length > 0) {
                $('button[type="submit"]').prop('disabled', false);
            }
        } else {
            $('#changeAmount').val('Insufficient amount');
            // Disable the submit button if cash amount is less than total
            $('button[type="submit"]').prop('disabled', true);
        }
    }
    
    // Show cash details by default (since cash is the default payment method)
    $('#cashPaymentDetails').show();
});
</script>
@endpush

@endsection 