<?php 
include 'header.php'; 

// Helper function to check if product is in wishlist
function isInWishlist($product_id) {
    if(!isset($_SESSION['user_id'])) return false;
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    return $stmt->fetchColumn() > 0;
}

if(!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                      FROM products p 
                      JOIN brands b ON p.brand_id = b.id 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ? AND p.deleted = 0");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    header("Location: products.php");
    exit;
}

// Get product attributes
$attr_stmt = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id = ?");
$attr_stmt->execute([$product_id]);
$attributes = $attr_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if product has attributes
$hasSize = false;
$hasColor = false;
$availableSizes = [];
$availableColors = [];

foreach($attributes as $attr) {
    if($attr['size']) {
        $hasSize = true;
        $availableSizes[] = $attr['size'];
    }
    if($attr['color']) {
        $hasColor = true;
        $availableColors[] = $attr['color'];
    }
}

$inWishlist = isInWishlist($product_id);
?>

<section class="product-detail">
    <div class="container">
        <!-- Quick Buy Now Button at Top -->
        <?php if($product['stock'] > 0 && !$hasSize && !$hasColor): ?>
        <div class="quick-buy-section">
            <a href="checkout.php?direct_product=<?php echo $product_id; ?>&quantity=1&direct_checkout=1" 
               class="btn btn-primary btn-large quick-buy-now-btn">
                <i class="fas fa-bolt"></i> Quick Buy Now
            </a>
            <p>Skip options and buy immediately</p>
        </div>
        <?php endif; ?>
        
        <div class="product-detail-layout">
            <div class="product-images">
                <div class="main-image">
                    <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" id="main-product-image">
                </div>
                
                <!-- Image Thumbnails -->
                <div class="image-thumbnails">
                    <div class="thumbnail active" data-image="images/products/<?php echo $product['image']; ?>">
                        <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                </div>
                
                <?php if($product['old_price'] && $product['old_price'] > $product['price']): ?>
                <div class="discount-badge">
                    Save Rs <?php echo number_format($product['old_price'] - $product['price'], 2); ?>
                </div>
                <?php endif; ?>
                
                <?php if($product['stock'] <= 0): ?>
                <div class="out-of-stock-badge">
                    Out of Stock
                </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <nav class="breadcrumb">
                    <a href="index.php">Home</a> &gt;
                    <a href="products.php">Products</a> &gt;
                    <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a> &gt;
                    <span><?php echo $product['name']; ?></span>
                </nav>
                
                <h1><?php echo $product['name']; ?></h1>
                
                <div class="product-meta">
                    <span class="brand"><strong>Brand:</strong> <?php echo $product['brand_name']; ?></span>
                    <span class="category"><strong>Category:</strong> <?php echo $product['category_name']; ?></span>
                    <span class="sku"><strong>SKU:</strong> PROD<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <div class="price-section">
                    <span class="current-price">Rs <?php echo number_format($product['price'], 2); ?></span>
                    <?php if($product['old_price'] && $product['old_price'] > $product['price']): ?>
                        <span class="old-price">Rs <?php echo number_format($product['old_price'], 2); ?></span>
                        <span class="discount-percentage">
                            (<?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>% off)
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <form class="product-form" id="product-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <?php if($hasSize): ?>
                    <div class="form-group">
                        <label>Size: <span class="required">*</span></label>
                        <div class="size-options">
                            <?php foreach(array_unique($availableSizes) as $size): ?>
                                <label class="size-option">
                                    <input type="radio" name="size" value="<?php echo $size; ?>" required 
                                           <?php echo $size === $availableSizes[0] ? 'checked' : ''; ?>>
                                    <span><?php echo $size; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="error-message size-error">
                            Please select a size
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($hasColor): ?>
                    <div class="form-group">
                        <label>Color: <span class="required">*</span></label>
                        <div class="color-options">
                            <?php foreach(array_unique($availableColors) as $color): ?>
                                <label class="color-option">
                                    <input type="radio" name="color" value="<?php echo $color; ?>" required
                                           <?php echo $color === $availableColors[0] ? 'checked' : ''; ?>>
                                    <span class="color-swatch" style="background-color: <?php echo strtolower($color); ?>" 
                                          title="<?php echo $color; ?>"></span>
                                    <span class="color-name"><?php echo $color; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="error-message color-error">
                            Please select a color
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Quantity:</label>
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" data-action="decrease">-</button>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                            <button type="button" class="quantity-btn" data-action="increase">+</button>
                        </div>
                    </div>
                    
                    <div class="stock-info">
                        <?php if($product['stock'] > 0): ?>
                            <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)</span>
                        <?php else: ?>
                            <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="action-buttons">
                        <?php if($product['stock'] > 0): ?>
                            <button type="button" class="btn btn-primary btn-large add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button type="button" class="btn btn-secondary btn-large buy-now-btn">
                                <i class="fas fa-bolt"></i> Buy Now
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-disabled btn-large" disabled>
                                <i class="fas fa-times-circle"></i> Out of Stock
                            </button>
                        <?php endif; ?>

                        <button type="button" 
                                class="btn btn-outline wishlist-btn <?php echo $inWishlist ? 'active' : ''; ?>" 
                                data-product="<?php echo $product['id']; ?>">
                            <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i> 
                            <span><?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
