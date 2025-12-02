<?php include 'header.php'; 

// Handle contact form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Basic validation
    $errors = [];
    
    if(empty($name)) {
        $errors[] = "Name is required";
    }
    
    if(empty($email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if(empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if(empty($message)) {
        $errors[] = "Message is required";
    }
    
    if(empty($errors)) {
        // In a real application, you would send an email here
        // For now, we'll just show a success message
        $success = "Thank you for your message! We'll get back to you within 24 hours.";
        
        // Clear form fields
        $name = $email = $subject = $message = '';
    }
}
?>

<section class="contact-hero">
    <div class="container">
        <div class="hero-content">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Get in touch with any questions or feedback.</p>
        </div>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Get In Touch</h2>
                <p>Have questions about our products or need assistance with your order? Our friendly customer service team is here to help.</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="method-info">
                            <h4>Our Location</h4>
                            <p>Mahendranagar, Nepal</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="method-info">
                            <h4>Phone Number</h4>
                            <p>+977 9862467605</p>
                            <p>+977 9800000000</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="method-info">
                            <h4>Email Address</h4>
                            <p>meropasal@gmail.com</p>
                            
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="method-info">
                            <h4>Working Hours</h4>
                            <p>Sunday - Friday: 9:00 AM - 6:00 PM</p>
                            <p>Saturday: 10:00 AM - 4:00 PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="social-contact">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="https://www.facebook.com/profile.php?id=100064915563029"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/ashish.pant.1650/"><i class="fab fa-instagram"></i></a>

                        <a href="https://wa.me/+9779862467605"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Send us a Message</h2>
                
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
                
                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" 
                               value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="map-section">
    <div class="container">
        <h2>Find Us</h2>
        <div class="map-placeholder"> 
            <div class="map-content">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Mahendranagar, Nepal</h3>
                <p>Visit our main store location in the heart of Mahendranagar</p>
            </div>
        </div>
    </div>
</section>

<style>
.contact-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.contact-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.contact-section {
    padding: 5rem 0;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
}

.contact-info h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: #333;
}

.contact-info > p {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #666;
    margin-bottom: 2rem;
}

.contact-methods {
    margin-bottom: 2rem;
}

.contact-method {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.method-icon {
    font-size: 1.5rem;
    color: #667eea;
    margin-right: 1rem;
    min-width: 40px;
}

.method-info h4 {
    margin-bottom: 0.5rem;
    color: #333;
}

.method-info p {
    margin: 0.25rem 0;
    color: #666;
}

.social-contact h4 {
    margin-bottom: 1rem;
    color: #333;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    text-decoration: none;
    transition: background 0.3s ease;
}

.social-links a:hover {
    background: #764ba2;
}

.contact-form-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.contact-form-container h2 {
    font-size: 2rem;
    margin-bottom: 2rem;
    color: #333;
}

.contact-form .form-group {
    margin-bottom: 1.5rem;
}

.contact-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #667eea;
}

.map-section {
    background: #f8f9fa;
    padding: 3rem 0;
}

.map-section h2 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 2rem;
    color: #333;
}

.map-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 2rem;
    border-radius: 10px;
    text-align: center;
}

.map-content i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.map-content h3 {
    font-size: 1.5rem;
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
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .contact-method {
        flex-direction: column;
        text-align: center;
    }
    
    .method-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}
</style>

<?php include 'footer.php'; ?>