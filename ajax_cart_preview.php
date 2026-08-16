<?php
include_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['cart'])) {
    $database = new Database();
    $db = $database->getConnection();

    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $query = "SELECT id, name, price FROM products WHERE id IN ($placeholders)";
    $stmt = $db->prepare($query);
    $stmt->execute(array_keys($_SESSION['cart']));

    $total = 0;
    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;

        echo '<div class="d-flex justify-content-between border-bottom py-2">';
        echo '<div>';
        echo '<small>' . $product['name'] . '</small><br>';
        echo '<small class="text-muted">Qty: ' . $quantity . '</small>';
        echo '</div>';
        echo '<small>$' . number_format($subtotal, 2) . '</small>';
        echo '</div>';
    }

    echo '<div class="d-flex justify-content-between mt-2 fw-bold">';
    echo '<span>Total:</span>';
    echo '<span>$' . number_format($total, 2) . '</span>';
    echo '</div>';
} else {
    echo '<p class="text-muted">Your cart is empty</p>';
}
?>