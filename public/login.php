<?php
require_once __DIR__ . '/inc/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$db = db();

$errors = [];
$identifier = '';
$msg = $_GET['msg'] ?? '';

// Token-based "remember me" auto-login
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
  $cookie = $_COOKIE['remember_token'];
  $cookie_hash = hash('sha256', $cookie);
  $stmt = $db->prepare('SELECT user_id FROM remember_tokens WHERE token = ? AND expires > NOW() LIMIT 1');
  $stmt->bind_param('s', $cookie_hash);
  $stmt->execute();
  $rt = $stmt->get_result()->fetch_assoc();
  if ($rt && !empty($rt['user_id'])) {
    $_SESSION['user_id'] = (int)$rt['user_id'];
    $newToken = bin2hex(random_bytes(32));
    $newHash = hash('sha256', $newToken);
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $ins = $db->prepare('INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)');
    $ins->bind_param('iss', $rt['user_id'], $newHash, $expires);
    $ins->execute();
    $del = $db->prepare('DELETE FROM remember_tokens WHERE token = ?');
    $del->bind_param('s', $cookie_hash);
    $del->execute();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('remember_token', $newToken, time() + 30*24*60*60, '/', '', $secure, true);
    header('Location: index.php');
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identifier = trim($_POST['identifier'] ?? '');
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember_me']);

  if ($identifier === '') {
    $errors[] = 'Please enter your email or phone number.';
  } elseif (strpos($identifier, '@') !== false && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
  } elseif (!strpos($identifier, '@') && !preg_match('/^\+?[0-9]{7,15}$/', $identifier)) {
    $errors[] = 'Please enter a valid phone number.';
  }

  if ($password === '') $errors[] = 'Please enter your password.';

  if (empty($errors)) {
    $stmt = $db->prepare('SELECT user_id AS id, password FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = (int)$user['id'];

      // Award daily login EcoPoints (5 points per login, max once per day)
      $today = date('Y-m-d');
      $stmt = $db->prepare('SELECT COUNT(*) as login_count FROM ecopoints_ledger WHERE user_id = ? AND reason = ? AND DATE(created_at) = ?');
      $reason = "Daily login";
      $stmt->bind_param('iss', $user['id'], $reason, $today);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();

      if ($result['login_count'] == 0) {
        $points = 5;
        // Update user eco_points
        $stmt = $db->prepare('UPDATE users SET eco_points = eco_points + ? WHERE user_id = ?');
        $stmt->bind_param('ii', $points, $user['id']);
        $stmt->execute();

        // Log in ecopoints_ledger
        $stmt = $db->prepare('INSERT INTO ecopoints_ledger (user_id, points, reason) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $user['id'], $points, $reason);
        $stmt->execute();
      }

      $db->query("DELETE FROM remember_tokens WHERE expires < NOW()");
      if ($remember) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        $ins = $db->prepare('INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)');
        $ins->bind_param('iss', $user['id'], $token_hash, $expires);
        $ins->execute();
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('remember_token', $token, time() + 30*24*60*60, '/', '', $secure, true);
      }
      $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
      unset($_SESSION['redirect_after_login']);
      header('Location: ' . $redirect);
      exit;
    } else {
      $errors[] = 'Invalid email/phone or password.';
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - DragonStone</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9f9f9;
      font-family: 'Poppins', sans-serif;
      color: #111;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .login-box {
      background: #fff;
      padding: 50px 60px;
      border-radius: 12px;
      box-shadow: 0 4px 25px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 420px;
      text-align: center;
    }
    .login-title {
      font-size: 26px;
      font-weight: 500;
      letter-spacing: 1px;
      margin-bottom: 25px;
      text-transform: uppercase;
    }
    .form-control {
      border: 1px solid #ccc;
      border-radius: 0;
      padding: 10px;
      font-size: 14px;
    }
    .form-control:focus {
      border-color: #000;
      box-shadow: none;
    }
    .btn-login {
      background: #000;
      color: #fff;
      border-radius: 0;
      padding: 12px 0;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 1px;
      transition: background 0.2s ease-in-out;
    }
    .btn-login:hover {
      background: #333;
    }
    .error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c2c7;
      border-radius: 6px;
      padding: 10px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: left;
    }
    .form-check-label {
      font-size: 14px;
      color: #555;
    }
    .register-link {
      font-size: 14px;
      margin-top: 15px;
      color: #333;
    }
    .register-link a {
      color: #000;
      text-decoration: none;
      font-weight: 500;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
    
    .return-home {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
      color: #555;
      font-size: 0.9rem;
      transition: 0.2s;
    }
    .return-home:hover {
      color: #000;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2 class="login-title">Sign In</h2>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-info">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="error">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3 text-start">
        <label for="identifier" class="form-label">Email or Phone</label>
        <input type="text" class="form-control" id="identifier" name="identifier" value="<?= htmlspecialchars($identifier) ?>" autofocus>
      </div>
      <div class="mb-3 text-start">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password">
      </div>
      <div class="form-check text-start mb-4">
        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
        <label class="form-check-label" for="remember_me">Remember me</label>
      </div>
      <button type="submit" class="btn btn-login w-100">Login</button>
    </form>

    <div class="register-link">
      Don't have an account? <a href="register.php">Register here</a>.
    </div>
    <div class="text-center mt-3">
    <a href="index.php" class="return-home">← Return to Home</a>
  </div>
  </div>
</body>
</html>
