<?php include 'header.php'; 
if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = getUserId();

// Handle add/edit/delete address actions
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'add_address') {
        $address_type = $_POST['address_type'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone = $_POST['phone'];
        $address_line1 = $_POST['address_line1'];
        $address_line2 = $_POST['address_line2'] ?? '';
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip_code = $_POST['zip_code'];
        $country = $_POST['country'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // If setting as default, remove default from other addresses
        if($is_default) {
            $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
        }
        
        $stmt = $pdo->prepare("INSERT INTO addresses (user_id, address_type, first_name, last_name, phone, address_line1, address_line2, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if($stmt->execute([$user_id, $address_type, $first_name, $last_name, $phone, $address_line1, $address_line2, $city, $state, $zip_code, $country, $is_default])) {
            $_SESSION['success'] = "Address added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add address. Please try again.";
        }
        header("Location: addresses.php");
        exit;
    }
    
    if($action == 'update_address') {
        $address_id = $_POST['address_id'];
        $address_type = $_POST['address_type'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone = $_POST['phone'];
        $address_line1 = $_POST['address_line1'];
        $address_line2 = $_POST['address_line2'] ?? '';
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip_code = $_POST['zip_code'];
        $country = $_POST['country'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // Verify address belongs to user
        $check_stmt = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$address_id, $user_id]);
        
        if($check_stmt->fetch()) {
            // If setting as default, remove default from other addresses
            if($is_default) {
                $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
            }
            
            $stmt = $pdo->prepare("UPDATE addresses SET address_type = ?, first_name = ?, last_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, zip_code = ?, country = ?, is_default = ?, updated_at = NOW() WHERE id = ?");
            
            if($stmt->execute([$address_type, $first_name, $last_name, $phone, $address_line1, $address_line2, $city, $state, $zip_code, $country, $is_default, $address_id])) {
                $_SESSION['success'] = "Address updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update address. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Address not found.";
        }
        header("Location: addresses.php");
        exit;
    }
    
    if($action == 'delete_address') {
        $address_id = $_POST['address_id'];
        
        // Verify address belongs to user
        $check_stmt = $pdo->prepare("SELECT id, is_default FROM addresses WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$address_id, $user_id]);
        $address = $check_stmt->fetch();
        
        if($address) {
            $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ?");
            if($stmt->execute([$address_id])) {
                // If deleted address was default, set another address as default
                if($address['is_default']) {
                    $new_default = $pdo->prepare("SELECT id FROM addresses WHERE user_id = ? LIMIT 1");
                    $new_default->execute([$user_id]);
                    $new_default_addr = $new_default->fetch();
                    if($new_default_addr) {
                        $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?")->execute([$new_default_addr['id']]);
                    }
                }
                $_SESSION['success'] = "Address deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete address. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Address not found.";
        }
        header("Location: addresses.php");
        exit;
    }
    
    if($action == 'set_default') {
        $address_id = $_POST['address_id'];
        
        // Verify address belongs to user
        $check_stmt = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$address_id, $user_id]);
        
        if($check_stmt->fetch()) {
            // Remove default from all addresses
            $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
            
            // Set new default
            $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?");
            if($stmt->execute([$address_id])) {
                $_SESSION['success'] = "Default address updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update default address. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Address not found.";
        }
        header("Location: addresses.php");
        exit;
    }
}

// Get user addresses
$addresses_stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$addresses_stmt->execute([$user_id]);
$addresses = $addresses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Get address to edit (if any)
$edit_address = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $edit_stmt->execute([$edit_id, $user_id]);
    $edit_address = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<section class="addresses-page">
    <div class="container">
        <div class="page-header">
            <h1>My Addresses</h1>
            <p>Manage your shipping addresses</p>
        </div>
        
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
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="addresses.php" class="active"><i class="fas fa-address-book"></i> Addresses</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>
            
            <main class="dashboard-main">
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <div class="addresses-container">
                    <!-- Add/Edit Address Form -->
                    <div class="address-form-section">
                        <h2><?php echo $edit_address ? 'Edit Address' : 'Add New Address'; ?></h2>
                        <form method="POST" class="address-form" id="address-form">
                            <input type="hidden" name="action" value="<?php echo $edit_address ? 'update_address' : 'add_address'; ?>">
                            <?php if($edit_address): ?>
                                <input type="hidden" name="address_id" value="<?php echo $edit_address['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Address Type *</label>
                                    <select name="address_type" required>
                                        <option value="home" <?php echo ($edit_address && $edit_address['address_type'] == 'home') ? 'selected' : ''; ?>>Home</option>
                                        <option value="work" <?php echo ($edit_address && $edit_address['address_type'] == 'work') ? 'selected' : ''; ?>>Work</option>
                                        <option value="other" <?php echo ($edit_address && $edit_address['address_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Default Address</label>
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="is_default" id="is_default" value="1" <?php echo ($edit_address && $edit_address['is_default']) || empty($addresses) ? 'checked' : ''; ?>>
                                        <label for="is_default">Set as default shipping address</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" name="first_name" value="<?php echo $edit_address ? htmlspecialchars($edit_address['first_name']) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" name="last_name" value="<?php echo $edit_address ? htmlspecialchars($edit_address['last_name']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="phone" value="<?php echo $edit_address ? htmlspecialchars($edit_address['phone']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Address Line 1 *</label>
                                <input type="text" name="address_line1" placeholder="Street address, P.O. box, company name" value="<?php echo $edit_address ? htmlspecialchars($edit_address['address_line1']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Address Line 2</label>
                                <input type="text" name="address_line2" placeholder="Apartment, suite, unit, building, floor, etc." value="<?php echo $edit_address ? htmlspecialchars($edit_address['address_line2']) : ''; ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" name="city" value="<?php echo $edit_address ? htmlspecialchars($edit_address['city']) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>State/Province *</label>
                                    <input type="text" name="state" value="<?php echo $edit_address ? htmlspecialchars($edit_address['state']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>ZIP/Postal Code *</label>
                                    <input type="text" name="zip_code" value="<?php echo $edit_address ? htmlspecialchars($edit_address['zip_code']) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Country *</label>
                                    <select name="country" required>
                                        <option value="US" <?php echo ($edit_address && $edit_address['country'] == 'US') ? 'selected' : ''; ?>>United States</option>
                                        <option value="CA" <?php echo ($edit_address && $edit_address['country'] == 'CA') ? 'selected' : ''; ?>>Canada</option>
                                        <option value="UK" <?php echo ($edit_address && $edit_address['country'] == 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="NP" <?php echo ($edit_address && $edit_address['country'] == 'NP') ? 'selected' : ''; ?>>Nepal</option>
                                        <option value="IN" <?php echo ($edit_address && $edit_address['country'] == 'IN') ? 'selected' : ''; ?>>India</option>
                                        <option value="AU" <?php echo ($edit_address && $edit_address['country'] == 'AU') ? 'selected' : ''; ?>>Australia</option>
                                        <option value="other" <?php echo ($edit_address && $edit_address['country'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $edit_address ? 'Update Address' : 'Add Address'; ?>
                                </button>
                                <?php if($edit_address): ?>
                                    <a href="addresses.php" class="btn btn-outline">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Addresses List -->
                    <div class="addresses-list-section">
                        <h2>Saved Addresses (<?php echo count($addresses); ?>)</h2>
                        
                        <?php if(empty($addresses)): ?>
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>No Addresses Saved</h3>
                                <p>Add your first address to get started with faster checkout.</p>
                            </div>
                        <?php else: ?>
                            <div class="addresses-grid">
                                <?php foreach($addresses as $address): ?>
                                <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                    <?php if($address['is_default']): ?>
                                        <div class="default-badge">Default</div>
                                    <?php endif; ?>
                                    
                                    <div class="address-header">
                                        <h4>
                                            <i class="fas fa-<?php echo $address['address_type'] == 'home' ? 'home' : ($address['address_type'] == 'work' ? 'briefcase' : 'map-marker-alt'); ?>"></i>
                                            <?php echo ucfirst($address['address_type']); ?> Address
                                        </h4>
                                        <div class="address-actions">
                                            <a href="addresses.php?edit=<?php echo $address['id']; ?>" class="action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="action" value="delete_address">
                                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                <button type="submit" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this address?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="address-body">
                                        <p class="address-name"><?php echo htmlspecialchars($address['first_name'] . ' ' . $address['last_name']); ?></p>
                                        <p class="address-phone"><?php echo htmlspecialchars($address['phone']); ?></p>
                                        <p class="address-line1"><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                        <?php if($address['address_line2']): ?>
                                            <p class="address-line2"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                        <?php endif; ?>
                                        <p class="address-city">
                                            <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['zip_code']); ?>
                                        </p>
                                        <p class="address-country"><?php echo htmlspecialchars($address['country']); ?></p>
                                    </div>
                                    
                                    <div class="address-footer">
                                        <?php if(!$address['is_default']): ?>
                                            <form method="POST" class="set-default-form">
                                                <input type="hidden" name="action" value="set_default">
                                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                <button type="submit" class="btn btn-outline btn-sm">
                                                    Set as Default
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="default-text">Default Address</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<style>
.addresses-page {
    padding: 40px 0;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.addresses-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.address-form-section, .addresses-list-section {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.address-form-section h2, .addresses-list-section h2 {
    margin: 0 0 25px 0;
    color: #333;
    font-size: 24px;
}

.address-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-group input, .form-group select {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.checkbox-group label {
    margin: 0;
    font-weight: normal;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-state i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.empty-state p {
    margin: 0;
}

.addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.address-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 25px;
    position: relative;
    transition: all 0.3s;
    background: white;
}

.address-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.address-card.default {
    border-color: #007bff;
    background: #f8f9fa;
}

.default-badge {
    position: absolute;
    top: -10px;
    right: 20px;
    background: #007bff;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.address-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.address-header h4 {
    margin: 0;
    color: #333;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.address-header h4 i {
    color: #007bff;
}

.address-actions {
    display: flex;
    gap: 5px;
}

.action-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s;
}

.action-btn:hover {
    background: #f8f9fa;
    color: #007bff;
}

.delete-btn:hover {
    color: #dc3545;
}

.action-form {
    margin: 0;
}

.address-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.address-name {
    font-weight: bold;
    color: #333;
    margin: 0;
    font-size: 16px;
}

.address-phone, .address-line1, .address-line2, .address-city, .address-country {
    color: #666;
    margin: 0;
    line-height: 1.4;
}

.address-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.set-default-form {
    margin: 0;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

.default-text {
    color: #28a745;
    font-weight: 500;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .addresses-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .address-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .address-actions {
        align-self: flex-end;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addressForm = document.getElementById('address-form');
    
    // Form validation
    addressForm.addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let valid = true;
        
        requiredFields.forEach(field => {
            if(!field.value.trim()) {
                valid = false;
                field.style.borderColor = '#dc3545';
                
                // Add error message
                let errorMsg = field.parentNode.querySelector('.error-message');
                if(!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.style.fontSize = '14px';
                    errorMsg.style.marginTop = '5px';
                    field.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'This field is required';
            } else {
                field.style.borderColor = '#ddd';
                const errorMsg = field.parentNode.querySelector('.error-message');
                if(errorMsg) {
                    errorMsg.remove();
                }
            }
        });
        
        // Phone number validation
        const phoneField = this.querySelector('input[name="phone"]');
        if(phoneField.value.trim()) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if(!phoneRegex.test(phoneField.value.replace(/[\s\-\(\)]/g, ''))) {
                valid = false;
                phoneField.style.borderColor = '#dc3545';
                let errorMsg = phoneField.parentNode.querySelector('.error-message');
                if(!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.style.fontSize = '14px';
                    errorMsg.style.marginTop = '5px';
                    phoneField.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Please enter a valid phone number';
            }
        }
        
        if(!valid) {
            e.preventDefault();
        }
    });
    
    // Real-time validation
    addressForm.querySelectorAll('input[required]').forEach(field => {
        field.addEventListener('blur', function() {
            if(!this.value.trim()) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ddd';
                const errorMsg = this.parentNode.querySelector('.error-message');
                if(errorMsg) {
                    errorMsg.remove();
                }
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>