<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to perform this action']);
    exit;
}

$user_id = $_SESSION['user_id'];

if($_POST['action'] == 'cancel') {
    $order_id = intval($_POST['order_id']);
    
    // Verify order belongs to user and is pending
    $check_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $check_stmt->execute([$order_id, $user_id]);
    $order = $check_stmt->fetch();
    
    if(!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled']);
        exit;
    }
    
    // Update order status to cancelled
    $update_stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    if($update_stmt->execute([$order_id])) {
        // Restore product stock
        $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll();
        
        foreach($items as $item) {
            $restore_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $restore_stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    }
    
} elseif($_POST['action'] == 'reorder') {
    $order_id = intval($_POST['order_id']);
    
    // Verify order belongs to user
    $check_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $check_stmt->execute([$order_id, $user_id]);
    $order = $check_stmt->fetch();
    
    if(!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items_stmt->execute([$order_id]);
    $items = $items_stmt->fetchAll();
    
    $added_count = 0;
    
    foreach($items as $item) {
        // Check if product still exists and has stock
        $product_stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND deleted = 0 AND stock > 0");
        $product_stmt->execute([$item['product_id']]);
        $product = $product_stmt->fetch();
        
        if($product) {
            // Check if item already in cart
            $cart_stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $cart_stmt->execute([$user_id, $item['product_id']]);
            $cart_item = $cart_stmt->fetch();
            
            if($cart_item) {
                // Update quantity
                $new_quantity = $cart_item['quantity'] + 1;
                if($new_quantity <= $product['stock']) {
                    $update_stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                    $update_stmt->execute([$new_quantity, $cart_item['id']]);
                    $added_count++;
                }
            } else {
                // Add to cart
                $insert_stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $insert_stmt->execute([$user_id, $item['product_id']]);
                $added_count++;
            }
        }
    }
    
    if($added_count > 0) {
        echo json_encode([
            'success' => true, 
            'message' => $added_count . ' items added to cart',
            'redirect' => 'cart.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No items could be added to cart']);
    }
    
} elseif($_POST['action'] == 'delete') {
    $order_id = intval($_POST['order_id']);
    
    // Verify order belongs to user and is cancelled
    $check_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'cancelled'");
    $check_stmt->execute([$order_id, $user_id]);
    $order = $check_stmt->fetch();
    
    if(!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found or cannot be deleted. Only cancelled orders can be deleted.']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete order items first (due to foreign key constraints)
        $delete_items_stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $delete_items_stmt->execute([$order_id]);
        
        // Delete the order
        $delete_order_stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $delete_order_stmt->execute([$order_id]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error deleting order: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>