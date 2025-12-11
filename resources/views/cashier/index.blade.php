@extends('cashier.layout')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Products</h5>
            </div>
            <div class="card-body">
                <div class="row" id="product-list">
                    @foreach($products as $product)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card product-card h-100" onclick="addToCart({{ $product }})">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h6 class="card-title font-weight-bold">{{ $product->name }}</h6>
                                <p class="card-text text-primary fs-5">${{ number_format($product->price, 2) }}</p>
                                <small class="text-muted">Stock: {{ $product->stock }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <h5 class="mb-0">Current Order</h5>
                <span id="cart-count" class="badge bg-white text-success">0 items</span>
            </div>
            
            <div class="card-body p-0">
                <ul class="list-group list-group-flush scrollable-cart" id="cart-items">
                    <li class="list-group-item text-center text-muted mt-5">Cart is empty</li>
                </ul>
            </div>

            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Total:</h4>
                    <h4 class="text-success">$<span id="total-amount">0.00</span></h4>
                </div>
                
                <div class="mb-3">
                    <label>Payment Method</label>
                    <select class="form-select" id="payment-method">
                        <option value="cash">Cash</option>
                        <option value="digital">Digital Wallet</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button class="btn btn-success btn-lg" onclick="processCheckout()">
                        <i class="fa fa-check-circle"></i> PAY NOW
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Initialize Cart State
    let cart = [];
    
    // 2. Add to Cart Function
    function addToCart(product) {
        // Check if item already exists in cart
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            if(existingItem.qty < product.stock) {
                existingItem.qty++;
            } else {
                alert('Not enough stock!');
                return;
            }
        } else {
            if(product.stock > 0) {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    qty: 1,
                    max_stock: product.stock
                });
            } else {
                alert('Out of stock!');
                return;
            }
        }
        updateCartUI();
    }

    // 3. Remove/Decrease Item
    function updateQty(productId, change) {
        const item = cart.find(item => item.id === productId);
        if (!item) return;

        item.qty += change;

        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== productId);
        } else if (item.qty > item.max_stock) {
            item.qty = item.max_stock; // Prevent going over stock
            alert("Max stock reached");
        }
        updateCartUI();
    }

    // 4. Update the UI (HTML)
    function updateCartUI() {
        const cartList = document.getElementById('cart-items');
        const totalSpan = document.getElementById('total-amount');
        const countSpan = document.getElementById('cart-count');
        
        cartList.innerHTML = '';
        let total = 0;
        let itemCount = 0;

        if (cart.length === 0) {
            cartList.innerHTML = '<li class="list-group-item text-center text-muted mt-5">Cart is empty</li>';
        }

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            itemCount += item.qty;

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <div>
                    <h6 class="my-0">${item.name}</h6>
                    <small class="text-muted">$${item.price.toFixed(2)} x ${item.qty}</small>
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-success fw-bold me-3">$${itemTotal.toFixed(2)}</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, -1)">-</button>
                        <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                </div>
            `;
            cartList.appendChild(li);
        });

        totalSpan.innerText = total.toFixed(2);
        countSpan.innerText = itemCount + ' items';
    }

    // 5. Checkout Logic (Send to Backend)
    function processCheckout() {
        if (cart.length === 0) {
            alert("Cart is empty!");
            return;
        }

        if(!confirm("Process transaction?")) return;

        const totalAmount = document.getElementById('total-amount').innerText;
        const paymentMethod = document.getElementById('payment-method').value;

        // Prepare data for Laravel
        const data = {
            cart: cart,
            total_amount: totalAmount,
            amount_paid: totalAmount, // For now assuming exact payment
            payment_method: paymentMethod
        };

        // Fetch API request
        fetch("{{ route('cashier.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Transaction Successful!");
                cart = []; // Clear cart
                updateCartUI();
                location.reload(); // Reload to update stock numbers on grid
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endsection