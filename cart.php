<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<?php
// Handle cart actions
if ($_POST) {
    if (isset($_POST['action'])) {
        $product_id = $_POST['product_id'];
        
        switch ($_POST['action']) {
            case 'add':
                $quantity = $_POST['quantity'];
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                break;
                
            case 'update':
                $quantity = $_POST['quantity'];
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                break;
                
            case 'remove':
                unset($_SESSION['cart'][$product_id]);
                break;
        }
    }
}

// Calculate total
$total = 0;
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $query = "SELECT * FROM products WHERE id IN ($placeholders)";
    $stmt = $db->prepare($query);
    $stmt->execute(array_keys($_SESSION['cart']));
    
    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<div class="container mt-4">
    <h2>Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="products.php">Continue shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo $item['product']['name']; ?></td>
                            <td>$<?php echo $item['product']['price']; ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['product']['stock_quantity']; ?>" 
                                           class="form-control form-control-sm" style="width: 80px;" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <p>Subtotal: $<?php echo number_format($total, 2); ?></p>
                        <p>Shipping: $<?php echo $total > 50 ? '0.00' : '5.99'; ?></p>
                        <hr>
                        <h6>Total: $<?php echo number_format($total > 50 ? $total : $total + 5.99, 2); ?></h6>
                        
                        <?php if ($total < 50): ?>
                            <div class="alert alert-warning mt-3">
                                Add $<?php echo number_format(50 - $total, 2); ?> more for free shipping!
                            </div>
                        <?php endif; ?>
                        
                        <a href="checkout.php" class="btn btn-success btn-lg w-100 mt-3">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>