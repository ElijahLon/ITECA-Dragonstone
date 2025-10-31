<?php
session_start();
require_once 'inc/db.php';
$db = db();

$errors = [];
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $surname = trim($_POST['surname']);
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $agree = isset($_POST['agree']);

    // Validation
    if (empty($first_name) || !preg_match("/^[a-zA-Z]+$/", $first_name))
        $errors[] = "Please enter a valid first name (letters only).";
    if (empty($surname) || !preg_match("/^[a-zA-Z]+$/", $surname))
        $errors[] = "Please enter a valid surname (letters only).";
    if (!preg_match("/^\d{9}$/", $phone_number))
        $errors[] = "Phone number must have 9 digits (after +27).";
    else {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0)
            $errors[] = "This phone number is already registered.";
        $stmt->close();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Please enter a valid email address.";
    else {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0)
            $errors[] = "This email is already registered.";
        $stmt->close();
    }
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/", $password))
        $errors[] = "Password must be 8+ chars with upper, lower, number, and special character.";
    if ($password !== $confirm_password)
        $errors[] = "Passwords do not match.";
    if (!$agree)
        $errors[] = "You must agree to the Terms & Conditions.";

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';
        $full_name = trim($first_name . ' ' . $surname);
        $phone = $phone_number;

        $stmt = $db->prepare("INSERT INTO users (first_name, surname, phone, email, role, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $first_name, $surname, $phone, $email, $role, $hashed_password);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id; // Auto-login after registration
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: " . $redirect);
            exit;
        } else $errors[] = "Database error: Please try again.";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - DragonStone</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #f8f9fa;
  font-family: 'Inter', sans-serif;
}
.register-container {
  max-width: 500px;
  margin: 80px auto;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 5px 30px rgba(0,0,0,0.08);
  padding: 40px 50px;
  transition: all 0.3s ease;
}
.register-container:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 35px rgba(0,0,0,0.1);
}
.register-header {
  text-align: center;
  margin-bottom: 30px;
}
.register-header img {
  width: 80px;
  height: auto;
  margin-bottom: 10px;
}
.register-header h2 {
  font-weight: 600;
  font-size: 1.6rem;
  letter-spacing: 0.5px;
}
.btn-primary {
  background-color: #111;
  border: none;
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  font-weight: 600;
  letter-spacing: 0.3px;
  transition: all 0.2s ease;
}
.btn-primary:hover {
  background-color: #333;
}
input.form-control {
  border-radius: 10px;
  padding: 10px 14px;
}
.phone-container {
  display: flex;
  align-items: center;
  gap: 10px;
}
.phone-container span {
  background: #e9ecef;
  border-radius: 10px;
  padding: 10px 15px;
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

<div class="register-container">
  <div class="register-header">
    <h2>Create Your Account</h2>
    <p class="text-muted">Join the DragonStone community today.</p>
  </div>

  <?php if (!empty($msg)): ?>
    <div class="alert alert-info">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
    </div>
  <?php elseif (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">First Name</label>
      <input type="text" name="first_name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Surname</label>
      <input type="text" name="surname" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Phone Number</label>
      <div class="phone-container">
        <span>+27</span>
        <input type="text" name="phone_number" class="form-control" placeholder="123456789" pattern="\d{9}" required>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="agree" id="agree" required>
      <label class="form-check-label" for="agree">
        I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>.
      </label>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
  </form>
    <div class="register-link">
      Already have an account? <a href="login.php">login here</a>.
    </div>

  <div class="text-center mt-3">
    <a href="index.php" class="return-home">← Return to Home</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
