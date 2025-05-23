<?php
session_start();

$user_id = $_SESSION['user_id'] ?? null; // get current user ID, or null if not logged in
$cart_count = 0;

if ($user_id) {
    // Database connection (adjust parameters accordingly)
    $mysqli = new mysqli('localhost', 'root', '', 'lottery_db');

    if ($mysqli->connect_error) {
        die('Database connection error: ' . $mysqli->connect_error);
    }

    // Query total quantity from cart for this user
    $stmt = $mysqli->prepare("SELECT SUM(quantity) as total_quantity FROM cart WHERE user_id = ? AND expiry_date > NOW()");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($total_quantity);
    $stmt->fetch();
    $stmt->close();
    $mysqli->close();

    $cart_count = $total_quantity ?: 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lottery System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/public/index.php">Coin Machine</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
      aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation"><span
        class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="lotteries.php">Draws</a></li>
            <li class="nav-item"><a class="nav-link" href="results.php">Draws Results</a></li>
          <li class="nav-item">
  <a class="nav-link position-relative" href="view_cart.php">
    <i class="bi bi-cart3" style="font-size: 1.2rem;"></i> 
    <?php if ($cart_count > 0): ?>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        <?= $cart_count ?>
        <span class="visually-hidden">items in cart</span>
      </span>
    <?php endif; ?>
  </a>
</li>

        
          <?php if (!empty($_SESSION['is_admin'])): ?>
            <li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin</a></li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
              <li><a class="dropdown-item" href="my_account.php">My Account</a></li>
              <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout (<?=htmlspecialchars($_SESSION['user_name'])?>)</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid p-0 m-0" style="width:100% !important;">
