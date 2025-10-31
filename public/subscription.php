<?php
session_start();

// Handle add to subscribe cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_subscribe'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['qty']);

    if (!isset($_SESSION['subscribe_cart'])) {
        $_SESSION['subscribe_cart'] = [];
    }

    if (isset($_SESSION['subscribe_cart'][$product_id])) {
        $_SESSION['subscribe_cart'][$product_id] += $qty;
    } else {
        $_SESSION['subscribe_cart'][$product_id] = $qty;
    }

    header('Location: subscription.php?added=1');
    exit;
}

// Handle remove from subscribe cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    if (isset($_SESSION['subscribe_cart'][$product_id])) {
        unset($_SESSION['subscribe_cart'][$product_id]);
    }
    header('Location: subscription.php');
    exit;
}

// Handle update subscribe cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subscribe_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $pid => $new_qty) {
            $pid = (int)$pid;
            $new_qty = max(0, (int)$new_qty);
            if ($new_qty > 0) {
                $_SESSION['subscribe_cart'][$pid] = $new_qty;
            } else {
                unset($_SESSION['subscribe_cart'][$pid]);
            }
        }
    }
    header('Location: subscription.php');
    exit;
}

require_once __DIR__ . '/inc/header.php';

$db = db();

// Get subscribe cart items
$subscribe_cart = $_SESSION['subscribe_cart'] ?? [];
$cart_items = [];
$cart_total = 0;
foreach ($subscribe_cart as $pid => $qty) {
    $stmt = $db->prepare('SELECT id, title, price, image FROM products WHERE id = ?');
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if ($product) {
        $product['qty'] = $qty;
        $product['line_total'] = $product['price'] * $qty;
        $cart_items[] = $product;
        $cart_total += $product['line_total'];
    }
}

