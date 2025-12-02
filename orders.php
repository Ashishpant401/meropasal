
<?php include 'header.php'; 
if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = getUserId();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total orders count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Get user orders with pagination
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$orders_stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$orders_stmt->bindValue(2, $limit, PDO::PARAM_INT);
$orders_stmt->bindValue(3, $offset, PDO::PARAM_INT);
$orders_stmt->execute();
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="orders-page">
    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>View and manage your orders</p>
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
                    <a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> My Orders</a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="addresses.php"><i class="fas fa-address-book"></i> Addresses</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>
            
            <main class="dashboard-main">
                <?php if(empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No Orders Yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-container">
                        <div class="orders-header">
                            <h2>Order History (<?php echo $total_orders; ?> orders)</h2>
                            <div class="filters">
                                <select id="status-filter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="orders-list">
                            <?php foreach($orders as $order): 
                                // Get order items count
                                $items_stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                                $items_stmt->execute([$order['id']]);
                                $items_count = $items_stmt->fetchColumn();
                            ?>
                            <div class="order-card" data-status="<?php echo $order['status']; ?>">
                                <div class="order-header">
                                    <div class="order-info">
                                        <div class="order-meta">
                                            <span class="order-number">Order #<?php echo $order['order_number']; ?></span>
                                            <span class="order-date">Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        <div class="order-stats">
                                            <span class="items-count"><?php echo $items_count; ?> item(s)</span>
                                            <span class="order-total">Total: Rs <?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="order-status">
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-items-preview">
                                    <?php 
                                    // Get first 2 order items for preview
                                    $preview_stmt = $pdo->prepare("
                                        SELECT oi.*, p.name, p.image 
                                        FROM order_items oi 
                                        JOIN products p ON oi.product_id = p.id 
                                        WHERE oi.order_id = ? 
                                        LIMIT 2
                                    ");
                                    $preview_stmt->execute([$order['id']]);
                                    $preview_items = $preview_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach($preview_items as $item):
                                    ?>
                                    <div class="preview-item">
                                        <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                        <span class="item-name"><?php echo $item['name']; ?></span>
                                        <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if($items_count > 2): ?>
                                        <div class="more-items">+<?php echo $items_count - 2; ?> more</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    
                                    <?php if($order['status'] == 'pending'): ?>
                                        <button class="btn btn-danger cancel-order" data-order-id="<?php echo $order['id']; ?>">
                                            <i class="fas fa-times"></i> Cancel Order
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if($order['status'] == 'delivered'): ?>
                                        <button class="btn btn-primary reorder-btn" data-order-id="<?php echo $order['id']; ?>">
                                            <i class="fas fa-redo"></i> Reorder
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if($order['status'] == 'cancelled'): ?>
                                        <button class="btn btn-danger delete-order" data-order-id="<?php echo $order['id']; ?>">
                                            <i class="fas fa-trash"></i> Delete Order
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if($page > 1): ?>
                                <a href="orders.php?page=<?php echo $page - 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="orders.php?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                                <a href="orders.php?page=<?php echo $page + 1; ?>" class="page-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</section>

<style>
.orders-page {
    padding: 40px 0;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0 0 10px 0;
    color: #333;
}

.page-header p {
    color: #666;
    margin: 0;
}

.dashboard-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
}

.dashboard-sidebar {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
}

.user-profile {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.profile-image {
    font-size: 60px;
    color: #007bff;
    margin-bottom: 15px;
}

.user-profile h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.user-profile p {
    color: #666;
    margin: 0;
}

.dashboard-nav {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.dashboard-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s;
}

.dashboard-nav a:hover {
    background: #f8f9fa;
    color: #007bff;
}

.dashboard-nav a.active {
    background: #007bff;
    color: white;
}

.dashboard-nav a i {
    width: 20px;
    text-align: center;
}

.dashboard-main {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    margin-bottom: 30px;
}

.orders-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.orders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.orders-header h2 {
    margin: 0;
    color: #333;
}

.filters select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: white;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 25px;
    transition: all 0.3s;
}

.order-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.order-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.order-number {
    font-weight: bold;
    font-size: 16px;
    color: #333;
}

.order-date {
    color: #666;
    font-size: 14px;
}

.order-stats {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.items-count, .order-total {
    font-size: 14px;
    color: #666;
}

.order-total {
    font-weight: bold;
    color: #333;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce7ff;
    color: #004085;
}

.status-shipped {
    background: #d1ecf1;
    color: #0c5460;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.order-items-preview {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f5f5f5;
}

.preview-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    flex: 0 1 auto;
}

.preview-item img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

.item-name {
    font-size: 14px;
    font-weight: 500;
}

.item-quantity {
    font-size: 12px;
    color: #666;
}

.more-items {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 15px;
    background: #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    color: #666;
}

.order-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.page-link {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
}

.page-link:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.page-link.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-danger {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
}

.btn-outline {
    background: transparent;
    border: 1px solid #007bff;
    color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status filter functionality
    const statusFilter = document.getElementById('status-filter');
    const orderCards = document.querySelectorAll('.order-card');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            
            orderCards.forEach(card => {
                if (selectedStatus === '' || card.dataset.status === selectedStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Cancel order functionality
    document.querySelectorAll('.cancel-order').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            if (confirm('Are you sure you want to cancel this order?')) {
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
    
    // Delete order functionality
    document.querySelectorAll('.delete-order').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            if (confirm('Are you sure you want to delete this cancelled order? This action cannot be undone.')) {
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                this.disabled = true;
                
                fetch('order_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Remove the order card from the DOM
                        const orderCard = this.closest('.order-card');
                        orderCard.style.opacity = '0';
                        orderCard.style.transform = 'translateX(-100px)';
                        
                        setTimeout(() => {
                            orderCard.remove();
                            // If no orders left, reload the page
                            if (document.querySelectorAll('.order-card').length === 0) {
                                window.location.reload();
                            }
                        }, 500);
                    } else {
                        showNotification(data.message, 'error');
                        // Restore button state
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting order', 'error');
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
                    // Redirect to cart if requested
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
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