    </main>
    <footer class="bg-dark text-white py-4 mt-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p><?= SITE_NAME ?> - Your trusted airline for seamless travel experiences.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/flights/search.php" class="text-white">Book Flights</a></li>
                        <li><a href="<?= BASE_URL ?>/status.php" class="text-white">Flight Status</a></li>
                        <li><a href="<?= BASE_URL ?>/contact.php" class="text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect</h5>
                    <a href="#" class="text-white mr-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white mr-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <hr class="bg-light">
            <p class="text-center mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="<?= BASE_URL ?>/assets/js/jquery-3.5.1.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($custom_js)): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= $custom_js ?>"></script>
    <?php endif; ?>
</body>
</html>