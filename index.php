<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<div class="hero-section">
    <div class="container">
        <h1 class="display-4">Welcome to Inab Beauty</h1>
        <p class="lead">Discover the perfect cosmetics for your beauty routine</p>
        <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
    </div>
</div>

<div class="container mt-5">
    <!-- Featured Categories -->
    <h2 class="text-center mb-4">Our Categories</h2>
    <div class="row">
        <?php
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM categories";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '
            <div class="col-md-4">
                <div class="card category-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">' . $row['name'] . '</h5>
                        <p class="card-text">' . $row['description'] . '</p>
                        <a href="products.php?category=' . $row['id'] . '" class="btn btn-outline-primary">View Products</a>
                    </div>
                </div>
            </div>';
        }
        ?>
    </div>

    <!-- Special Offers -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-gift"></i> Special Offers</h4>
                <ul>
                    <li>Free shipping on orders over $50</li>
                    <li>Buy 2 lip glosses, get 1 free</li>
                    <li>20% off on all baby products this month</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>