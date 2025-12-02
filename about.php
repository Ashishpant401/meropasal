<?php include 'header.php'; ?>

<section class="about-hero">
    <div class="container">
        <div class="hero-content">
            <h1>About MeroPasal</h1>
            <p>Your trusted fashion destination since 2015</p>
        </div>
    </div>
</section>

<section class="our-values">
    <div class="container">
        <h2>Our Values</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3>Quality First</h3>
                <p>We carefully select each product to ensure it meets our high standards for quality and durability.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3>Customer Focus</h3>
                <p>Your satisfaction is our priority. We're committed to providing exceptional service at every step.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h3>Fair Pricing</h3>
                <p>We believe great fashion should be accessible to everyone with transparent and competitive pricing.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Quick and reliable delivery across Nepal with multiple shipping options to suit your needs.</p>
            </div>
        </div>
    </div>
</section>

<section class="team-section">
    <div class="container">
        <h2>Why Choose MeroPasal?</h2>
        <div class="features-grid">
            <div class="feature">
                <i class="fas fa-check-circle"></i>
                <h4>100% Authentic Products</h4>
                <p>All our products are genuine and sourced directly from authorized suppliers.</p>
            </div>
            <div class="feature">
                <i class="fas fa-undo-alt"></i>
                <h4>Easy Returns</h4>
                <p>30-day return policy for your peace of mind.</p>
            </div>
            <div class="feature">
                <i class="fas fa-headset"></i>
                <h4>24/7 Support</h4>
                <p>Our customer service team is always here to help you.</p>
            </div>
            <div class="feature">
                <i class="fas fa-shield-alt"></i>
                <h4>Secure Shopping</h4>
                <p>Your data and payments are protected with advanced security.</p>
            </div>
        </div>
    </div>
</section>

<style>
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 0;
    text-align: center;
}

.about-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.about-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    padding: 5rem 0;
}

.story-content h2 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    color: #333;
}

.story-content p {
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 2rem;
    color: #666;
}

.milestones {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 3rem;
}

.milestone {
    text-align: center;
    padding: 1.5rem;
    border-radius: 8px;
    background: #f8f9fa;
}

.year {
    font-size: 2rem;
    font-weight: bold;
    color: #667eea;
    display: block;
    margin-bottom: 0.5rem;
}

.story-image img {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.our-values {
    background: #f8f9fa;
    padding: 5rem 0;
    text-align: center;
}

.our-values h2 {
    font-size: 2.5rem;
    margin-bottom: 3rem;
    color: #333;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.value-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
}

.value-icon {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.value-card h3 {
    margin-bottom: 1rem;
    color: #333;
}

.team-section {
    padding: 5rem 0;
}

.team-section h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 3rem;
    color: #333;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.feature {
    text-align: center;
    padding: 2rem;
}

.feature i {
    font-size: 2.5rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.feature h4 {
    margin-bottom: 1rem;
    color: #333;
}

@media (max-width: 768px) {
    .story-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .about-hero h1 {
        font-size: 2rem;
    }
    
    .milestones {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'footer.php'; ?>