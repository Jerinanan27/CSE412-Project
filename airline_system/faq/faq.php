<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

include '../includes/header.php';
?>

<!-- FAQ Section -->
<div class="container mt-5 mb-5">
    <h2 class="mb-4 text-center text-success fw-bold">Frequently Asked Questions</h2>
    <p class="text-center text-muted mb-4">Find answers to your travel queries below or use the search bar to get started.</p>

    <!-- Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-success text-white"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="faqSearch" placeholder="Search FAQs (e.g., visa, payment, flight)" aria-label="Search FAQs">
            </div>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="accordion" id="faqAccordion">
        <!-- Flight Booking FAQ -->
        <div class="accordion-item faq-item" data-keywords="flight booking domestic international Dhaka Cox’s Bazar cancellation">
            <h2 class="accordion-header" id="flightBooking">
                <button class="accordion-button bg-light text-success" type="button" data-bs-toggle="collapse" data-bs-target="#flightBookingContent" aria-expanded="true" aria-controls="flightBookingContent">
                    <i class="fas fa-plane me-2"></i> Flight Booking
                </button>
            </h2>
            <div id="flightBookingContent" class="accordion-collapse collapse show" aria-labelledby="flightBooking" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <div class="faq-item mb-3">
                        <h6>How do I book a domestic flight in Bangladesh?</h6>
                        <p>Search for flights using our form, selecting routes like Dhaka to Cox’s Bazar or Chattogram. Choose your travel class (Economy or Business) and complete the booking with bKash, Nagad, or card payments.</p>
                    </div>
                    <div class="faq-item mb-3">
                        <h6>What payment methods are accepted?</h6>
                        <p>We accept bKash, Nagad, Rocket, major credit/debit cards, and bank transfers. Payments must be completed during booking.</p>
                    </div>
                    <div class="faq-item mb-3">
                        <h6>What is the cancellation policy for flights?</h6>
                        <p>Cancellation policies depend on the fare type. Check your booking confirmation for details. Domestic flights may have lower fees, while international cancellations may incur higher charges.</p>
                    </div>
                    <div class="faq-item">
                        <h6>How do I check my flight status?</h6>
                        <p>Enter your booking reference or flight number (e.g., BG123 for Biman Bangladesh) on our Flight Status page to get real-time updates.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hotel Booking FAQ -->
        <div class="accordion-item faq-item" data-keywords="hotel booking resort check-in cancellation Cox’s Bazar Saint Martin">
            <h2 class="accordion-header" id="hotelBooking">
                <button class="accordion-button collapsed bg-light text-success" type="button" data-bs-toggle="collapse" data-bs-target="#hotelBookingContent" aria-expanded="false" aria-controls="hotelBookingContent">
                    <i class="fas fa-hotel me-2"></i> Hotel Booking
                </button>
            </h2>
            <div id="hotelBookingContent" class="accordion-collapse collapse" aria-labelledby="hotelBooking" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <div class="faq-item mb-3">
                        <h6>How do I book a hotel in Cox’s Bazar or Saint Martin?</h6>
                        <p>Search for hotels in popular destinations like Cox’s Bazar or Saint Martin using our booking system. Select your check-in dates and pay via bKash, Nagad, or cards.</p>
                    </div>
                    <div class="faq-item mb-3">
                        <h6>What are the check-in and check-out times?</h6>
                        <p>Standard check-in is 2:00 PM, and check-out is 12:00 PM. Resorts in Bangladesh may offer flexible timings; check with the property.</p>
                    </div>
                    <div class="faq-item mb-3">
                        <h6>How can I modify my hotel booking?</h6>
                        <p>Modify your booking via your account dashboard. Changes depend on availability and may incur fees, especially for peak seasons like winter in Cox’s Bazar.</p>
                    </div>
                    <div class="faq-item">
                        <h6>What is the cancellation policy for hotels?</h6>
                        <p>Cancellation policies vary by hotel. Most require cancellations 48 hours before check-in to avoid charges. Check your confirmation for details.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visa and General FAQ -->
        <div class="accordion-item faq-item" data-keywords="visa general account support baggage payment">
            <h2 class="accordion-header" id="general">
                <button class="accordion-button collapsed bg-light text-success" type="button" data-bs-toggle="collapse" data-bs-target="#generalContent" aria-expanded="false" aria-controls="generalContent">
                    <i class="fas fa-info-circle me-2"></i>General Information
                </button>
            </h2>
            <div id="generalContent" class="accordion-collapse collapse" aria-labelledby="general" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    
                    <div class="faq-item mb-3">
                        <h6>How can I contact customer support in Bangladesh?</h6>
                        <p>Reach us via our contact form, call our hotline (+880 1234-567890), or message us on WhatsApp. Our Dhaka-based team is available 24/7.</p>
                    </div>
                    <div class="faq-item mb-3">
                        <h6>What is the baggage policy for domestic flights?</h6>
                        <p>Domestic flights (e.g., US-Bangla, Novoair) allow 20kg for Economy and 30kg for Business. Extra baggage can be purchased at the airport or online.</p>
                    </div>
                    <div class="faq-item">
                        <h6>How do I create an account?</h6>
                        <p>Register on our website with your email or mobile number. Verify your account to book flights, hotels, or visa services.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom JavaScript for Search Functionality -->
<script>
    document.getElementById('faqSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const keywords = item.getAttribute('data-keywords')?.toLowerCase() || '';
            const content = item.textContent.toLowerCase();
            const accordionCollapse = item.querySelector('.accordion-collapse') || item;

            if (searchTerm === '' || keywords.includes(searchTerm) || content.includes(searchTerm)) {
                item.style.display = 'block';
                accordionCollapse.classList.add('show'); // Open matching sections
            } else {
                item.style.display = 'none';
                accordionCollapse.classList.remove('show'); // Close non-matching sections
            }
        });
    });
</script>

<style>
    /* Custom FAQ Styling */
    .accordion-item {
        border: none;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .accordion-button {
        background-color: #e6f5f1 !important;
        color: #006A4E !important;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 1.25rem;
        transition: all 0.3s ease;
    }

    .accordion-button:not(.collapsed) {
        background-color: #006A4E !important;
        color: white !important;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: #F42A41;
    }

    .accordion-body {
        background-color: white;
        padding: 1.5rem;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .faq-item h6 {
        color: #006A4E;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .faq-item p {
        color: #333;
        margin-bottom: 0;
    }

    .text-success {
        color: #006A4E !important;
    }

    .input-group-text {
        border: none;
        border-radius: 8px 0 0 8px;
    }

    .form-control {
        border: 1px solid #006A4E;
        border-radius: 0 8px 8px 0;
        padding: 0.75rem;
        font-size: 0.95rem;
    }

    .form-control:focus {
        border-color: #F42A41;
        box-shadow: 0 0 5px rgba(244, 42, 65, 0.3);
    }

    @media (max-width: 576px) {
        .accordion-button {
            font-size: 1rem;
            padding: 1rem;
        }

        .accordion-body {
            padding: 1rem;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>