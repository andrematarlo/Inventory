@extends('layouts.app')

@section('title', 'Student Kiosk')

@section('styles')
<style>
    /* Touch-friendly styles */
    .kiosk-container {
        height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
    }
    
    .kiosk-header {
        background: #343a40;
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    
    .kiosk-content {
        display: flex;
        flex: 1;
        overflow: hidden;
    }
    
    /* Classifications sidebar */
    .menu-sidebar {
        width: 250px;
        background: #e9ecef;
        overflow-y: auto;
        padding: 1rem;
    }
    
    .category-btn {
        width: 100%;
        padding: 1rem;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
        border: none;
        border-radius: 10px;
        background: white;
        text-align: left;
        transition: all 0.2s;
    }
    
    .category-btn.active {
        background: #007bff;
        color: white;
    }
    
    /* Menu items grid */
    .menu-items {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .menu-item {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .menu-item:hover {
        transform: translateY(-5px);
    }
    
    .menu-item-img {
        height: 150px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .menu-item-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .menu-item-info {
        padding: 1rem;
    }
    
    .menu-item-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .menu-item-price {
        color: #007bff;
        font-size: 1.2rem;
        font-weight: 700;
    }
    
    /* Cart sidebar */
    .cart-sidebar {
        width: 350px;
        background: white;
        border-left: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
    }
    
    .cart-header {
        padding: 1rem;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    
    .cart-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .cart-item-info {
        flex: 1;
    }
    
    .cart-item-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .cart-item-price {
        color: #007bff;
    }
    
    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .quantity-btn {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
        border-radius: 50%;
    }
    
    .cart-quantity {
        font-size: 1.2rem;
        min-width: 40px;
        text-align: center;
    }
    
    .cart-footer {
        padding: 1rem;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    
    .cart-total {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        text-align: right;
    }
    
    .payment-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .payment-btn {
        padding: 1rem;
        font-size: 1.2rem;
        border-radius: 10px;
    }
    
    .student-id-input {
        display: none;
        margin-bottom: 1rem;
    }
    
    .student-id-input.show {
        display: block;
    }
    
    .btn-place-order {
        width: 100%;
        padding: 1rem;
        font-size: 1.2rem;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="kiosk-container">
    <div class="kiosk-header">
        <h1>Welcome to School Canteen</h1>
        <p class="mb-0">Touch to Order</p>
    </div>
    
    <div class="kiosk-content">
        <!-- Classifications Sidebar -->
        <div class="menu-sidebar">
            <div class="menu-categories">
                @foreach($classifications as $classification)
                <button class="category-btn {{ $loop->first ? 'active' : '' }}" 
                        data-classification="{{ $classification->ClassificationID }}">
                    {{ $classification->ClassificationName }}
                </button>
                @endforeach
            </div>
        </div>
        
        <!-- Menu Items Grid -->
        <div class="menu-items" id="menuItems">
            @foreach($items as $item)
            <div class="menu-item" 
                 data-id="{{ $item->ItemID }}" 
                 data-name="{{ $item->ItemName }}" 
                 data-price="{{ $item->UnitPrice ?? 0 }}">
                <div class="menu-item-img">
                    @if($item->ImagePath)
                        <img src="{{ asset('storage/' . $item->ImagePath) }}" alt="{{ $item->ItemName }}">
                    @else
                        <i class="bi bi-card-image" style="font-size: 3rem; color: #6c757d;"></i>
                    @endif
                </div>
                <div class="menu-item-info">
                    <div class="menu-item-name">{{ $item->ItemName }}</div>
                    <div class="menu-item-price">₱{{ number_format($item->UnitPrice ?? 0, 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Cart Sidebar -->
        <div class="cart-sidebar">
            <div class="cart-header">
                <h3>Your Order</h3>
            </div>
            
            <div class="cart-items" id="cartItems">
                <!-- Cart items will be dynamically added here -->
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                    <p class="mt-3">Your cart is empty</p>
                    <p>Touch items to add them to your cart</p>
                </div>
            </div>
            
            <div class="cart-footer">
                <div class="cart-total">
                    Total: <span id="cartTotal">₱0.00</span>
                </div>
                
                <div class="payment-options">
                    <button class="payment-btn btn btn-outline-primary" data-payment="cash">
                        <i class="bi bi-cash"></i> Cash
                    </button>
                    <button class="payment-btn btn btn-outline-primary" data-payment="deposit">
                        <i class="bi bi-wallet2"></i> Cash Deposit
                    </button>
                </div>
                
                <div class="student-id-input" id="studentIdSection">
                    <input type="text" class="form-control form-control-lg" id="studentId" 
                           placeholder="Enter Student ID">
                </div>
                
                <button class="btn btn-primary btn-place-order" id="placeOrderBtn" disabled>
                    Place Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Order Success Modal -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Order Placed Successfully!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Thank You For Your Order</h4>
                <p class="mb-0">Your order number is: <span id="orderNumber" class="fw-bold"></span></p>
                <p class="text-muted mt-2">Please proceed to the cashier for payment.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">New Order</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cart = [];
    let selectedPaymentMethod = null;
    
    // Event Listeners
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            // Load items for this classification
            loadItems(this.dataset.classification);
        });
    });
    
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedPaymentMethod = this.dataset.payment;
            
            const studentIdSection = document.getElementById('studentIdSection');
            if (selectedPaymentMethod === 'deposit') {
                studentIdSection.classList.add('show');
            } else {
                studentIdSection.classList.remove('show');
            }
            
            updatePlaceOrderButton();
        });
    });
    
    // Functions
    async function loadItems(classificationId) {
        try {
            const response = await fetch(`/inventory/pos/kiosk/items/${classificationId}`);
            const items = await response.json();
            
            const menuItems = document.getElementById('menuItems');
            menuItems.innerHTML = '';
            
            items.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'menu-item';
                itemElement.dataset.id = item.ItemID;
                itemElement.dataset.name = item.ItemName;
                itemElement.dataset.price = item.UnitPrice || 0;
                
                const imagePath = item.ImagePath 
                    ? `<img src="/storage/${item.ImagePath}" alt="${item.ItemName}">`
                    : `<i class="bi bi-card-image" style="font-size: 3rem; color: #6c757d;"></i>`;
                
                itemElement.innerHTML = `
                    <div class="menu-item-img">
                        ${imagePath}
                    </div>
                    <div class="menu-item-info">
                        <div class="menu-item-name">${item.ItemName}</div>
                        <div class="menu-item-price">₱${parseFloat(item.UnitPrice || 0).toFixed(2)}</div>
                    </div>
                `;
                
                itemElement.addEventListener('click', () => addToCart(item));
                menuItems.appendChild(itemElement);
            });
        } catch (error) {
            console.error('Error loading items:', error);
        }
    }
    
    function addToCart(item) {
        const existingItem = cart.find(i => i.id === item.ItemID);
        
        if (existingItem) {
            existingItem.quantity++;
            existingItem.subtotal = existingItem.quantity * existingItem.price;
        } else {
            cart.push({
                id: item.ItemID,
                name: item.ItemName,
                price: parseFloat(item.UnitPrice || 0),
                quantity: 1,
                subtotal: parseFloat(item.UnitPrice || 0)
            });
        }
        
        updateCart();
    }
    
    function updateCart() {
        const cartItems = document.getElementById('cartItems');
        const cartTotal = document.getElementById('cartTotal');
        
        if (cart.length === 0) {
            cartItems.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                    <p class="mt-3">Your cart is empty</p>
                    <p>Touch items to add them to your cart</p>
                </div>
            `;
        } else {
            cartItems.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="btn btn-outline-secondary quantity-btn" 
                                onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <span class="cart-quantity">${item.quantity}</span>
                        <button class="btn btn-outline-secondary quantity-btn" 
                                onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                </div>
            `).join('');
        }
        
        const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
        cartTotal.textContent = `₱${total.toFixed(2)}`;
        
        updatePlaceOrderButton();
    }
    
    function updateQuantity(itemId, newQuantity) {
        const item = cart.find(i => i.id === itemId);
        
        if (item) {
            if (newQuantity <= 0) {
                const index = cart.findIndex(i => i.id === itemId);
                cart.splice(index, 1);
            } else {
                item.quantity = newQuantity;
                item.subtotal = item.quantity * item.price;
            }
            
            updateCart();
        }
    }
    
    function updatePlaceOrderButton() {
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const studentId = document.getElementById('studentId').value;
        
        placeOrderBtn.disabled = cart.length === 0 || 
                                !selectedPaymentMethod || 
                                (selectedPaymentMethod === 'deposit' && !studentId);
    }
    
    document.getElementById('studentId').addEventListener('input', updatePlaceOrderButton);
    
    document.getElementById('placeOrderBtn').addEventListener('click', async function() {
        if (cart.length === 0) return;
        
        const orderData = {
            items: cart.map(item => ({
                id: item.id,
                quantity: item.quantity
            })),
            payment_method: selectedPaymentMethod,
            student_id: selectedPaymentMethod === 'deposit' ? 
                       document.getElementById('studentId').value : null
        };
        
        try {
            const response = await fetch('/inventory/pos/kiosk/place-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(orderData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('orderNumber').textContent = result.order_number;
                const modal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
                modal.show();
                
                // Clear cart and form
                cart.length = 0;
                document.getElementById('studentId').value = '';
                selectedPaymentMethod = null;
                document.querySelectorAll('.payment-btn').forEach(btn => btn.classList.remove('active'));
                document.getElementById('studentIdSection').classList.remove('show');
                updateCart();
            } else {
                alert(result.message || 'Failed to place order. Please try again.');
            }
        } catch (error) {
            console.error('Error placing order:', error);
            alert('An error occurred while placing your order. Please try again.');
        }
    });
    
    // Initialize the first category
    if (document.querySelector('.category-btn')) {
        const firstCategory = document.querySelector('.category-btn');
        firstCategory.classList.add('active');
        loadItems(firstCategory.dataset.classification);
    }
});
</script>
@endsection 