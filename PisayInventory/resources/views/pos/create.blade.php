@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background: #F5F5DC !important; min-height: 100vh;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0" style="color:rgb(2, 2, 2) !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">CVicsC</h1>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Cart Button in Header -->
                        <button type="button" class="btn btn-light position-relative shadow-sm" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#cartOffcanvas"
                                style="background: #FFFFFF !important; padding: 0.5rem 1rem;">
                            <i class="bi bi-cart fs-5" style="color:rgb(2, 2, 2) !important;"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" 
                                  style="color: #FFFFFF !important;">
                                0
                            </span>
                        </button>
                    </div>
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
            <div class="col-lg-12">
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
                            <div class="col-lg-3 menu-item {{ $item->StocksAvailable <= 0 ? 'out-of-stock' : '' }} {{ $item->StocksAvailable <= 5 ? 'low-stock' : '' }}" 
                                 data-category="{{ $item->ClassificationID }}"
                                 data-item-id="{{ $item->MenuItemID }}">
                                <div class="card h-100 border-0 shadow-sm menu-item-card">
                                    @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                                             class="card-img-top" 
                                             alt="{{ $item->ItemName }}"
                                             style="height: 250px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 250px;">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-2">{{ $item->ItemName }}</h5>
                                        <p class="card-text text-muted small mb-2">
                                            <span class="badge bg-secondary">
                                                {{ $item->classification ? $item->classification->ClassificationName : 'Uncategorized' }}
                                            </span>
                                        </p>
                                        <p class="card-text text-muted small mb-3">{{ $item->Description }}</p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-primary">₱{{ number_format($item->Price, 2) }}</span>
                                                <span class="badge stock-badge bg-{{ $item->StocksAvailable > 5 ? 'success' : ($item->StocksAvailable > 0 ? 'warning' : 'danger') }}">
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
            <div class="col-lg-12">
                <!-- Cart Offcanvas -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" style="width: 400px;">
                    <div class="offcanvas-header bg-primary text-white">
                        <h5 class="mb-0 fw-bold" style="color: #000000 !important;">
                            <i class="bi bi-cart me-2"></i>Your Order
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body p-0 d-flex flex-column">
                        <!-- Cart Items will be dynamically added here -->
                        <div class="cart-items flex-grow-1" style="overflow-y: auto;">
                        </div>

                        <!-- Empty Cart Message -->
                        <div class="cart-empty text-center py-5">
                            <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3" style="color: #000000 !important;">Your cart is empty</p>
                            <p class="text-muted small" style="color: #000000 !important;">Add items from the menu to start your order</p>
                        </div>

                        <!-- Order Summary -->
                        <div class="cart-summary p-3 border-top" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color: #000000 !important;">Subtotal</span>
                                <span style="color: #000000 !important;">₱<span id="subtotal">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                <span class="fw-bold fs-5" style="color: #000000 !important;">Total</span>
                                <span class="fw-bold fs-5" style="color: #27AE60 !important;">₱<span id="total">0.00</span></span>
                            </div>
                        </div>

                        <!-- Checkout Section -->
                        <div class="border-top p-3">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold" style="color: #000000 !important;">Total Amount</h5>
                                    <h5 class="mb-0 fw-bold" style="color: #27AE60 !important;">₱<span id="footer-total">0.00</span></h5>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="mb-3">
                                <label for="customerName" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customerName" name="customer_name" required>
                            </div>
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
                                               id="depositPayment" value="deposit">
                                        <label class="form-check-label" for="depositPayment">
                                            <i class="bi bi-wallet2 me-1"></i> Cash Deposit
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Cash Payment Details -->
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

                            <!-- Cash Deposit Details -->
                            <div id="depositPaymentDetails" class="mb-3 p-3 border rounded" style="display: none;">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="student_id" name="student_id" 
                                               placeholder="Enter student ID">
                                        <button type="button" class="btn btn-outline-primary" id="checkBalance">
                                            <i class="bi bi-search"></i> Check Balance
                                        </button>
                                    </div>
                                    <div id="studentInfo" class="mt-2" style="display: none;">
                                        <div class="alert alert-info mb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Student Name:</strong> <span id="studentName"></span><br>
                                                    <strong>Current Balance:</strong> <span id="studentBalance"></span>
                                                </div>
                                                <div>
                                                    <strong>Order Total:</strong> <span id="orderTotal"></span>
                                                </div>
                                            </div>
                                        </div>
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
        </div>

        <!-- Hidden inputs for cart data -->
        <div id="cartData"></div>
    </form>
