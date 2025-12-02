<?php
include 'header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$selected_items = [];
$direct_purchase = false;

// Check for direct product purchase (Buy Now)
if(isset($_GET['direct_product']) && isset($_GET['direct_checkout'])) {
    $product_id = intval($_GET['direct_product']);
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    $size = isset($_GET['size']) ? $_GET['size'] : '';
    $color = isset($_GET['color']) ? $_GET['color'] : '';
    
    // Verify product exists and is available
    $stmt = $pdo->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                          FROM products p 
                          JOIN brands b ON p.brand_id = b.id 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.deleted = 0 AND p.stock > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($product) {
        // Check if product has attributes and validate them
        if($size || $color) {
            $attr_stmt = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id = ? AND size = ? AND color = ?");
            $attr_stmt->execute([$product_id, $size, $color]);
            $attribute = $attr_stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$attribute) {
                $_SESSION['error'] = "Selected product variation is not available";
                header("Location: product_detail.php?id=" . $product_id);
                exit;
            }
        }
        
        // Create a virtual cart item for direct purchase
        $cart_items[] = [
            'id' => 'direct_' . $product_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price'],
            'name' => $product['name'],
            'image' => $product['image'],
            'stock' => $product['stock'],
            'size' => $size,
            'color' => $color
        ];
        
        $direct_purchase = true;
    } else {
        $_SESSION['error'] = "Product not available for purchase";
        header("Location: products.php");
        exit;
    }
} 
// Handle regular cart checkout
else {
    // Get selected cart items or handle buy now
    if(isset($_GET['buy_now'])) {
        $buy_now_item = intval($_GET['buy_now']);
        
        // Verify the item belongs to the user and is selected
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ? AND selected = 1");
        $stmt->execute([$buy_now_item, $user_id]);
        $valid_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($valid_item) {
            $selected_items = [$buy_now_item];
        } else {
            $_SESSION['error'] = "Invalid product for checkout";
            header("Location: cart.php");
            exit;
        }
    } 
    // Check if selected_items is coming from POST (form submission)
    elseif(isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
        if(is_array($_POST['selected_items'])) {
            $selected_items = $_POST['selected_items'];
        } else {
            // If it's a JSON string, decode it
            $decoded = json_decode($_POST['selected_items'], true);
            if(json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $selected_items = $decoded;
            }
        }
    }

    // If no specific items selected and not buy now, get all selected cart items
    if(empty($selected_items) && !isset($_GET['buy_now'])) {
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND selected = 1");
        $stmt->execute([$user_id]);
        $selected_items = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Build the SQL query
    $cart_where = "c.user_id = ? AND p.deleted = 0";
    $cart_params = [$user_id];

    if(!empty($selected_items)) {
        $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
        $cart_where .= " AND c.id IN ($placeholders)";
        $cart_params = array_merge($cart_params, $selected_items);
    }

    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock, c.size, c.color 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE $cart_where");
    $stmt->execute($cart_params);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if(empty($cart_items)) {
    $_SESSION['error'] = "No items selected for checkout";
    header("Location: cart.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 10.00;
$tax = $subtotal * 0.1;
$total = $subtotal + $shipping + $tax;

// Get user info for pre-filling form
$user_stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Handle checkout submission
if(isset($_POST['place_order'])) {
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'shipping_address', 'payment_method'];
    $missing_fields = [];
    
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if(!empty($missing_fields)) {
        $_SESSION['error'] = "Please fill in all required fields: " . implode(', ', $missing_fields);
        header("Location: checkout.php");
        exit;
    }
    
    // Validate stock
    $out_of_stock = [];
    foreach($cart_items as $item) {
        if($item['stock'] < $item['quantity']) {
            $out_of_stock[] = $item['name'];
        }
    }
    
    if(!empty($out_of_stock)) {
        $_SESSION['error'] = "Some items are out of stock: " . implode(', ', $out_of_stock);
        header("Location: checkout.php");
        exit;
    }
    
    // Generate unique order number
    $order_number = 'ORD' . date('YmdHis') . mt_rand(100, 999);
    
    // Create order
    $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method, first_name, last_name, email, phone, order_notes) 
                                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $order_stmt->execute([
            $user_id,
            $order_number,
            $total,
            $_POST['shipping_address'],
            $_POST['payment_method'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['order_notes'] ?? ''
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $order_item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size, color) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach($cart_items as $item) {
            $order_item_stmt->execute([
                $order_id, 
                $item['product_id'],
                $item['quantity'], 
                $item['price'],
                $item['size'],
                $item['color']
            ]);
            
            // Update product stock
            $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update_stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Only clear cart items if this is NOT a direct purchase
        if(!$direct_purchase) {
            // Clear the purchased cart items
            if(!empty($selected_items)) {
                $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
                $delete_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND id IN ($placeholders)");
                $delete_params = array_merge([$user_id], $selected_items);
                $delete_stmt->execute($delete_params);
            } else {
                // If no specific items selected, clear all selected items
                $delete_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND selected = 1");
                $delete_stmt->execute([$user_id]);
            }
        }
        
        $_SESSION['success'] = "Order placed successfully! Order Number: " . $order_number;
        header("Location: order_confirmation.php?id=" . $order_id);
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error placing order: " . $e->getMessage();
        header("Location: checkout.php");
        exit;
    }
}
?>

<section class="checkout-page">
    <div class="container">
        <h1>Checkout</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="checkout-layout">
            <div class="checkout-form">
                <form method="POST" id="checkout-form">
                    <!-- Store purchase type -->
                    <input type="hidden" name="direct_purchase" value="<?php echo $direct_purchase ? '1' : '0'; ?>">
                    
                    <!-- Store selected items as hidden inputs for cart purchases -->
                    <?php if(!$direct_purchase && !empty($selected_items)): ?>
                        <?php foreach($selected_items as $item_id): ?>
                            <input type="hidden" name="selected_items[]" value="<?php echo $item_id; ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Store direct product info for direct purchases -->
                    <?php if($direct_purchase && !empty($cart_items)): ?>
                        <?php $item = $cart_items[0]; ?>
                        <input type="hidden" name="direct_product_id" value="<?php echo $item['product_id']; ?>">
                        <input type="hidden" name="direct_quantity" value="<?php echo $item['quantity']; ?>">
                        <input type="hidden" name="direct_size" value="<?php echo $item['size']; ?>">
                        <input type="hidden" name="direct_color" value="<?php echo $item['color']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" required value="<?php echo htmlspecialchars($user_info['first_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" required value="<?php echo htmlspecialchars($user_info['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="tel" name="phone" required value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Shipping Information</h3>
                        <div class="form-group">
                            <label>Shipping Address *</label>
                            <textarea name="shipping_address" required placeholder="Enter your complete shipping address "><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Order Notes (Optional)</label>
                            <textarea name="order_notes" placeholder="Any special instructions for your order..."><?php echo htmlspecialchars($_POST['order_notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Payment Method</h3>
                        <div class="payment-options">
                            <!-- <label class="payment-option">
                                <input type="radio" name="payment_method" value="credit_card" required <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'credit_card') ? 'checked' : ''; ?>>
                                <span>Credit Card</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="paypal" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'paypal') ? 'checked' : ''; ?>>
                                <span>PayPal</span>
                            </label> -->
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash_on_delivery') ? 'checked' : ''; ?>>
                                <span>Cash on Delivery</span>
                            </label>
                            <!-- <label class="payment-option">
                                <input type="radio" name="payment_method" value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_transfer') ? 'checked' : ''; ?>>
                                <span>Bank Transfer</span>
                            </label> -->
                        </div>
                    </div>
                    
                    <button type="submit" name="place_order" class="btn btn-primary btn-large">
                        <i class="fas fa-lock"></i> Place Order - Rs <?php echo number_format($total, 2); ?>
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="order-items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="order-item">
                        <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                        <div class="item-details">
                            <h4><?php echo $item['name']; ?></h4>
                            <?php if($item['size']): ?><p>Size: <?php echo $item['size']; ?></p><?php endif; ?>
                            <?php if($item['color']): ?><p>Color: <?php echo $item['color']; ?></p><?php endif; ?>
                            <p>Qty: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="item-price">Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal (<?php echo count($cart_items); ?> items):</span>
                        <span>Rs <?php echo number_format($subtotal, 2); ?></span>
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
                
                <div class="security-features">
                    <div class="security-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure SSL Encryption</span>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-lock"></i>
                        <span>Safe & Secure Checkout</span>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-undo"></i>
                        <span>30-Day Return Policy</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
}

.form-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    color: #333;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-option:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.payment-option input:checked + span {
    color: #007bff;
    font-weight: bold;
}

.order-summary {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
    height: fit-content;
}

.order-summary h3 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.item-details p {
    margin: 2px 0;
    font-size: 12px;
    color: #666;
}

.item-price {
    font-weight: bold;
    color: #333;
}

.summary-totals {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 5px 0;
}

.summary-row.total {
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.security-features {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 14px;
    color: #28a745;
}

.security-item i {
    width: 20px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.btn-large {
    padding: 15px 30px;
    font-size: 18px;
    width: 100%;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let valid = true;
        
        requiredFields.forEach(field => {
            if(!field.value.trim()) {
                valid = false;
                field.style.borderColor = '#dc3545';
                
                // Add error message
                let errorMsg = field.parentNode.querySelector('.error-message');
                if(!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.style.fontSize = '14px';
                    errorMsg.style.marginTop = '5px';
                    field.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'This field is required';
            } else {
                field.style.borderColor = '#ddd';
                const errorMsg = field.parentNode.querySelector('.error-message');
                if(errorMsg) {
                    errorMsg.remove();
                }
            }
        });
        
        // Check if payment method is selected
        const paymentSelected = form.querySelector('input[name="payment_method"]:checked');
        if(!paymentSelected) {
            valid = false;
            alert('Please select a payment method.');
        }
        
        if(!valid) {
            e.preventDefault();
        }
    });
});
</script>

<?php include 'footer.php'; ?>