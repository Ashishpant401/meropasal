<?php
include 'header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock, p.old_price 
                      FROM cart c 
                      JOIN products p ON c.product_id = p.id 
                      WHERE c.user_id = ? AND p.deleted = 0 
                      ORDER BY c.created_at DESC");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$selected_subtotal = 0;
$selected_items_count = 0;

foreach($cart_items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    
    if($item['selected']) {
        $selected_subtotal += $item_total;
        $selected_items_count += $item['quantity'];
    }
}

$shipping = $selected_subtotal > 0 ? 10.00 : 0;
$tax = $selected_subtotal * 0.1;
$total = $selected_subtotal + $shipping + $tax;
?>

<section class="cart-page">
    <div class="container">
        <h1>Shopping Cart</h1>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if(empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Browse our products and add items to your cart</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <div class="cart-header">
                        <div class="select-all">
                            <label class="checkbox-container">
                                <input type="checkbox" id="select-all-checkbox" <?php echo $selected_items_count > 0 ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Select All (<?php echo count($cart_items); ?> items)
                            </label>
                        </div>
                        <button class="btn btn-outline btn-small" id="clear-cart-btn">
                            <i class="fas fa-trash"></i> Clear Cart
                        </button>
                    </div>
                    
                    <div class="items-list">
                        <?php foreach($cart_items as $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                            <div class="item-select">
                                <label class="checkbox-container">
                                    <input type="checkbox" class="item-checkbox" 
                                           data-product-id="<?php echo $item['product_id']; ?>" 
                                           <?php echo $item['selected'] ? 'checked' : ''; ?>>
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            
                            <div class="item-image">
                                <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            
                            <div class="item-details">
                                <h3><?php echo $item['name']; ?></h3>
                                <?php if($item['size']): ?>
                                    <p class="item-attribute">Size: <?php echo $item['size']; ?></p>
                                <?php endif; ?>
                                <?php if($item['color']): ?>
                                    <p class="item-attribute">Color: <?php echo $item['color']; ?></p>
                                <?php endif; ?>
                                
                                <div class="item-price-mobile">
                                    <span class="current-price">Rs <?php echo number_format($item['price'], 2); ?></span>
                                    <?php if($item['old_price'] && $item['old_price'] > $item['price']): ?>
                                        <span class="old-price">Rs <?php echo number_format($item['old_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="stock-info">
                                    <?php if($item['stock'] > 0): ?>
                                        <span class="in-stock">In Stock (<?php echo $item['stock']; ?> available)</span>
                                    <?php else: ?>
                                        <span class="out-of-stock">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-actions">
                                    <button class="btn-remove" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                    <button class="btn-wishlist" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="far fa-heart"></i> Move to Wishlist
                                    </button>
                                </div>
                            </div>
                            
                            <div class="item-quantity">
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn" data-action="decrease" data-product-id="<?php echo $item['product_id']; ?>">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" data-product-id="<?php echo $item['product_id']; ?>">
                                    <button type="button" class="quantity-btn" data-action="increase" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                </div>
                            </div>
                            
                            <div class="item-price">
                                <span class="current-price">Rs <?php echo number_format($item['price'], 2); ?></span>
                                <?php if($item['old_price'] && $item['old_price'] > $item['price']): ?>
                                    <span class="old-price">Rs <?php echo number_format($item['old_price'], 2); ?></span>
                                <?php endif; ?>
                                <div class="item-total">
                                    Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Selected Items (<?php echo $selected_items_count; ?>):</span>
                                <span>Rs <?php echo number_format($selected_subtotal, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>Rs <?php echo number_format($shipping, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax:</span>
                                <span>Rs <?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="summary-row total">
                                <strong>Total:</strong>
                                <strong>Rs <?php echo number_format($total, 2); ?></strong>
                            </div>
                        </div>
                        
                        <div class="checkout-actions">
                            <?php if($selected_items_count > 0): ?>
                                <form method="POST" action="checkout.php" id="checkout-form">
                                    <?php foreach($cart_items as $item): ?>
                                        <?php if($item['selected']): ?>
                                            <input type="hidden" name="selected_items[]" value="<?php echo $item['id']; ?>">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-primary btn-large btn-checkout">
                                        <i class="fas fa-lock"></i> Proceed to Checkout
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-disabled btn-large" disabled>
                                    Select items to checkout
                                </button>
                            <?php endif; ?>
                            
                            <a href="products.php" class="btn btn-outline">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                        </div>
                        
                        <div class="security-features">
                            <div class="security-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure Checkout</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-undo"></i>
                                <span>30-Day Returns</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-truck"></i>
                                <span>Free Shipping Over Rs 5000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity update functionality
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const productId = this.dataset.productId;
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            let value = parseInt(input.value);
            
            if(action === 'increase' && value < parseInt(input.max)) {
                input.value = value + 1;
                updateQuantity(productId, value + 1);
            } else if(action === 'decrease' && value > parseInt(input.min)) {
                input.value = value - 1;
                updateQuantity(productId, value - 1);
            }
        });
    });
    
    // Direct quantity input change
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const value = parseInt(this.value);
            const max = parseInt(this.max);
            const min = parseInt(this.min);
            
            if(value < min) {
                this.value = min;
            } else if(value > max) {
                this.value = max;
            }
            
            updateQuantity(productId, this.value);
        });
    });
    
    // Individual item selection
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const selected = this.checked ? 1 : 0;
            
            toggleItemSelection(productId, selected);
        });
    });
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const selected = this.checked ? 1 : 0;
            
            if(selected) {
                selectAllItems();
            } else {
                unselectAllItems();
            }
        });
    }
    
    // Remove item
    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeItem(productId);
        });
    });
    
    // Clear cart
    const clearCartBtn = document.getElementById('clear-cart-btn');
    if(clearCartBtn) {
        clearCartBtn.addEventListener('click', function() {
            if(confirm('Are you sure you want to clear your entire cart?')) {
                clearCart();
            }
        });
    }
    
    // Move to wishlist
    document.querySelectorAll('.btn-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            moveToWishlist(productId);
        });
    });
    
    // Functions
    function updateQuantity(productId, quantity) {
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                updateCartDisplay(data);
            } else {
                showNotification(data.message, 'error');
                // Reload to sync with server state
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating quantity', 'error');
        });
    }
    
    function toggleItemSelection(productId, selected) {
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=toggle_select&product_id=${productId}&selected=${selected}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                updateCartDisplay(data);
            } else {
                showNotification(data.message, 'error');
            location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating selection', 'error');
        });
    }
    
    function selectAllItems() {
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=select_all'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                updateCartDisplay(data);
                // Check all checkboxes
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                });
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error selecting all items', 'error');
        });
    }
    
    function unselectAllItems() {
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=unselect_all'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                updateCartDisplay(data);
                // Uncheck all checkboxes
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.getElementById('select-all-checkbox').checked = false;
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error unselecting all items', 'error');
        });
    }
    
    function removeItem(productId) {
        if(confirm('Are you sure you want to remove this item from your cart?')) {
            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification(data.message, 'success');
                    updateCartDisplay(data);
                    // Remove item from DOM
                    const itemElement = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    if(itemElement) {
                        itemElement.remove();
                    }
                    // Reload if cart is empty
                    if(data.cart_count === 0) {
                        location.reload();
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error removing item', 'error');
            });
        }
    }
    
    function clearCart() {
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                updateCartDisplay(data);
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error clearing cart', 'error');
        });
    }
    
    function moveToWishlist(productId) {
        fetch('wishlist_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                // Remove from cart after moving to wishlist
                removeItem(productId);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error moving to wishlist', 'error');
        });
    }
    
    function updateCartDisplay(data) {
        // Update cart count in header
        updateCartCount(data.cart_count);
        
        // Reload page to update totals and selection state
        location.reload();
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Add show class for animation
        setTimeout(() => notification.classList.add('show'), 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }
});
</script>

