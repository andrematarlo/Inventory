@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div class="row">
        <!-- Menu Items Column -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Menu Items</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search items...">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Category filters -->
                    <div class="mb-3 category-filters">
                        <button type="button" class="btn btn-outline-primary me-2 mb-2 category-filter active" data-category="all">All Items</button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-outline-primary me-2 mb-2 category-filter" 
                                    data-category="{{ $category->ClassificationId }}">
                                {{ $category->ClassificationName }}
                            </button>
                        @endforeach
                    </div>
                    
                    <!-- Menu items grid -->
                    <div class="row" id="menuItemsContainer">
                        @forelse($menuItems as $item)
                        <div class="col-md-4 col-sm-6 mb-4 menu-item" data-category="{{ $item->ClassificationId }}">
                            <div class="card h-100 menu-item-card" data-item-id="{{ $item->MenuItemID }}" data-item-name="{{ $item->ItemName }}" data-item-price="{{ $item->Price }}">
                                <div class="menu-item-image">
                                    @if($item->image_path)
                                        <img src="{{ asset('storage/' . $item->image_path) }}" class="card-img-top" alt="{{ $item->ItemName }}">
                                    @else
                                        <div class="no-image-placeholder d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $item->ItemName }}</h6>
                                    <p class="card-text mb-1 text-muted small">{{ Str::limit($item->Description, 50) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="fw-bold">₱{{ number_format($item->Price, 2) }}</span>
                                        <button class="btn btn-sm btn-primary add-to-cart">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                No menu items available. Please add menu items first.
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Column -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Current Order</h5>
                </div>
                <div class="card-body">
                    @if($isStudent)
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Your Balance:</span>
                            <span class="fw-bold">₱{{ number_format($studentBalance, 2) }}</span>
                        </div>
                    </div>
                    @endif
                    
                    <div id="cartEmptyMessage" class="{{ empty($cartItems) ? '' : 'd-none' }}">
                        <div class="alert alert-light text-center">
                            <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                            <p class="mb-0 mt-2">Your cart is empty</p>
                            <p class="text-muted small">Add items from the menu</p>
                        </div>
                    </div>
                    
                    <div id="cartItems" class="{{ empty($cartItems) ? 'd-none' : '' }}">
                        <div class="list-group mb-3" id="cartItemsList">
                            <!-- Cart items will be inserted here dynamically -->
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total:</span>
                            <span class="fw-bold" id="cartTotal">₱0.00</span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" name="paymentMethod">
                                <option value="cash">Cash</option>
                                @if($isStudent)
                                <option value="deposit">Use Deposit</option>
                                @endif
                            </select>
                        </div>
                        
                        <div id="cashPaymentOptions">
                            <div class="mb-3">
                                <label for="amountTendered" class="form-label">Amount Tendered</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="amountTendered" step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="change" class="form-label">Change</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="change" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary w-100" id="placeOrderBtn">
                            <i class="bi bi-cart-check"></i> Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quantityModalLabel">Set Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="itemQuantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="itemQuantity" min="1" value="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addToCartBtn">Add to Cart</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let cartItems = [];
        let selectedItem = null;
        
        // Handle category filtering
        $('.category-filter').on('click', function() {
            const categoryId = $(this).data('category');
            
            // Toggle active state
            $('.category-filter').removeClass('active');
            $(this).addClass('active');
            
            // Filter menu items
            if (categoryId === 'all') {
                $('.menu-item').show();
            } else {
                $('.menu-item').hide();
                $(`.menu-item[data-category="${categoryId}"]`).show();
            }
        });
        
        // Handle search
        $('#searchInput').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            $('.menu-item').each(function() {
                const itemName = $(this).find('.card-title').text().toLowerCase();
                if (itemName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Open quantity modal when adding item
        $('.menu-item-card').on('click', function() {
            selectedItem = {
                id: $(this).data('item-id'),
                name: $(this).data('item-name'),
                price: $(this).data('item-price'),
                quantity: 1
            };
            
            $('#quantityModal').modal('show');
        });
        
        // Add to cart from modal
        $('#addToCartBtn').on('click', function() {
            const quantity = parseInt($('#itemQuantity').val()) || 1;
            
            if (selectedItem) {
                selectedItem.quantity = quantity;
                selectedItem.subtotal = selectedItem.price * quantity;
                
                // Check if item already in cart
                const existingItemIndex = cartItems.findIndex(item => item.id === selectedItem.id);
                
                if (existingItemIndex !== -1) {
                    // Update quantity if item exists
                    cartItems[existingItemIndex].quantity += quantity;
                    cartItems[existingItemIndex].subtotal = cartItems[existingItemIndex].price * cartItems[existingItemIndex].quantity;
                } else {
                    // Add new item
                    cartItems.push(selectedItem);
                }
                
                updateCartDisplay();
                $('#quantityModal').modal('hide');
                
                // Reset quantity input
                $('#itemQuantity').val(1);
            }
        });
        
        // Handle payment method change
        $('#paymentMethod').on('change', function() {
            const method = $(this).val();
            
            if (method === 'cash') {
                $('#cashPaymentOptions').show();
            } else {
                $('#cashPaymentOptions').hide();
            }
        });
        
        // Calculate change
        $('#amountTendered').on('input', function() {
            const amountTendered = parseFloat($(this).val()) || 0;
            const total = calculateTotal();
            const change = amountTendered - total;
            
            $('#change').val(change.toFixed(2));
        });
        
        // Place order
        $('#placeOrderBtn').on('click', function() {
            if (cartItems.length === 0) {
                alert('Please add items to your cart');
                return;
            }
            
            const paymentMethod = $('#paymentMethod').val();
            let amountTendered = 0;
            let changeAmount = 0;
            
            if (paymentMethod === 'cash') {
                amountTendered = parseFloat($('#amountTendered').val()) || 0;
                changeAmount = parseFloat($('#change').val()) || 0;
                
                if (amountTendered <= 0) {
                    alert('Please enter a valid amount tendered');
                    return;
                }
                
                if (changeAmount < 0) {
                    alert('Amount tendered is less than the total amount');
                    return;
                }
            }
            
            // Create order data
            const orderData = {
                payment_method: paymentMethod,
                items: cartItems,
                total_amount: calculateTotal(),
                amount_tendered: amountTendered,
                change_amount: changeAmount,
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            // If student order, add student ID
            if ({{ $isStudent ? 'true' : 'false' }}) {
                orderData.student_id = '{{ $studentId }}';
            }
            
            // Submit order
            $.ajax({
                url: "{{ route('pos.store') }}",
                type: 'POST',
                data: orderData,
                success: function(response) {
                    if (response.success) {
                        // Redirect to order details
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Failed to place order');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to place order';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    alert(errorMessage);
                }
            });
        });
        
        // Update cart display
        function updateCartDisplay() {
            if (cartItems.length === 0) {
                $('#cartEmptyMessage').removeClass('d-none');
                $('#cartItems').addClass('d-none');
                return;
            }
            
            $('#cartEmptyMessage').addClass('d-none');
            $('#cartItems').removeClass('d-none');
            
            // Clear current items
            $('#cartItemsList').empty();
            
            // Add items to cart display
            cartItems.forEach((item, index) => {
                const itemHtml = `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">${item.name}</h6>
                            <small class="text-muted">₱${item.price.toFixed(2)} x ${item.quantity}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="me-3">₱${item.subtotal.toFixed(2)}</span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary decrease-qty" data-index="${index}">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary increase-qty" data-index="${index}">
                                    <i class="bi bi-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger remove-item" data-index="${index}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#cartItemsList').append(itemHtml);
            });
            
            // Update total
            const total = calculateTotal();
            $('#cartTotal').text(`₱${total.toFixed(2)}`);
            
            // Update change if amount tendered is set
            const amountTendered = parseFloat($('#amountTendered').val()) || 0;
            if (amountTendered > 0) {
                const change = amountTendered - total;
                $('#change').val(change.toFixed(2));
            }
            
            // Attach handlers to new buttons
            $('.increase-qty').on('click', function() {
                const index = $(this).data('index');
                cartItems[index].quantity++;
                cartItems[index].subtotal = cartItems[index].price * cartItems[index].quantity;
                updateCartDisplay();
            });
            
            $('.decrease-qty').on('click', function() {
                const index = $(this).data('index');
                if (cartItems[index].quantity > 1) {
                    cartItems[index].quantity--;
                    cartItems[index].subtotal = cartItems[index].price * cartItems[index].quantity;
                    updateCartDisplay();
                }
            });
            
            $('.remove-item').on('click', function() {
                const index = $(this).data('index');
                cartItems.splice(index, 1);
                updateCartDisplay();
            });
        }
        
        function calculateTotal() {
            return cartItems.reduce((sum, item) => sum + item.subtotal, 0);
        }
    });
</script>
@endsection 