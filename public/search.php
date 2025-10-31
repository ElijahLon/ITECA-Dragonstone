
<?php
require_once __DIR__.'/inc/db.php';
include __DIR__.'/inc/header.php';
$q = isset($_GET['q']) ? '%'.$_GET['q'].'%' : '%';
$db = db();
$stmt = $db->prepare('SELECT * FROM products WHERE title LIKE ? LIMIT 50');
$stmt->bind_param('s', $q); $stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h3>Search results</h3>
<div class="row">
<?php foreach($res as $p): ?>
 <div class="col-md-3 mb-3">
   <div class="card"><img src="<?=htmlspecialchars($p['image'])?>" class="card-img-top">
    <div class="card-body"><h6><?=htmlspecialchars($p['title'])?></h6><p><?=number_format($p['price'],2)?></p></div>
   </div>
 </div>
<?php endforeach; ?>
</div>
<?php include __DIR__.'/inc/footer.php'; ?>
