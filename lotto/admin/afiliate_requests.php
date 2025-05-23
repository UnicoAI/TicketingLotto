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

include '../includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Affiliate Requests</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        form.inline { display: inline; }
    </style>
</head>
<body>
<h1>Affiliate Requests</h1>

<?php if (!empty($msg)): ?>
    <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<!-- Filter form -->
<form method="GET" style="margin-bottom:20px;">
    <label for="status">Filter by status:</label>
    <select name="status" id="status" onchange="this.form.submit()">
        <option value="">All</option>
        <?php foreach ($statuses as $status_option): ?>
            <option value="<?= $status_option ?>" <?= $filter_status === $status_option ? 'selected' : '' ?>>
                <?= ucfirst($status_option) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<table>
    <thead>
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
        <tr><td colspan="7">No referrals found.</td></tr>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['user_id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['request_date'] ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>
                    <form method="POST" class="inline">
                        <input type="hidden" name="change_status_id" value="<?= $row['id'] ?>" />
                        <select name="new_status" required>
                            <?php foreach ($statuses as $status_option): ?>
                                <option value="<?= $status_option ?>" <?= $row['status'] === $status_option ? 'selected' : '' ?>>
                                    <?= ucfirst($status_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<div style="margin-top:20px;">
    <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(['status' => $filter_status, 'page' => $page - 1]) ?>">Previous</a>
    <?php endif; ?>

    Page <?= $page ?> of <?= $total_pages ?>

    <?php if ($page < $total_pages): ?>
        <a href="?<?= http_build_query(['status' => $filter_status, 'page' => $page + 1]) ?>">Next</a>
    <?php endif; ?>
</div>

</body>
</html>

<?php

include '../includes/footer.php';
?>
