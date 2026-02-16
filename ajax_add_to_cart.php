<?php
include 'config.php';
session_start();

if ($_POST && isset($_POST['product_id']) && isset($_POST['action']) && $_POST['action'] == 'add') {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Validate product exists and check stock
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT id, stock_quantity FROM products WHERE id = ? LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    $available = intval($product['stock_quantity']);
    $currentInCart = isset($_SESSION['cart'][$product_id]) ? intval($_SESSION['cart'][$product_id]) : 0;
    $requestedTotal = $currentInCart + $quantity;

    if ($requestedTotal > $available) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Requested quantity exceeds available stock',
            'available' => $available,
            'inCart' => $currentInCart
        ]);
        exit;
    }

    // Add product to cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'cartCount' => array_sum($_SESSION['cart']),
        'message' => 'Product added to cart successfully'
    ]);
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
?>