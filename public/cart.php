<?php
session_start();
require_once __DIR__.'/inc/db.php';
$db = db();

$cart = $_SESSION['cart'] ?? [];
$items = [];
$subtotal = 0;

// --- HANDLE ADD TO CART POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['qty']);

    // Add or update quantity in session cart
    if (isset($cart[$pid])) {
        $cart[$pid] += $qty;
    } else {
        $cart[$pid] = $qty;
    }

    $_SESSION['cart'] = $cart;

    // AJAX call: return OK and stop page render
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo 'OK';
        exit;
    }

    // Fallback: redirect to cart page for normal form submissions
    header('Location: cart.php');
    exit;
}

// --- HANDLE UPDATE CART QUANTITIES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $pid => $new_qty) {
            $pid = (int)$pid;
            $new_qty = max(0, (int)$new_qty);
            if ($new_qty > 0) {
                $cart[$pid] = $new_qty;
            } else {
                unset($cart[$pid]);
            }
        }
        $_SESSION['cart'] = $cart;
    }
    header('Location: cart.php');
    exit;
}

// --- HANDLE AJAX QUANTITY UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $pid = (int)$_POST['update_qty'];
    $qty = max(0, (int)$_POST['qty']);
    if ($qty > 0) {
        $cart[$pid] = $qty;
    } else {
        unset($cart[$pid]);
    }
    $_SESSION['cart'] = $cart;
    echo 'OK';
    exit;
}

// --- AJAX mini-cart support ---
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $response = [];
    foreach ($cart as $pid => $qty) {
        $stmt = $db->prepare('SELECT id, title, price, currency, image FROM products WHERE id = ?');
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if ($p) {
            $p['qty'] = $qty;
            $p['line'] = $p['price'] * $qty;
            $response[] = $p;
        }
    }
    echo json_encode($response);
    exit;
}

// --- Normal cart page rendering ---
include 'inc/header.php';
?>

<div class="container py-5" style="min-height: calc(100vh - 200px);">
    <h1 class="mb-4" style="font-weight: 700; color: #000;">Your Shopping Cart</h1>

    <?php if (empty($cart)): ?>
        <div class="text-center">
            <p class="mb-3">Your cart is empty.</p>
            <a href="index.php" class="btn btn-dark">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="post">
            <div class="row">
                <div class="col-lg-12">
                        <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-family: 'Poppins', sans-serif;">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th scope="col" style="font-weight: 600; color: #000;">Product</th>
                                            <th scope="col" style="font-weight: 600; color: #000;">Price</th>
                                            <th scope="col" style="font-weight: 600; color: #000;">Qty</th>
                                            <th scope="col" style="font-weight: 600; color: #000;">Total</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($cart as $pid => $qty):
                                            $stmt = $db->prepare('SELECT id, title, price, currency, image FROM products WHERE id = ?');
                                            $stmt->bind_param('i', $pid);
                                            $stmt->execute();
                                            $product = $stmt->get_result()->fetch_assoc();
                                            if (!$product) continue;
                                            $line = $product['price'] * $qty;
                                            $subtotal += $line;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="" class="me-3" style="width: 70px; height: auto;">
                                                    <span><?= htmlspecialchars($product['title']) ?></span>
                                                </div>
                                            </td>
                                            <td>R<?= number_format($product['price'], 2) ?></td>
                                            <td>
                                                <input type="number" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="1" class="form-control" style="width: 80px;">
                                            </td>
                                            <td>R<?= number_format($line, 2) ?></td>
                                            <td><a href="remove_from_cart.php?id=<?= $pid ?>" class="btn btn-sm btn-outline-danger">Remove</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

               co     <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" name="update_cart" class="btn btn-outline-primary me-2" style="border-radius: 8px; border-color: #ddd; color: #555; font-weight: 500;">Update Cart</button>
                            <div class="card shadow-sm p-3 d-inline-block" style="border-radius: 12px; border: none;">
                                <h5 style="font-weight: 600; color: #000;">Subtotal: R<?= number_format($subtotal, 2) ?></h5>
                            <a href="delivery.php" class="btn btn-dark mt-2" style="border-radius: 8px; font-weight: 600; background: #000; border: none;">Proceed to Checkout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'inc/footer.php'; ?>
