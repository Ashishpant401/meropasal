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
?>

<section class="products-page">
    <div class="container">
        <div class="page-header">
            <h1>Products</h1>
            <div class="filter-sort">
                <select id="sort">
                    <option value="newest">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>
        </div>

        <div class="products-layout">
            <aside class="sidebar">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="?category=men">Men</a></li>
                        <li><a href="?category=women">Women</a></li>
                        <li><a href="?category=kids">Kids</a></li>
                    </ul>
                </div>
                
                <div class="filter-section">
                    <h3>Brands</h3>
                    <ul>
                        <?php
                        $brands = $pdo->query("SELECT * FROM brands")->fetchAll();
                        foreach($brands as $brand):
                        ?>
                        <li><a href="?brand=<?php echo $brand['slug']; ?>"><?php echo $brand['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
             
            </aside>

            <main class="products-main">
                <div class="products-grid">
                    <?php
                    $where = ["p.deleted = 0"];
                    $params = [];
                    
                    if(isset($_GET['category'])) {
                        $where[] = "p.gender = ?";
                        $params[] = $_GET['category'];
                    }
                    
                    if(isset($_GET['brand'])) {
                        $where[] = "b.slug = ?";
                        $params[] = $_GET['brand'];
                    }
                    
                    if(isset($_GET['search'])) {
                        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
                        $params[] = "%{$_GET['search']}%";
                        $params[] = "%{$_GET['search']}%";
                    }
                    
                    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";
                    
                    $sql = "SELECT p.*, b.name as brand_name FROM products p 
                           JOIN brands b ON p.brand_id = b.id 
                           $where_sql ORDER BY p.created_at DESC";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(empty($products)):
                    ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or search terms</p>
                    </div>
                    <?php else: ?>
                        <?php foreach($products as $product): 
                            $inWishlist = isInWishlist($product['id']);
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                <div class="product-actions">
                                    <button class="wishlist-btn <?php echo $inWishlist ? 'active' : ''; ?>" 
                                            data-product="<?php echo $product['id']; ?>">
                                        <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?php echo $product['name']; ?></h3>
                                <p class="brand"><?php echo $product['brand_name']; ?></p>
                                <div class="price">
                                    <?php if($product['old_price']): ?>
                                        <span class="old-price">Rs <?php echo $product['old_price']; ?></span>
                                    <?php endif; ?>
                                    <span class="current-price">Rs <?php echo $product['price']; ?></span>
                                </div>
                                <div class="product-buttons">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">View Details</a>
                                    <?php if($product['stock'] > 0): ?>
                                        <button class="btn btn-primary add-to-cart" data-product="<?php echo $product['id']; ?>">Add to Cart</button>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</section>



<script>
// Wishlist functionality
document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.product;
        const icon = this.querySelector('i');
        
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
                
                // Update wishlist count
                updateWishlistCount(data.wishlist_count);
                
                showNotification(data.message, data.success ? 'success' : 'error');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating wishlist', 'error');
        });
    });
});

// Add to cart functionality
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.product;
        
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=1'
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
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
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


// Product sorting functionality
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            sortProducts(sortValue);
        });
    }
});

function sortProducts(sortType) {
    const productGrid = document.querySelector('.products-grid');
    const products = Array.from(productGrid.querySelectorAll('.product-card'));
    
    // Store original order for reference
    if (!productGrid.dataset.originalOrder) {
        productGrid.dataset.originalOrder = JSON.stringify(
            products.map((product, index) => ({
                element: product.outerHTML,
                index: index
            }))
        );
    }
    
    let sortedProducts = [...products];
    
    switch(sortType) {
        case 'newest':
            // Already sorted by newest by default (from PHP)
            // Restore original order
            const originalOrder = JSON.parse(productGrid.dataset.originalOrder);
            sortedProducts = originalOrder.sort((a, b) => a.index - b.index)
                .map(item => {
                    const div = document.createElement('div');
                    div.innerHTML = item.element;
                    return div.firstChild;
                });
            break;
            
        case 'price_low':
            // Sort by price (low to high)
            sortedProducts.sort((a, b) => {
                const priceA = parseFloat(a.querySelector('.current-price').textContent.replace('Rs ', '').replace(/,/g, ''));
                const priceB = parseFloat(b.querySelector('.current-price').textContent.replace('Rs ', '').replace(/,/g, ''));
                return priceA - priceB;
            });
            break;
            
        case 'price_high':
            // Sort by price (high to low)
            sortedProducts.sort((a, b) => {
                const priceA = parseFloat(a.querySelector('.current-price').textContent.replace('Rs ', '').replace(/,/g, ''));
                const priceB = parseFloat(b.querySelector('.current-price').textContent.replace('Rs ', '').replace(/,/g, ''));
                return priceB - priceA;
            });
            break;
    }
    
    // Clear the grid
    productGrid.innerHTML = '';
    
    // Re-add sorted products
    sortedProducts.forEach(product => {
        productGrid.appendChild(product);
    });
    
    // Re-attach event listeners to the newly added buttons
    reattachEventListeners();
}


</script>

<?php include 'footer.php'; ?>