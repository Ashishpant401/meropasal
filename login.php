<?php include 'header.php'; 
if(isLoggedIn()) {
    header("Location: index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<section class="auth-page">
    <div class="container">
        <div class="auth-form">
            <h1>Login to Your Account</h1>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large btn-block">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</section>
<style>
    /* Auth Page Styles */
.auth-page {
    padding: 4rem 0;
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
}

.auth-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.auth-form {
    max-width: 450px;
    width: 100%;
    margin: 0 auto;
    background: white;
    padding: 3rem;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.auth-form h1 {
    text-align: center;
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: #1a202c;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    width: 100%;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
    width: 100%;
    text-align: center;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    text-align: center;
}

.form-group input {
    width: 100%;
    padding: 1rem 1.2rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8fafc;
    text-align: center;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

/* Button Styles */
.btn-block {
    width: 100%;
    display: block;
    text-align: center;
}

.btn-large {
    padding: 1.2rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: inline-block;
    text-align: center;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

/* Alert Styles */
.alert {
    padding: 1rem 1.2rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
    width: 100%;
    text-align: center;
}

.alert-error {
    background: #fed7d7;
    color: #c53030;
    border: 1px solid #feb2b2;
}

/* Auth Links */
.auth-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
    width: 100%;
}

.auth-links p {
    color: #4a5568;
    margin: 0;
    text-align: center;
}

.auth-links a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
    text-align: center;
}

.auth-links a:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .auth-page {
        padding: 2rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .auth-form {
        padding: 2rem 1.5rem;
        margin: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .auth-form h1 {
        font-size: 1.8rem;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .auth-form {
        padding: 1.5rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .auth-form h1 {
        font-size: 1.6rem;
        text-align: center;
    }
    
    .form-group input {
        padding: 0.8rem 1rem;
        text-align: center;
    }
    
    .btn-large {
        padding: 1rem 1.5rem;
        font-size: 1rem;
    }
}

/* Loading State */
.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-primary:disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Focus States for Accessibility */
.form-group input:focus,
.btn-primary:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}

/* Animation for form */
.auth-form {
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Password toggle (optional enhancement) */
.password-toggle {
    position: relative;
    width: 100%;
    text-align: center;
}

.password-toggle input {
    padding-right: 3rem;
    text-align: center;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    font-size: 1rem;
}

.toggle-password:hover {
    color: #667eea;
}
</style>

<?php include 'footer.php'; ?>