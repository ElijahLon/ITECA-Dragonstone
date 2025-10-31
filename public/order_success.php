<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/header.php';

$db = db();

if (!isset($_GET['order_id'])) {
    echo '<div class="alert alert-danger mt-4 text-center">No order ID provided.</div>';
    include __DIR__ . '/inc/footer.php';
    exit;
}

$order_id = intval($_GET['order_id']);

// Get order details
$stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<div class="alert alert-danger mt-4 text-center">Order not found.</div>';
    include __DIR__ . '/inc/footer.php';
    exit;
}

// Get order items
$stmt = $db->prepare('
    SELECT oi.qty, oi.price, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">🎉 Order Successful!</h2>
        <p class="text-center text-success fs-5">Thank you for your purchase.</p>

        <h4>Order Summary</h4>
        <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
        <p><strong>Total Paid:</strong> R<?= number_format($order['total'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>

        <hr>

        <h4>Products</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price (each)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['qty']) ?></td>
                    <td>R<?= number_format($item['price'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <hr>

        <div class="text-center">
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
