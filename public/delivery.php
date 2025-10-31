<?php
require_once __DIR__ . '/inc/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$db = db();
$user_id = $_SESSION['user_id'] ?? 0;
$user_email = $_SESSION['email'] ?? 'customer@example.com';

// Check if user is logged in; if not, redirect to login with message
if ($user_id == 0) {
    $_SESSION['redirect_after_login'] = 'delivery.php';
    header('Location: login.php?msg=' . urlencode('Please log in or register to proceed to checkout.'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Details - DragonStone</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body {
  background-color: #f8f9fa;
  font-family: 'Poppins', sans-serif;
  color: #333;
}
.delivery-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}
.delivery-form {
  background-color: #fff;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.btn-primary {
  background-color: #000;
  border-color: #000;
  border-radius: 8px;
  padding: 12px 24px;
  font-weight: 600;
}
.btn-primary:hover {
  background-color: #333;
  border-color: #333;
}
.btn-outline-secondary {
  border-color: #ddd;
  color: #555;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 500;
}
.btn-outline-secondary:hover {
  background-color: #f8f9fa;
  color: #000;
}
.form-control {
  border-radius: 8px;
  border: 1px solid #ddd;
  padding: 12px 16px;
}
.form-control:focus {
  border-color: #000;
  box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
}
.form-label {
  font-weight: 600;
  color: #000;
  margin-bottom: 8px;
}
.card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
</style>
</head>
<body>

<div class="delivery-container">
    <div class="row">
        <!-- Delivery Form -->
        <div class="col-md-8 delivery-form">
            <h2>Delivery Details</h2>

            <?php if ($user_id > 0): ?>
                <?php
                $stmt = $db->prepare('SELECT * FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC');
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                if (!empty($addresses)): ?>
                    <div class="mb-4">
                        <h5>Use Saved Address</h5>
                        <?php foreach ($addresses as $addr): ?>
                            <div class="form-check">
                                <input class="form-check-input saved-address-radio" type="radio" name="saved_address" value="<?= $addr['id'] ?>" id="addr_<?= $addr['id'] ?>">
                                <label class="form-check-label" for="addr_<?= $addr['id'] ?>">
                                    <?= htmlspecialchars($addr['address']) ?><?php if (!empty($addr['unit'])): ?>, <?= htmlspecialchars($addr['unit']) ?><?php endif; ?>, <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['postal_code']) ?> (<?= htmlspecialchars($addr['delivery_type']) ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form action="checkout.php" method="POST" id="deliveryForm">

                <div class="mb-3">
                    <label for="address" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="address" name="address" required placeholder="123 Greenway Blvd">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" required placeholder="Cape Town">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="postal" class="form-label">Postal Code</label>
                        <input type="text" class="form-control" id="postal" name="postal" required placeholder="8001">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="unit" class="form-label">Unit / Apartment Number (optional)</label>
                    <input type="text" class="form-control" id="unit" name="unit" placeholder="Unit 12B">
                </div>

                <div class="mb-4">
                    <label class="form-label d-block mb-2">Delivery Preference</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery_type" id="door" value="Door" required>
                        <label class="form-check-label" for="door">Deliver to my door</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery_type" id="curb" value="Curb">
                        <label class="form-check-label" for="curb">Deliver to curbside / entrance</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Delivery Instructions (optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any gate codes, delivery notes, etc."></textarea>
                </div>

                <!-- Save to Profile Button -->
                <button type="button" class="btn btn-outline-secondary mb-3 w-100" onclick="saveAddress()">💾 Save Address to Profile</button>

                <!-- Continue to Checkout -->
                <button type="submit" class="btn btn-primary w-100">Continue to Checkout</button>
            </form>

            <!-- Back Button -->
            <div class="mt-4">
                <a href="cart.php" class="text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
                    </svg>
                    Back to Cart
                </a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Order Summary</h5>
                    <?php
                    $cart = $_SESSION['cart'] ?? [];
                    $subtotal = 0;
                    foreach ($cart as $pid => $qty) {
                        $stmt = $db->prepare('SELECT name, price FROM products WHERE id = ?');
                        $stmt->bind_param('i', $pid);
                        $stmt->execute();
                        $product = $stmt->get_result()->fetch_assoc();
                        if ($product) {
                            $line_total = $product['price'] * $qty;
                            $subtotal += $line_total;
                            echo "<p>{$product['name']} x{$qty}: R" . number_format($line_total, 2) . "</p>";
                        }
                    }
                    ?>
                    <hr>
                    <p><strong>Subtotal: R<?= number_format($subtotal, 2) ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  // Placeholder JS function for saving address
  function saveAddress() {
    const formData = new FormData(document.getElementById('deliveryForm'));
    formData.append('user_id', <?= $user_id ?>);

    fetch('save_address.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('✅ Address saved to your profile.');
        location.reload(); // Reload to show new saved address
      } else {
        alert('⚠️ Failed to save address. Please try again.');
      }
    })
    .catch(() => alert('⚠️ An error occurred.'));
  }

  // Handle saved address selection
  document.querySelectorAll('.saved-address-radio').forEach(radio => {
    radio.addEventListener('change', function() {
      if (this.checked) {
        // Fetch address details and populate form
        fetch('get_address.php?id=' + this.value)
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              document.getElementById('address').value = data.address.address;
              document.getElementById('city').value = data.address.city;
              document.getElementById('postal').value = data.address.postal_code;
              document.getElementById('unit').value = data.address.unit || '';
              document.querySelector(`input[name="delivery_type"][value="${data.address.delivery_type}"]`).checked = true;
              document.getElementById('notes').value = data.address.notes || '';
            }
          })
          .catch(() => alert('⚠️ Error loading address.'));
      }
    });
  });
</script>

</body>
</html>
