<?php
require 'config.php';

if (isset($_POST['email'], $_POST['password'])) {
    $email = $mysqli->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $res = $mysqli->query("SELECT * FROM users WHERE email='$email' AND is_admin=1");
    if ($res && $res->num_rows == 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = $user;
            header("Location: index.php");
            exit;
        }
    }
    $error = "Invalid email or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
<div class="card p-4" style="width: 320px;">
    <h3 class="mb-3">Admin Login</h3>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required class="form-control mb-2" />
        <input type="password" name="password" placeholder="Password" required class="form-control mb-3" />
        <button class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>
