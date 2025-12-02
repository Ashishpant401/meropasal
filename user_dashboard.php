<?php include 'header.php'; 
if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = getUserId();

// Get user orders
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="dashboard">
    <div class="container">
        <h1>Welcome, <?php echo $user['first_name'] . ' ' . $user['last_name']; ?>!</h1>
        
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
                    <a href="user_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="addresses.php"><i class="fas fa-address-book"></i> Addresses</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>
            
            <main class="dashboard-main">
                <div class="dashboard-cards">
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Orders</h3>
                            <span class="count"><?php echo count($orders); ?></span>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="card-info">
                            <h3>Wishlist</h3>
                            <span class="count">
                                <?php 
                                $wishlist_count = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                                $wishlist_count->execute([$user_id]);
                                echo $wishlist_count->fetchColumn();
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="card-info">
                            <h3>Addresses</h3>
                            <span class="count">1</span>
                        </div>
                    </div>
                </div>
                
                <div class="recent-orders">
                    <h2>Recent Orders</h2>
                    <?php if(empty($orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No orders yet</p>
                            <a href="products.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h4>Order #<?php echo $order['id']; ?></h4>
                                        <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-status">
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="order-footer">
                                    <span class="order-total">Rs <?php echo $order['total_amount']; ?></span>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">View Details</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>