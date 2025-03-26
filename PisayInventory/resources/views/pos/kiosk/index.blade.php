@extends('layouts.app')

@section('title', 'Student Kiosk')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2>Student Kiosk</h2>
            <p class="text-muted">Place your order here</p>
        </div>
    </div>
    
    <div class="row">
        <!-- Menu Categories -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <button type="button" class="list-group-item list-group-item-action active" 
                                onclick="filterItems('all')">
                            All Items
                        </button>
                        @foreach($categories as $category)
                        <button type="button" class="list-group-item list-group-item-action" 
                                onclick="filterItems('{{ $category->ClassificationId }}')">
                            {{ $category->ClassificationName }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menu Items -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Menu Items</h5>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search menu...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="menuItemsContainer">
                        @foreach($menuItems as $item)
                        <div class="col-md-4 mb-4 menu-item" 
                             data-category="{{ $item->ClassificationID }}" 
                             data-name="{{ $item->ItemName }}" 
                             data-price="{{ $item->Price }}"
                             data-stock="{{ $item->StocksAvailable }}">
                            <div class="card h-100">
                                @if($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                     class="card-img-top menu-item-img" 
                                     alt="{{ $item->ItemName }}" 
                                     onerror="this.src='{{ asset('images/no-image.png') }}'">
                                @else
                                <img src="{{ asset('images/no-image.png') }}" 
                                     class="card-img-top menu-item-img" 
                                     alt="{{ $item->ItemName }}">
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title mb-1">{{ $item->ItemName }}</h6>
                                    <p class="card-text text-primary mb-1">₱{{ number_format($item->Price, 2) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="badge bg-success">Stock: {{ $item->StocksAvailable }}</span>
                                        <button class="btn btn-sm btn-primary add-to-cart"
                                                data-id="{{ $item->MenuItemID }}"
                                                data-name="{{ $item->ItemName }}"
                                                data-price="{{ $item->Price }}">
                                            <i class="bi bi-plus-circle"></i> Add
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
        
        <!-- Order Cart -->
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 10px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Your Order</h5>
                </div>
                <div class="card-body">
                    <div id="cart-items">
                        <p class="text-center text-muted" id="empty-cart-message">Your cart is empty</p>
                        <div id="cart-items-list"></div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total:</span>
                        <span id="cart-total">₱0.00</span>
                    </div>
                    <button id="place-order-btn" class="btn btn-success w-100" disabled>
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize cart
    let cart = [];
    const cartItemsList = $('#cart-items-list');
    const cartTotal = $('#cart-total');
    const emptyCartMessage = $('#empty-cart-message');
    const placeOrderBtn = $('#place-order-btn');
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.menu-item').each(function() {
            const itemName = $(this).data('name').toLowerCase();
            if (itemName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Add to cart functionality
    $('.add-to-cart').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = parseFloat($(this).data('price'));
        
        // Check if item already in cart
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                quantity: 1,
                isCustom: false
            });
        }
        
        updateCart();
    });
    
    // Update cart display
    function updateCart() {
        cartItemsList.empty();
        
        if (cart.length === 0) {
            emptyCartMessage.show();
            placeOrderBtn.prop('disabled', true);
        } else {
            emptyCartMessage.hide();
            placeOrderBtn.prop('disabled', false);
            
            let total = 0;
            
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                const itemHtml = `
                    <div class="cart-item mb-2">
                        <div class="d-flex justify-content-between">
                            <span>${item.name}</span>
                            <span>₱${itemTotal.toFixed(2)}</span>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary decrease-quantity" data-index="${index}">-</button>
                                <input type="text" class="form-control text-center item-quantity" value="${item.quantity}" readonly>
                                <button class="btn btn-outline-secondary increase-quantity" data-index="${index}">+</button>
                            </div>
                            <button class="btn btn-sm btn-outline-danger ms-2 remove-item" data-index="${index}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                cartItemsList.append(itemHtml);
            });
            
            cartTotal.text(`₱${total.toFixed(2)}`);
        }
    }
    
    // Handle quantity changes and removal
    $(document).on('click', '.increase-quantity', function() {
        const index = $(this).data('index');
        cart[index].quantity += 1;
        updateCart();
    });
    
    $(document).on('click', '.decrease-quantity', function() {
        const index = $(this).data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity -= 1;
        } else {
            cart.splice(index, 1);
        }
        updateCart();
    });
    
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCart();
    });
    
    // Place order
    $('#place-order-btn').on('click', function() {
        if (cart.length === 0) return;
        
        // Here you would send the order to the server
        $.ajax({
            url: '{{ route("pos.kiosk.order") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                items: cart,
                total_amount: parseFloat(cartTotal.text().replace('₱', '').replace(',', '')),
                payment_type: 'deposit' // For student kiosk, we assume deposit payment
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Placed',
                    text: 'Your order has been placed successfully!',
                }).then(() => {
                    // Clear cart and update display
                    cart = [];
                    updateCart();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Order Failed',
                    text: xhr.responseJSON?.message || 'Failed to place your order. Please try again.',
                });
            }
        });
    });
});

// Filter items by category
function filterItems(category) {
    $('.list-group-item').removeClass('active');
    $(event.currentTarget).addClass('active');
    
    if (category === 'all') {
        $('.menu-item').show();
    } else {
        $('.menu-item').hide();
        $(`.menu-item[data-category="${category}"]`).show();
    }
}
</script>
@endpush

@push('styles')
<style>
.menu-item-img {
    height: 150px;
    object-fit: cover;
}

.cart-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.item-quantity {
    max-width: 40px;
}

.sticky-top {
    z-index: 1000;
}
</style>
@endpush 