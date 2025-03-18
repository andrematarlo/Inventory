@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row" style="min-height: 85vh;">
        <!-- Left Side - Menu Items -->
        <div class="col-lg-8 mb-4">
            <!-- Add Custom Item Button (Moved outside the card) -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="fs-3 fw-bold mb-0">Student Kiosk</h2>
                <div>
                    <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                    <button id="add-custom-item-btn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#custom-item-modal">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </button>
                </div>
            </div>

            <!-- Alerts -->
            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <div class="card shadow h-100">
                <div class="card-body d-flex flex-column">
                    <!-- Categories -->
                    <div class="mb-4 overflow-auto" style="white-space: nowrap;">
                        <button class="category-btn btn btn-primary me-2 mb-2" data-category="all">
                            All Items
                        </button>
                        @foreach($categories as $category)
                            <button class="category-btn btn btn-outline-primary me-2 mb-2" 
                                    data-category="{{ $category->ClassificationId }}">
                                {{ $category->ClassificationName }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Menu Items Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 flex-grow-1 overflow-auto" id="menu-items-container">
                        @if($menuItems->isEmpty())
                            <div class="col-12 text-center py-5 my-5">
                                <div class="display-1 text-muted mb-4"><i class="fas fa-box-open"></i></div>
                                <h3 class="fs-4 text-muted mb-3">No Menu Items Available</h3>
                                <p class="text-muted mb-4">There are no menu items available for students to order.</p>
                                <p class="mb-0">Click the "Add Item" button above to add menu items.</p>
                            </div>
                        @else
                            @foreach($menuItems as $item)
                                <div class="col">
                                    <div class="card h-100 menu-item" 
                                         data-id="{{ $item->ItemId }}"
                                         data-name="{{ $item->ItemName }}"
                                         data-price="{{ $item->Price }}"
                                         data-category="{{ $item->ClassificationId }}">
                                        <div class="card-img-top bg-light text-center" style="height: 120px; overflow: hidden;">
                                            @if(isset($item->ImagePath) && !empty($item->ImagePath))
                                                <img src="{{ asset($item->ImagePath) }}" alt="{{ $item->ItemName }}" class="h-100 object-fit-cover">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                    <i class="fas fa-image fa-2x"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title text-truncate">{{ $item->ItemName }}</h5>
                                            <p class="card-text text-truncate text-muted small">
                                                {{ isset($item->Description) ? $item->Description : 'No description' }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                                <span class="fw-bold text-primary">₱{{ number_format($item->Price, 2) }}</span>
                                                <span class="badge bg-light text-dark">Stock: {{ $item->StocksAvailable ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Cart -->
        <div class="col-lg-4">
            <div class="card shadow h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="card-title text-center fw-bold mb-4">Order Summary</h2>
                    
                    <!-- Cart Items -->
                    <div class="flex-grow-1 overflow-auto mb-4" id="cart-container" style="min-height: 300px;">
                        <div id="empty-cart-message" class="text-center py-5 text-muted">
                            <div class="mb-2"><i class="fas fa-shopping-cart fa-3x"></i></div>
                            <p class="mb-1">Your cart is empty</p>
                            <p class="small">Add items from the menu</p>
                        </div>
                        <div id="cart-items" class="d-none">
                            <!-- Cart items will be added here via JavaScript -->
                        </div>
                    </div>

                    <!-- Cart Footer -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-4">Total:</span>
                            <span class="fw-bold fs-4 text-primary" id="cart-total">₱0.00</span>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2">Payment Method</label>
                            <div class="d-flex gap-2">
                                <button id="pay-cash" class="payment-btn btn btn-outline-primary flex-grow-1 py-2">
                                    <i class="fas fa-money-bill me-1"></i> Cash
                                </button>
                                <button id="pay-deposit" class="payment-btn btn btn-outline-primary flex-grow-1 py-2">
                                    <i class="fas fa-piggy-bank me-1"></i> Deposit
                                </button>
                            </div>
                        </div>
                        
                        <!-- Student Selection (for deposit payment) -->
                        <div id="student-selection" class="mb-4 d-none">
                            <label class="form-label fw-medium mb-2">Select Student</label>
                            <select id="student-id" class="form-select">
                                <option value="">Select a student</option>
                                <!-- Students will be loaded via AJAX -->
                            </select>
                            <div class="invalid-feedback">Please select a student for deposit payment</div>
                        </div>

                        <button id="place-order-btn" class="btn btn-lg btn-secondary w-100 py-3 fw-bold" disabled>
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="order-form" action="{{ route('pos.store') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="cart_items" id="items-input">
    <input type="hidden" name="payment_type" id="payment-type-input">
    <input type="hidden" name="total_amount" id="total-amount-input">
    <input type="hidden" name="student_id" id="student-id-input">
</form>

<!-- Custom Item Modal -->
<div class="modal fade" id="custom-item-modal" tabindex="-1" aria-labelledby="customItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customItemModalLabel">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('pos.add-menu-item') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Item Name*</label>
                        <input type="text" id="item_name" name="item_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price*</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category*</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->ClassificationId }}">{{ $category->ClassificationName }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save to Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cart = {};
        let selectedPaymentMethod = null;
        let customItemCounter = 0;
        const cartItems = document.getElementById('cart-items');
        
        // Initialize Bootstrap components
        const customItemModal = new bootstrap.Modal(document.getElementById('custom-item-modal'));
        
        // Focus input field when modal is shown
        document.getElementById('custom-item-modal').addEventListener('shown.bs.modal', function() {
            document.getElementById('item_name').focus();
        });
        
        // Add student selection change listener
        document.getElementById('student-id').addEventListener('change', function() {
            if (selectedPaymentMethod === 'deposit') {
                updateCart(); // This will update the button state
            }
        });
        
        // Handle tab changes to reset forms
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                if (event.target.id === 'custom-tab') {
                    document.getElementById('custom-item-name').focus();
                } else if (event.target.id === 'menu-tab') {
                    document.getElementById('item_name').focus();
                }
            });
        });
        
        // Menu item click
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = parseFloat(this.getAttribute('data-price'));
                
                if (cart[id]) {
                    cart[id].quantity++;
                } else {
                    cart[id] = {
                        id: id,
                        name: name,
                        price: price,
                        quantity: 1
                    };
                }
                
                updateCart();
            });
        });
        
        // Category filtering
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.category-btn').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                
                const categoryId = this.getAttribute('data-category');
                
                // Filter menu items
                document.querySelectorAll('.menu-item').forEach(item => {
                    const parent = item.closest('.col');
                    if (categoryId === 'all' || item.getAttribute('data-category') === categoryId) {
                        parent.style.display = 'block';
                    } else {
                        parent.style.display = 'none';
                    }
                });
            });
        });
        
        // Payment method selection
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.payment-btn').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                
                const studentSelectionDiv = document.getElementById('student-selection');
                
                if (this.id === 'pay-cash') {
                    selectedPaymentMethod = 'cash';
                    studentSelectionDiv.classList.add('d-none');
                } else if (this.id === 'pay-deposit') {
                    selectedPaymentMethod = 'deposit';
                    studentSelectionDiv.classList.remove('d-none');
                    
                    // Initialize Select2 for student selection if not already done
                    if (!$('#student-id').data('select2')) {
                        $('#student-id').select2({
                            width: '100%',
                            placeholder: 'Search for a student',
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
                                        results: data.map(function(student) {
                                            return {
                                                id: student.student_id,
                                                text: student.student_id + ' - ' + student.first_name + ' ' + student.last_name
                                            };
                                        })
                                    };
                                },
                                cache: true
                            }
                        });
                    }
                }
                
                // Update place order button
                updateCart();
            });
        });
        
        // Place order button
        document.getElementById('place-order-btn').addEventListener('click', function() {
            if (Object.keys(cart).length === 0 || !selectedPaymentMethod) {
                return;
            }
            
            // Validate student selection for deposit payments
            if (selectedPaymentMethod === 'deposit') {
                const studentId = document.getElementById('student-id').value;
                if (!studentId) {
                    document.getElementById('student-id').classList.add('is-invalid');
                    return;
                } else {
                    document.getElementById('student-id').classList.remove('is-invalid');
                }
                
                // Set the student ID input
                document.getElementById('student-id-input').value = studentId;
            }
            
            // Prepare form data
            const itemsInput = document.getElementById('items-input');
            const paymentTypeInput = document.getElementById('payment-type-input');
            const totalAmountInput = document.getElementById('total-amount-input');
            const orderForm = document.getElementById('order-form');
            
            let total = 0;
            const items = [];
            
            Object.keys(cart).forEach(itemId => {
                const item = cart[itemId];
                total += item.price * item.quantity;
                
                // Ensure we're only sending data that matches the database schema
                items.push({
                    id: item.id,
                    quantity: item.quantity,
                    price: item.price,
                    name: item.name,
                    isCustom: item.isCustom || false
                });
            });
            
            // Log the items for debugging
            console.log('Submitting order with cart_items:', items);
            
            // Create a deep copy of the form data for logging
            const formDataForLog = {
                cart_items: JSON.parse(JSON.stringify(items)),
                payment_type: selectedPaymentMethod,
                total_amount: total.toFixed(2),
                student_id: selectedPaymentMethod === 'deposit' ? document.getElementById('student-id-input').value : null
            };
            console.log('Full form data to be submitted:', formDataForLog);
            
            // Set form input values
            itemsInput.value = JSON.stringify(items);
            paymentTypeInput.value = selectedPaymentMethod;
            totalAmountInput.value = total.toFixed(2);
            
            // Submit form
            console.log('Form data prepared, submitting form...');
            console.log('Payment type:', selectedPaymentMethod);
            console.log('Total amount:', total.toFixed(2));
            if (selectedPaymentMethod === 'deposit') {
                console.log('Student ID:', document.getElementById('student-id-input').value);
            }
            orderForm.submit();
        });
        
        // Function to update cart display
        function updateCart() {
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const cartTotal = document.getElementById('cart-total');
            const placeOrderBtn = document.getElementById('place-order-btn');
            
            cartItems.innerHTML = '';
            
            let total = 0;
            const cartKeys = Object.keys(cart);
            
            if (cartKeys.length === 0) {
                emptyCartMessage.classList.remove('d-none');
                cartItems.classList.add('d-none');
                placeOrderBtn.classList.remove('btn-primary');
                placeOrderBtn.classList.add('btn-secondary');
                placeOrderBtn.disabled = true;
            } else {
                emptyCartMessage.classList.add('d-none');
                cartItems.classList.remove('d-none');
                
                cartKeys.forEach(itemId => {
                    const item = cart[itemId];
                    total += item.price * item.quantity;
                    
                    const itemElement = document.createElement('div');
                    itemElement.className = 'card mb-2';
                    itemElement.innerHTML = `
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="card-title mb-0">${item.name}</h6>
                                <span class="badge bg-primary">₱${item.price.toFixed(2)}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Quantity:</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button class="btn btn-outline-secondary decrement-btn" type="button" data-id="${itemId}">-</button>
                                    <span class="form-control text-center">${item.quantity}</span>
                                    <button class="btn btn-outline-secondary increment-btn" type="button" data-id="${itemId}">+</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    cartItems.appendChild(itemElement);
                });
                
                // Update order button state based on payment method
                if (selectedPaymentMethod) {
                    // For deposit payments, also check student selection
                    if (selectedPaymentMethod === 'deposit') {
                        const studentId = document.getElementById('student-id').value;
                        if (!studentId) {
                            placeOrderBtn.classList.remove('btn-primary');
                            placeOrderBtn.classList.add('btn-secondary');
                            placeOrderBtn.disabled = true;
                        } else {
                            placeOrderBtn.classList.remove('btn-secondary');
                            placeOrderBtn.classList.add('btn-primary');
                            placeOrderBtn.disabled = false;
                        }
                    } else {
                        placeOrderBtn.classList.remove('btn-secondary');
                        placeOrderBtn.classList.add('btn-primary');
                        placeOrderBtn.disabled = false;
                    }
                } else {
                    placeOrderBtn.classList.remove('btn-primary');
                    placeOrderBtn.classList.add('btn-secondary');
                    placeOrderBtn.disabled = true;
                }
            }
            
            cartTotal.textContent = `₱${total.toFixed(2)}`;
            
            // Add event listeners to new buttons
            document.querySelectorAll('.increment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    cart[itemId].quantity++;
                    updateCart();
                });
            });
            
            document.querySelectorAll('.decrement-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    if (cart[itemId].quantity > 1) {
                        cart[itemId].quantity--;
                    } else {
                        delete cart[itemId];
                    }
                    updateCart();
                });
            });
        }
        
        // Initialize cart
        updateCart();
    });
</script>
@endpush
@endsection 