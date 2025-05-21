<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
// Fetch featured flights
$featured_flights = $pdo->query("
 SELECT * FROM flights
 WHERE departure_time > NOW()
 ORDER BY RAND() LIMIT 3
")->fetchAll();
$page_title = "SkyHigh AirLine | Home";
include 'includes/header.php';
?>

<!-- Hero Section with Airplane Background Image -->
<div class="hero-section">
    <!-- Background image as a separate div with absolute positioning -->
    <div class="hero-bg-image"></div>
    
    <!-- Dark overlay for better text readability -->
    <div class="hero-overlay"></div>
    
    <div class="container text-center text-white py-5" style="position: relative; z-index: 3;">
        <h1 class="display-4">Fly Beyond Horizons</h1>
        <p class="lead">Book your next adventure with the world's most trusted airline</p>
        <a href="<?= BASE_URL ?>/flights/search.php" class="btn btn-primary btn-lg">Search Flights</a>
    </div>
</div>

<!-- Travel Offers & Destinations Section -->
<div class="container py-5">
    <div class="section-header text-center mb-5">
        <h2 class="display-4 mb-3">Exclusive Offers</h2>
        <div class="divider mx-auto mb-3"></div>
        <p class="text-muted">Special deals and packages to your dream destinations</p>
    </div>
    
    <!-- Special Offers Cards -->
    <div class="row mb-5">
        <div class="col-lg-8 mb-4">
            <!-- Large Promotional Banner -->
            <div class="promo-card h-100 position-relative rounded overflow-hidden shadow-lg">
                <img src="https://images.unsplash.com/photo-1571536802807-30451e3955d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" 
                     alt="Special summer offer" class="img-fluid w-100 h-100" style="object-fit: cover;">
                <div class="promo-overlay position-absolute"></div>
                <div class="promo-content position-absolute text-white p-4">
                    <span class="badge badge-danger mb-2">Limited Time</span>
                    <h3 class="mb-2">Summer Flash Sale</h3>
                    <p class="mb-3">Up to 30% off on select international flights</p>
                    <a href="<?= BASE_URL ?>/offers/summer-sale.php" class="btn btn-light">View Offer</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <!-- Business Class Offer -->
            <div class="promo-card h-100 position-relative rounded overflow-hidden shadow-lg">
               <img src="https://images.unsplash.com/photo-1570075755662-9de614ccbe6f?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8N3x8YnVzaW5lc3MlMjBjbGFzcyUyMHVwZ3JhZGUlMjBpbiUyMHBsYW5lfGVufDB8fDB8fHww" 
     alt="Business class upgrade" class="img-fluid w-100 h-100" style="object-fit: cover;">
                <div class="promo-overlay position-absolute"></div>
                <div class="promo-content position-absolute text-white p-4">
                    <span class="badge badge-primary mb-2">Premium</span>
                    <h4 class="mb-2">Business Class Upgrade</h4>
                    <p>From only $199 extra</p>
                    <a href="<?= BASE_URL ?>/offers/business-upgrade.php" class="btn btn-sm btn-light">Learn More</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Explore Bangladesh Feature -->
    <div class="explore-country mb-5">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="mb-0">Explore Bangladesh</h2>
                    <a href="<?= BASE_URL ?>/destinations/bangladesh.php" class="text-primary">View All <i class="fas fa-arrow-right ml-2"></i></a>
                </div>
                <hr>
            </div>
        </div>
        
        <div class="row">
            <!-- Cox's Bazar -->
            <div class="col-md-4 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1626239889138-a7e4f971059e?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8Y294J3MlMjBiYXphcnxlbnwwfHwwfHx8MA%3D%3D" 
     alt="Cox's Bazar Beach" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-1">Cox's Bazar</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt mr-1"></i> Chittagong Division
                            </p>
                            <span class="price-tag">From $99</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sundarbans -->
            <div class="col-md-4 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1697401517543-0f453a29709d?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTV8fHN1bmRhcmJhbnxlbnwwfHwwfHx8MA%3D%3D" 
     alt="Sundarbans Mangrove Forest" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-1">Sundarbans</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt mr-1"></i> Khulna Division
                            </p>
                            <span class="price-tag">From $149</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sylhet -->
            <div class="col-md-4 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1730072787459-7d5f55be3422?q=80&w=1931&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                         alt="Tea Gardens of Sylhet" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-1">Sylhet</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt mr-1"></i> Sylhet Division
                            </p>
                            <span class="price-tag">From $79</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Popular International Destinations -->
    <div class="popular-destinations">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="mb-0">Popular Destinations</h2>
                    <a href="<?= BASE_URL ?>/destinations/" class="text-primary">View All <i class="fas fa-arrow-right ml-2"></i></a>
                </div>
                <hr>
            </div>
        </div>
        
        <div class="row">
            <!-- Dubai -->
            <div class="col-md-3 col-6 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1518684079-3c830dcef090?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Dubai" class="img-fluid w-100" style="height: 180px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-0">Dubai</h5>
                        <span class="small">From $299</span>
                    </div>
                </div>
            </div>
            
            <!-- Singapore -->
            <div class="col-md-3 col-6 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Singapore" class="img-fluid w-100" style="height: 180px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-0">Singapore</h5>
                        <span class="small">From $349</span>
                    </div>
                </div>
            </div>
            
            <!-- Bangkok -->
            <div class="col-md-3 col-6 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8YmFuZ2tva3xlbnwwfHwwfHx8MA%3D%3D" 
                         alt="Bangkok" class="img-fluid w-100" style="height: 180px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-0">Bangkok</h5>
                        <span class="small">From $249</span>
                    </div>
                </div>
            </div>
            
            <!-- Kuala Lumpur -->
            <div class="col-md-3 col-6 mb-4">
                <div class="destination-card position-relative rounded overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1596422846543-75c6fc197f07?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Kuala Lumpur" class="img-fluid w-100" style="height: 180px; object-fit: cover;">
                    <div class="destination-overlay position-absolute"></div>
                    <div class="destination-content position-absolute w-100 text-white p-3">
                        <h5 class="mb-0">Kuala Lumpur</h5>
                        <span class="small">From $279</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to action -->
    <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>/flights/search.php" class="btn btn-lg btn-outline-primary">Find Your Next Adventure</a>
    </div>
</div>

<!-- Services and Benefits Section -->
<div class="services-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-4 mb-3">Why Choose SkyHigh Airlines</h2>
            <div class="divider mx-auto mb-3"></div>
            <p class="text-muted">Experience the difference with our premium service</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="service-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="service-icon mb-3">
                        <i class="fas fa-star fa-3x text-warning"></i>
                    </div>
                    <h3 class="h4 mb-3">★ 4.8/5</h3>
                    <p class="text-muted mb-0">Over 2 million satisfied customers have rated our services as exceptional, making us the most trusted airline worldwide.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="service-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="service-icon mb-3">
                        <i class="fas fa-globe-americas fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">150+ Destinations</h3>
                    <p class="text-muted mb-0">Fly to over 150 destinations across 6 continents with our extensive global network of premium routes.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="service-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="service-icon mb-3">
                        <i class="fas fa-headset fa-3x text-success"></i>
                    </div>
                    <h3 class="h4 mb-3">24/7 Support</h3>
                    <p class="text-muted mb-0">Our dedicated customer support team is available round the clock to assist you with any inquiries or requirements.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Promotion/CTA Section -->
<div class="promo-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-4 mb-3">Join SkyHigh Rewards</h2>
                <p class="lead">Earn miles with every flight and enjoy exclusive benefits including priority boarding, lounge access, and free upgrades.</p>
                <a href="<?= BASE_URL ?>/rewards/signup.php" class="btn btn-lg btn-primary mt-3">Join Now</a>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80" 
                     alt="Business class passengers" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-4 mb-3">What Our Passengers Say</h2>
            <div class="divider mx-auto mb-3"></div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="testimonial-card p-4 bg-white rounded shadow-sm h-100">
                    <div class="testimonial-rating text-warning mb-3">
                        ★★★★★
                    </div>
                    <p class="testimonial-content mb-4">"The service on my flight to Tokyo was exceptional. The cabin crew went above and beyond to make me comfortable."</p>
                    <div class="testimonial-author">
                        <strong>Michael R.</strong> - Executive Traveler
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="testimonial-card p-4 bg-white rounded shadow-sm h-100">
                    <div class="testimonial-rating text-warning mb-3">
                        ★★★★★
                    </div>
                    <p class="testimonial-content mb-4">"The in-flight entertainment and meal options were incredible. Made my 12-hour flight feel like a breeze!"</p>
                    <div class="testimonial-author">
                        <strong>Sarah L.</strong> - Family Traveler
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="testimonial-card p-4 bg-white rounded shadow-sm h-100">
                    <div class="testimonial-rating text-warning mb-3">
                        ★★★★★
                    </div>
                    <p class="testimonial-content mb-4">"The SkyHigh rewards program has given me incredible value. The points accumulate quickly and the redemption options are fantastic."</p>
                    <div class="testimonial-author">
                        <strong>David T.</strong> - Frequent Flyer
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add custom CSS for new design elements -->
<style>
    /* Hero Section Styling */
    .hero-section {
        min-height: 500px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
        background-size: cover;
        background-position: center;
        z-index: 1;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 40, 0.5);
        z-index: 2;
    }
    
    /* Section Divider */
    .divider {
        height: 4px;
        width: 60px;
        background: #007bff;
    }
    
    /* Flight Card Styling */
    .flight-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .flight-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }
    
    .flight-route {
        position: relative;
    }
    
    .flight-line {
        height: 2px;
        background: #e9ecef;
        flex-grow: 1;
        margin: 0 10px;
    }
    
    .flight-path {
        display: flex;
        align-items: center;
        width: 60%;
    }
    
    .flight-path i {
        color: #007bff;
        font-size: 24px;
        transform: rotate(90deg);
    }
    
    /* Service Cards Styling */
    .service-card {
        transition: transform 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
    }
    
    .service-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Testimonial Cards */
    .testimonial-card {
        transition: transform 0.3s ease;
    }
    
    .testimonial-card:hover {
        transform: translateY(-5px);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .hero-section {
            height: 500px;
        }
        
        .hero-content h1 {
            font-size: 2.5rem;
        }
    }
</style>

<!-- Add a small helper function for flight duration calculation -->
<?php
// Helper function to calculate flight duration
function calculateFlightDuration($departure, $arrival) {
    $dep = new DateTime($departure);
    $arr = new DateTime($arrival);
    $interval = $dep->diff($arr);
    
    $hours = $interval->h + ($interval->days * 24);
    $minutes = $interval->i;
    
    return $hours . 'h ' . $minutes . 'm';
}
?>

<?php include 'includes/footer.php'; ?>