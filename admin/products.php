
<?php
require_once __DIR__.'/../public/inc/db.php';
require_once __DIR__.'/../public/inc/auth.php';
require_role(['admin','manager']);
$db = db();

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])){
    $id = (int)$_POST['delete_id'];
    $db->query('DELETE FROM products WHERE id = '.$id);
    header('Location: products.php?deleted=1'); exit;
}

// Handle product addition/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])){
    $id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $sku = trim($_POST['sku']);
    $stock_quantity = (int)$_POST['stock_quantity'];

    if ($id) {
        // Update existing product
        $stmt = $db->prepare('UPDATE products SET title=?, description=?, price=?, category_id=?, sku=?, stock_quantity=? WHERE id=?');
        $stmt->bind_param('ssdissi', $title, $description, $price, $category_id, $sku, $stock_quantity, $id);
        $stmt->execute();
        header('Location: products.php?updated=1'); exit;
    } else {
        // Add new product
        $stmt = $db->prepare('INSERT INTO products (title, description, price, category_id, sku, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssdiss', $title, $description, $price, $category_id, $sku, $stock_quantity);
        $stmt->execute();
        header('Location: products.php?added=1'); exit;
    }
}

// Get categories for dropdown
$categories = $db->query('SELECT * FROM categories ORDER BY title')->fetch_all(MYSQLI_ASSOC);

