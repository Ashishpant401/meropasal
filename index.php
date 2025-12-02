<?php 
include 'header.php'; 

// Get featured products
$stmt = $pdo->query("SELECT p.*, b.name as brand_name FROM products p 
                   JOIN brands b ON p.brand_id = b.id 
                   WHERE p.deleted = 0 
                   ORDER BY p.created_at DESC LIMIT 8");
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome to MeroPasal</h1>
            <p>Discover the latest fashion trends for men, women, and kids</p>
            <a href="products.php" class="btn btn-primary">Shop Now</a>
        </div>
    </div>
</section>

<section class="featured-categories">
    <div class="container">
        <h2>Shop by Category</h2>
        <div class="categories-grid">
            <div class="category-card">
                <img src="images/men-category.jpg" alt="Men's Fashion">
                <div class="category-info">
                    <h3>Men</h3>
                    <a href="products.php?category=men" class="btn btn-outline">Shop Men</a>
                </div>
            </div>
            <div class="category-card">
                <img src="images/women-category.jpg" alt="Women's Fashion">
                <div class="category-info">
                    <h3>Women</h3>
                    <a href="products.php?category=women" class="btn btn-outline">Shop Women</a>
                </div>
            </div>
            <div class="category-card">
                <img src="images/kids-category.jpg" alt="Kids Fashion">
                <div class="category-info">
                    <h3>Kids</h3>
                    <a href="products.php?category=kids" class="btn btn-outline">Shop Kids</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php foreach($featured_products as $product): 
                // Check if product is in wishlist
                $in_wishlist = false;
                if(isset($_SESSION['user_id'])) {
                    $wish_stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
                    $wish_stmt->execute([$_SESSION['user_id'], $product['id']]);
                    $in_wishlist = $wish_stmt->fetchColumn() > 0;
                }
            ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-actions">
                        <button class="wishlist-btn <?php echo $in_wishlist ? 'active' : ''; ?>" 
                                data-product="<?php echo $product['id']; ?>"
                                <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="brand"><?php echo $product['brand_name']; ?></p>
                    <div class="price">
                        <?php if($product['old_price']): ?>
                            <span class="old-price">Rs <?php echo number_format($product['old_price']); ?></span>
                        <?php endif; ?>
                        <span class="current-price">Rs <?php echo number_format($product['price']); ?></span>
                    </div>
                    <div class="product-buttons">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">View Details</a>
                        <button class="btn btn-primary add-to-cart" 
                                data-product="<?php echo $product['id']; ?>"
                                <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                            <?php echo $product['stock'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<script>
// Add to Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product');
            
            // Check if user is logged in
            <?php if(!isset($_SESSION['user_id'])): ?>
                showNotification('Please login to add items to cart', 'error');
                return;
            <?php endif; ?>
            
            // Check if product is out of stock
            if(this.disabled) {
                showNotification('This product is out of stock', 'error');
                return;
            }
            
            addToCart(productId);
        });
    });
    
    // Wishlist buttons
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product');
            
            // Check if user is logged in
            <?php if(!isset($_SESSION['user_id'])): ?>
                showNotification('Please login to manage wishlist', 'error');
                return;
            <?php endif; ?>
            
            toggleWishlist(productId, this);
        });
    });
});

function addToCart(productId) {
    const button = document.querySelector(`.add-to-cart[data-product="${productId}"]`);
    
    // Show loading state
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    fetch('cart_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification(data.message, 'success');
            updateCartCount(data.cart_count);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function toggleWishlist(productId, button) {
    const icon = button.querySelector('i');
    
    // Show loading state
    button.disabled = true;
    
    fetch('wishlist_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            button.classList.toggle('active');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            
            // Update wishlist count
            updateWishlistCount(data.wishlist_count);
            
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating wishlist', 'error');
    })
    .finally(() => {
        button.disabled = false;
    });
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

function updateWishlistCount(count) {
    const wishlistCountElements = document.querySelectorAll('.wishlist-count');
    wishlistCountElements.forEach(element => {
        element.textContent = count;
    });
}

function showNotification(message, type) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
<style>
  
.hero {
    background-image: url('./images/hero-fashion.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: white;
    padding: 100px 0;
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center; /* Add this for horizontal centering */
}

.hero-content {
    text-align: center; /* Keep text centered within the content */
}

.hero-content h1 {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-content p {
    font-size: 1.3rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

</style>

<?php include 'footer.php'; ?>