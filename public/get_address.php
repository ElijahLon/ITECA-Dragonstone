<?php
require_once __DIR__ . '/inc/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$db = db();
$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $address_id = (int)$_GET['id'];

    $stmt = $db->prepare('SELECT * FROM user_addresses WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $address_id, $user_id);
    $stmt->execute();
    $address = $stmt->get_result()->fetch_assoc();

    if ($address) {
        echo json_encode(['success' => true, 'address' => $address]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Address not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