<style>
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
    margin-top: 30px;
}

.cart-items {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.select-all {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-container {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-container input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 3px;
    position: relative;
    transition: all 0.3s;
}

.checkbox-container input:checked + .checkmark {
    background: #007bff;
    border-color: #007bff;
}

.checkbox-container input:checked + .checkmark:after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.btn-small {
    padding: 8px 15px;
    font-size: 14px;
}

.items-list {
    padding: 0;
}

.cart-item {
    display: grid;
    grid-template-columns: auto 80px 1fr auto auto;
    gap: 15px;
    padding: 20px;
    border-bottom: 1px solid #f5f5f5;
    align-items: start;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-select {
    display: flex;
    align-items: center;
}

.item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-details h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
}

.item-attribute {
    margin: 2px 0;
    font-size: 14px;
    color: #666;
}

.item-price-mobile {
    display: none;
}

.stock-info {
    margin: 8px 0;
}

.in-stock {
    color: #28a745;
    font-size: 14px;
}

.out-of-stock {
    color: #dc3545;
    font-size: 14px;
}

.item-actions {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.btn-remove, .btn-wishlist {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
}

.btn-remove:hover {
    color: #dc3545;
}

.btn-wishlist:hover {
    color: #007bff;
}

.item-quantity {
    display: flex;
    align-items: center;
}

.quantity-selector {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.quantity-btn {
    background: #f8f9fa;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
}

.quantity-input {
    width: 50px;
    border: none;
    text-align: center;
    font-size: 14px;
    padding: 8px 0;
}

.item-price {
    text-align: right;
}

.item-price .current-price {
    display: block;
    font-weight: 600;
    font-size: 16px;
}

.item-price .old-price {
    display: block;
    font-size: 14px;
    color: #999;
    text-decoration: line-through;
}

.item-total {
    font-weight: bold;
    font-size: 16px;
    margin-top: 5px;
    color: #333;
}

.cart-summary {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.summary-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.summary-card h3 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.summary-details {
    margin-bottom: 25px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 5px 0;
}

.summary-row.total {
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 18px;
    font-weight: bold;
}

.checkout-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-checkout {
    width: 100%;
}

.security-features {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 14px;
    color: #28a745;
}

.security-item i {
    width: 16px;
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart-icon {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-cart h2 {
    margin-bottom: 10px;
    color: #333;
}

.empty-cart p {
    color: #666;
    margin-bottom: 30px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    z-index: 10000;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    transform: translateX(400px);
    transition: transform 0.3s ease;
    max-width: 400px;
}

.notification.show {
    transform: translateX(0);
}

.notification.success {
    background: #28a745;
}

.notification.error {
    background: #dc3545;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: auto 1fr;
        grid-template-areas: 
            "select image"
            "details details"
            "quantity price"
            "actions actions";
        gap: 10px;
    }
    
    .item-select { grid-area: select; }
    .item-image { grid-area: image; }
    .item-details { grid-area: details; }
    .item-quantity { grid-area: quantity; }
    .item-price { 
        grid-area: price; 
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .item-actions { grid-area: actions; }
    
    .item-price-mobile {
        display: block;
        margin: 5px 0;
    }
    
    .item-price .current-price,
    .item-price .old-price {
        display: none;
    }
    
    .cart-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

<?php include 'footer.php'; ?>