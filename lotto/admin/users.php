<?php
require 'config.php';
redirectIfNotAdmin();

if (isset($_GET['toggle_admin'])) {
    $uid = (int)$_GET['toggle_admin'];
    $res = $mysqli->query("SELECT is_admin FROM users WHERE id=$uid");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $newVal = $row['is_admin'] == 1 ? 0 : 1;
        $mysqli->query("UPDATE users SET is_admin=$newVal WHERE id=$uid");
    }
    header("Location: users.php");
    exit;
}

if (isset($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid != $_SESSION['user']['id']) { // prevent self-delete
        $mysqli->query("DELETE FROM users WHERE id=$uid");
    }
    header("Location: users.php");
    exit;
}

$res = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h1 class="mb-4 fw-bold text-primary">Manage Users</h1>
  
    <a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <div class="table-responsive">
                <table class="table table-hover table-bordered text-center align-middle">
                    <thead class="table-primary">
       <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Admin</th><th>Created At</th><th>Actions</th>
        </tr></thead>
        <tbody>
            <?php while($user = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
                <td><?= $user['created_at'] ?></td>
                <td>
                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                    <a href="?toggle_admin=<?= $user['id'] ?>" class="btn btn-sm btn-warning"><?= $user['is_admin'] ? 'Revoke Admin' : 'Make Admin' ?></a>
                    <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                    <?php else: ?>
                    <em>Yourself</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
</div>
</div>
</body>
</html>
