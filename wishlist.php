<?php
include 'header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle remove from wishlist
if(isset($_POST['remove_from_wishlist'])) {
    $wishlist_id = $_POST['wishlist_id'];
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    if($stmt->execute([$wishlist_id, $user_id])) {
        $_SESSION['success'] = "Item removed from wishlist!";
    }
    header("Location: wishlist.php");
    exit;
}

// Handle move to cart
if(isset($_POST['move_to_cart'])) {
    $wishlist_id = $_POST['wishlist_id'];
    
    // Get wishlist item details
    $stmt = $pdo->prepare("SELECT w.product_id FROM wishlist w WHERE w.id = ? AND w.user_id = ?");
    $stmt->execute([$wishlist_id, $user_id]);
    $wishlist_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($wishlist_item) {
        // Check if item already exists in cart
        $check_stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $check_stmt->execute([$user_id, $wishlist_item['product_id']]);
        
        if(!$check_stmt->fetch()) {
            // Add to cart
            $cart_stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $cart_stmt->execute([$user_id, $wishlist_item['product_id']]);
            $_SESSION['success'] = "Item moved to cart!";
        } else {
            $_SESSION['success'] = "Item already in cart!";
        }
        
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
        $stmt->execute([$wishlist_id, $user_id]);
    }
    header("Location: wishlist.php");
    exit;
}

// Handle bulk remove
if(isset($_POST['remove_selected'])) {
    $selected_items = $_POST['selected_items'] ?? [];
    
    if(!empty($selected_items)) {
        $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id IN ($placeholders) AND user_id = ?");
        $params = array_merge($selected_items, [$user_id]);
        
        if($stmt->execute($params)) {
            $_SESSION['success'] = "Selected items removed from wishlist!";
        }
    }
    header("Location: wishlist.php");
    exit;
}

// Get wishlist items with product details - FIXED QUERY
$stmt = $pdo->prepare("SELECT w.id as wishlist_id, p.*, b.name as brand_name 
                      FROM wishlist w 
                      JOIN products p ON w.product_id = p.id 
                      JOIN brands b ON p.brand_id = b.id 
                      WHERE w.user_id = ? AND p.deleted = 0 
                      ORDER BY w.created_at DESC"); // Changed from w.added_at to w.created_at
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="wishlist-page">
    <div class="container">
        <h1>My Wishlist</h1>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(empty($wishlist_items)): ?>
            <div class="empty-wishlist">
                <i class="far fa-heart"></i>
                <h2>Your wishlist is empty</h2>
                <p>Save your favorite items here for later</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" id="wishlist-form">
                <div class="wishlist-header">
                    <div class="select-all">
                        <input type="checkbox" id="select-all" checked>
                        <label for="select-all">Select All (<?php echo count($wishlist_items); ?> items)</label>
                    </div>
                    <div class="wishlist-actions">
                        <button type="button" id="remove-selected" class="btn btn-outline btn-sm">
                            <i class="fas fa-trash"></i> Remove Selected
                        </button>
                    </div>
                </div>
                
                <div class="wishlist-items">
                    <?php foreach($wishlist_items as $item): ?>
                    <div class="wishlist-item" data-item="<?php echo $item['wishlist_id']; ?>">
                        <div class="item-select">
                            <input type="checkbox" name="selected_items[]" value="<?php echo $item['wishlist_id']; ?>" class="item-checkbox" checked>
                        </div>
                        <div class="item-image">
                            <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                        </div>
                        <div class="item-details">
                            <h3><?php echo $item['name']; ?></h3>
                            <p class="brand"><?php echo $item['brand_name']; ?></p>
                            <p class="item-price">Rs <?php echo number_format($item['price'], 2); ?></p>
                            
                            <?php if($item['stock'] > 0): ?>
                                <p class="stock-available">In Stock</p>
                            <?php else: ?>
                                <p class="stock-out">Out of Stock</p>
                            <?php endif; ?>
                        </div>
                        <div class="item-actions">
                            <?php if($item['stock'] > 0): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="wishlist_id" value="<?php echo $item['wishlist_id']; ?>">
                                    <button type="submit" name="move_to_cart" class="btn btn-primary btn-sm">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="wishlist_id" value="<?php echo $item['wishlist_id']; ?>">
                                <button type="submit" name="remove_from_wishlist" class="btn btn-outline btn-sm" onclick="return confirm('Are you sure you want to remove this item?')">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                            <a href="product_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </form>
            
            <!-- Hidden form for bulk remove -->
            <form method="POST" id="bulk-remove-form" style="display: none;">
                <input type="hidden" name="remove_selected" value="1">
                <div id="selected-items-inputs"></div>
            </form>
        <?php endif; ?>
    </div>
</section>

<style>
.wishlist-page {
    padding: 40px 0;
}

.empty-wishlist {
    text-align: center;
    padding: 60px 20px;
}

.empty-wishlist i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-wishlist h2 {
    color: #333;
    margin-bottom: 10px;
}

.empty-wishlist p {
    color: #666;
    margin-bottom: 30px;
}

.wishlist-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 10px 10px 0 0;
    border-bottom: 1px solid #eee;
}

.select-all {
    display: flex;
    align-items: center;
    gap: 10px;
}

.wishlist-items {
    background: white;
    border-radius: 0 0 10px 10px;
}

.wishlist-item {
    display: grid;
    grid-template-columns: 50px 100px 1fr auto;
    gap: 20px;
    align-items: center;
    padding: 25px;
    border-bottom: 1px solid #f5f5f5;
}

.wishlist-item:last-child {
    border-bottom: none;
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

.brand {
    color: #666;
    font-size: 14px;
    margin: 0 0 8px 0;
}

.item-price {
    font-weight: bold;
    color: #333;
    font-size: 16px;
    margin: 0 0 8px 0;
}

.stock-available {
    color: #28a745;
    font-size: 14px;
    margin: 0;
}

.stock-out {
    color: #dc3545;
    font-size: 14px;
    margin: 0;
}

.item-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .wishlist-item {
        grid-template-columns: 40px 80px 1fr;
        grid-template-rows: auto auto;
        gap: 15px;
    }
    
    .item-actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
    }
    
    .wishlist-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const removeSelectedBtn = document.getElementById('remove-selected');
    const bulkRemoveForm = document.getElementById('bulk-remove-form');
    const selectedItemsInputs = document.getElementById('selected-items-inputs');
    
    // Select all functionality
    selectAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
    
    // Remove selected items
    removeSelectedBtn.addEventListener('click', function() {
        const selectedItems = Array.from(itemCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        if (selectedItems.length === 0) {
            alert('Please select items to remove.');
            return;
        }
        
        if (confirm(`Are you sure you want to remove ${selectedItems.length} item(s) from your wishlist?`)) {
            // Clear previous inputs
            selectedItemsInputs.innerHTML = '';
            
            // Add selected items to form
            selectedItems.forEach(itemId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_items[]';
                input.value = itemId;
                selectedItemsInputs.appendChild(input);
            });
            
            // Submit the form
            bulkRemoveForm.submit();
        }
    });
});
</script>

<?php include 'footer.php'; ?>