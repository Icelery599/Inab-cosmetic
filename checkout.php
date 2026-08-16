<?php include 'header.php'; ?>
<?php include_once 'config.php'; ?>

<?php
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Handle form submission
if ($_POST && isset($_POST['place_order'])) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction();

        // Insert customer
        $customer_query = "INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)";
        $customer_stmt = $db->prepare($customer_query);
        $customer_stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address']
        ]);
        $customer_id = $db->lastInsertId();

        // Validate stock and calculate order total while locking rows
        $order_total = 0;
        $lockedProducts = [];
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Lock the product row for update to prevent race conditions
            $product_query = "SELECT id, price, stock_quantity FROM products WHERE id = ? FOR UPDATE";
            $product_stmt = $db->prepare($product_query);
            $product_stmt->execute([$product_id]);
            $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('Product not found (ID: ' . intval($product_id) . ')');
            }

            $available = intval($product['stock_quantity']);
            if ($quantity > $available) {
                throw new Exception('Insufficient stock for product: ' . $product['id']);
            }

            $order_total += $product['price'] * $quantity;
            $lockedProducts[] = [
                'id' => $product['id'],
                'quantity' => $quantity,
                'unit_price' => $product['price']
            ];
        }

        // Add shipping cost
        $shipping_cost = $order_total > 50 ? 0 : 5.99;
        $order_total += $shipping_cost;

        // Insert order
        $order_query = "INSERT INTO orders (customer_id, total_amount, shipping_address) VALUES (?, ?, ?)";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->execute([
            $customer_id,
            $order_total,
            $_POST['address']
        ]);
        $order_id = $db->lastInsertId();

        // Insert order items and decrement stock
        foreach ($lockedProducts as $p) {
            $order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
            $order_item_stmt = $db->prepare($order_item_query);
            $order_item_stmt->execute([
                $order_id,
                $p['id'],
                $p['quantity'],
                $p['unit_price']
            ]);

            // Decrement stock
            $update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $update_stmt = $db->prepare($update_stock);
            $update_stmt->execute([$p['quantity'], $p['id']]);
        }

        logPublicUserActivity('checkout_completed', 'Placed order #' . $order_id, null, $order_id);

        $db->commit();

        // Clear cart and show success message
        unset($_SESSION['cart']);
        $success = true;

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error processing order: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Checkout</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <h4>Order Placed Successfully!</h4>
            <p>Thank you for your order. Your order number is #<?php echo $order_id; ?></p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <?php logPublicUserActivity('checkout_started', 'Viewed checkout form'); ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Billing Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Full Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Address *</label>
                                    <textarea name="address" class="form-control" required></textarea>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5>Payment Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label>Card Number</label>
                                        <input type="text" class="form-control" placeholder="1234 5678 9012 3456">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Expiry Date</label>
                                            <input type="text" class="form-control" placeholder="MM/YY">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>CVV</label>
                                            <input type="text" class="form-control" placeholder="123">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="place_order" class="btn btn-success btn-lg w-100 mt-4">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $subtotal = 0;
                        foreach ($_SESSION['cart'] as $product_id => $quantity) {
                            $database = new Database();
                            $db = $database->getConnection();
                            $query = "SELECT name, price FROM products WHERE id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$product_id]);
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            $item_total = $product['price'] * $quantity;
                            $subtotal += $item_total;

                            echo '<p>' . $product['name'] . ' x ' . $quantity . ' - $' . number_format($item_total, 2) . '</p>';
                        }

                        $shipping = $subtotal > 50 ? 0 : 5.99;
                        $total = $subtotal + $shipping;
                        ?>
                        <hr>
                        <p>Subtotal: $<?php echo number_format($subtotal, 2); ?></p>
                        <p>Shipping: $<?php echo number_format($shipping, 2); ?></p>
                        <h6>Total: $<?php echo number_format($total, 2); ?></h6>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6>Special Offers</h6>
                        <ul class="small">
                            <li>Free shipping on orders over $50</li>
                            <li>Use code WELCOME10 for 10% off</li>
                            <li>Refer a friend and get $15 credit</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>