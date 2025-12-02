<?php 
include 'header.php'; 

if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = getUserId();

// Get order details
$stmt = $pdo->prepare("SELECT o.*, COUNT(oi.id) as item_count 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      WHERE o.id = ? AND o.user_id = ? 
                      GROUP BY o.id");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header("Location: user_dashboard.php");
    exit;
}

// Get order items with product details, sizes, and colors
$items_stmt = $pdo->prepare("SELECT oi.*, p.name, p.image, p.price as unit_price, 
                            (oi.price * oi.quantity) as total_price,
                            b.name as brand_name
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           LEFT JOIN brands b ON p.brand_id = b.id 
                           WHERE oi.order_id = ? 
                           ORDER BY oi.id");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate order totals
$subtotal = 0;
foreach($order_items as $item) {
    $subtotal += $item['total_price'];
}
$tax = $subtotal * 0.1; // 10% tax
$shipping = 10.00; // Fixed shipping
$total = $subtotal + $tax + $shipping;
?>

<section class="order-confirmation">
    <div class="container">
        <div class="confirmation-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Confirmed!</h1>
            <p class="success-message">Thank you for your purchase. Your order has been confirmed and will be shipped soon.</p>
        </div>

        <div class="confirmation-layout">
            <div class="order-summary">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    <div class="order-items">
                        <?php foreach($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="item-details">
                                <h4 class="item-name"><?php echo $item['name']; ?></h4>
                                <?php if($item['brand_name']): ?>
                                    <p class="item-brand">Brand: <?php echo $item['brand_name']; ?></p>
                                <?php endif; ?>
                                
                                <!-- Display Size and Color -->
                                <div class="item-attributes">
                                    <?php if($item['size']): ?>
                                        <div class="attribute">
                                            <span class="attribute-label">Size:</span>
                                            <span class="attribute-value"><?php echo $item['size']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($item['color']): ?>
                                        <div class="attribute">
                                            <span class="attribute-label">Color:</span>
                                            <span class="attribute-value color-display">
                                                <span class="color-swatch" style="background-color: <?php echo strtolower($item['color']); ?>"></span>
                                                <?php echo $item['color']; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-quantity">
                                    <span class="quantity-label">Quantity:</span>
                                    <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="item-price">
                                <span class="unit-price">Rs <?php echo number_format($item['price'], 2); ?> each</span>
                                <span class="total-price">Rs <?php echo number_format($item['total_price'], 2); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal (<?php echo count($order_items); ?> items):</span>
                            <span>Rs <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Shipping:</span>
                            <span>Rs <?php echo number_format($shipping, 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax:</span>
                            <span>Rs <?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <strong>Total:</strong>
                            <strong>Rs <?php echo number_format($total, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-details">
                <div class="details-card">
                    <h3>Order Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Order Number:</span>
                            <strong class="detail-value">#<?php echo $order['order_number']; ?></strong>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Order Date:</span>
                            <span class="detail-value"><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Amount:</span>
                            <strong class="detail-value">Rs <?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value payment-method">
                                <i class="fas fa-<?php echo getPaymentIcon($order['payment_method']); ?>"></i>
                                <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Order Status:</span>
                            <span class="detail-value status-badge status-<?php echo $order['status']; ?>">
                                <i class="fas fa-<?php echo getStatusIcon($order['status']); ?>"></i>
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="details-card">
                    <h3>Shipping Information</h3>
                    <div class="shipping-info">
                        <div class="address-details">
                            <p><strong><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></strong></p>
                            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo $order['phone']; ?></p>
                            <p><i class="fas fa-envelope"></i> <?php echo $order['email']; ?></p>
                        </div>
                    </div>
                </div>

                <?php if(!empty($order['order_notes'])): ?>
                <div class="details-card">
                    <h3>Order Notes</h3>
                    <div class="order-notes">
                        <p><?php echo nl2br(htmlspecialchars($order['order_notes'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="confirmation-actions">
            <div class="action-buttons">
                <a href="user_dashboard.php?tab=orders" class="btn btn-outline">
                    <i class="fas fa-list"></i> View All Orders
                </a>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>

        <div class="next-steps">
            <h3>What's Next?</h3>
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="step-content">
                        <h4>Order Confirmation</h4>
                        <p>You'll receive an email confirmation with your order details.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="step-content">
                        <h4>Shipping</h4>
                        <p>Your order will be processed and shipped within 1-2 business days.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="step-content">
                        <h4>Delivery</h4>
                        <p>Expected delivery in 3-5 business days after shipping.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="support-info">
            <div class="support-card">
                <i class="fas fa-headset"></i>
                <div class="support-content">
                    <h4>Need Help?</h4>
                    <p>Our customer support team is here to help with any questions about your order.</p>
                    <a href="contact.php" class="support-link">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
// Helper functions for icons
function getPaymentIcon($method) {
    $icons = [
        'credit_card' => 'credit-card',
        'paypal' => 'paypal',
        'cash_on_delivery' => 'money-bill-wave',
        'bank_transfer' => 'university'
    ];
    return $icons[$method] ?? 'credit-card';
}

function getStatusIcon($status) {
    $icons = [
        'pending' => 'clock',
        'processing' => 'cog',
        'shipped' => 'shipping-fast',
        'delivered' => 'check-circle',
        'cancelled' => 'times-circle'
    ];
    return $icons[$status] ?? 'clock';
}
?>

<style>
.order-confirmation {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 40px;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.success-icon {
    font-size: 80px;
    color: #28a745;
    margin-bottom: 20px;
}

.success-icon i {
    animation: bounceIn 1s ease;
}

.confirmation-header h1 {
    font-size: 36px;
    margin-bottom: 15px;
    color: #333;
}

.success-message {
    font-size: 18px;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.confirmation-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
    margin-bottom: 40px;
}

.summary-card, .details-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.summary-card h3, .details-card h3 {
    margin-top: 0;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
    color: #333;
}

.order-items {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-bottom: 1px solid #f8f9fa;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    flex-shrink: 0;
}

.item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.item-details {
    flex: 1;
}

.item-name {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.item-brand {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
}

.item-attributes {
    display: flex;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.attribute {
    display: flex;
    align-items: center;
    gap: 5px;
}

.attribute-label {
    font-size: 13px;
    color: #666;
    font-weight: 500;
}

.attribute-value {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.color-display {
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-swatch {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    display: inline-block;
}

.item-quantity {
    margin-top: 5px;
}

.quantity-label {
    font-size: 13px;
    color: #666;
}

.quantity-value {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.item-price {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.unit-price {
    font-size: 13px;
    color: #666;
}

.total-price {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.order-totals {
    border-top: 2px solid #e9ecef;
    padding-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 5px 0;
    color: #666;
}

.total-row.grand-total {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.detail-grid {
    display: grid;
    gap: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    font-weight: 600;
    color: #333;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce7ff; color: #004085; }
.status-shipped { background: #d1ecf1; color: #0c5460; }
.status-delivered { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.shipping-info {
    line-height: 1.6;
}

.address-details p {
    margin: 0 0 8px 0;
}

.address-details i {
    width: 16px;
    color: #666;
}

.order-notes {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.confirmation-actions {
    text-align: center;
    margin: 40px 0;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.next-steps {
    background: white;
    padding: 40px;
    border-radius: 15px;
    margin: 40px 0;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.next-steps h3 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.step-item {
    text-align: center;
    padding: 20px;
}

.step-icon {
    width: 60px;
    height: 60px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 24px;
}

.step-content h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.step-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.support-info {
    text-align: center;
}

.support-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    gap: 20px;
    text-align: left;
}

.support-card i {
    font-size: 40px;
    color: #007bff;
}

.support-content h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.support-content p {
    margin: 0 0 15px 0;
    color: #666;
}

.support-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.support-link:hover {
    text-decoration: underline;
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); opacity: 1; }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

@media (max-width: 768px) {
    .confirmation-layout {
        grid-template-columns: 1fr;
    }
    
    .confirmation-header {
        padding: 30px 20px;
    }
    
    .confirmation-header h1 {
        font-size: 28px;
    }
    
    .success-icon {
        font-size: 60px;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
    
    .item-price {
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .support-card {
        flex-direction: column;
        text-align: center;
    }
    
    .steps-grid {
        grid-template-columns: 1fr;
    }
}

@media print {
    .confirmation-actions,
    .next-steps,
    .support-info {
        display: none;
    }
    
    .order-confirmation {
        background: white;
        padding: 20px 0;
    }
    
    .summary-card, .details-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<?php include 'footer.php'; ?>