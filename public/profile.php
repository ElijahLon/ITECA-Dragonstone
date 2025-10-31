<?php
require_once __DIR__.'/inc/db.php';
require_once __DIR__.'/inc/auth.php';
require_login();
$user = current_user();
$db = db();
$msg = '';
$msgType = '';

// Fetch current subscription
$subscription = null;
$stmt = $db->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND active = 1 ORDER BY id DESC LIMIT 1');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $subscription = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (empty($name) || empty($email)) {
        $msg = 'Name and email are required.';
        $msgType = 'danger';
    } else {
        $verify = true;
        if (!empty($new_password) || $email !== $user['email']) {
            $stmt = $db->prepare('SELECT password FROM users WHERE (user_id = ? OR id = ?) LIMIT 1');
            $stmt->bind_param('ii', $user['id'], $user['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stored_hash = $row['password'] ?? '';
            $verify = password_verify($current_password, $stored_hash);
            if (!$verify) {
                $msg = 'Current password is incorrect.';
                $msgType = 'danger';
            }
        }

        if ($verify) {
            $sql = 'UPDATE users SET name = ?, email = ?, phone = ?';
            $params = [$name, $email, $phone];
            $types = 'sss';

            if (!empty($new_password)) {
                $sql .= ', password = ?';
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                $types .= 's';
            }
                $sql .= ' WHERE user_id = ?';
                $params[] = $user['id']; // assuming current_user() sets $user['id'] as user_id
                $types .= 'i';

                $stmt = $db->prepare($sql);
                $stmt->bind_param($types, ...$params);


            if ($stmt->execute()) {
                $msg = 'Profile updated successfully.';
                $msgType = 'success';
                $user = current_user();
            } else {
                $msg = 'Error updating profile: ' . $db->error;
                $msgType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your Profile - DragonStone</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #f8f9fa;
  font-family: 'Inter', sans-serif;
  color: #111;
}
.profile-wrapper {
  max-width: 1000px;
  margin: 60px auto;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 5px 30px rgba(0,0,0,0.08);
  padding: 40px 60px;
}
h2 {
  font-weight: 600;
  margin-bottom: 25px;
}
.form-control {
  border-radius: 10px;
  padding: 10px 14px;
}
.btn-dark {
  background-color: #111;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  transition: all 0.2s ease;
}
.btn-dark:hover {
  background-color: #333;
}
.card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 3px 15px rgba(0,0,0,0.05);
}
.alert {
  border-radius: 10px;
}
.account-summary {
  background: #f1f3f5;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
}
.account-summary strong {
  display: block;
  font-size: 1.1rem;
}
.account-summary p {
  color: #555;
  margin: 0;
}
.register-link {
  font-size: 15px;
  margin-top: 20px;
  color: #333;
  text-align: right;
}

.register-link a {
  color: #000;
  text-decoration: none;
  font-weight: 500;
  transition: 0.2s;
}

.register-link a:hover {
  text-decoration: underline;
  color: #0149ac;
}

</style>
</head>
<body>

<div class="profile-wrapper">
  <div class="text-center mb-4">
    <img src="assets/img/logo.png" alt="Logo" style="height:150px;">
    <h2 class="mt-3">Your Profile</h2>
    <p class="text-muted">Manage your personal details and account security.</p>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-<?=$msgType?>"><?=htmlspecialchars($msg)?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['first_name'])?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>" required>
          <div class="form-text">Changing your email requires password verification.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-control" value="<?=htmlspecialchars($user['phone'] ?? '')?>">
        </div>

        <hr>

        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control">
          <div class="form-text">Required to change your email or password.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control">
          <div class="form-text">Leave blank to keep your current password.</div>
        </div>

        <button type="submit" class="btn btn-dark mt-3">Update Profile</button>
      </form>

        <div class="text-right mt-4 register-link">
        <a href="index.php" class="return-home">
            ← Return to Home
        </a>
        </div>

    </div>
        

    <div class="col-lg-4">
      <div class="card p-3">
        <div class="card-body text-center">
          <h5 class="card-title mb-3">Account Summary</h5>
          <div class="account-summary">
            <strong>EcoPoints</strong>
            <p><?=intval($user['eco_points'])?></p>
          </div>
          <div class="account-summary mt-3">
            <strong>Member Since</strong>
            <p><?=date('F j, Y', strtotime($user['created_at'] ?? 'now'))?></p>
          </div>
          <?php if ($subscription): ?>
            <div class="account-summary mt-3">
              <strong>Current Subscription</strong>
              <p><?=htmlspecialchars($subscription['plan'])?></p>
              <small>Next charge: <?=date('F j, Y', strtotime($subscription['next_charge']))?></small>
            </div>
          <?php else: ?>
            <div class="account-summary mt-3">
              <strong>Subscription</strong>
              <p>No active subscription</p>
              <a href="subscription.php" class="btn btn-sm btn-outline-dark mt-2">Subscribe Now</a>
            </div>
          <?php endif; ?>
        </div>
      </div>


    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
