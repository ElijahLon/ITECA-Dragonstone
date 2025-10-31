<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/header.php';

$db = db();

// ✅ Validate GET parameters
if (!isset($_GET['reference']) || !isset($_GET['uid'])) {
    echo '<div class="alert alert-danger mt-4">Invalid payment verification request.</div>';
    include __DIR__ . '/inc/footer.php';
    exit;
}

$reference = $_GET['reference'];
$user_id   = intval($_GET['uid']);
$type      = $_GET['type'] ?? 'order';
$plan      = $_GET['plan'] ?? '';

// 🔹 Step 1: Verify payment with Paystack API
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer sk_test_9b6c7eeb81ad30b5d31c5fb2556f52dfa2c1c074", // Your test secret key
        "Cache-Control: no-cache",
    ],
]);
$response = curl_exec($curl);
curl_close($curl);

$verification = json_decode($response, true);

// 🔹 Step 2: Check verification status
if (isset($verification['data']['status']) && $verification['data']['status'] === 'success') {

    // ✅ Subscription payments
    if ($type === 'subscription') {
        $interval_months = 1; // Monthly
        $next_charge = date('Y-m-d', strtotime('+1 month'));
        $product_id = 0; // Placeholder (change if needed)

        $stmt = $db->prepare('
            INSERT INTO subscriptions
            (user_id, product_id, interval_months, next_charge, active, plan, transaction_ref)
            VALUES (?, ?, ?, ?, 1, ?, ?)
        ');
        $stmt->bind_param('iiisss', $user_id, $product_id, $interval_months, $next_charge, $plan, $reference);
        $stmt->execute();

        echo '<div class="alert alert-success mt-4 text-center">';
        echo '<h4>✅ Subscription Successful!</h4>';
        echo '<p>Thank you for subscribing to the ' . htmlspecialchars($plan) . ' plan.</p>';
        echo '<p><strong>Next Charge:</strong> ' . htmlspecialchars($next_charge) . '</p>';
        echo '<a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>';
        echo '</div>';
    }

    // ✅ Subscription cart payments
    elseif ($type === 'subscription_cart') {
        $duration = intval($_GET['duration'] ?? 1);
        $payment_type = $_GET['payment_type'] ?? 'full';
        $items_json = $_GET['items'] ?? '[]';
        $items = json_decode($items_json, true);

        if (empty($items)) {
            echo '<div class="alert alert-danger mt-4">Invalid subscription cart data.</div>';
            include __DIR__ . '/inc/footer.php';
            exit;
        }

        // Create subscriptions for each item
        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $qty = intval($item['qty']);
            $next_charge = date('Y-m-d', strtotime('+1 month')); // Always next month for recurring

            // Insert one subscription per product (qty represents monthly quantity)
            $stmt = $db->prepare('
                INSERT INTO subscriptions
                (user_id, product_id, interval_months, next_charge, active, plan, transaction_ref, payment_type)
                VALUES (?, ?, ?, ?, 1, ?, ?, ?)
            ');
            $plan_name = "Custom Subscription ($qty items/month for $duration months)";
            $stmt->bind_param('iiissss', $user_id, $product_id, $duration, $next_charge, $plan_name, $reference, $payment_type);
            $stmt->execute();
        }

        echo '<div class="alert alert-success mt-4 text-center">';
        echo '<h4>✅ Subscription Cart Successful!</h4>';
        echo '<p>Thank you for subscribing to your custom cleaning supplies plan.</p>';
        echo '<p><strong>Duration:</strong> ' . htmlspecialchars($duration) . ' months</p>';
        if ($payment_type === 'recurring') {
            echo '<p><strong>Payment Type:</strong> Recurring Monthly Payments</p>';
            echo '<p><strong>Next Charge:</strong> ' . htmlspecialchars($next_charge) . '</p>';
        } else {
            echo '<p><strong>Payment Type:</strong> Full Amount Paid</p>';
        }
        echo '<a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>';
        echo '</div>';

        // Clear the subscribe cart after successful payment
        unset($_SESSION['subscribe_cart']);
    }
    // ✅ Order payments
    else {
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            echo '<div class="alert alert-warning mt-4">Your cart is empty.</div>';
            include __DIR__ . '/inc/footer.php';
            exit;
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($cart as $pid => $qty) {
            $stmt = $db->prepare('SELECT price FROM products WHERE id = ?');
            $stmt->bind_param('i', $pid);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $price = $result['price'] ?? 0;
            $subtotal += $price * $qty;
        }

        $shipping = 25.00;
        $tax = round($subtotal * 0.12, 2);
        $total = $subtotal + $shipping + $tax;

        // Create order
        $status = 'paid';
        $stmt = $db->prepare('
            INSERT INTO orders 
            (user_id, subtotal, shipping, tax, total, status, transaction_ref)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('idddsss', $user_id, $subtotal, $shipping, $tax, $total, $status, $reference);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Insert order items
        foreach ($cart as $pid => $qty) {
            $stmt = $db->prepare('
                SELECT price, carbon_material, carbon_manufacture, carbon_packaging 
                FROM products 
                WHERE id = ?
            ');
            $stmt->bind_param('i', $pid);
            $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc();

            if ($p) {
                // ✅ Fix: safely handle null or string values
                $carbon = floatval($p['carbon_material'] ?? 0)
                        + floatval($p['carbon_manufacture'] ?? 0)
                        + floatval($p['carbon_packaging'] ?? 0);

                $stmt = $db->prepare('
                    INSERT INTO order_items (order_id, product_id, qty, price, carbon_kg)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->bind_param('iiidd', $order_id, $pid, $qty, $p['price'], $carbon);
                $stmt->execute();
            }
        }

        // Award EcoPoints for purchase (10 points per R100 spent)
        $points_earned = floor($total / 10); // 10 points per R100
        if ($points_earned > 0) {
            // Update user eco_points
            $stmt = $db->prepare('UPDATE users SET eco_points = eco_points + ? WHERE user_id = ?');
            $stmt->bind_param('ii', $points_earned, $user_id);
            $stmt->execute();

            // Log in ecopoints_ledger
            $stmt = $db->prepare('INSERT INTO ecopoints_ledger (user_id, points, reason, reference_id) VALUES (?, ?, ?, ?)');
            $reason = "Purchase reward";
            $stmt->bind_param('iiss', $user_id, $points_earned, $reason, $reference);
            $stmt->execute();
        }

        // Clear cart
        unset($_SESSION['cart']);

        // ✅ Success message
        echo '<div class="alert alert-success mt-4 text-center">';
        echo '<h4>✅ Payment Successful!</h4>';
        echo '<p>Thank you for your purchase. Your order has been placed successfully.</p>';
        echo '<p><strong>Order ID:</strong> ' . htmlspecialchars($order_id) . '</p>';
        echo '<p><strong>Total Paid:</strong> R' . number_format($total, 2) . '</p>';
        if ($points_earned > 0) {
            echo '<p><strong>EcoPoints Earned:</strong> ' . htmlspecialchars($points_earned) . ' points</p>';
        }
        echo '<a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>';
        echo '</div>';
    }

} else {
    // ❌ Payment failed
    echo '<div class="alert alert-danger mt-4 text-center">';
    echo '<h4>❌ Payment Failed!</h4>';
    echo '<p>Your payment could not be verified. Please try again.</p>';

    if ($type === 'subscription') {
        echo '<a href="subscription.php" class="btn btn-warning mt-3">Back to Subscriptions</a>';
    } else {
        echo '<a href="checkout.php" class="btn btn-warning mt-3">Back to Checkout</a>';
    }

    echo '</div>';
}

include __DIR__ . '/inc/footer.php';
?>
