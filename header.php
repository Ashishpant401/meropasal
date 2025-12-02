<?php 
session_start();
include 'config.php';

// Function to get cart count
function getCartCount() {
    if(!isset($_SESSION['user_id'])) return 0;
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn() ?: 0;
}

// Function to get wishlist count
function getWishlistCount() {
    if(!isset($_SESSION['user_id'])) return 0;
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn() ?: 0;
}

// Get counts
$cart_count = getCartCount();
$wishlist_count = getWishlistCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeroPasal - Your Fashion Destination</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="top-header">
            <div class="container">
                <div class="welcome-text">MeroPasal</div>
                <div class="header-links">
                    <?php if(isLoggedIn()): ?>
                        <a href="user_dashboard.php"><i class="fas fa-user"></i> My Account</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <nav class="main-nav">
            <div class="container">
                <div class="logo">
                    <h1><a href="index.php">MeroPasal</a></h1>
                </div>
                
                <div class="search-bar">
                    <form action="products.php" method="GET">
                        <input type="text" name="search" placeholder="Search for products...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <div class="nav-icons">
                    <a href="wishlist.php" class="nav-icon">
                        <i class="fas fa-heart"></i>
                        <span class="count wishlist-count"><?php echo $wishlist_count; ?></span>
                    </a>
                    <a href="cart.php" class="nav-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="count cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </nav>
        
        <nav class="category-nav">
            <div class="container">
                <ul> <li> <a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="products.php?category=men">Men</a></li>
                    <li><a href="products.php?category=women">Women</a></li>
                    <li><a href="products.php?category=kids">Kids</a></li>
                     <li><a href="about.php">About</a></li>
                      <li><a href="contact.php">Contact</a></li>
                   
                </ul>
            </div>
        </nav>
        
    </header>

    <main>
        