@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .menu-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .category-btn.active {
        background-color: #4CAF50 !important;
        border-color: #4CAF50 !important;
        color: white !important;
    }
    .cart-item {
        background-color: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 10px;
        padding: 15px;
        transition: all 0.3s ease;
    }
    .cart-item:hover {
        background-color: #e9ecef;
    }
    .quantity-control {
        width: 120px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        border-radius: 20px;
        padding: 5px;
        border: 1px solid #dee2e6;
    }
    .quantity-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: #e9ecef;
        color: #495057;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .quantity-btn:hover {
        background: #dee2e6;
    }
    .quantity-input {
        width: 40px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: bold;
    }
    .cart-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        padding: 20px;
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }
    .cart-items-container {
        flex-grow: 1;
        overflow-y: auto;
        padding-right: 10px;
    }
    .cart-items-container::-webkit-scrollbar {
        width: 5px;
    }
    .cart-items-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .cart-items-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    .cart-items-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    .menu-image {
        height: 150px;
        object-fit: cover;
        border-radius: 10px 10px 0 0;
    }
    .price-tag {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.9);
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: bold;
        color: #4CAF50;
    }
    .stock-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(255,255,255,0.9);
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Left Side - Menu Items -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Student Kiosk</h2>
                <div>
                    <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Orders
                    </a>
                </div>
            </div>

                    <!-- Categories -->
            <div class="mb-4 categories-wrapper">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-success category-btn active" data-category="all">
                            All Items
                        </button>
                        @foreach($categories as $category)
                        <button class="btn btn-outline-success category-btn" 
                                    data-category="{{ $category->ClassificationId }}">
                                {{ $category->ClassificationName }}
                            </button>
                        @endforeach
                </div>
                    </div>

                    <!-- Menu Items Grid -->
            <div class="row g-4" id="menu-items-container">
                            @foreach($menuItems as $item)
                    <div class="col-md-6 col-xl-4 menu-item" data-category="{{ $item->ClassificationId }}">
                        <div class="menu-card card h-100 position-relative">
                            <div class="position-relative">
                                            @if(isset($item->ImagePath) && !empty($item->ImagePath))
                                    <img src="{{ asset($item->ImagePath) }}" class="menu-image w-100" alt="{{ $item->ItemName }}">
                                            @else
                                    <div class="menu-image w-100 bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-utensils fa-2x text-muted"></i>
                                                </div>
                                            @endif
                                <div class="price-tag">₱{{ number_format($item->Price, 2) }}</div>
                                <div class="stock-badge">
                                    <i class="fas fa-box me-1"></i>{{ $item->StocksAvailable ?? 0 }}
                                </div>
                                        </div>
                            <div class="card-body">
                                <h5 class="card-title mb-2">{{ $item->ItemName }}</h5>
                                <p class="card-text text-muted small mb-3">
                                                {{ isset($item->Description) ? $item->Description : 'No description' }}
                                            </p>
                                <button class="btn btn-success w-100 add-to-cart-btn"
                                        data-id="{{ $item->ItemId }}"
                                        data-name="{{ $item->ItemName }}"
                                        data-price="{{ $item->Price }}">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
            </div>
        </div>

        <!-- Right Side - Cart -->
        <div class="col-lg-4">
            <div class="cart-container">
                <h3 class="text-center mb-4">Order Summary</h3>
                    
                    <!-- Cart Items -->
                <div class="cart-items-container mb-4">
                    <div id="empty-cart-message" class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Your cart is empty</h5>
                        <p class="text-muted small">Add items from the menu to get started</p>
                        </div>
                    <div id="cart-items">
                        <!-- Cart items will be added here -->
                        </div>
                    </div>

                    <!-- Cart Footer -->
                <div class="mt-auto">
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">Total:</h4>
                            <h4 class="mb-0 text-success" id="cart-total">₱0.00</h4>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2">Payment Method</label>
                            <div class="d-flex gap-2">
                                <button id="pay-cash" class="btn btn-outline-success flex-grow-1 payment-btn">
                                    <i class="fas fa-money-bill me-2"></i>Cash
                                </button>
                                <button id="pay-deposit" class="btn btn-outline-success flex-grow-1 payment-btn">
                                    <i class="fas fa-piggy-bank me-2"></i>Deposit
                                </button>
                            </div>
                        </div>
                        
                        <!-- Student Selection -->
                        <div id="student-selection" class="mb-4 d-none">
                            <label class="form-label fw-bold mb-2">Select Student</label>
                            <select id="student-id" class="form-select">
                                <option value="">Search for a student...</option>
                            </select>
                        </div>

                        <button id="place-order-btn" class="btn btn-lg btn-success w-100" disabled>
                            <i class="fas fa-check-circle me-2"></i>Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize cart
        const POS = {
            cart: {},
            selectedPaymentMethod: null,
            
            init() {
                this.initializeEventListeners();
                this.updateCartDisplay();
            },
            
            initializeEventListeners() {
                // Category buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.handleCategoryFilter(e));
                });
                
                // Add to cart buttons
                document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.handleAddToCart(e));
                });
                
                // Payment method buttons
        document.querySelectorAll('.payment-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.handlePaymentMethodSelection(e));
                });
                
                // Place order button
                document.getElementById('place-order-btn').addEventListener('click', 
                    () => this.handlePlaceOrder());
                
                // Initialize Select2
                        $('#student-id').select2({
                            width: '100%',
                            ajax: {
                                url: '{{ url("/inventory/students/select2") }}',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        search: params.term || '',
                                        page: params.page || 1
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                results: data.map(student => ({
                                                id: student.student_id,
                                    text: `${student.student_id} - ${student.first_name} ${student.last_name}`
                                }))
                                    };
                                },
                                cache: true
                    }
                }).on('change', () => this.updateCartDisplay());
            },
            
            handleCategoryFilter(e) {
                const selectedCategory = e.target.getAttribute('data-category');
                
                // Update active state
                document.querySelectorAll('.category-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                e.target.classList.add('active');
                
                // Filter items
                document.querySelectorAll('.menu-item').forEach(item => {
                    const itemCategory = item.getAttribute('data-category');
                    item.style.display = 
                        (selectedCategory === 'all' || itemCategory === selectedCategory) 
                        ? '' : 'none';
                });
            },
            
            handleAddToCart(e) {
                e.preventDefault();
                const btn = e.target.closest('.add-to-cart-btn');
                const itemId = btn.getAttribute('data-id');
                const itemName = btn.getAttribute('data-name');
                const itemPrice = parseFloat(btn.getAttribute('data-price'));
                
                // Add to cart
                if (!this.cart[itemId]) {
                    this.cart[itemId] = {
                        id: itemId,
                        name: itemName,
                        price: itemPrice,
                        quantity: 1
                    };
                } else {
                    this.cart[itemId].quantity++;
                }
                
                // Visual feedback
                const originalBtn = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalBtn;
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-success');
                }, 1000);
                
                this.updateCartDisplay();
            },
            
            updateCartDisplay() {
                const cartItems = document.getElementById('cart-items');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const cartTotal = document.getElementById('cart-total');
            const placeOrderBtn = document.getElementById('place-order-btn');
            
                // Clear existing cart items
            cartItems.innerHTML = '';
                let total = 0;
            
                const cartKeys = Object.keys(this.cart);
            
            if (cartKeys.length === 0) {
                emptyCartMessage.classList.remove('d-none');
                cartItems.classList.add('d-none');
                placeOrderBtn.disabled = true;
            } else {
                emptyCartMessage.classList.add('d-none');
                cartItems.classList.remove('d-none');
                
                    // Create a new cart items container
                    const cartItemsContainer = document.createElement('div');
                
                cartKeys.forEach(itemId => {
                        const item = this.cart[itemId];
                        const subtotal = item.price * item.quantity;
                        total += subtotal;
                    
                    const itemElement = document.createElement('div');
                        itemElement.className = 'cart-item';
                    itemElement.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">${item.name}</h6>
                                <span class="text-success">₱${item.price.toFixed(2)}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Subtotal: ₱${subtotal.toFixed(2)}</span>
                                <div class="quantity-control">
                                    <button class="quantity-btn minus" data-id="${itemId}">-</button>
                                    <input type="text" class="quantity-input" value="${item.quantity}" readonly>
                                    <button class="quantity-btn plus" data-id="${itemId}">+</button>
                            </div>
                        </div>
                    `;
                        
                        cartItemsContainer.appendChild(itemElement);
                        
                        // Add quantity button listeners
                        const plusBtn = itemElement.querySelector('.plus');
                        const minusBtn = itemElement.querySelector('.minus');
                        
                        plusBtn.addEventListener('click', () => {
                            this.cart[itemId].quantity++;
                            this.updateCartDisplay();
                        });
                        
                        minusBtn.addEventListener('click', () => {
                            if (this.cart[itemId].quantity > 1) {
                                this.cart[itemId].quantity--;
                            } else {
                                delete this.cart[itemId];
                            }
                            this.updateCartDisplay();
                        });
                    });
                    
                    // Replace the cart items with the new container
                    cartItems.appendChild(cartItemsContainer);
                    
                    // Update order button state
                    placeOrderBtn.disabled = !this.selectedPaymentMethod || 
                        (this.selectedPaymentMethod === 'deposit' && 
                         !document.getElementById('student-id').value);
                }
                
                cartTotal.textContent = `₱${total.toFixed(2)}`;
            },
            
            handlePaymentMethodSelection(e) {
                const btn = e.target.closest('.payment-btn');
                
                document.querySelectorAll('.payment-btn').forEach(b => {
                    b.classList.remove('btn-success');
                    b.classList.add('btn-outline-success');
                });
                
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                
                this.selectedPaymentMethod = btn.id === 'pay-cash' ? 'cash' : 'deposit';
                
                const studentSelection = document.getElementById('student-selection');
                studentSelection.classList.toggle('d-none', this.selectedPaymentMethod === 'cash');
                
                this.updateCartDisplay();
            },
            
            handlePlaceOrder() {
                if (Object.keys(this.cart).length === 0 || !this.selectedPaymentMethod) {
                    return;
                }
                
                const items = Object.values(this.cart).map(item => ({
                    id: item.id,
                    quantity: item.quantity,
                    price: item.price,
                    name: item.name
                }));
                
                const total = items.reduce((sum, item) => 
                    sum + (item.price * item.quantity), 0);
                
                const orderData = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    cart_items: JSON.stringify(items),
                    payment_type: this.selectedPaymentMethod,
                    total_amount: total.toFixed(2),
                    student_id: this.selectedPaymentMethod === 'deposit' ? 
                        document.getElementById('student-id').value : null
                };
                
                Swal.fire({
                    title: 'Processing Order...',
                    html: 'Please wait while we process your order',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '{{ route("pos.store") }}',
                    method: 'POST',
                    data: orderData,
                    dataType: 'json',
                    success: (response) => {
                        Swal.close();
                        
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Created',
                                text: `Order #${response.order.OrderNumber} created successfully`,
                                showCancelButton: true,
                                confirmButtonText: 'View Order',
                                cancelButtonText: 'New Order',
                                confirmButtonColor: '#4CAF50'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 
                                        `/inventory/pos/orders/${response.order.OrderID}`;
                                }
                            });
                            
                            this.resetOrder();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to create order',
                                confirmButtonColor: '#F44336'
                            });
                        }
                    },
                    error: (xhr) => {
                        Swal.close();
                        
                        let errorMessage = 'Failed to create order';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.alert) {
                                Swal.fire({
                                    icon: response.alert.type,
                                    title: response.alert.title,
                                    text: response.alert.text,
                                    confirmButtonColor: '#F44336'
                                });
                                return;
                            }
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Order Failed',
                            text: errorMessage,
                            confirmButtonColor: '#F44336'
                        });
                    }
                });
            },
            
            resetOrder() {
                this.cart = {};
                this.selectedPaymentMethod = null;
                
                document.querySelectorAll('.payment-btn').forEach(btn => {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                });
                
                document.getElementById('student-selection').classList.add('d-none');
                
                if ($('#student-id').data('select2')) {
                    $('#student-id').val(null).trigger('change');
                }
                
                this.updateCartDisplay();
            }
        };
        
        // Initialize POS system
        POS.init();
    });
</script>
@endpush
@endsection 