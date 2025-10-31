<?php
session_start();
require_once __DIR__.'/inc/db.php';
$db = db();

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$html = '';

if (!empty($cart)) {
  foreach ($cart as $pid => $qty) {
    $stmt = $db->prepare('SELECT name, price, image FROM products WHERE id=?');
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if ($p) {
      $subtotal = $p['price'] * $qty;
      $total += $subtotal;
      $html .= "
        <div class='d-flex align-items-center mb-3'>
          <img src='{$p['image']}' width='60' height='60' class='rounded me-2'>
          <div>
            <strong>{$p['name']}</strong><br>
            <small>{$qty} × R{$p['price']}</small>
          </div>
        </div>";
    }
  }
}

echo json_encode(['total' => $total, 'items_html' => $html]);
