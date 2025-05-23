<?php
require 'config.php';
redirectIfNotAdmin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // First get the photo path to delete the image file if exists
    $res = $mysqli->query("SELECT photo FROM lotteries WHERE id=$id");
    if ($res && $res->num_rows) {
        $lottery = $res->fetch_assoc();
        if (!empty($lottery['photo']) && file_exists($lottery['photo'])) {
            @unlink($lottery['photo']);
        }
    }

    // Delete the lottery record
    $mysqli->query("DELETE FROM lotteries WHERE id=$id");

    header("Location: lotteries.php");
    exit;
}

if (isset($_GET['toggle_active'])) {
    $id = (int)$_GET['toggle_active'];
    $res = $mysqli->query("SELECT is_active FROM lotteries WHERE id=$id");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $newVal = $row['is_active'] == 1 ? 0 : 1;
        $mysqli->query("UPDATE lotteries SET is_active=$newVal WHERE id=$id");
    }
    header("Location: lotteries.php");
    exit;
}

$res = $mysqli->query("SELECT * FROM lotteries ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Lotteries</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
<style>
    .thumbnail {
        max-width: 100px;
        height: auto;
    }
</style>
</head>
<body>
<div class="container mt-4">
    <h2>Lotteries Management</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <a href="lottery_add.php" class="btn btn-success mb-3">Add Lottery</a>
    <table class="table table-bordered table-striped">
        <thead><tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Title</th>
            <th>Price</th>
            <th>Winning Price</th>
            <th>Goal (Money to Raise)</th>
            <th>Total Raised</th>
            <th>Active</th>
            <th>Created At</th>
            <th>Expiry Date</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
            <?php while($lottery = $res->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$lottery['id'] ?></td>
                <td>
                    <?php if ($lottery['photo']): ?>
            <img src="../<?= htmlspecialchars($lottery['photo']) ?>" class="card-img-top" alt="Lottery photo">
            <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($lottery['title']) ?></td>
                  <td>$<?= number_format($lottery['price'], 2) ?></td>
                <td>$<?= number_format($lottery['winning_price'], 2) ?></td>
                <td>
                  $<?= number_format($lottery['money_to_raise'], 2) ?>
                  <?php
                    $goal = floatval($lottery['money_to_raise']);
                    $raised = floatval($lottery['total_raised']);
                    $percent = $goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0;
                  ?>
                  <div class="progress mt-1" style="height: 18px;">
                    <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
                      <?= $percent ?>%
                    </div>
                  </div>
                </td>
                <td>$<?= number_format($lottery['total_raised'], 2) ?></td>
                <td><?= $lottery['is_active'] ? 'Yes' : 'No' ?></td>
                <td><?= htmlspecialchars($lottery['created_at']) ?></td>
                <td><?= htmlspecialchars($lottery['expiry_date']) ?></td>
                <td>
                    <a href="lottery_edit.php?id=<?= (int)$lottery['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="lottery_draw.php?id=<?= (int)$lottery['id'] ?>" class="btn btn-sm btn-info">Draw Winner</a>
                    <a href="?toggle_active=<?= (int)$lottery['id'] ?>" class="btn btn-sm btn-warning">
                        <?= $lottery['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </a>
                    <a href="?delete=<?= (int)$lottery['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this lottery?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
