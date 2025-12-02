<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage cart']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Function to get cart count
function getCartCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    return $count ? $count : 0;
}

// Function to get selected items count
function getSelectedCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ? AND selected = 1");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    return $count ? $count : 0;
}

$action = $_POST['action'] ?? '';

switch($action) {
    case 'add':
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity'] ?? 1);
        $size = $_POST['size'] ?? null;
        $color = $_POST['color'] ?? null;
        
        // Check if product exists and is available
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND deleted = 0");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not available']);
            exit;
        }
        
        // Check stock
        if($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Only ' . $product['stock'] . ' items left in stock']);
            exit;
        }
        
        // Check if item already exists in cart with same attributes
        $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $check_params = [$user_id, $product_id];
        
        if($size) {
            $check_sql .= " AND size = ?";
            $check_params[] = $size;
        } else {
            $check_sql .= " AND (size IS NULL OR size = '')";
        }
        
        if($color) {
            $check_sql .= " AND color = ?";
            $check_params[] = $color;
        } else {
            $check_sql .= " AND (color IS NULL OR color = '')";
        }
        
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute($check_params);
        $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            if($new_quantity > $product['stock']) {
                $new_quantity = $product['stock'];
            }
            
            $update_stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            if($update_stmt->execute([$new_quantity, $existing_item['id']])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cart updated successfully!',
                    'cart_count' => getCartCount($pdo, $user_id)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
        } else {
            // Add new item (default selected = 1 for new items)
            $insert_stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color, selected, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            if($insert_stmt->execute([$user_id, $product_id, $quantity, $size, $color])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Item added to cart!',
                    'cart_count' => getCartCount($pdo, $user_id)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
            }
        }
        break;

    case 'update':
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        // Check stock
        $stmt = $pdo->prepare("SELECT p.stock FROM products p JOIN cart c ON p.id = c.product_id WHERE c.user_id = ? AND c.product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $stock = $stmt->fetchColumn();
        
        if($stock === false) {
            echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
            exit;
        }
        
        if($quantity > $stock) {
            echo json_encode(['success' => false, 'message' => 'Only ' . $stock . ' items left in stock']);
            exit;
        }
        
        if($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            if($stmt->execute([$user_id, $product_id])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Item removed from cart!',
                    'cart_count' => getCartCount($pdo, $user_id),
                    'selected_count' => getSelectedCount($pdo, $user_id)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
            if($stmt->execute([$quantity, $user_id, $product_id])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Quantity updated successfully!',
                    'cart_count' => getCartCount($pdo, $user_id),
                    'selected_count' => getSelectedCount($pdo, $user_id)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
            }
        }
        break;

    case 'remove':
        $product_id = intval($_POST['product_id']);
        
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        if($stmt->execute([$user_id, $product_id])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Item removed from cart!',
                'cart_count' => getCartCount($pdo, $user_id),
                'selected_count' => getSelectedCount($pdo, $user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
        }
        break;

    case 'clear':
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        if($stmt->execute([$user_id])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Cart cleared successfully!',
                'cart_count' => 0,
                'selected_count' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
        }
        break;

    case 'toggle_select':
        $product_id = intval($_POST['product_id']);
        $selected = intval($_POST['selected'] ?? 0);
        
        $stmt = $pdo->prepare("UPDATE cart SET selected = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
        if($stmt->execute([$selected, $user_id, $product_id])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Selection updated!',
                'selected_count' => getSelectedCount($pdo, $user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update selection']);
        }
        break;

    case 'select_all':
        $stmt = $pdo->prepare("UPDATE cart SET selected = 1, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        if($stmt->execute([$user_id])) {
            echo json_encode([
                'success' => true, 
                'message' => 'All items selected!',
                'selected_count' => getSelectedCount($pdo, $user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to select all items']);
        }
        break;

    case 'unselect_all':
        $stmt = $pdo->prepare("UPDATE cart SET selected = 0, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        if($stmt->execute([$user_id])) {
            echo json_encode([
                'success' => true, 
                'message' => 'All items unselected!',
                'selected_count' => getSelectedCount($pdo, $user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to unselect all items']);
        }
        break;

    case 'get_cart_count':
        // Simple endpoint to get cart count
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount($pdo, $user_id),
            'selected_count' => getSelectedCount($pdo, $user_id)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>