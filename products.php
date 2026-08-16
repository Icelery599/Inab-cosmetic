<?php include 'header.php'; ?>
<?php include_once 'config.php'; ?>

<div class="container mt-4">
    <h2>Our Products</h2>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search products..." id="searchInput">
                <button class="btn btn-outline-secondary" type="button" onclick="searchProducts()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="sortSelect" onchange="sortProducts()">
                <option value="name_asc">Sort by Name (A-Z)</option>
                <option value="name_desc">Sort by Name (Z-A)</option>
                <option value="price_asc">Sort by Price (Low to High)</option>
                <option value="price_desc">Sort by Price (High to Low)</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="priceFilter" onchange="filterByPrice()">
                <option value="all">All Prices</option>
                <option value="0-25">$0 - $25</option>
                <option value="25-50">$25 - $50</option>
                <option value="50-100">$50 - $100</option>
                <option value="100+">$100+</option>
            </select>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Categories</h5>
                </div>
                <div class="card-body">
                    <?php
                    $database = new Database();
                    $db = $database->getConnection();

                    $query = "SELECT * FROM categories";
                    $stmt = $db->prepare($query);
                    $stmt->execute();

                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $active = (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : '';
                        echo '<a href="products.php?category=' . $category['id'] . '" class="list-group-item ' . $active . '">' . $category['name'] . '</a>';
                    }
                    ?>
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Price Range</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Min Price: $<span id="minPriceValue">0</span></label>
                        <input type="range" class="form-range" min="0" max="200" value="0" id="minPrice">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Price: $<span id="maxPriceValue">200</span></label>
                        <input type="range" class="form-range" min="0" max="200" value="200" id="maxPrice">
                    </div>
                    <button class="btn btn-sm btn-primary w-100" onclick="applyPriceRange()">Apply Range</button>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div class="row" id="productsGrid">
                <?php
                $whereClause = "";
                $params = [];

                if (isset($_GET['category']) && !empty($_GET['category'])) {
                    $whereClause = "WHERE p.category_id = ?";
                    $params[] = $_GET['category'];
                }

                $query = "SELECT p.*, c.name as category_name
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         $whereClause
                         ORDER BY p.created_at DESC";

                $stmt = $db->prepare($query);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0) {
                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '
                        <div class="col-lg-4 col-md-6 mb-4 product-card position-relative" data-price="' . $product['price'] . '" data-name="' . strtolower($product['name']) . '">
                            <div class="card h-100">
                                ' . (intval($product['stock_quantity']) <= 0 ? '<span class="stock-badge">Out of stock</span>' : '') . '
                                <img src="https://via.placeholder.com/300x200?text=' . urlencode($product['name']) . '"
                                     class="card-img-top product-image" alt="' . $product['name'] . '">
                                <div class="card-body">
                                    <h5 class="card-title">' . $product['name'] . '</h5>
                                    <p class="card-text">' . $product['description'] . '</p>
                                    <p class="card-text"><strong>Category:</strong> ' . $product['category_name'] . '</p>
                                    <h6 class="text-primary">$' . $product['price'] . '</h6>
                                </div>
                                <div class="card-footer">
                                    <form method="post" action="cart.php" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="' . $product['id'] . '">
                                        <input type="hidden" name="action" value="add">
                                        <div class="input-group">
                                            ' . (
                                                intval($product['stock_quantity']) > 0
                                                ? '<input type="number" name="quantity" value="1" min="1" max="' . $product['stock_quantity'] . '" class="form-control">'
                                                : '<input type="number" name="quantity" value="0" min="0" class="form-control" disabled>'
                                            ) . '
                                            ' . (
                                                intval($product['stock_quantity']) > 0
                                                ? '<button type="submit" class="btn btn-primary add-to-cart-btn"><i class="fas fa-cart-plus"></i></button>'
                                                : '<button type="button" class="btn btn-secondary" disabled>Out of stock</button>'
                                            ) . '
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="col-12"><p>No products found.</p></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Product search functionality
    function searchProducts() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
            const productName = card.getAttribute('data-name');
            if (productName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Sort products
    function sortProducts() {
        const sortBy = document.getElementById('sortSelect').value;
        const productsGrid = document.getElementById('productsGrid');
        const productCards = Array.from(document.querySelectorAll('.product-card'));

        productCards.sort((a, b) => {
            const priceA = parseFloat(a.getAttribute('data-price'));
            const priceB = parseFloat(b.getAttribute('data-price'));
            const nameA = a.getAttribute('data-name');
            const nameB = b.getAttribute('data-name');

            switch (sortBy) {
                case 'name_asc':
                    return nameA.localeCompare(nameB);
                case 'name_desc':
                    return nameB.localeCompare(nameA);
                case 'price_asc':
                    return priceA - priceB;
                case 'price_desc':
                    return priceB - priceA;
                default:
                    return 0;
            }
        });

        // Re-append sorted cards
        productCards.forEach(card => productsGrid.appendChild(card));
    }

    // Price range filtering
    function applyPriceRange() {
        const minPrice = parseFloat(document.getElementById('minPrice').value);
        const maxPrice = parseFloat(document.getElementById('maxPrice').value);
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
            const price = parseFloat(card.getAttribute('data-price'));
            if (price >= minPrice && price <= maxPrice) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Update price range values
    document.getElementById('minPrice').addEventListener('input', function() {
        document.getElementById('minPriceValue').textContent = this.value;
    });

    document.getElementById('maxPrice').addEventListener('input', function() {
        document.getElementById('maxPriceValue').textContent = this.value;
    });

    // AJAX add to cart
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartForms = document.querySelectorAll('.add-to-cart-form');

        addToCartForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const addToCartBtn = this.querySelector('.add-to-cart-btn') || this.querySelector('button[type="submit"]');

                // Show loading state (if button exists)
                if (addToCartBtn) {
                    addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    addToCartBtn.disabled = true;
                }

                fetch('ajax_add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Product added to cart!', 'success');
                        updateCartCount(data.cartCount);

                        // Refresh cart preview immediately and show it briefly (uses header.js helper)
                        if (typeof loadAndShowCartPreview === 'function') {
                            loadAndShowCartPreview(true);
                        } else {
                            // fallback: attempt direct fetch
                            fetch('ajax_cart_preview.php')
                                .then(res => res.text())
                                .then(html => {
                                    const cartPreview = document.getElementById('cartPreview');
                                    document.getElementById('cartPreviewItems').innerHTML = html;
                                    if (cartPreview) cartPreview.classList.add('visible');
                                    setTimeout(() => { if (cartPreview) cartPreview.classList.remove('visible'); }, 4000);
                                })
                                .catch(() => {});
                        }

                    } else {
                        // Show more specific server error if provided
                        const msg = data.message ? data.message : 'Error adding product to cart';
                        showToast(msg, 'error');
                    }
                })
                .catch(error => {
                    showToast('Network error occurred', 'error');
                })
                .finally(() => {
                    // Restore button state
                    addToCartBtn.innerHTML = '<i class="fas fa-cart-plus"></i>';
                    addToCartBtn.disabled = false;
                });
            });
        });
    });

    // Real-time search with debounce
    document.getElementById('searchInput').addEventListener('input',
        debounce(searchProducts, 300)
    );
</script>

<?php include 'footer.php'; ?>