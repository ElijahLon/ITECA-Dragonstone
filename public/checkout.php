<?php
require_once __DIR__ . '/inc/db.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) session_start();

$db = db();
$cart = $_SESSION['cart'] ?? [];

// Empty cart check
if (!$cart) {
    echo '<div class="container py-5"><div class="alert alert-info text-center shadow-sm rounded-3">Your cart is empty.</div></div>';
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
$subtotal = 0;
$discount = 0;
$promo_msg = '';
$promo_code = '';

// Get delivery details from POST or saved address
$saved_address_id = $_POST['saved_address'] ?? null;
if ($saved_address_id && $user_id > 0) {
    $stmt = $db->prepare('SELECT * FROM user_addresses WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $saved_address_id, $user_id);
    $stmt->execute();
    $saved_addr = $stmt->get_result()->fetch_assoc();
    if ($saved_addr) {
        $delivery_address = $saved_addr['address'];
        $delivery_city = $saved_addr['city'];
        $delivery_postal = $saved_addr['postal_code'];
        $delivery_unit = $saved_addr['unit'];
        $delivery_type = $saved_addr['delivery_type'];
        $delivery_notes = $saved_addr['notes'];
    } else {
        $delivery_address = $_POST['address'] ?? '';
        $delivery_city = $_POST['city'] ?? '';
        $delivery_postal = $_POST['postal'] ?? '';
        $delivery_unit = $_POST['unit'] ?? '';
        $delivery_type = $_POST['delivery_type'] ?? '';
        $delivery_notes = $_POST['notes'] ?? '';
    }
} else {
    $delivery_address = $_POST['address'] ?? '';
    $delivery_city = $_POST['city'] ?? '';
    $delivery_postal = $_POST['postal'] ?? '';
    $delivery_unit = $_POST['unit'] ?? '';
    $delivery_type = $_POST['delivery_type'] ?? '';
    $delivery_notes = $_POST['notes'] ?? '';
}

// Calculate subtotal
foreach ($cart as $pid => $qty) {
    $stmt = $db->prepare('SELECT name, price FROM products WHERE id = ?');
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if ($product) {
        $subtotal += $product['price'] * $qty;
    }
}

// Handle Promo Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $promo_code = trim($_POST['promo_code']);
    $stmt = $db->prepare("SELECT * FROM promos WHERE code = ? AND expires_at >= NOW() AND active = 1 LIMIT 1");
    $stmt->bind_param('s', $promo_code);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();

    if ($promo) {
        $discount = ($promo['discount_type'] === 'percent')
            ? ($subtotal * $promo['discount_value'] / 100)
            : min($promo['discount_value'], $subtotal);
        $promo_msg = "<div class='alert alert-success mt-3 fade show animate__animated animate__fadeIn'>Promo code <strong>" . htmlspecialchars($promo_code) . "</strong> applied! You saved R" . number_format($discount, 2) . ".</div>";
    } else {
        $promo_msg = "<div class='alert alert-danger mt-3 fade show animate__animated animate__shakeX'>Invalid or expired promo code.</div>";
    }
}

$total = $subtotal - $discount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - DragonStone</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #fff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
.checkout-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.order-summary { background-color: #f8f9fa; padding: 20px; border-radius: 4px; }
.payment-section { padding: 20px; }
.btn-primary { background-color: #000; border-color: #000; }
.btn-primary:hover { background-color: #333; border-color: #333; }
.table th, .table td { border: none; padding: 8px 0; }
.table thead th { border-bottom: 1px solid #dee2e6; font-weight: normal; }
.total-row { font-weight: bold; }
.delivery-address-box { background-color: #fff; border: 1px solid #e0e0e0; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
.delivery-address-box h5 { margin-bottom: 10px; font-weight: 600; }
.delivery-address-box p { margin-bottom: 5px; font-size: 14px; }
</style>
</head>
<body>

<div class="checkout-container">
    <div class="row">
        <!-- Payment Section -->
        <div class="col-md-8 payment-section">
            <h2>Checkout</h2>

            <!-- Delivery Address -->
            <?php if ($delivery_address): ?>
                <div class="delivery-address-box mt-4">
                    <h5>Delivery Address</h5>
                    <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($delivery_address) ?><?php if ($delivery_unit): ?>, <?= htmlspecialchars($delivery_unit) ?><?php endif; ?></p>
                    <p class="mb-1"><strong>City:</strong> <?= htmlspecialchars($delivery_city) ?>, <?= htmlspecialchars($delivery_postal) ?></p>
                    <p class="mb-1"><strong>Delivery:</strong> <?= htmlspecialchars($delivery_type) ?></p>
                    <?php if ($delivery_notes): ?>
                        <p class="mb-1"><strong>Notes:</strong> <?= htmlspecialchars($delivery_notes) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Promo Code -->
            <div class="mt-4">
                <h5>Promo Code</h5>
                <form method="post" class="d-flex">
                    <input type="text" name="promo_code" class="form-control me-2" placeholder="Enter promo code" value="<?= htmlspecialchars($promo_code) ?>">
                    <button type="submit" class="btn btn-dark">Apply</button>
                </form>
                <?= $promo_msg ?>
            </div>

            <!-- Payment Button -->
            <div class="mt-4">
                <form id="paymentForm">
                    <input type="hidden" id="email-address" value="customer@example.com" required />
                    <input type="hidden" id="amount" value="<?= $total * 100 ?>">
                    <input type="hidden" id="user_id" value="<?= $user_id ?>">
                    <button type="button" class="btn btn-primary w-100 py-3" onclick="payWithPaystack()">Proceed to Pay</button>
                </form>
            </div>

            <!-- Back Button -->
            <div class="mt-4">
                <a href="delivery.php" class="text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
                    </svg>
                    Back to Delivery
                </a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4 order-summary">
            <h5>Order Summary</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart as $pid => $qty):
                    $stmt = $db->prepare('SELECT name, price FROM products WHERE id = ?');
                    $stmt->bind_param('i', $pid);
                    $stmt->execute();
                    $product = $stmt->get_result()->fetch_assoc();
                    if ($product) {
                        $line_total = $product['price'] * $qty;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= $qty ?></td>
                        <td>R<?= number_format($line_total, 2) ?></td>
                    </tr>
                <?php } endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Subtotal</td>
                        <td>R<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php if ($discount > 0): ?>
                        <tr class="text-success">
                            <td colspan="2">Discount (<?= htmlspecialchars($promo_code) ?>)</td>
                            <td>-R<?= number_format($discount, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="2">Total</td>
                        <td>R<?= number_format($total, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function payWithPaystack() {
  let handler = PaystackPop.setup({
    key: 'pk_test_f94c3e1eed48d5192c7a266598ad84965d9a1091',
    email: document.getElementById('email-address').value,
    amount: document.getElementById('amount').value,
    currency: 'ZAR', // Keep as ZAR for Paystack API, but display as R
    ref: 'DRG' + Math.floor((Math.random() * 1000000000) + 1),
    callback: function(response) {
      window.location = 'verify_payment.php?reference=' + response.reference + '&uid=' + document.getElementById('user_id').value;
    },
    onClose: function() { alert('Payment window closed.'); }
  });
  handler.openIframe();
}
</script>
</body>
</html>
