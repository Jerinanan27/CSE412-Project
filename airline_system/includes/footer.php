    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="text-uppercase fw-bold mb-4">About <?= SITE_NAME ?></h5>
                    <p class="mb-4">We're revolutionizing travel with seamless booking experiences for flights and hotels worldwide. Our mission is to make your journeys unforgettable.</p>
                    <div class="d-flex">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="text-uppercase fw-bold mb-4">Book</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/flights/search.php" class="text-white text-decoration-none">Flights</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/hotels/" class="text-white text-decoration-none">Hotels</a></li>
                        
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="text-uppercase fw-bold mb-4">Help</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/faq.php" class="text-white text-decoration-none">FAQs</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/support.php" class="text-white text-decoration-none">Customer Support</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/feedback.php" class="text-white text-decoration-none">Feedback</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="text-uppercase fw-bold mb-4">Newsletter</h5>
                    <p>Subscribe for exclusive deals and travel tips</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email" required>
                            <button class="btn btn-primary" type="submit">Join</button>
                        </div>
                    </form>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone-alt me-2"></i>
                        <span>+1 (800) 123-4567</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope me-2"></i>
                        <span>contact@<?= strtolower(str_replace(' ', '', SITE_NAME)) ?>.com</span>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?= BASE_URL ?>/privacy.php" class="text-white text-decoration-none me-3">Privacy Policy</a>
                    <a href="<?= BASE_URL ?>/terms.php" class="text-white text-decoration-none me-3">Terms</a>
                    <a href="<?= BASE_URL ?>/cookies.php" class="text-white text-decoration-none">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Chatbot Elements -->
    <div class="chatbot-icon" onclick="toggleChatbot()">
        <i class="fas fa-comment-dots"></i>
    </div>
    <div class="chatbot-container" id="chatbotContainer">
        <div class="chatbot-header d-flex justify-content-between align-items-center">
            <span>Travel Assistant</span>
            <button class="btn btn-sm btn-light" onclick="toggleChatbot()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chatbot-body" id="chatbotBody">
            <div class="text-center py-4">
                <i class="fas fa-robot fa-3x text-primary mb-3"></i>
                <p class="mb-0">Hello! I'm your travel assistant. How can I help you today?</p>
            </div>
        </div>
        <div class="chatbot-footer">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Type your message...">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        function toggleChatbot() {
            const chatbot = document.getElementById('chatbotContainer');
            chatbot.style.display = chatbot.style.display === 'flex' ? 'none' : 'flex';
        }
        
        // Simple chatbot interaction
        document.querySelector('.chatbot-footer button').addEventListener('click', function() {
            const input = document.querySelector('.chatbot-footer input');
            const message = input.value.trim();
            if (message) {
                const chatbotBody = document.getElementById('chatbotBody');
                const userMessage = document.createElement('div');
                userMessage.className = 'mb-2 text-end';
                userMessage.innerHTML = `<span class="badge bg-primary">${message}</span>`;
                chatbotBody.appendChild(userMessage);
                
                // Simple bot response
                setTimeout(() => {
                    const botMessage = document.createElement('div');
                    botMessage.className = 'mb-2';
                    botMessage.innerHTML = `<span class="badge bg-secondary">Thanks for your message! Our team will get back to you soon.</span>`;
                    chatbotBody.appendChild(botMessage);
                    chatbotBody.scrollTop = chatbotBody.scrollHeight;
                }, 1000);
                
                input.value = '';
                chatbotBody.scrollTop = chatbotBody.scrollHeight;
            }
        });
    </script>
    <?php if (isset($custom_js)): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= $custom_js ?>"></script>
    <?php endif; ?>
</body>
</html>