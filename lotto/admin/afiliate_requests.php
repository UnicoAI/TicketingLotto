<?php
//session_start();
include '../config/connection.php';
require 'config.php';
redirectIfNotAdmin();
// Assume admin check here

// Handle status change (approve, reject, or any status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status_id'], $_POST['new_status'])) {
    $change_id = (int)$_POST['change_status_id'];
    $new_status = $_POST['new_status'];

    // Sanitize $new_status, allow only certain statuses
    $allowed_statuses = ['pending', 'approved', 'rejected'];
    if (!in_array($new_status, $allowed_statuses)) {
        $msg = "Invalid status.";
    } else {
        // Get user_id from referrals
        $stmt = $conn->prepare("SELECT user_id FROM referrals WHERE id = ?");
        $stmt->bind_param("i", $change_id);
        $stmt->execute();
        $stmt->bind_result($user_id);
        if ($stmt->fetch()) {
            $stmt->close();

            // If approving, set can_refer and referral_code in users table
            if ($new_status === 'approved') {
                // Generate referral code
                $referral_code = 'REF' . strtoupper(bin2hex(random_bytes(3)));

                $stmt = $conn->prepare("UPDATE users SET can_refer = 1, referral_code = ? WHERE id = ?");
                $stmt->bind_param("si", $referral_code, $user_id);
                $stmt->execute();
                $stmt->close();
            } elseif ($new_status !== 'approved') {
                // If changing from approved to something else, you might want to reset can_refer? Optional:
                // $stmt = $conn->prepare("UPDATE users SET can_refer = 0, referral_code = NULL WHERE id = ?");
                // $stmt->bind_param("i", $user_id);
                // $stmt->execute();
                // $stmt->close();
            }

            // Update referral status
            $stmt = $conn->prepare("UPDATE referrals SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $change_id);
            $stmt->execute();
            $stmt->close();

            $msg = "Referral status updated successfully.";
        } else {
            $stmt->close();
            $msg = "Referral not found.";
        }
    }
}

// Pagination and filtering setup
$statuses = ['pending', 'approved', 'rejected']; // Possible filter statuses

// Get filter and page from GET params
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $statuses) ? $_GET['status'] : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Items per page
$offset = ($page - 1) * $per_page;

// Count total referrals with filter
if ($filter_status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE status = ?");
    $stmt->bind_param("s", $filter_status);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals");
}
$stmt->execute();
$stmt->bind_result($total_items);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_items / $per_page);

// Fetch referrals with join, filter, pagination
if ($filter_status) {
    $stmt = $conn->prepare("SELECT r.id, r.user_id, r.request_date, r.status, u.name, u.email FROM referrals r JOIN users u ON r.user_id = u.id WHERE r.status = ? ORDER BY r.request_date DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $filter_status, $per_page, $offset);
} else {
    $stmt = $conn->prepare("SELECT r.id, r.user_id, r.request_date, r.status, u.name, u.email FROM referrals r JOIN users u ON r.user_id = u.id ORDER BY r.request_date DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$cart_count = 0;
// // Query total quantity from cart for this user
    $stmt = $mysqli->prepare("SELECT SUM(quantity) as total_quantity FROM cart WHERE user_id = ? AND expiry_date > NOW()");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($total_quantity);
    $stmt->fetch();
    $stmt->close();
    $mysqli->close();

    $cart_count = $total_quantity ?: 0;
// Close the statement
?>

<!DOCTYPE html>
<html>
<head>
    <title>Affiliate Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        form.inline { display: inline; }
    </style>
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
            <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout  (<?=htmlspecialchars($_SESSION['user_name'])?>)</a></li>

        
        
         
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h2 class="mb-4 fw-bold text-primary">Affiliate Requests</h2>
            <p class="text-muted">Manage affiliate requests from users below.</p>
<a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
            <?php if (!empty($msg)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <!-- Filter form -->
            <form method="GET" class="row g-3 align-items-center mb-4">
                <div class="col-auto">
                    <label for="status" class="col-form-label fw-semibold">Filter by Status:</label>
                </div>
                <div class="col-auto">
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All</option>
                        <?php foreach ($statuses as $status_option): ?>
                            <option value="<?= $status_option ?>" <?= $filter_status === $status_option ? 'selected' : '' ?>>
                                <?= ucfirst($status_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Referral ID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Change Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="7" class="text-muted">No referrals found.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['user_id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= $row['request_date'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'approved' ? 'success' : ($row['status'] === 'pending' ? 'warning text-dark' : 'secondary') ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-flex justify-content-center gap-2">
                                            <input type="hidden" name="change_status_id" value="<?= $row['id'] ?>">
                                            <select name="new_status" class="form-select form-select-sm w-auto" required>
                                                <?php foreach ($statuses as $status_option): ?>
                                                    <option value="<?= $status_option ?>" <?= $row['status'] === $status_option ? 'selected' : '' ?>>
                                                        <?= ucfirst($status_option) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <?php if ($page > 1): ?>
                        <a class="btn btn-outline-secondary btn-sm" href="?<?= http_build_query(['status' => $filter_status, 'page' => $page - 1]) ?>">← Previous</a>
                    <?php endif; ?>
                </div>

                <span class="fw-semibold">Page <?= $page ?> of <?= $total_pages ?></span>

                <div>
                    <?php if ($page < $total_pages): ?>
                        <a class="btn btn-outline-secondary btn-sm" href="?<?= http_build_query(['status' => $filter_status, 'page' => $page + 1]) ?>">Next →</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php

include '../includes/footer.php';
?>
