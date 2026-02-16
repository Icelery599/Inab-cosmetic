<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate total items in cart
$totalItems = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inab Beauty Cosmetics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .promo-banner {
            background: #ff6b6b;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            position: relative;
        }
        .promo-banner .close {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
        }
        .category-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 20;
            padding: 0.35rem 0.6rem;
            font-size: 0.85rem;
            border-radius: 0.4rem;
            background: rgba(220,53,69,0.95);
            color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }
        .cart-preview {
            position: absolute;
            right: 0;
            top: 100%;
            width: 320px;
            background: white;
            border: 1px solid #ddd;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity 220ms ease, transform 220ms ease, visibility 220ms;
        }
        .cart-preview.visible {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
    </style>
</head>
<body>
    <!-- Promo Banner -->
    <div class="promo-banner" id="promoBanner">
        🎉 SPECIAL OFFER: Get 20% off on your first order! Use code: WELCOME10 🎉
        <button class="close" onclick="closePromoBanner()">&times;</button>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container">
        <div class="toast align-items-center text-white bg-success border-0" id="successToast">
            <div class="d-flex">
                <div class="toast-body" id="successMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        
        <div class="toast align-items-center text-white bg-danger border-0" id="errorToast">
            <div class="d-flex">
                <div class="toast-body" id="errorMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-spa"></i> Glamour Beauty
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php?category=1">Skin Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php?category=2">Lip Gloss</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php?category=3">Baby Cosmetics</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">All Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="opening_hours.php">Opening Hours</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <div class="navbar-nav position-relative">
                    <a class="nav-link cart-icon" href="cart.php" onmouseover="showCartPreview()" onmouseout="hideCartPreview()">
                        <i class="fas fa-shopping-cart"></i> Cart 
                        <span class="badge bg-danger" id="cartCount"><?php echo array_sum($_SESSION['cart']); ?></span>
                    </a>
                    <div class="cart-preview" id="cartPreview" onmouseover="showCartPreview()" onmouseout="hideCartPreview()">
                        <div class="p-3">
                            <h6>Cart Preview</h6>
                            <div id="cartPreviewItems">
                                <!-- Cart items will be loaded here -->
                            </div>
                            <a href="cart.php" class="btn btn-primary w-100 mt-2">View Cart</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Global utility functions
        function showToast(message, type = 'success') {
            const toastElement = type === 'success' ? document.getElementById('successToast') : document.getElementById('errorToast');
            const messageElement = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
            
            messageElement.textContent = message;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }

        function closePromoBanner() {
            document.getElementById('promoBanner').style.display = 'none';
        }

        // Load cart preview HTML and optionally show it
        function loadAndShowCartPreview(autoHide = false) {
            const cartCount = document.getElementById('cartCount').textContent;
            const cartPreview = document.getElementById('cartPreview');

            if (parseInt(cartCount) > 0) {
                fetch('ajax_cart_preview.php')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('cartPreviewItems').innerHTML = data;
                        cartPreview.classList.add('visible');
                        if (autoHide) setTimeout(() => cartPreview.classList.remove('visible'), 4000);
                    })
                    .catch(() => {
                        // ignore preview load errors
                    });
            }
        }

        // Show preview on hover (or programmatically via loadAndShowCartPreview)
        function showCartPreview() {
            loadAndShowCartPreview(false);
        }

        // Hide preview with slight delay to allow mouse movement into preview
        let _hideCartTimer = null;
        function hideCartPreview() {
            clearTimeout(_hideCartTimer);
            _hideCartTimer = setTimeout(() => {
                document.getElementById('cartPreview').classList.remove('visible');
            }, 300);
        }

        // Update cart count
        function updateCartCount(count) {
            document.getElementById('cartCount').textContent = count;
        }

        // Form validation
        function validateForm(form) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }

        // Price formatting
        function formatPrice(price) {
            return '$' + parseFloat(price).toFixed(2);
        }

        // Debounce function for search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>