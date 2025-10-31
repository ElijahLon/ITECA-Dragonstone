
<?php
require_once __DIR__.'/../public/inc/db.php';
require_once __DIR__.'/../public/inc/auth.php';
require_role(['admin','manager','analyst']);
$user = current_user();
$db = db();

// Get some stats
$total_users = $db->query('SELECT COUNT(*) as count FROM users')->fetch_assoc()['count'];
$total_products = $db->query('SELECT COUNT(*) as count FROM products')->fetch_assoc()['count'];
$total_orders = $db->query('SELECT COUNT(*) as count FROM orders')->fetch_assoc()['count'];
$active_subscriptions = $db->query('SELECT COUNT(*) as count FROM subscriptions WHERE active = 1')->fetch_assoc()['count'];
?>
<!doctype html><html><head>
  <meta charset="utf-8"><title>Admin - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../public/assets/css/style.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      min-height: 100vh;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #2c3e50;
    }
    .navbar {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
      border-bottom: 3px solid #3498db;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .navbar-brand {
      font-weight: 700;
      color: #ffffff !important;
      font-size: 1.5rem;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    .nav-link {
      color: #ecf0f1 !important;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    .nav-link:hover { color: #3498db !important; }

    .dashboard-header {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid #e9ecef;
    }
    .dashboard-title {
      color: #2c3e50;
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      text-align: center;
    }
    .dashboard-subtitle {
      color: #7f8c8d;
      text-align: center;
      font-size: 1.1rem;
    }

    .stats-section {
      margin-bottom: 3rem;
    }
    .stat-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: none;
      border-radius: 15px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      height: 100%;
      position: relative;
      overflow: hidden;
    }
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #3498db, #2980b9);
    }
    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 48px rgba(0,0,0,0.15);
    }
    .stat-card.users::before { background: linear-gradient(90deg, #27ae60, #2ecc71); }
    .stat-card.products::before { background: linear-gradient(90deg, #e74c3c, #c0392b); }
    .stat-card.orders::before { background: linear-gradient(90deg, #f39c12, #e67e22); }
    .stat-card.subscriptions::before { background: linear-gradient(90deg, #9b59b6, #8e44ad); }

    .stat-number {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    .stat-label {
      color: #7f8c8d;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-size: 0.9rem;
    }
    .stat-users .stat-number { color: #27ae60; }
    .stat-products .stat-number { color: #e74c3c; }
    .stat-orders .stat-number { color: #f39c12; }
    .stat-subscriptions .stat-number { color: #9b59b6; }

    .management-section {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid #e9ecef;
    }
    .management-title {
      color: #2c3e50;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      font-size: 2rem;
    }

    .management-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: none;
      border-radius: 12px;
      box-shadow: 0 6px 24px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      height: 100%;
      text-decoration: none;
      color: inherit;
      position: relative;
      overflow: hidden;
    }
    .management-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #3498db, #2980b9);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    .management-card:hover::before {
      transform: scaleX(1);
    }
    .management-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.15);
      color: inherit;
    }

    .management-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      display: block;
      text-align: center;
    }
    .management-card .card-title {
      color: #2c3e50 !important;
      font-weight: 600;
      text-align: center;
      margin-bottom: 0.5rem;
    }
    .management-card .card-text {
      color: #7f8c8d !important;
      text-align: center;
      font-size: 0.9rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .dashboard-title { font-size: 2rem; }
      .stat-number { font-size: 2.5rem; }
      .management-title { font-size: 1.5rem; }
    }
  </style>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">DragonStone Admin</a>
    <div class="navbar-nav ms-auto">
      <span class="nav-link">Logged in as <?=htmlspecialchars($user['name'] ?? 'Unknown')?> (<strong><?=htmlspecialchars($user['role'] ?? 'Unknown')?></strong>)</span>
    </div>
  </div>
</nav>

<div class="container-fluid py-5">
  <!-- Dashboard Header -->
  <div class="dashboard-header">
    <h1 class="dashboard-title">Admin Dashboard</h1>
    <p class="dashboard-subtitle">Manage your e-commerce platform with powerful business insights</p>
  </div>

  <!-- Stats Cards -->
  <div class="stats-section">
    <div class="row">
      <div class="col-md-3 mb-4">
        <div class="card stat-card h-100 users">
          <div class="card-body text-center">
            <div class="stat-number"><?=$total_users?></div>
            <div class="stat-label">Total Users</div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card stat-card h-100 products">
          <div class="card-body text-center">
            <div class="stat-number"><?=$total_products?></div>
            <div class="stat-label">Products</div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card stat-card h-100 orders">
          <div class="card-body text-center">
            <div class="stat-number"><?=$total_orders?></div>
            <div class="stat-label">Orders</div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card stat-card h-100 subscriptions">
          <div class="card-body text-center">
            <div class="stat-number"><?=$active_subscriptions?></div>
            <div class="stat-label">Active Subscriptions</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Management Links -->
  <div class="management-section">
    <h2 class="management-title">Management Center</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <a href="users.php" class="card management-card text-decoration-none h-100">
          <div class="card-body text-center">
            <div class="management-icon">👥</div>
            <h5 class="card-title">User Management</h5>
            <p class="card-text">Block users, manage roles, and oversee account permissions</p>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="products.php" class="card management-card text-decoration-none h-100">
          <div class="card-body text-center">
            <div class="management-icon">📦</div>
            <h5 class="card-title">Product Management</h5>
            <p class="card-text">Add, edit, delete, and organize your product catalog</p>
          </div>
        </a>
      </div>

      <div class="col-md-6">
        <a href="../public/index.php" class="card management-card text-decoration-none h-100">
          <div class="card-body text-center">
            <div class="management-icon">🏪</div>
            <h5 class="card-title">Visit Storefront</h5>
            <p class="card-text">Preview your public store and customer experience</p>
          </div>
        </a>
      </div>
    </div>
  </div>
</div>
</body></html>
