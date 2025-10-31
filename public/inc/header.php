<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/auth.php';
$user = current_user();

// Check for active subscription
$subscription = null;
if ($user) {
    $stmt = db()->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND active = 1 ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $subscription = $result->fetch_assoc();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>DragonStone</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<?php
$__body_classes = 'bg-white';
if (!empty($body_class)) {
  if (is_array($body_class)) {
    $__body_classes .= ' ' . implode(' ', array_map('trim', $body_class));
  } else {
    $__body_classes .= ' ' . trim((string)$body_class);
  }
}
?>
<body class="<?= htmlspecialchars($__body_classes, ENT_QUOTES, 'UTF-8') ?>">

<!-- 🔝 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-black sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><img src="assets/img/logo.png" alt="DragonStone" style="height: 35px; width: auto;"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="shopDrop" role="button" data-bs-toggle="dropdown">Shop</a>
          <ul class="dropdown-menu">
            <?php
              $cats = db()->query('SELECT * FROM categories')->fetch_all(MYSQLI_ASSOC);
              foreach($cats as $c){
                echo '<li><a class="dropdown-item" href="category.php?cat='.urlencode($c['slug']).'">'.htmlspecialchars($c['title']).'</a></li>';
              }
            ?>
          </ul>
        </li>
      </ul>

      <form class="d-flex me-3" action="search.php" method="get">
        <input class="form-control form-control-sm me-2" name="q" placeholder="Search products...">
        <button class="btn btn-outline-light btn-sm" type="submit">Search</button>
      </form>

      <!-- 🔸 Right side (User & Cart icons) -->
      <div class="d-flex align-items-center ms-auto">
        <a class="text-light me-3" href="index.php">Home</a>
        <?php if($user): ?>
          <span class="me-2 text-light">
            Hi, <?= htmlspecialchars(trim($user['first_name'] . ' ' . ($user['surname'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
          </span>
          <a class="btn btn-outline-light me-2" href="profile.php">Profile</a>

          <?php if(in_array($user['role'], ['admin','manager','analyst'])): ?>
            <a class="btn btn-warning me-2" href="../admin/index.php">Admin</a>
          <?php endif; ?>

          <!-- 🛒 Cart text with symbol triggers the mini cart -->
          <span id="openMiniCartBtn" class="text-light me-2" style="cursor:pointer;">Cart 🛒</span>

          <a class="text-light me-2" href="subscription.php">Subscribe </a>

          <!-- Community Hub -->
          <a class="btn btn-outline-light me-2" href="community.php">Community</a>
        

          <!-- 🔒 Logout -->
          <a class="btn btn-danger d-flex align-items-center ms-3" href="logout.php">
            <span class="me-1">Logout</span> 🔒
          </a>
        <?php else: ?>
          <a class="btn btn-light me-2 text-dark" href="login.php">Login</a>
          <a class="btn btn-light me-2 text-dark" href="register.php">Register</a>
          <span id="openMiniCartBtn" class="text-light" style="cursor:pointer;">Cart 🛒</span>
        <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container mt-4">
<!-- 🧩 Page content starts here -->


<!-- 🛒 Slide-out Cart Sidebar -->
<div id="miniCartOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:1040;"></div>

<div id="miniCart" class="shadow-lg" style="
  position:fixed; top:0; right:-400px; width:380px; height:100%;
  background:#fff; z-index:1050; transition:right 0.3s ease;
  display:flex; flex-direction:column;
  border-left:3px solid #000;
">
  <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
    <span id="miniCartClose" style="cursor:pointer; font-weight:500;">← Continue Shopping</span>
    <a href="cart.php" class="text-dark fw-bold">View Cart</a>
  </div>
  <div class="p-3 flex-grow-1 overflow-auto" id="miniCartContent">
    <p class="text-center text-muted mt-5">Cart is empty.</p>
  </div>
  <div class="p-3 border-top">
    <div class="d-flex justify-content-between mb-2">
      <strong>Total:</strong>
      <strong id="miniCartTotal">0.00</strong>
    </div>
    <a href="delivery.php" class="btn btn-dark w-100">Checkout</a>
  </div>
</div>

<script>
  // 🛒 Mini Cart open/close behavior
  function openMiniCart() {
    $('#miniCart').css('right', '0');
    $('#miniCartOverlay').show();
  }
  function closeMiniCart() {
    $('#miniCart').css('right', '-400px');
    $('#miniCartOverlay').hide();
  }
  $('#miniCartClose, #miniCartOverlay').on('click', closeMiniCart);
  $('#openMiniCartBtn').on('click', function(){
    window.location.href = 'cart.php';
  });

  // 🔄 Update total inside the mini cart
  function updateMiniCartTotal() {
    let total = 0;
    $('#miniCartContent .line-price').each(function(){
      total += parseFloat($(this).text()) || 0;
    });
    $('#miniCartTotal').text(total.toFixed(2));
  }
</script>
