<?php include 'header.php'; 
if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = getUserId();

if(!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Get order details
$order_stmt = $pdo->prepare("
    SELECT o.*, CONCAT(o.first_name, ' ', o.last_name) as customer_name 
    FROM orders o 
    WHERE o.id = ? AND o.user_id = ?
");
$order_stmt->execute([$order_id, $user_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header("Location: orders.php");
    exit;
}

// Get order items with size and color from order_items table
$items_stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image, p.description
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal from items
$subtotal = 0;
foreach($order_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 10.00;
$tax = $subtotal * 0.1;

// Get user info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="order-detail-page">
    <div class="container">
        <div class="page-header">
            <div class="header-content">
                <h1>Order Details</h1>
                <div class="order-meta">
                    <span class="order-number">Order #<?php echo $order['order_number']; ?></span>
                    <span class="order-date">Placed on <?php echo date('F d, Y g:i A', strtotime($order['created_at'])); ?></span>
                    <span class="order-total">Total: Rs <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <a href="orders.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Invoice
                </button>
            </div>
        </div>
        
        <div class="dashboard-layout">
            <aside class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?php echo $user['username']; ?></h3>
                    <p><?php echo $user['email']; ?></p>
                </div>
                
                <nav class="dashboard-nav">
                    <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="addresses.php"><i class="fas fa-address-book"></i> Addresses</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>
            
            <main class="dashboard-main">
                <div class="order-detail-container">
                    <!-- Order Status Timeline -->
                    <div class="order-status-timeline">
                        <h3>Order Status</h3>
                        <div class="timeline">
                            <div class="timeline-step <?php echo $order['status'] == 'pending' ? 'active' : ''; ?>">
                                <div class="step-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="step-label">Pending</span>
                            </div>
                            <div class="timeline-step <?php echo $order['status'] == 'processing' ? 'active' : ''; ?>">
                                <div class="step-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <span class="step-label">Processing</span>
                            </div>
                            <div class="timeline-step <?php echo $order['status'] == 'shipped' ? 'active' : ''; ?>">
                                <div class="step-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <span class="step-label">Shipped</span>
                            </div>
                            <div class="timeline-step <?php echo $order['status'] == 'delivered' ? 'active' : ''; ?>">
                                <div class="step-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span class="step-label">Delivered</span>
                            </div>
                        </div>
                        <div class="current-status">
                            <strong>Current Status:</strong>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="order-items-section">
                        <h3>Order Items (<?php echo count($order_items); ?>)</h3>
                        <div class="order-items-list">
                            <?php foreach($order_items as $item): 
                                $item_subtotal = $item['price'] * $item['quantity'];
                            ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo $item['name']; ?></h4>
                                    <p class="item-description"><?php echo $item['description']; ?></p>
                                    
                                    <!-- Product Attributes Display -->
                                    <div class="item-attributes">
                                        <?php if(!empty($item['size'])): ?>
                                            <div class="attribute-item">
                                                <strong>Size:</strong>
                                                <span class="attribute-value size-value"><?php echo $item['size']; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($item['color'])): ?>
                                            <div class="attribute-item">
                                                <strong>Color:</strong>
                                                <span class="attribute-value color-swatch" style="background-color: <?php echo strtolower($item['color']); ?>"></span>
                                                <span class="color-name"><?php echo $item['color']; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="attribute-item">
                                            <strong>Quantity:</strong>
                                            <span class="attribute-value quantity-badge"><?php echo $item['quantity']; ?></span>
                                        </div>
                                        
                                        <div class="attribute-item">
                                            <strong>Unit Price:</strong>
                                            <span class="attribute-value price">Rs <?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-price">
                                    <span class="subtotal">Rs <?php echo number_format($item_subtotal, 2); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Order Summary and Shipping Info -->
                    <div class="order-info-grid">
                        <div class="shipping-info">
                            <h3>Shipping Information</h3>
                            <div class="info-card">
                                <div class="info-row">
                                    <strong>Customer:</strong>
                                    <span><?php echo $order['customer_name']; ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Email:</strong>
                                    <span><?php echo $order['email']; ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Phone:</strong>
                                    <span><?php echo $order['phone']; ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Shipping Address:</strong>
                                    <span><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="payment-info">
                            <h3>Payment Information</h3>
                            <div class="info-card">
                                <div class="info-row">
                                    <strong>Payment Method:</strong>
                                    <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Order Notes:</strong>
                                    <span><?php echo $order['order_notes'] ? nl2br(htmlspecialchars($order['order_notes'])) : 'No notes provided'; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-summary">
                            <h3>Order Summary</h3>
                            <div class="summary-card">
                                <div class="summary-row">
                                    <span>Subtotal (<?php echo count($order_items); ?> items):</span>
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
                                    <strong>Rs <?php echo number_format($order['total_amount'], 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Actions -->
                    <div class="order-actions">
                        <?php if($order['status'] == 'pending'): ?>
                            <button class="btn btn-danger cancel-order" data-order-id="<?php echo $order['id']; ?>">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        <?php endif; ?>
                        
                        <?php if($order['status'] == 'delivered'): ?>
                            <button class="btn btn-primary reorder-btn" data-order-id="<?php echo $order['id']; ?>">
                                <i class="fas fa-redo"></i> Reorder All Items
                            </button>
                        <?php endif; ?>
                        
                        <a href="contact.php" class="btn btn-outline">
                            <i class="fas fa-headset"></i> Contact Support
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<style>
.order-detail-page {
    padding: 40px 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.header-content h1 {
    margin: 0 0 10px 0;
    color: #333;
}

.order-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.order-number {
    font-weight: bold;
    color: #333;
}

.order-date, .order-total {
    color: #666;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.order-detail-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.order-status-timeline {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-status-timeline h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.timeline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.timeline-step.active .step-icon {
    background: #007bff;
    color: white;
}

.step-label {
    font-size: 14px;
    font-weight: 500;
    color: #666;
}

.timeline-step.active .step-label {
    color: #007bff;
    font-weight: bold;
}

.current-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce7ff; color: #004085; }
.status-shipped { background: #d1ecf1; color: #0c5460; }
.status-delivered { background: #d4edda; color: #155724; }

.order-items-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-items-section h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.order-items-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 20px;
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    align-items: start;
}

.item-image img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.item-details h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #333;
}

.item-description {
    color: #666;
    font-size: 14px;
    margin: 0 0 15px 0;
    line-height: 1.4;
}

/* Item Attributes Styles */
.item-attributes {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.attribute-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.attribute-item strong {
    color: #333;
    min-width: 70px;
}

.attribute-value {
    color: #666;
}

.size-value {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    font-weight: 500;
}

.quantity-badge {
    background: #007bff;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 12px;
    min-width: 30px;
    text-align: center;
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #ddd;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.color-name {
    color: #666;
    font-size: 13px;
    font-weight: 500;
}

.item-price {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.subtotal {
    font-weight: bold;
    color: #333;
    font-size: 16px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.shipping-info, .payment-info, .order-summary {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.shipping-info h3, .payment-info h3, .order-summary h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.info-card, .summary-card {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
}

.info-row strong {
    color: #333;
    min-width: 120px;
}

.info-row span {
    color: #666;
    text-align: right;
    flex: 1;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.summary-row.total {
    border-top: 2px solid #e9ecef;
    padding-top: 15px;
    margin-top: 10px;
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.order-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    padding: 25px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Print Styles */
@media print {
    .dashboard-sidebar,
    .header-actions,
    .order-actions {
        display: none !important;
    }
    
    .dashboard-layout {
        grid-template-columns: 1fr !important;
    }
    
    .order-detail-page {
        padding: 0 !important;
    }
    
    .order-detail-container > * {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .item-price {
        grid-column: 1 / -1;
        text-align: left;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e9ecef;
        flex-direction: row;
        justify-content: space-between;
    }
    
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .attribute-item {
        flex-wrap: wrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cancel order functionality
    document.querySelectorAll('.cancel-order').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
                this.disabled = true;
                
                fetch('order_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=cancel&order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Reload page after 2 seconds to show updated status
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showNotification(data.message, 'error');
                        // Restore button state
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error cancelling order', 'error');
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        });
    });
    
    // Reorder functionality
    document.querySelectorAll('.reorder-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            if (confirm('Add all items from this order to your cart?')) {
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                this.disabled = true;
                
                fetch('order_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reorder&order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Redirect to cart after 2 seconds
                        setTimeout(() => {
                            window.location.href = 'cart.php';
                        }, 2000);
                    } else {
                        showNotification(data.message, 'error');
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error processing reorder', 'error');
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        });
    });
    
    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        
        if (type === 'success') {
            notification.style.background = '#28a745';
        } else {
            notification.style.background = '#dc3545';
        }
        
        // Add to page
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
});
</script>

<?php include 'footer.php'; ?>