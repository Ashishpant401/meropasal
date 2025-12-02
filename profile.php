<?php include 'header.php'; 
if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = getUserId();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Basic validation
    $errors = [];
    
    if(empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if(empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    if(empty($email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists (excluding current user)
    $email_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->execute([$email, $user_id]);
    if($email_check->fetch()) {
        $errors[] = "Email already exists";
    }
    
    if(empty($errors)) {
        // Update user profile
        $update_stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        if($update_stmt->execute([$first_name, $last_name, $email, $phone, $user_id])) {
            $success = "Profile updated successfully!";
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

// Get user info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="dashboard">
    <div class="container">
        <h1>My Profile</h1>
        
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
                    <a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                    <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
                    <a href="addresses.php"><i class="fas fa-address-book"></i> Addresses</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>
            
            <main class="dashboard-main">
                <div class="profile-section">
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small>Username cannot be changed</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                    
                    <div class="profile-actions">
                        <div class="action-card">
                            <h3>Change Password</h3>
                            <p>Update your password to keep your account secure</p>
                            <a href="change_password.php" class="btn btn-outline">Change Password</a>
                        </div>
                        
                        <div class="action-card">
                            <h3>Account Security</h3>
                            <p>Manage your account security settings</p>
                            <a href="security.php" class="btn btn-outline">Security Settings</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<style>
.profile-section {
    max-width: 600px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.profile-form {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: #007bff;
}

.form-group small {
    color: #666;
    font-size: 0.875rem;
}

.profile-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.action-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.action-card h3 {
    margin-bottom: 0.5rem;
    color: #333;
}

.action-card p {
    color: #666;
    margin-bottom: 1rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-error ul {
    margin: 0;
    padding-left: 1rem;
}

@media (max-width: 768px) {
    .form-grid,
    .profile-actions {
        grid-template-columns: 1fr;
    }
    
    .profile-form {
        padding: 1rem;
    }
}
</style>

<?php include 'footer.php'; ?>