</div>

@push('styles')
<style>
/* Force beige background and black text on all parent elements */
html, 
body, 
#app,
main,
.container-fluid,
.py-4 {
    background-color: #F5F5DC !important;
    background-image: none !important;
    color: #000000 !important;
}

/* Make sure text is black in cards and other elements */
.card,
.offcanvas,
.cart-item,
.form-control,
.input-group-text,
.card-title,
.card-text,
.form-label,
h1, h2, h3, h4, h5, h6,
p,
span:not(.badge),
label {
    color: #000000 !important;
}

/* Keep white text for primary buttons and headers */
.btn-primary,
.bg-primary,
.card.bg-primary,
.card.bg-primary *,
.offcanvas-header.bg-primary,
.offcanvas-header.bg-primary * {
    color: #FFFFFF !important;
}

/* Override any background colors */
.container-fluid[style*="background"] {
    background: #F5F5DC !important;
}

/* Make sure no other elements override the beige background */
*[class*="bg-"] {
    background-color: transparent !important;
}

/* Keep your cards and other elements with light background */
.card,
.offcanvas,
.cart-item,
.form-control,
.input-group-text {
    background-color: #FFFFFF !important; /* White background for cards */
}

/* Keep the green color for buttons */
:root {
    --bs-primary: #27AE60;
    --bs-primary-rgb: 39, 174, 96;
}

.btn-primary {
    background-color: #27AE60 !important;
    border-color: #27AE60;
}

.btn-primary:hover, 
.btn-primary:focus {
    background-color: #219452 !important;
    border-color: #219452;
}

/* Make cards pop more against beige background */
.menu-item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2) !important;
}

/* Keep your other existing styles... */
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
.menu-item.out-of-stock {
    display: none; /* Hide out of stock items */
}

/* Optional: Add a visual indicator for low stock items */
.menu-item.low-stock .card {
    opacity: 0.7;
}

.quantity-wrapper {
    position: relative;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-left: none;
    border-right: none;
}

/* Consolidated quantity control styles */
.quantity-controls {
    display: inline-flex !important;
    width: 100px;
}

.quantity-controls .form-control {
    text-align: center;
    background-color: #fff !important;
    border-radius: 0;
    width: 40px !important;
    padding: 0.25rem 0.1rem;
    border-left: 0;
    border-right: 0;
    -moz-appearance: textfield;
    font-size: 0.875rem;
}

.quantity-controls .btn {
    border-radius: 0;
    padding: 0.25rem 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa !important;
    font-size: 0.875rem;
}

.quantity-controls .btn:active,
.quantity-controls .btn:focus {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    box-shadow: none !important;
}