// Get products
$prods = $db->query('SELECT p.id, p.title, p.price, p.stock_quantity, p.description, c.title as cat, p.category_id FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC');
?>
<!doctype html><html><head>
  <meta charset="utf-8"><title>Product Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../public/assets/css/style.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      min-height: 100vh;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
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

    .page-header {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid #e9ecef;
    }
    .page-title {
      color: #2c3e50;
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      text-align: center;
    }
    .page-subtitle {
      color: #7f8c8d;
      text-align: center;
      font-size: 1.1rem;
    }

    .product-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: none;
      border-radius: 12px;
      box-shadow: 0 6px 24px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      margin-bottom: 1.5rem;
    }
    .product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }

    .product-header {
      background: linear-gradient(90deg, #3498db, #2980b9);
      color: white;
      padding: 1rem;
      border-radius: 12px 12px 0 0;
    }
    .product-title {
      font-weight: 600;
      margin: 0;
    }
    .product-sku {
      opacity: 0.9;
      font-size: 0.9rem;
    }

    .product-body {
      padding: 1.5rem;
    }
    .product-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .info-item {
      background: #f8f9fa;
      padding: 0.75rem;
      border-radius: 8px;
      border-left: 3px solid #3498db;
    }
    .info-label {
      font-weight: 600;
      color: #2c3e50;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.25rem;
    }
    .info-value {
      color: #34495e;
      font-weight: 500;
    }

    .product-actions {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    .btn-edit {
      background: linear-gradient(135deg, #f39c12, #e67e22);
      border: none;
      color: white;
      transition: all 0.3s ease;
    }
    .btn-edit:hover {
      background: linear-gradient(135deg, #e67e22, #d35400);
      transform: translateY(-1px);
    }
    .btn-delete {
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      border: none;
      color: white;
      transition: all 0.3s ease;
    }
    .btn-delete:hover {
      background: linear-gradient(135deg, #c0392b, #a93226);
      transform: translateY(-1px);
    }

    .add-product-section {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid #e9ecef;
    }
    .add-product-title {
      color: #2c3e50;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      font-size: 1.8rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }
    .form-label {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }
    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem;
      transition: border-color 0.3s ease;
    }
    .form-control:focus {
      border-color: #3498db;
      box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .btn-add {
      background: linear-gradient(135deg, #27ae60, #2ecc71);
      border: none;
      color: white;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-add:hover {
      background: linear-gradient(135deg, #2ecc71, #27ae60);
      transform: translateY(-1px);
    }

    @media (max-width: 768px) {
      .page-title { font-size: 2rem; }
      .product-info { grid-template-columns: 1fr; }
      .product-actions { justify-content: center; }
    }
  </style>
</head><body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">DragonStone Admin</a>
    <div class="navbar-nav ms-auto">
      <a class="nav-link" href="index.php">← Back to Dashboard</a>
      <span class="nav-link">Product Management</span>
    </div>
  </div>
</nav>

<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="page-header">
    <h1 class="page-title">Product Management</h1>
    <p class="page-subtitle">Add, edit, delete, and organize your product catalog</p>
  </div>

  <!-- Success Messages -->
  <?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      Product added successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      Product updated successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      Product deleted successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Add New Product Section -->
  <div class="add-product-section">
    <h2 class="add-product-title">Add New Product</h2>
    <form method="post">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">Product Title *</label>
            <input type="text" name="title" class="form-control" required>
          </div>
        </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Stock Quantity *</label>
              <input type="number" name="stock_quantity" class="form-control" required>
            </div>
          </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">Price (ZAR) *</label>
            <input type="number" name="price" step="0.01" class="form-control" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">Stock Quantity *</label>
            <input type="number" name="stock_quantity" class="form-control" required>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">Category *</label>
            <select name="category_id" class="form-control" required>
              <option value="">Select Category</option>
              <?php foreach($categories as $cat): ?>
                <option value="<?=$cat['id']?>"><?=htmlspecialchars($cat['title'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
        </div>
      </div>
      <div class="text-center">
        <button type="submit" name="save_product" class="btn btn-add">Add Product</button>
      </div>
    </form>
  </div>

  <!-- Products List -->
  <div class="row">
    <?php while($p = $prods->fetch_assoc()): ?>
    <div class="col-lg-6 col-xl-4">
      <div class="card product-card">
        <div class="product-header">
          <h5 class="product-title"><?=htmlspecialchars($p['title'])?></h5>
          <div class="product-sku">ID: <?=$p['id']?></div>
        </div>
        <div class="product-body">
          <div class="product-info">
            <div class="info-item">
              <div class="info-label">Category</div>
              <div class="info-value"><?=htmlspecialchars($p['cat'])?></div>
            </div>
            <div class="info-item">
              <div class="info-label">Price</div>
              <div class="info-value">R<?=number_format($p['price'],2)?></div>
            </div>
            <div class="info-item">
              <div class="info-label">Stock</div>
              <div class="info-value"><?=$p['stock_quantity'] ?? 0?> units</div>
            </div>
            <div class="info-item">
              <div class="info-label">ID</div>
              <div class="info-value">#<?=$p['id']?></div>
            </div>
          </div>
          <?php if (!empty($p['description'])): ?>
          <div class="mb-3">
            <strong>Description:</strong><br>
            <small class="text-muted"><?=htmlspecialchars(substr($p['description'], 0, 100))?>...</small>
          </div>
          <?php endif; ?>
          <div class="product-actions">
            <button class="btn btn-edit btn-sm" onclick="editProduct(<?=$p['id']?>, '<?=addslashes($p['title'])?>', <?=$p['price']?>, <?=$p['stock_quantity'] ?? 0?>, <?=$p['category_id']?>, '<?=addslashes($p['description'] ?? '')?>')">Edit</button>
            <form method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
              <input type="hidden" name="delete_id" value="<?=$p['id']?>">
              <button type="submit" class="btn btn-delete btn-sm">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<script>
function editProduct(id, title, price, stock, categoryId, description) {
    // Populate the add form with product data for editing
    document.querySelector('input[name="title"]').value = title;
    document.querySelector('input[name="price"]').value = price;
    document.querySelector('input[name="stock_quantity"]').value = stock;
    document.querySelector('select[name="category_id"]').value = categoryId;
    document.querySelector('textarea[name="description"]').value = description;

    // Add hidden input for product ID
    let hiddenInput = document.querySelector('input[name="product_id"]');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'product_id';
        document.querySelector('form').appendChild(hiddenInput);
    }
    hiddenInput.value = id;

    // Change button text
    document.querySelector('.btn-add').textContent = 'Update Product';

    // Scroll to form
    document.querySelector('.add-product-section').scrollIntoView({ behavior: 'smooth' });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