// Get products from "Cleaning & Household Supplies" category
$products = $db->query("
    SELECT p.*, c.title as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = 'cleaning-household'
    ORDER BY p.title
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DragonStone Subscriptions</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }
    section {
      padding: 80px 0;
    }
    h1 {
      font-weight: 700;
      margin-bottom: 15px;
      color: #000;
    }
    p.lead {
      font-weight: 400;
      color: #555;
      margin-bottom: 50px;
    }
    .subscription-card {
      border-radius: 16px;
      overflow: hidden;
      transition: transform 0.3s, box-shadow 0.3s;
      background-color: #fff;
      border: none;
      position: relative;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .subscription-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .card-header {
      background: #000;
      color: #fff;
      font-weight: 700;
      font-size: 1.5rem;
      text-align: center;
      padding: 25px 10px;
    }
    .card-body {
      padding: 30px 25px;
    }
    .price {
      font-size: 2.25rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 25px;
      color: #000;
    }
    .features {
      list-style: none;
      padding: 0;
      margin-bottom: 25px;
    }
    .features li {
      padding: 12px 0;
      display: flex;
      align-items: center;
      font-size: 1rem;
      border-bottom: 1px solid #eee;
    }
    .features li i {
      font-size: 1.25rem;
      margin-right: 12px;
      color: #2e7d32;
    }
    .subscribe-btn {
      display: block;
      width: 100%;
      font-weight: 600;
      background: #000;
      border: none;
      border-radius: 10px;
      padding: 14px 0;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .subscribe-btn:hover {
      background: #333;
      transform: translateY(-2px);
    }
    .popular-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #ff6b35;
      color: #fff;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    .hero-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      padding: 100px 0;
      text-align: center;
    }
    .hero-section h1 {
      font-size: 3rem;
      margin-bottom: 20px;
    }
    .hero-section p {
      font-size: 1.2rem;
      margin-bottom: 0;
    }
    .testimonials {
      background: #fff;
      padding: 80px 0;
    }
    .testimonial-card {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 30px;
      margin: 20px 0;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .testimonial-card p {
      font-style: italic;
      color: #555;
    }
    .testimonial-card .author {
      font-weight: 600;
      margin-top: 15px;
      color: #000;
    }
    .return-home {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
      color: #555;
      font-size: 0.9rem;
      transition: 0.2s;
    }
    .return-home:hover {
      color: #000;
    }
    .card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .btn-success {
      background: #2e7d32;
      border: none;
      border-radius: 8px;
      font-weight: 600;
    }
    .btn-success:hover {
      background: #1b5e20;
    }
    .form-control {
      border-radius: 8px;
      border: 1px solid #ddd;
    }
    .form-control:focus {
      border-color: #000;
      box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<section class="hero-section">
  <div class="container">
    <h1>Join the Sustainable Revolution</h1>
    <p>Make a difference with every purchase. Eco-friendly products delivered monthly to your doorstep.</p>
  </div>
</section>

<div class="container-fluid py-5">
  <div class="row">
    <!-- Products Section -->
    <div class="col-lg-8">
      <h1 class="mb-4">Subscribe to Cleaning Supplies</h1>

      <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Product added to subscription cart!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="row">
        <?php foreach($products as $product): ?>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100">
            <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['title']) ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
              <p class="card-text text-muted"><?= htmlspecialchars($product['short_desc']) ?></p>
              <p class="card-text fw-bold">R<?= number_format($product['price'], 2) ?></p>

              <form method="post" class="mt-auto">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="input-group mb-2">
                  <span class="input-group-text">Qty</span>
                  <input type="number" name="qty" value="1" min="1" class="form-control">
                </div>
                <button type="submit" name="add_to_subscribe" class="btn btn-success w-100">Add to Subscribe Cart</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Subscribe Cart Sidebar -->
    <div class="col-lg-4">
      <div class="card sticky-top" style="top: 20px;">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Subscription Cart</h5>
        </div>
        <div class="card-body">
          <?php if (empty($cart_items)): ?>
            <p class="text-muted">Your subscription cart is empty.</p>
          <?php else: ?>
            <form method="post" id="subscribe-cart-form">
              <div class="mb-3">
                <label class="form-label fw-bold">Subscription Duration</label>
                <select name="duration" class="form-select" id="duration-select">
                  <option value="1">1 Month</option>
                  <option value="3">3 Months</option>
                  <option value="6">6 Months</option>
                  <option value="12">12 Months</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Payment Type</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="payment_type" id="full-payment" value="full" checked>
                  <label class="form-check-label" for="full-payment">
                    Pay Full Amount Now
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="payment_type" id="recurring-payment" value="recurring">
                  <label class="form-check-label" for="recurring-payment">
                    Set Up Recurring Monthly Payments
                  </label>
                </div>
              </div>

              <div class="mb-3">
                <h6>Products:</h6>
                <?php foreach($cart_items as $item): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <small class="text-muted d-block"><?= htmlspecialchars($item['title']) ?></small>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">Qty</span>
                      <input type="number" name="qty[<?= $item['id'] ?>]" value="<?= $item['qty'] ?>" min="1" class="form-control form-control-sm">
                    </div>
                  </div>
                  <div class="text-end">
                    <small class="text-muted">R<?= number_format($item['price'], 2) ?> each</small>
                    <div class="fw-bold">R<?= number_format($item['line_total'], 2) ?></div>
                    <a href="?remove=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>

              <hr>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold" id="total-label">Monthly Total:</span>
                <span class="fw-bold fs-5" id="monthly-total">R<?= number_format($cart_total, 2) ?></span>
              </div>

              <button type="submit" name="update_subscribe_cart" class="btn btn-outline-primary w-100 mb-2">Update Cart</button>
              <button type="button" id="proceed-btn" class="btn btn-success w-100">Proceed to Payment</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="testimonials">
  <div class="container">
    <h2 class="text-center mb-5">What Our Subscribers Say</h2>
    <div class="row">
      <div class="col-md-4">
        <div class="testimonial-card">
          <p>"The products are amazing and knowing they're eco-friendly makes me feel great. The subscription service is so convenient!"</p>
          <div class="author">- Sarah M.</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card">
          <p>"I've reduced my carbon footprint significantly. The EcoPoints system is a great incentive to stay subscribed."</p>
          <div class="author">- John D.</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card">
          <p>"Premium plan gives me access to the community hub where I can learn more about sustainable living. Highly recommend!"</p>
          <div class="author">- Emma L.</div>
        </div>
      </div>
    </div>
    <div class="text-center mt-4">
      <a href="index.php" class="return-home">← Back to Home</a>
    </div>
  </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Paystack Integration Script -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
// Update total display when payment type changes
document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const totalLabel = document.getElementById('total-label');
        const monthlyTotal = parseFloat(document.getElementById('monthly-total').textContent.replace('R', '').replace(',', ''));
        const duration = parseInt(document.getElementById('duration-select').value);

        if (this.value === 'full') {
            totalLabel.textContent = 'Total Amount:';
            const totalAmount = monthlyTotal * duration;
            document.getElementById('monthly-total').textContent = 'R' + totalAmount.toFixed(2);
        } else {
            totalLabel.textContent = 'Monthly Total:';
            document.getElementById('monthly-total').textContent = 'R' + monthlyTotal.toFixed(2);
        }
    });
});