// Quantity selector
document.querySelectorAll('.quantity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.dataset.action;
        const input = this.parentElement.querySelector('.quantity-input');
        let value = parseInt(input.value);
        
        if(action === 'increase' && value < parseInt(input.max)) {
            input.value = value + 1;
        } else if(action === 'decrease' && value > parseInt(input.min)) {
            input.value = value - 1;
        }
    });
});

// Validation function for attributes
function validateAttributes() {
    let isValid = true;
    
    // Hide previous error messages
    document.querySelectorAll('.error-message').forEach(error => {
        error.style.display = 'none';
    });
    
    // Check size selection
    const sizeSelected = document.querySelector('input[name="size"]:checked');
    if (document.querySelector('input[name="size"]') && !sizeSelected) {
        document.querySelector('.size-error').style.display = 'block';
        isValid = false;
    }
    
    // Check color selection
    const colorSelected = document.querySelector('input[name="color"]:checked');
    if (document.querySelector('input[name="color"]') && !colorSelected) {
        document.querySelector('.color-error').style.display = 'block';
        isValid = false;
    }
    
    return isValid;
}

// Add to cart functionality - USES CART
document.querySelector('.add-to-cart-btn')?.addEventListener('click', function() {
    // Validate attributes first
    if (!validateAttributes()) {
        showNotification('Please select all required options', 'error');
        return;
    }
    
    const formData = new FormData(document.getElementById('product-form'));
    formData.append('action', 'add');
    
    // Show loading state
    const submitBtn = this;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    submitBtn.disabled = true;
    
    fetch('cart_action.php', {
        method: 'POST',
        body: formData
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
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// BUY NOW functionality - DIRECT TO CHECKOUT (NO CART INVOLVEMENT)
document.querySelector('.buy-now-btn')?.addEventListener('click', function() {
    // Validate attributes first
    if (!validateAttributes()) {
        showNotification('Please select all required options before buying', 'error');
        return;
    }
    
    // Get form data directly from form elements
    const form = document.getElementById('product-form');
    const productId = form.querySelector('input[name="product_id"]').value;
    const quantity = form.querySelector('input[name="quantity"]').value;
    
    // Get selected attributes
    let size = '';
    let color = '';
    
    const sizeSelected = form.querySelector('input[name="size"]:checked');
    if (sizeSelected) {
        size = sizeSelected.value;
    }
    
    const colorSelected = form.querySelector('input[name="color"]:checked');
    if (colorSelected) {
        color = colorSelected.value;
    }
    
    // Build checkout URL with direct product parameters
    let checkoutUrl = `checkout.php?direct_product=${productId}&quantity=${quantity}&direct_checkout=1`;
    
    if (size) {
        checkoutUrl += `&size=${encodeURIComponent(size)}`;
    }
    
    if (color) {
        checkoutUrl += `&color=${encodeURIComponent(color)}`;
    }
    
    // Check if user is logged in
    <?php if(!isset($_SESSION['user_id'])): ?>
        window.location.href = `login.php?redirect=${encodeURIComponent(checkoutUrl)}`;
        return;
    <?php endif; ?>
    
    // Show loading state briefly
    const buyNowBtn = this;
    const originalText = buyNowBtn.innerHTML;
    buyNowBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...';
    buyNowBtn.disabled = true;
    
    // REDIRECT DIRECTLY TO CHECKOUT - NO AJAX CALL TO CART_ACTION.PHP
    setTimeout(() => {
        window.location.href = checkoutUrl;
    }, 500);
});

// Quick Buy Now functionality (for products without attributes)
document.querySelector('.quick-buy-now-btn')?.addEventListener('click', function(e) {
    e.preventDefault();
    
    const href = this.getAttribute('href');
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        window.location.href = `login.php?redirect=${encodeURIComponent(href)}`;
        return;
    <?php endif; ?>
    
    // Show loading state
    const quickBuyBtn = this;
    const originalText = quickBuyBtn.innerHTML;
    quickBuyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...';
    quickBuyBtn.disabled = true;
    
    setTimeout(() => {
        window.location.href = href;
    }, 500);
});

// Add event listeners to clear error messages when options are selected
document.querySelectorAll('input[name="size"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelector('.size-error').style.display = 'none';
    });
});

document.querySelectorAll('input[name="color"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelector('.color-error').style.display = 'none';
    });
});

