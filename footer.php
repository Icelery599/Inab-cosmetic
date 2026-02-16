    <!-- Footer -->
    <footer class="bg-dark text-light mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-4">
                    <h5>Glamour Beauty</h5>
                    <p>Your trusted partner in beauty and cosmetics. We offer premium quality products for all your beauty needs.</p>
                    <div class="newsletter-form">
                        <h6>Subscribe to Newsletter</h6>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your email" id="newsletterEmail">
                            <button class="btn btn-primary" type="button" onclick="subscribeNewsletter()">Subscribe</button>
                        </div>
                        <div id="newsletterMessage"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Home</a></li>
                        <li><a href="products.php" class="text-light">Products</a></li>
                        <li><a href="opening_hours.php" class="text-light">Opening Hours</a></li>
                        <li><a href="contact.php" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Categories</h5>
                    <ul class="list-unstyled">
                        <li><a href="products.php?category=1" class="text-light">Skin Care</a></li>
                        <li><a href="products.php?category=2" class="text-light">Lip Gloss</a></li>
                        <li><a href="products.php?category=3" class="text-light">Baby Cosmetics</a></li>
                        <li><a href="products.php" class="text-light">All Products</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Info</h5>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> 123 Beauty Street<br>
                        Cosmetic City, CC 12345<br>
                        <i class="fas fa-phone"></i> (555) 123-4567<br>
                        <i class="fas fa-envelope"></i> info@glamourbeauty.com
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Glamour Beauty. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Secure payments • Free shipping over $50 • 30-day return policy</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Newsletter subscription
        function subscribeNewsletter() {
            const email = document.getElementById('newsletterEmail').value;
            const messageDiv = document.getElementById('newsletterMessage');
            
            if (!isValidEmail(email)) {
                messageDiv.innerHTML = '<div class="alert alert-danger mt-2">Please enter a valid email address.</div>';
                return;
            }
            
            // Simulate API call
            messageDiv.innerHTML = '<div class="alert alert-info mt-2">Subscribing...</div>';
            
            setTimeout(() => {
                messageDiv.innerHTML = '<div class="alert alert-success mt-2">Thank you for subscribing to our newsletter!</div>';
                document.getElementById('newsletterEmail').value = '';
            }, 1000);
        }
        
        // Back to top button
        const backToTopButton = document.createElement('button');
        backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopButton.className = 'btn btn-primary position-fixed';
        backToTopButton.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; display: none;';
        backToTopButton.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
        document.body.appendChild(backToTopButton);
        
        window.addEventListener('scroll', () => {
            backToTopButton.style.display = window.pageYOffset > 300 ? 'block' : 'none';
        });
        
        // Add loading state to all forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !form.classList.contains('no-loading')) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                submitBtn.disabled = true;
            }
        });
    </script>
</body>
</html>