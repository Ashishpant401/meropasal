<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];

if($_POST['action'] == 'toggle') {
    $product_id = intval($_POST['product_id']);
    
    // Check if product exists and is not deleted
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND deleted = 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not available']);
        exit;
    }
    
    // Check if already in wishlist
    $check_stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check_stmt->execute([$user_id, $product_id]);
    $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if($existing_item) {
        // Remove from wishlist
        $delete_stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
        if($delete_stmt->execute([$existing_item['id']])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Removed from wishlist!',
                'wishlist_count' => getWishlistCount($pdo, $user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
        }
    } else {
        // Add to wishlist
        $insert_stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        if($insert_stmt->execute([$user_id, $product_id])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Added to wishlist!',
                'wishlist_count' => getWishlistCount($pdo, $user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
        }
    }
}

function getWishlistCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn() ?: 0;
}
?>