// Wishlist functionality
document.querySelector('.wishlist-btn')?.addEventListener('click', function() {
    const productId = this.dataset.product;
    const icon = this.querySelector('i');
    const text = this.querySelector('span');
    
    // Check if user is logged in
    <?php if(!isset($_SESSION['user_id'])): ?>
        window.location.href = 'login.php?redirect=product_detail.php?id=<?php echo $product_id; ?>';
        return;
    <?php endif; ?>
    
    // Show loading state
    this.disabled = true;
    
    fetch('wishlist_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=toggle&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            this.classList.toggle('active');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            
            // Update button text
            if(this.classList.contains('active')) {
                text.textContent = 'Remove from Wishlist';
            } else {
                text.textContent = 'Add to Wishlist';
            }
            
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
        this.disabled = false;
    });
});

// Utility functions
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
</script>

<style>
/* Product Detail Layout */
.product-detail-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin: 30px 0;
}

.product-images {
    position: relative;
}

.product-images .main-image {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.product-images .main-image img {
    width: 100%;
    height: auto;
    display: block;
}

.image-thumbnails {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.thumbnail {
    width: 60px;
    height: 60px;
    border-radius: 5px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.thumbnail.active {
    border-color: #007bff;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.discount-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #dc3545;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 14px;
}

.out-of-stock-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #6c757d;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 14px;
}

.quick-buy-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border: 2px dashed #dee2e6;
    text-align: center;
    margin-bottom: 20px;
}

.quick-buy-section p {
    margin-top: 8px;
    font-size: 12px;
    color: #666;
}

.breadcrumb {
    margin-bottom: 20px;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #007bff;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.product-info h1 {
    font-size: 28px;
    margin-bottom: 15px;
    color: #333;
    line-height: 1.3;
}

.product-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.product-meta span {
    font-size: 14px;
    color: #666;
}

.price-section {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.current-price {
    font-size: 28px;
    font-weight: bold;
    color: #007bff;
}

.old-price {
    font-size: 20px;
    color: #999;
    text-decoration: line-through;
    margin-left: 10px;
}

.discount-percentage {
    color: #28a745;
    font-weight: bold;
    margin-left: 10px;
}

.product-description {
    margin-bottom: 25px;
    line-height: 1.6;
    color: #555;
    font-size: 15px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.required {
    color: #dc3545;
}

.size-options, .color-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.size-option, .color-option {
    cursor: pointer;
    border: 2px solid #e9ecef;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.size-option:hover, .color-option:hover {
    border-color: #007bff;
}

.size-option {
    padding: 10px 15px;
}

.size-option input:checked + span {
    color: #007bff;
    font-weight: bold;
}

.color-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
}

.color-option input:checked + .color-swatch {
    border-color: #007bff;
    transform: scale(1.1);
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #ddd;
    display: inline-block;
    transition: all 0.3s ease;
}

.color-name {
    font-size: 14px;
}

.size-option input:checked + span,
.color-option input:checked ~ .color-name {
    color: #007bff;
    font-weight: bold;
}

.size-option input:checked,
.color-option input:checked {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.quantity-selector {
    display: flex;
    align-items: center;
    width: fit-content;
    border: 2px solid #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.quantity-btn {
    background: #f8f9fa;
    border: none;
    padding: 12px 15px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
}

.quantity-btn:hover {
    background: #e9ecef;
}

.quantity-input {
    width: 60px;
    border: none;
    text-align: center;
    font-size: 16px;
    background: white;
    padding: 12px 0;
}

.stock-info {
    margin: 20px 0;
    padding: 10px;
    border-radius: 5px;
    background: #f8f9fa;
}

.in-stock {
    color: #28a745;
    font-weight: 600;
}

.out-of-stock {
    color: #dc3545;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin: 30px 0;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    justify-content: center;
}

.btn-large {
    padding: 15px 25px;
    font-size: 16px;
    font-weight: 600;
    flex: 1;
    min-width: 140px;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #28a745;
    color: white;
}

.btn-secondary:hover {
    background: #1e7e34;
}

.btn-outline {
    background: transparent;
    border: 2px solid #007bff;
    color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
}

.btn-disabled {
    background: #6c757d;
    color: white;
    cursor: not-allowed;
}

.wishlist-btn.active {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.wishlist-btn.active:hover {
    background-color: #c82333;
}

.error-message {
    display: none;
    color: #dc3545;
    font-size: 14px;
    margin-top: 5px;
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
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.notification.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.notification-content {
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .product-detail-layout {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-large {
        flex: none;
    }
    
    .product-meta {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<?php include 'footer.php'; ?>