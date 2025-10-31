
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/inc/db.php';
$db = db();
// Load a few products per category for demo rows
$cats = $db->query("SELECT * FROM categories LIMIT 7")->fetch_all(MYSQLI_ASSOC);
$rows = [];
foreach($cats as $c){
  $cid = (int)$c['id'];
  // Try to fetch products by category. If the column doesn't exist in the DB we
  // catch the exception and fall back to a safe query to avoid a fatal error.
  try {
    $res_query = $db->query("SELECT * FROM products WHERE category_id = {$cid} LIMIT 5");
    $res = $res_query ? $res_query->fetch_all(MYSQLI_ASSOC) : [];
  } catch (mysqli_sql_exception $e) {
    // Fallback: select some products unfiltered so the page can still render.
    $res_query = $db->query("SELECT * FROM products LIMIT 4");
    $res = $res_query ? $res_query->fetch_all(MYSQLI_ASSOC) : [];
  }

  $rows[] = ['category'=>$c, 'products'=>$res];
}
?>
<?php include __DIR__.'/inc/header.php'; ?>

<div class="banner mb-4 position-relative">
  <img src="assets/img/banner.jpg" class="img-fluid" alt="banner" style="width: 100%; height: auto;">
  <a class="btn btn-dark position-absolute" href="category.php" style="bottom: 0%; right: 55%; transform: translate(-50%, -50%); padding: 20px 40px; border-radius: 0; font-size: 24px; font-weight: bold;">Shop now</a>
  <a class="btn btn-dark position-absolute" href="learn.php" style="bottom: 0%; right: 70%; transform: translate(-50%, -50%); padding: 20px 40px; border-radius: 0; font-size: 24px; font-weight: bold;">Learn more</a>
</div>

<!-- Community Sidebar -->
<div id="communitySidebar" class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1000; display: none;">
  <div class="card shadow-lg" style="width: 250px; border-radius: 12px; overflow: hidden;">
    <div class="card-body text-center p-3" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
      <h6 class="card-title mb-2" style="font-weight: bold;">Join Our Community</h6>
      <p class="card-text small mb-3">Connect with eco-conscious shoppers and share sustainable tips.</p>
      <a href="community.php" class="btn btn-light btn-sm">Chat Now</a>
    </div>
  </div>
</div>
//lol
<!-- Minimized Chat Button -->
<div id="minimizedChat" class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1000; display: none;">
  <button class="btn btn-success rounded-circle shadow-lg" style="width: 60px; height: 60px; font-size: 24px;" title="Open Community Chat">
    //💬
  </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('communitySidebar');
    const minimizedChat = document.getElementById('minimizedChat');

    // Show sidebar after 5 seconds
    setTimeout(function() {
        sidebar.style.display = 'block';
    }, 5000);

    // When sidebar is closed, show minimized chat button
    function closeSidebar() {
        sidebar.style.display = 'none';
        minimizedChat.style.display = 'block';
    }

    // Add close functionality to the sidebar (clicking outside or on the card)
    sidebar.addEventListener('click', function(e) {
        if (e.target === sidebar || e.target.closest('.card-body a')) {
            // If clicking on the Chat Now link, don't close
            if (!e.target.closest('a')) {
                closeSidebar();
            }
        }
    });

    // Clicking on minimized chat button opens community.php
    minimizedChat.addEventListener('click', function() {
        window.location.href = 'community.php';
    });
});
</script>

<div class="container-fluid px-0">
  <?php foreach($rows as $row):
        // defensive: category values may be missing
        $catTitle = htmlspecialchars($row['category']['title'] ?? 'Category');
        $catSlug = urlencode($row['category']['slug'] ?? '');
  ?>
  <section class="py-3 border-top">
    <div class="d-flex justify-content-between align-items-center mb-2 px-3">
      <h4 class="mb-0"><?= $catTitle ?></h4>
      <a class="more-link" href="category.php?cat=<?= $catSlug ?>">See more →</a>
    </div>
    <div class="row gx-3 px-3">
      <?php foreach($row['products'] as $p):
            $img = htmlspecialchars($p['image'] ?? 'assets/img/hero.jpg');
            $title = htmlspecialchars($p['title'] ?? 'Untitled');
            $price = number_format((float)($p['price'] ?? 0), 2);
            $currency = htmlspecialchars($p['currency'] ?? 'R');
            $pid = (int)($p['id'] ?? 0);
      ?>
      <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
        <div class="card product-card">
          <img src="<?= $img ?>" alt="<?= $title ?>" class="card-img-top">
          <div class="card-body">
            <h6 class="card-title"><?= $title ?></h6>
            <p class="mb-1"><strong><?= $price ?> <?= $currency ?></strong></p>
            <a href="product.php?id=<?= $pid ?>" class="btn btn-sm btn-outline-dark">View</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>

<?php include __DIR__.'/inc/footer.php'; ?>
