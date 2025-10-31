<?php
require_once __DIR__.'/../public/inc/db.php';
require_once __DIR__.'/../public/inc/auth.php';
require_role(['admin','manager']);
$user = current_user();
$db = db();

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'];
    $allowed_roles = ['user', 'analyst', 'manager', 'admin'];
    if (in_array($new_role, $allowed_roles)) {
        $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->bind_param('si', $new_role, $user_id);
        $stmt->execute();
        header('Location: users.php?updated=1');
        exit;
    }
}

// Handle user blocking/unblocking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_user'])) {
    $user_id = (int)$_POST['block_user'];
    $blocked = isset($_POST['blocked']) ? 1 : 0;
    $stmt = $db->prepare('UPDATE users SET blocked = ? WHERE id = ?');
    $stmt->bind_param('ii', $blocked, $user_id);
    $stmt->execute();
    header('Location: users.php?blocked=1');
    exit;
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['delete_user'];
    // Don't allow deleting self
    if ($user_id !== $user['id']) {
        $db->query('DELETE FROM users WHERE id = ' . $user_id);
        header('Location: users.php?deleted=1');
        exit;
    }
}

// Get users with subscription info
$users = $db->query('
    SELECT u.user_id as id, u.first_name, u.surname, CONCAT(u.first_name, " ", u.surname) as name, u.email, u.role, u.eco_points, u.created_at,
           CASE WHEN s.active = 1 THEN "Yes" ELSE "No" END as has_subscription,
           COALESCE((SELECT COUNT(*) FROM orders WHERE user_id = u.user_id), 0) as total_orders
    FROM users u
    LEFT JOIN subscriptions s ON u.user_id = s.user_id AND s.active = 1
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html><head>
  <meta charset="utf-8"><title>User Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../public/assets/css/style.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .user-card { transition: transform 0.2s; border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .user-card:hover { transform: translateY(-2px); }
    .navbar-brand { font-weight: bold; color: #000 !important; }
    .nav-link { color: #000 !important; }
  </style>
</head><body class="bg-white">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand" href="index.php">DragonStone Admin</a>
    <div class="navbar-nav ms-auto">
      <a class="nav-link" href="index.php">← Back to Dashboard</a>
      <span class="nav-link">Logged in as <?=htmlspecialchars($user['name'] ?? 'Unknown')?></span>
    </div>
  </div>
</nav>

<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <h1 class="display-4 fw-bold text-center mb-4" style="color: #000;">User Management</h1>
      <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          User role updated successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          User deleted successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <?php foreach($users as $u): ?>
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card user-card h-100">
        <div class="card-body">
          <h5 class="card-title" style="color: #000;"><?=htmlspecialchars($u['name'])?></h5>
          <p class="card-text">
            <strong>Email:</strong> <?=htmlspecialchars($u['email'])?><br>
            <strong>Role:</strong> <span class="badge bg-secondary"><?=htmlspecialchars($u['role'])?></span><br>
            <strong>Status:</strong> <span class="badge <?=isset($u['blocked']) && $u['blocked'] ? 'bg-danger' : 'bg-success'?>"><?=isset($u['blocked']) && $u['blocked'] ? 'Blocked' : 'Active'?></span><br>
            <strong>Subscription:</strong> <?=htmlspecialchars($u['has_subscription'])?><br>
            <strong>Total Orders:</strong> <?=htmlspecialchars($u['total_orders'])?><br>
            <strong>Joined:</strong> <?=date('M j, Y', strtotime($u['created_at']))?>
          </p>
          <div class="d-flex gap-2 flex-wrap">
            <form method="post" class="d-inline">
              <input type="hidden" name="user_id" value="<?=$u['id']?>">
              <select name="role" class="form-select form-select-sm d-inline w-auto">
                <option value="user" <?=$u['role']=='user'?'selected':''?>>User</option>
                <option value="analyst" <?=$u['role']=='analyst'?'selected':''?>>Analyst</option>
                <option value="manager" <?=$u['role']=='manager'?'selected':''?>>Manager</option>
                <option value="admin" <?=$u['role']=='admin'?'selected':''?>>Admin</option>
              </select>
              <button type="submit" name="update_role" class="btn btn-sm btn-outline-primary">Update Role</button>
            </form>
            <?php if ($u['id'] !== $user['id']): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="block_user" value="<?=$u['id']?>">
              <input type="hidden" name="blocked" value="<?=isset($u['blocked']) && $u['blocked'] ? '0' : '1'?>">
              <button type="submit" class="btn btn-sm <?=isset($u['blocked']) && $u['blocked'] ? 'btn-success' : 'btn-warning'?>">
                <?=isset($u['blocked']) && $u['blocked'] ? 'Unblock' : 'Block'?>
              </button>
            </form>
            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
              <button type="submit" name="delete_user" value="<?=$u['id']?>" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