.quantity-controls .quantity-decrease {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.quantity-controls .quantity-increase {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Update menu item grid layout and spacing */
.menu-items-grid .row.g-4 {
    margin-right: -0.5rem;
    margin-left: -0.5rem;
    row-gap: 2.5rem !important;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between; /* This will help distribute space evenly */
}

.menu-items-grid .row.g-4 > .menu-item {
    width: 23% !important; /* Slightly wider cards with some space between */
    flex: 0 0 23% !important;
    max-width: 23% !important;
    margin: 1rem 1%;
}

/* Make cards more visually appealing with spacing */
.menu-item-card {
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Increase image size */
.menu-item-card .card-img-top,
.menu-item-card .bg-light {
    height: 220px !important; /* Slightly taller images */
}

/* Add more padding inside cards */
.menu-item-card .card-body {
    padding: 1.25rem !important;
}

/* Make card title larger */
.menu-item-card .card-title {
    font-size: 1.3rem !important;
    margin-bottom: 1rem !important;
}

/* Make price and stock badge larger */
.menu-item-card .fw-bold.text-primary {
    font-size: 1.3rem !important;
}

.menu-item-card .stock-badge {
    font-size: 1rem !important;
}

/* Style for close button */
.btn-close,
.offcanvas .btn-close,
.alert .btn-close {
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat !important;
    opacity: 1 !important;
    padding: 1rem !important;
}

/* Hover state for close button */
.btn-close:hover {
    opacity: 0.75 !important;
    background-color: transparent !important;
}

/* Override any Bootstrap close button styles */
.btn-close-white {
    filter: none !important;
}

/* Make sure close button is visible in offcanvas header */
.offcanvas-header .btn-close {
    margin-right: 0 !important;
    padding: 0.5rem !important;
    background-color: transparent !important;
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
        
        const categoryId = $(this).data('category');
        
        // Add loading state
        $('.menu-items-grid').fadeOut(200);
        
        setTimeout(() => {
            if (categoryId === 'all') {
                // Show all items that are in stock
                $('.menu-item').each(function() {
                    if (!$(this).hasClass('out-of-stock')) {
                        $(this).show();
                    }
                });
            } else {
                // Show only items from selected classification that are in stock
                $('.menu-item').each(function() {
                    const itemCategory = $(this).data('category');
                    if (itemCategory == categoryId && !$(this).hasClass('out-of-stock')) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            $('.menu-items-grid').fadeIn(200);
        }, 200);
    });

    // View switching (grid/list)
    $('[data-view]').click(function() {
        const view = $(this).data('view');
        $('[data-view]').removeClass('active');
        $(this).addClass('active');
        
        if (view === 'list') {
            $('.menu-item').removeClass('col-lg-3').addClass('col-12');
            $('.menu-item-card').addClass('flex-row');
        } else {
            $('.menu-item').removeClass('col-12').addClass('col-lg-3');
            $('.menu-item-card').removeClass('flex-row');
        }
    });

    // Add to cart functionality
    $('.add-to-cart').click(function() {
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');
        const price = parseFloat($(this).data('item-price'));
        const stock = parseInt($(this).data('item-stock'));
        
        // Get the image source from the menu item card
        const itemImage = $(this).closest('.menu-item-card').find('img').attr('src') || '';
        const defaultImage = '<div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="bi bi-image text-muted"></i></div>';
        
        // Check if item already exists in cart
        const existingItem = $(`.cart-item[data-item-id="${itemId}"]`);
        if (existingItem.length) {
            const currentQty = parseInt(existingItem.find('.quantity-input').val());
            if (currentQty < stock) {
                existingItem.find('.quantity-input').val(currentQty + 1).trigger('change');
            }
        } else {
            // Add new item to cart with image and improved layout
            const cartItem = `
                <div class="cart-item p-3 border-bottom" data-item-id="${itemId}" data-price="${price.toFixed(2)}">
                    <div class="d-flex gap-3">
                        <div class="cart-item-image">
                            ${itemImage ? 
                                `<img src="${itemImage}" alt="${itemName}" class="rounded" style="width: 70px; height: 70px; object-fit: cover;">` : 
                                `<div class="bg-light d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                    <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                </div>`
                            }
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1 fw-bold">${itemName}</h6>
                                    <span class="text-primary">₱${price.toFixed(2)}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="input-group quantity-controls" style="width: 110px;">
                                        <button type="button" class="btn btn-outline-secondary quantity-decrease">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" class="form-control quantity-input text-center" 
                                               value="1" min="1" max="${stock}">
                                        <button type="button" class="btn btn-outline-secondary quantity-increase">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted d-block small">Subtotal:</span>
                                        <span class="fw-bold item-subtotal">₱${price.toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>
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

        // Animate the cart badge
        $('.cart-count').addClass('shake');
        setTimeout(() => {
            $('.cart-count').removeClass('shake');
        }, 500);

        // Show the cart offcanvas if it's the first item
        if ($('.cart-item').length === 1) {
            new bootstrap.Offcanvas('#cartOffcanvas').show();
        }
    });

    // Handle quantity changes
    $(document).on('click', '.quantity-decrease', function() {
        const input = $(this).closest('.quantity-controls').find('.quantity-input');
        const currentVal = parseInt(input.val());
        if (currentVal > 1) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    $(document).on('click', '.quantity-increase', function() {
        const input = $(this).closest('.quantity-controls').find('.quantity-input');
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
        const subtotal = price * quantity;
        
        // Update the subtotal display
        cartItem.find('.item-subtotal').text(`₱${subtotal.toFixed(2)}`);
        
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
        animateNumber($('#footer-total'), total);  // Update the new total display
        
        // Also update any quick-cash buttons that need the total
        if ($('.quick-cash[data-amount="exact"]').length) {
            $('.quick-cash[data-amount="exact"]').data('amount', total.toFixed(2));
        }
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
        
        // Show/hide payment details
        if (paymentType === 'cash') {
            $('#cashPaymentDetails').show();
            $('#depositPaymentDetails').hide();
        } else {
            $('#cashPaymentDetails').hide();
            $('#depositPaymentDetails').show();
        }
    });
        
    // Check student balance
    $('#checkBalance').click(function() {
        const studentId = $('#student_id').val();
            if (!studentId) {
                Swal.fire({
                icon: 'error',
                title: 'Student ID Required',
                text: 'Please enter a student ID to check balance.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...');

        // Get student balance
        $.ajax({
            url: `{{ route('pos.check-student-balance', '') }}/${studentId}`,
            type: 'GET',
            success: function(response) {
                console.log('Balance check response:', response); // Debug log
                if (response.success) {
                    const student = response.student;
                    const balance = parseFloat(response.balance);
                    const orderTotal = parseFloat($('#total').text());

                    // Update student info display
                    $('#studentName').text(student.name);
                    $('#studentBalance').text(`₱${balance.toFixed(2)}`);
                    $('#orderTotal').text(`₱${orderTotal.toFixed(2)}`);
                    $('#studentInfo').show();

                    // Enable/disable submit button based on balance
                    if (balance >= orderTotal) {
                        $('button[type="submit"]').prop('disabled', false);
                    } else {
                        $('button[type="submit"]').prop('disabled', true);
                Swal.fire({
                            icon: 'warning',
                    title: 'Insufficient Balance',
                            text: `Student balance (₱${balance.toFixed(2)}) is less than the order total (₱${orderTotal.toFixed(2)})`,
                    confirmButtonText: 'OK'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Could not find student with the provided ID.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Balance check error:', {xhr, status, error}); // Debug log
                let errorMessage = 'Failed to check student balance. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                if (xhr.responseJSON && xhr.responseJSON.debug_message) {
                    console.error('Debug message:', xhr.responseJSON.debug_message);
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                $('#checkBalance').prop('disabled', false).html('<i class="bi bi-search"></i> Check Balance');
            }
        });
    });

    // Form submission
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        
        const paymentType = $('input[name="payment_type"]:checked').val();
        
        // Validate payment method specific requirements
        if (paymentType === 'deposit') {
            const studentId = $('#student_id').val();
            if (!studentId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Student Required',
                    text: 'Please enter a student ID for deposit payment.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Check if balance was verified
            if ($('#studentInfo').is(':hidden')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Balance Not Verified',
                    text: 'Please check the student balance before proceeding.',
                    confirmButtonText: 'OK'
                });
                return;
            }
        } else if (paymentType === 'cash') {
            const cashAmount = parseFloat($('#cashAmount').val()) || 0;
            const total = parseFloat($('#total').text());
            
            if (cashAmount < total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Cash Amount',
                    text: `Cash amount (₱${cashAmount.toFixed(2)}) is less than the total (₱${total.toFixed(2)})`,
                    confirmButtonText: 'OK'
                });
                return;
            }
        }

        // Get customer name
        const customerName = $('#customerName').val();
        if (!customerName) {
            Swal.fire({
                icon: 'error',
                title: 'Customer Name Required',
                text: 'Please enter the customer name before placing the order.',
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
                price: parseFloat($(this).data('price')),
                name: $(this).find('h6').text()
            });
        });

        // Add cart items to form
        $('#cartData').html(`<input type="hidden" name="cart_items" value='${JSON.stringify(cartItems)}'>`);

        // Calculate total amount
        const total = parseFloat($('#total').text());
        $('#cartData').append(`<input type="hidden" name="total_amount" value="${total}">`);
        
        // Add payment type and details
        $('#cartData').append(`<input type="hidden" name="payment_type" value="${paymentType}">`);
        $('#cartData').append(`<input type="hidden" name="customer_name" value="${encodeURIComponent(customerName)}">`);

        if (paymentType === 'cash') {
            const cashAmount = parseFloat($('#cashAmount').val()) || 0;
            const changeAmount = cashAmount - total;
            $('#cartData').append(`<input type="hidden" name="amount_tendered" value="${cashAmount}">`);
            $('#cartData').append(`<input type="hidden" name="change_amount" value="${changeAmount}">`);
        } else {
            const studentId = $('#student_id').val();
            $('#cartData').append(`<input type="hidden" name="student_id" value="${studentId}">`);
        }

        // Add notes if any
        const notes = $('#notes').val();
        if (notes) {
            $('#cartData').append(`<input type="hidden" name="notes" value="${encodeURIComponent(notes)}">`);
        }

                // Show loading state
        const submitButton = $('button[type="submit"]');
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        // Submit the form
                $.ajax({
                    url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                        title: 'Order Placed Successfully!',
                        text: 'Your order has been processed.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('pos.index') }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to place order. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                let errorMessage = 'Failed to place order. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                        Swal.fire({
                            icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                            confirmButtonText: 'OK'
                        });
            },
            complete: function() {
                submitButton.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Place Order');
                }
            });
    });
});
</script>
@endpush

@endsection 