// Update total when duration changes
document.getElementById('duration-select').addEventListener('change', function() {
    const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
    const totalLabel = document.getElementById('total-label');
    const monthlyTotal = parseFloat(document.getElementById('monthly-total').textContent.replace('R', '').replace(',', ''));
    const duration = parseInt(this.value);

    if (paymentType === 'full') {
        totalLabel.textContent = 'Total Amount:';
        const totalAmount = monthlyTotal * duration;
        document.getElementById('monthly-total').textContent = 'R' + totalAmount.toFixed(2);
    } else {
        totalLabel.textContent = 'First Month Amount:';
        document.getElementById('monthly-total').textContent = 'R' + monthlyTotal.toFixed(2);
    }
});

document.getElementById('proceed-btn').addEventListener('click', function() {
    const form = document.getElementById('subscribe-cart-form');
    const formData = new FormData(form);

    // Collect cart data
    const cartData = {
        duration: formData.get('duration'),
        payment_type: formData.get('payment_type'),
        items: []
    };

    // Get all qty inputs
    const qtyInputs = form.querySelectorAll('input[name^="qty["]');
    qtyInputs.forEach(input => {
        const productId = input.name.match(/qty\[(\d+)\]/)[1];
        const qty = parseInt(input.value);
        if (qty > 0) {
            cartData.items.push({
                product_id: productId,
                qty: qty
            });
        }
    });

    if (cartData.items.length === 0) {
        alert('Please add products to your subscription cart.');
        return;
    }

    // Calculate amount based on payment type
    const monthlyTotal = parseFloat(document.getElementById('monthly-total').textContent.replace('R', '').replace(',', ''));
    let totalAmount;
    if (cartData.payment_type === 'full') {
        totalAmount = monthlyTotal * parseInt(cartData.duration);
    } else {
        // For recurring payments, charge only the first month
        totalAmount = monthlyTotal;
    }

    // Initialize Paystack payment
    const handler = PaystackPop.setup({
        key: 'pk_test_f94c3e1eed48d5192c7a266598ad84965d9a1091',
        email: '<?= $user_email ?>',
        amount: totalAmount * 100, // Convert to kobo
        currency: 'ZAR',
        ref: 'DRG' + Math.floor((Math.random() * 1000000000) + 1),
        metadata: {
            custom_fields: [
                {
                    display_name: "Subscription Type",
                    variable_name: "type",
                    value: "subscription_cart"
                },
                {
                    display_name: "Duration",
                    variable_name: "duration",
                    value: cartData.duration
                },
                {
                    display_name: "Payment Type",
                    variable_name: "payment_type",
                    value: cartData.payment_type
                },
                {
                    display_name: "Items",
                    variable_name: "items",
                    value: JSON.stringify(cartData.items)
                }
            ]
        },
        callback: function(response) {
            // Redirect to verification with cart data
            const params = new URLSearchParams({
                reference: response.reference,
                uid: '<?= $user_id ?>',
                type: 'subscription_cart',
                duration: cartData.duration,
                payment_type: cartData.payment_type,
                items: JSON.stringify(cartData.items)
            });
            window.location.href = 'verify_payment.php?' + params.toString();
        },
        onClose: function() {
            alert('Payment window closed.');
        }
    });
    handler.openIframe();
});

function subscribe(plan, amount) {
  var handler = PaystackPop.setup({
    key: 'pk_test_f94c3e1eed48d5192c7a266598ad84965d9a1091',
    email: '<?= $user_email ?>',
    amount: amount * 100,
    currency: 'ZAR', // Keep as ZAR for Paystack API, but display as R
    ref: 'DRG' + Math.floor((Math.random() * 1000000000) + 1),
    metadata: {
      custom_fields: [
        {
          display_name: "Subscription Plan",
          variable_name: "plan",
          value: plan
        }
      ]
    },
    callback: function(response) {
      window.location = 'verify_payment.php?reference=' + response.reference + '&uid=<?= $user_id ?>&type=subscription&plan=' + encodeURIComponent(plan);
    },
    onClose: function() { alert('Payment window closed.'); }
  });
  handler.openIframe();
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
