@extends('layouts.kiosk')

@section('title', 'PSHS Kiosk')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    // Disable all permissions for kiosk view
    $hrPermissions = false;
    $purchasingPermissions = false;
    $receivingPermissions = false;
    $inventoryPermissions = false;
    $posPermissions = false;
    $adminPermissions = false;
    $studentPermissions = false;
    $laboratoryPermissions = false;
    $kioskPermissions = false;
    $isAdmin = false;
    $isCashier = false;
    $isStudent = false;
    
    // Set all access permissions to false
    $hasStudentAccess = false;
    $hasLaboratoryAccess = false;
    $hasPOSAccess = false;
    $hasKioskAccess = false;
    $canManagePOS = false;
    $canViewReports = false;
@endphp
<div class="container-fluid py-4" style="background: url('{{ asset('images/background.jpg') }}') no-repeat center center fixed; background-size: cover; min-height: 100vh;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card header-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="text-white mb-0">PSHS Kiosk</h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="search-input-container">
                            <input type="text" class="form-control search-input" id="searchInput" placeholder="Search menu items...">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                        <button type="button" class="cart-icon" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                            <i class="bi bi-cart3"></i>
                            <span class="cart-count badge bg-danger">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="cartOffcanvasLabel">Your Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="cart-items">
                <!-- Cart items will be added here dynamically -->
            </div>
            <div class="cart-summary mt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span class="cart-subtotal">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Service Charge (10%):</span>
                    <span class="cart-service-charge">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="cart-total">₱0.00</strong>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount Tendered</label>
                    <input type="number" class="form-control" id="amountTendered" placeholder="Enter amount" step="0.01" min="0">
                </div>
                <button type="button" class="btn btn-primary w-100" id="checkoutBtn" disabled>
                    Place Order
                </button>
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
                                            <span class="badge stock-badge bg-success" style="color: blue !important;">
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
    </div>
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

/* Search input styling */
.search-input {
    border-radius: 20px !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
    transition: all 0.3s ease !important;
    font-size: 1rem !important;
    color: #000000 !important;
    font-weight: 500 !important;
    background-color: #FFFFFF !important;
}

.search-input::placeholder {
    color: #6c757d !important;
    opacity: 0.8 !important;
}

.search-input:focus {
    color: #000000 !important;
    background-color: #FFFFFF !important;
    border-color: #27AE60 !important;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15) !important;
}

/* Cart icon styling */
.cart-icon {
    position: relative;
    font-size: 1.5rem;
    color: white;
    margin-left: 20px;
    cursor: pointer;
    transition: transform 0.2s ease;
    background: none;
    border: none;
    padding: 0;
}

.cart-icon:hover {
    transform: scale(1.1);
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 50%;
}

/* Cart item styling */
.cart-item {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 60px;
    height: 60px;
    overflow: hidden;
    border-radius: 8px;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.quantity-controls {
    width: 120px;
}

.quantity-controls input {
    text-align: center;
    width: 40px;
}

/* Offcanvas styling */
.offcanvas {
    width: 400px;
}

.offcanvas-header {
    background-color: #27AE60;
    color: white;
}

.offcanvas-title {
    font-weight: 600;
}

.cart-summary {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-top: auto;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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

    // Add to cart functionality
    $('.add-to-cart').click(function() {
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');
        const price = parseFloat($(this).data('item-price'));
        const stock = parseInt($(this).data('item-stock'));
        
        // Get the image source from the menu item card
        const itemImage = $(this).closest('.menu-item-card').find('img').attr('src') || '';
        
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
                <div class="cart-item" data-item-id="${itemId}" data-price="${price.toFixed(2)}">
                    <div class="d-flex gap-3">
                        <div class="cart-item-image">
                            ${itemImage ? 
                                `<img src="${itemImage}" alt="${itemName}" class="rounded">` : 
                                `<div class="bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted"></i>
                                </div>`
                            }
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">${itemName}</h6>
                                    <span class="text-primary">₱${price.toFixed(2)}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="input-group quantity-controls">
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
            `;
            $('.cart-items').append(cartItem);
        }
        
        // Show the cart offcanvas
        const cartOffcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
        cartOffcanvas.show();
        
        updateCartCount();
        updateTotals();
        updateSubmitButton();
    });

    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.cart-item').remove();
        updateCartCount();
        updateTotals();
        updateSubmitButton();
    });

    // Quantity controls
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
        const item = $(this).closest('.cart-item');
        const price = parseFloat(item.data('price'));
        const quantity = parseInt($(this).val());
        const subtotal = price * quantity;
        item.find('.item-subtotal').text(`₱${subtotal.toFixed(2)}`);
        updateTotals();
    });

    // Update cart count
    function updateCartCount() {
        const count = $('.cart-item').length;
        $('.cart-count').text(count);
    }

    // Update totals
    function updateTotals() {
        let subtotal = 0;
        $('.cart-item').each(function() {
            const price = parseFloat($(this).data('price'));
            const quantity = parseInt($(this).find('.quantity-input').val());
            subtotal += price * quantity;
        });
        
        const serviceCharge = subtotal * 0.1;
        const total = subtotal + serviceCharge;
        
        $('.cart-subtotal').text(`₱${subtotal.toFixed(2)}`);
        $('.cart-service-charge').text(`₱${serviceCharge.toFixed(2)}`);
        $('.cart-total').text(`₱${total.toFixed(2)}`);
    }

    // Update submit button state
    function updateSubmitButton() {
        const hasItems = $('.cart-item').length > 0;
        const hasAmountTendered = $('#amountTendered').val().trim() !== '';
        $('#checkoutBtn').prop('disabled', !hasItems || !hasAmountTendered);
    }

    // Amount tendered input change
    $('#amountTendered').on('input', function() {
        updateSubmitButton();
    });

    // Checkout button click
    $('#checkoutBtn').click(function() {
        const amountTendered = parseFloat($('#amountTendered').val()) || 0;
        const total = parseFloat($('.cart-total').text().replace('₱', ''));

        // Validate inputs
        if (!amountTendered || amountTendered < total) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Cash Amount',
                text: `Cash amount (₱${amountTendered.toFixed(2)}) is less than the total (₱${total.toFixed(2)})`,
                confirmButtonText: 'OK'
            });
            return;
        }

        // Prepare cart items data
        const cartItems = [];
        $('.cart-item').each(function() {
            cartItems.push({
                id: $(this).data('item-id'),
                quantity: parseInt($(this).find('.quantity-input').val()),
                price: parseFloat($(this).data('price'))
            });
        });

        // Submit order
        $.ajax({
            url: "{{ route('pos.store') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                payment_type: 'cash',
                amount_tendered: amountTendered,
                total_amount: total,
                items: cartItems
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Placed Successfully',
                        text: `Your order number is: ${response.order_number}`,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Clear cart and close offcanvas
                        $('.cart-items').empty();
                        updateCartCount();
                        updateTotals();
                        updateSubmitButton();
                        $('#amountTendered').val('');
                        bootstrap.Offcanvas.getInstance(document.getElementById('cartOffcanvas')).hide();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Order Failed',
                        text: response.message || 'Failed to place order. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error response:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Order Failed',
                    text: xhr.responseJSON?.message || 'Failed to place order. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Search functionality
    $('.search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.menu-item').each(function() {
            const itemName = $(this).find('.card-title').text().toLowerCase();
            const itemDescription = $(this).find('.card-text').text().toLowerCase();
            
            if (itemName.includes(searchTerm) || itemDescription.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush
@endsection 