<?php
require 'config.php';
redirectIfNotAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<nav class="navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="index.php">Admin Panel</a>
    <div class="ms-auto pe-3">
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
    <div class="list-group">
        <a href="users.php" class="list-group-item list-group-item-action">Manage Users</a>
        <a href="lotteries.php" class="list-group-item list-group-item-action">Manage Lotteries</a>
        <a href="afiliate_requests.php" class="list-group-item list-group-item-action">Afiliate Requests</a>
    </div>
</div>
</body>
</html>
