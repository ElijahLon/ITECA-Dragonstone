<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Get the currently logged-in user.
 * Returns associative array of user info, or null if not logged in.
 */
function current_user() {
    if (!isset($_SESSION['user_id'])) return null;

    $db = db();

    // Select fields that exist in your 'users' table
    $stmt = $db->prepare(
        'SELECT user_id AS id, first_name, surname, email, phone, role, created_at, eco_points
         FROM users 
         WHERE user_id = ? 
         LIMIT 1'
    );

    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

/**
 * Redirect to login page if user is not logged in.
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Redirect to login page if user role is not in allowed roles.
 * $roles should be an array of allowed role strings, e.g. ['admin', 'customer']
 */
function require_role($roles = []) {
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles)) {
        header('Location: ../login.php');
        exit;
    }
}
?> 