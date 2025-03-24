@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">New Order</h1>
                        <p class="mb-0 opacity-75">Select items to add to your order</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Menu Section (Left side) -->
        <div class="col-lg-8">
            <!-- Categories -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-lg btn-outline-primary category-btn active" data-category="all">
                            <i class="bi bi-grid-3x3-gap me-2"></i>All Items
                        </button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-lg btn-outline-primary category-btn" 
                                    data-category="{{ $category->ClassificationId }}">
                                <i class="bi bi-collection me-2"></i>{{ $category->ClassificationName }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Menu Items Grid -->
            <div class="menu-items-grid">
                <div class="row g-4">
                    @foreach($menuItems as $item)
                        <div class="col-md-4 menu-item" data-category="{{ $item->ClassificationId }}">
                            <div class="card h-100 shadow-sm menu-item-card">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-2">{{ $item->ItemName }}</h5>
                                    <p class="card-text text-muted small mb-3">{{ $item->Description }}</p>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-primary fs-5">₱{{ number_format($item->Price, 2) }}</span>
                                            <span class="badge bg-success">
                                                Stock: {{ $item->StocksAvailable }}
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-primary w-100 btn-lg add-to-cart"
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

        <!-- Order Summary (Right side) -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-cart3 me-2"></i>Your Order
                        <span class="badge bg-light text-primary ms-2 cart-count">0</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Cart Items -->
                    <div class="cart-items" style="max-height: 400px; overflow-y: auto;">
                        <!-- Items will be added here dynamically -->
                    </div>

                    <!-- Empty Cart Message -->
                    <div class="cart-empty text-center py-5">
                        <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Your cart is empty</p>
                        <p class="text-muted small">Tap items to add them to your order</p>
                    </div>

                    <!-- Order Summary -->
                    <div class="cart-summary p-4 border-top" style="display: none;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted fs-5">Subtotal</span>
                            <span class="fs-5">₱<span id="subtotal">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                            <span class="fw-bold fs-4">Total</span>
                            <span class="fw-bold text-primary fs-4">₱<span id="total">0.00</span></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="card-footer bg-white p-4">
                    <h5 class="mb-3">Select Payment Method</h5>
                    
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-lg btn-outline-primary payment-method-btn" data-method="cash">
                            <i class="bi bi-cash-stack me-2"></i>Pay with Cash
                        </button>
                        
                        @if(Auth::check() && Auth::user()->role === 'Students')
                        <button type="button" class="btn btn-lg btn-outline-primary payment-method-btn" data-method="deposit">
                            <i class="bi bi-wallet2 me-2"></i>Pay with Deposit
                            <div class="small text-muted">Balance: ₱{{ number_format($studentBalance, 2) }}</div>
                        </button>
                        @endif
                    </div>

                    <!-- Cash Payment Form (hidden by default) -->
                    <div id="cashPaymentForm" class="mt-4" style="display: none;">
                        <div class="mb-4">
                            <label class="form-label fs-5">Cash Amount</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control form-control-lg" id="cashAmount" step="0.01">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <button type="button" class="btn btn-lg btn-outline-secondary quick-cash" data-amount="100">₱100</button>
                            <button type="button" class="btn btn-lg btn-outline-secondary quick-cash" data-amount="200">₱200</button>
                            <button type="button" class="btn btn-lg btn-outline-secondary quick-cash" data-amount="500">₱500</button>
                            <button type="button" class="btn btn-lg btn-outline-secondary quick-cash" data-amount="1000">₱1000</button>
                            <button type="button" class="btn btn-lg btn-outline-primary quick-cash" data-amount="exact">Exact</button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fs-5">Change</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control form-control-lg" id="changeAmount" readonly>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary btn-lg w-100 mt-4" id="placeOrderBtn" disabled>
                        <i class="bi bi-check-circle me-2"></i>Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Touchscreen-friendly styles */
.btn-lg {
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
}

.menu-item-card {
    cursor: pointer;
    transition: transform 0.2s;
}

.menu-item-card:hover,
.menu-item-card:active {
    transform: translateY(-5px);
}

.category-btn {
    min-width: 120px;
    text-align: center;
}

.cart-item {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.quantity-controls .btn {
    padding: 0.75rem 1rem;
}

.payment-method-btn {
    text-align: left;
    padding: 1.5rem;
}

.payment-method-btn.active {
    background-color: var(--bs-primary);
    color: white;
}

.quick-cash {
    min-width: 100px;
}

/* Make inputs larger for touch */
.form-control-lg {
    height: calc(3.5rem + 2px);
    font-size: 1.25rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Category filtering
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        
        const category = $(this).data('category');
        
        if (category === 'all') {
            $('.menu-item').fadeIn(300);
        } else {
            $('.menu-item').hide();
            $(`.menu-item[data-category="${category}"]`).fadeIn(300);
        }
    });

    // Payment method selection
    $('.payment-method-btn').click(function() {
        $('.payment-method-btn').removeClass('active');
        $(this).addClass('active');
        
        const method = $(this).data('method');
        
        if (method === 'cash') {
            $('#cashPaymentForm').slideDown();
        } else {
            $('#cashPaymentForm').slideUp();
        }
        
        updatePlaceOrderButton();
    });

    // Quick cash buttons
    $('.quick-cash').click(function() {
        const amount = $(this).data('amount');
        if (amount === 'exact') {
            $('#cashAmount').val($('#total').text()).trigger('input');
        } else {
            $('#cashAmount').val(amount).trigger('input');
        }
    });

    // Calculate change
    $('#cashAmount').on('input', function() {
        const cashAmount = parseFloat($(this).val()) || 0;
        const total = parseFloat($('#total').text());
        const change = cashAmount - total;
        
        if (change >= 0) {
            $('#changeAmount').val(change.toFixed(2));
        } else {
            $('#changeAmount').val('Insufficient amount');
        }
        
        updatePlaceOrderButton();
    });

    // Update place order button state
    function updatePlaceOrderButton() {
        const hasItems = $('.cart-item').length > 0;
        const paymentMethod = $('.payment-method-btn.active').data('method');
        let isValid = false;
        
        if (paymentMethod === 'cash') {
            const cashAmount = parseFloat($('#cashAmount').val()) || 0;
            const total = parseFloat($('#total').text());
            isValid = cashAmount >= total;
        } else if (paymentMethod === 'deposit') {
            const balance = parseFloat('{{ $studentBalance }}');
            const total = parseFloat($('#total').text());
            isValid = balance >= total;
        }
        
        $('#placeOrderBtn').prop('disabled', !hasItems || !isValid);
    }

    // Add other necessary cart functionality here
});
</script>
@endpush 