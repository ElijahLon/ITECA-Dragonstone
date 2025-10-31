<?php
require_once __DIR__.'/inc/db.php';
require_once __DIR__.'/inc/header.php';

$db = db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare('SELECT p.*, c.title as category_title 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
    echo '<div class="alert alert-danger">Product not found</div>';
    include __DIR__.'/inc/footer.php';
    exit;
}

$base_carbon = (float)($p['carbon_material'] ?? 0) 
             + (float)($p['carbon_manufacture'] ?? 0) 
             + (float)($p['carbon_packaging'] ?? 0);
?>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
      <img src="<?= htmlspecialchars($p['image']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($p['title']) ?>" style="max-width: 100%; height: auto;">
    </div>

    <div class="col-md-6">
      <h1 class="mb-2" style="font-weight: 600; color: #000;"><?= htmlspecialchars($p['title']) ?></h1>
      <p class="text-muted mb-3" style="font-size: 1.1rem;"><?= htmlspecialchars($p['category_title']) ?></p>
      <h3 class="mb-3" style="color: #2e7d32; font-weight: 700;"><?= number_format($p['price'], 2) ?> <?= htmlspecialchars($p['currency']) ?></h3>
      <p class="mb-4" style="line-height: 1.6; color: #555;"><?= htmlspecialchars($p['short_desc'] ?? '') ?></p>

      <!-- Carbon Calculator Card -->
      <div class="card border-0 shadow-sm mb-4" style="background: #f3efe7; border-radius: 12px;">
        <div class="card-body p-4">
          <h5 class="card-title mb-3" style="color: #000; font-weight: 600;">🌱 Carbon Footprint Calculator</h5>
          <p class="mb-3" style="color: #555;">Base lifecycle emissions: <strong style="color: #2e7d32;"><?= number_format($base_carbon, 3) ?> kg CO₂e</strong></p>
          <div class="mb-3">
            <label for="distance" class="form-label fw-bold" style="color: #000;">Shipping Distance (km)</label>
            <input id="distance" type="number" class="form-control" value="100" min="0" style="border-radius: 8px; border: 1px solid #ddd;">
          </div>
          <div class="p-3 rounded" style="background: #fff; border: 1px solid #e9ecef;">
            <p class="mb-0 fw-bold" style="color: #000; font-size: 1.1rem;">
              Total Estimated Carbon: <span id="carbonTotal" style="color: #2e7d32; font-size: 1.2rem;"><?= number_format($base_carbon + (100 * $p['carbon_shipping_per_km']), 3) ?> kg CO₂e</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Add to Cart Form -->
      <form id="addToCartForm" method="post" action="cart.php" class="mb-4">
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <div class="mb-3">
          <label for="qty" class="form-label fw-bold" style="color: #000;">Quantity</label>
          <input type="number" name="qty" value="1" class="form-control" min="1" max="<?= $p['stock'] ?>" id="qty" style="border-radius: 8px; border: 1px solid #ddd;">
        </div>
        <button type="submit" class="btn btn-dark btn-lg w-100 py-3" style="border-radius: 8px; font-weight: 600; background: #000; border: none;">Add to Cart</button>
      </form>
    </div>
  </div>
</div>

<!-- 🛒 Slide-Out Cart Panel -->
<div id="cartPanel" class="cart-panel">
  <div class="cart-header">
    <h5 class="mb-0" style="font-weight: 600; color: #000;">Your Cart</h5>
    <button id="closeCart" class="btn-close" style="filter: invert(1);"></button>
  </div>
  <div id="cartItems" class="cart-body"></div>
  <div class="cart-actions mt-3">
    <button id="continue-shopping" class="btn btn-outline-secondary w-100 mb-2" style="border-radius: 8px; border-color: #ddd; color: #555;">
      Continue Shopping
    </button>
    <div class="cart-footer">
      <h6 class="mb-3" style="font-weight: 600; color: #000;">Total: R<span id="cartTotal">0.00</span></h6>
      <a href="delivery.php" class="btn btn-dark w-100 py-2" style="border-radius: 8px; font-weight: 600; background: #000; border: none;">Proceed to Checkout</a>
    </div>
  </div>
</div>

<!-- 🔥 Styling -->
<style>
.cart-panel {
  position: fixed;
  top: 0;
  right: -400px;
  width: 350px;
  height: 100%;
  background: #fff;
  box-shadow: -3px 0 15px rgba(0,0,0,0.2);
  transition: right 0.4s ease;
  z-index: 1050;
  display: flex;
  flex-direction: column;
  font-family: 'Poppins', sans-serif;
}

.cart-panel.open {
  right: 0;
}

.cart-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
}

.cart-body {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
}

.cart-footer {
  padding: 15px;
  border-top: 1px solid #eee;
  background: #fafafa;
}
</style>

<!-- ✅ Script -->
<script>
$('#distance').on('input', function(){
  var d = parseFloat($(this).val()) || 0;
  var rate = <?= floatval($p['carbon_shipping_per_km']) ?>;
  var base = <?= floatval($base_carbon) ?>;
  var total = base + (d * rate);
  $('#carbonTotal').text(total.toFixed(3));
});

// 🛒 Add to Cart (AJAX)
$('#addToCartForm').on('submit', function(e) {
  e.preventDefault();
  var form = $(this);
  $.post('cart.php', form.serialize(), function() {
    updateCartPanel();
    $('#cartPanel').addClass('open');
  });
});

// 🧾 Load Cart Items (AJAX)
function updateCartPanel() {
  $.get('cart_summary.php', function(data) {
    $('#cartItems').html(data.items_html);
    $('#cartTotal').text(data.total.toFixed(2));
  }, 'json');
}

$('#closeCart').on('click', function() {
  $('#cartPanel').removeClass('open');
});
 // Close the slide-out panel when "Continue Shopping" is clicked
  document.getElementById('continue-shopping').addEventListener('click', function () {
    const panel = document.querySelector('.cart-panel'); // adjust selector if needed
    panel.classList.remove('open'); // hides the slide-out
  });
</script>

<?php include __DIR__.'/inc/footer.php'; ?>
