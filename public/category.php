<?php
require_once __DIR__.'/inc/db.php';
require_once __DIR__.'/inc/header.php';
$db = db();
$slug = isset($_GET['cat']) ? $_GET['cat'] : '';
$cat = null;
if ($slug){
    $stmt = $db->prepare('SELECT * FROM categories WHERE slug = ?');
    $stmt->bind_param('s',$slug);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
}
$cat_id = $cat ? (int)$cat['id'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';

// Query the maximum price for the current category or all products
$max_price_query = 'SELECT MAX(price) AS max_price FROM products';
if ($cat_id) {
    $max_price_query .= ' WHERE category_id = ' . $cat_id;
}
$max_price_result = $db->query($max_price_query)->fetch_assoc();
$max_price_limit = (float)($max_price_result['max_price'] ?? 1000);
if ($max_price_limit <= 0) {
    $max_price_limit = 1000;
}

$max_price = isset($_GET['max_price']) ? min((float)$_GET['max_price'], $max_price_limit) : $max_price_limit;

$sql = 'SELECT * FROM products WHERE 1=1';
if ($cat_id) {
    $sql .= ' AND category_id = ' . $cat_id;
}
if ($max_price) {
    $sql .= ' AND price <= ' . $max_price;
}

switch($sort) {
    case 'low':
        $sql .= ' ORDER BY price ASC';
        break;
    case 'high':
        $sql .= ' ORDER BY price DESC';
        break;
    default:
        $sql .= ' ORDER BY created_at DESC';
}

$products = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!-- Community Banner -->
<div class="alert alert-info text-center mb-4" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
  <h5 class="alert-heading mb-2" style="color: #495057;">Join Our Community Hub</h5>
  <p class="mb-3" style="color: #6c757d;">Connect with fellow eco-conscious shoppers, share tips on sustainable living, and discuss the latest in green fashion.</p>
  <a href="community.php" class="btn btn-primary">Join the Conversation</a>
</div>

<div class="row">
  <div class="col-md-3">
    <h5 style="font-weight: 600; color: #000;">Filters</h5>
    <form method="get" action="">
      <?php if($cat): ?>
        <input type="hidden" name="cat" value="<?=htmlspecialchars($slug)?>">
      <?php endif; ?>
      <div class="mb-2">
        <label style="font-weight: 600; color: #000;">Maximum Price</label>
        <input type="range" name="max_price" min="0" max="<?=$max_price_limit?>" class="form-range" value="<?=htmlspecialchars($max_price)?>">
        <div class="text-muted">R<span id="priceValue"><?=$max_price?></span></div>
      </div>
      <div class="mb-3">
        <label style="font-weight: 600; color: #000;">Category</label>
        <select name="cat" class="form-select" style="border-radius: 8px; border: 1px solid #ddd;">
          <option value="">All Categories</option>
          <?php
          $cats = $db->query("SELECT * FROM categories ORDER BY title ASC");
          while($c = $cats->fetch_assoc()){
            $selected = ($slug == $c['slug']) ? 'selected' : '';
            echo '<option value="'.htmlspecialchars($c['slug']).'" '.$selected.'>'.htmlspecialchars($c['title']).'</option>';
          }
          ?>
        </select>
      </div>
      <div class="mb-3">
        <label style="font-weight: 600; color: #000;">Sort By</label>
        <select name="sort" class="form-select" style="border-radius: 8px; border: 1px solid #ddd;">
          <option value="new" <?=$sort=='new'?'selected':''?>>Newest</option>
          <option value="low" <?=$sort=='low'?'selected':''?>>Price: Low to High</option>
          <option value="high" <?=$sort=='high'?'selected':''?>>Price: High to Low</option>
        </select>
      </div>
      <button type="submit" class="btn btn-dark w-100" style="border-radius: 8px; font-weight: 600; background: #000; border: none;">Apply Filters</button>
    </form>
  </div>
  <div class="col-md-9">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 style="font-weight: 700; color: #000;"><?= $cat ? htmlspecialchars($cat['title']) : 'All products' ?></h3>
      <div>
        <small class="text-muted">Showing <?=count($products)?> results</small>
      </div>
    </div>
    <div class="row">
      <?php foreach($products as $p):
            // defensive defaults to avoid undefined index notices
            $img = htmlspecialchars($p['image'] ?? 'assets/img/hero.jpg');
            $title = htmlspecialchars($p['title'] ?? 'Untitled');
            $price = number_format((float)($p['price'] ?? 0), 2);
            $currency = htmlspecialchars($p['currency'] ?? 'R');
            $pid = (int)($p['id'] ?? 0);
      ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100 product-card" style="border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
          <img src="<?= $img ?>" alt="<?= $title ?>" class="card-img-top" style="border-radius: 12px 12px 0 0;">
          <div class="card-body">
            <h5 class="card-title" style="font-weight: 600; color: #000;"><?= $title ?></h5>
            <p class="mb-1" style="font-weight: 700; color: #2e7d32;"><?= $price ?> <?= $currency ?></p>
            <a href="product.php?id=<?= $pid ?>" class="btn btn-dark" style="border-radius: 8px; font-weight: 600; background: #000; border: none;">View product</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
document.querySelector('input[name="max_price"]').addEventListener('input', function() {
    document.getElementById('priceValue').textContent = this.value;
});
</script>
<?php include __DIR__.'/inc/footer.php'; ?>
