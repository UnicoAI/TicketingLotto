<?php
//session_start();
include '../config/connection.php'; // provides $conn
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$pending_request = false;

$stmt2 = $conn->prepare("SELECT status FROM referrals WHERE user_id = ? AND status = 'pending' LIMIT 1");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$stmt2->bind_result($pending_status);
if ($stmt2->fetch()) {
    $pending_request = true;
}
$stmt2->close();
// Handle form submission for updating user profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['request_affiliate'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $referral_code_entered = trim($_POST['referred_by_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validate inputs
    if (empty($name) || empty($email)) {
        $error = "Name and email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== '' && $password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email is unique for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email is already in use by another account.";
        }
        $stmt->close();
    }

    if (empty($error)) {
        // Update user data
        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password_hash = ?, referred_by_code = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $password_hash, $referral_code_entered, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, referred_by_code = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $referral_code_entered, $user_id);
        }

        if ($stmt->execute()) {
            $message = "Account updated successfully.";
            $_SESSION['user_name'] = $name; // update session display name if used
        } else {
            $error = "Failed to update account.";
        }
        $stmt->close();
    }
}

// Fetch current user info, including affiliate status and codes
$stmt = $conn->prepare("SELECT name, email, referral_code, referred_by_code, can_refer FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $referral_code, $referred_by_code, $can_refer);
$stmt->fetch();
$stmt->close();

$conn->close();
$referral_link = "http://localhost/lotto/public/register.php?ref=" . urlencode($referral_code);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Account</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
    <h1>My Account</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?=htmlspecialchars($message)?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

   <?php if ($can_refer): ?>
    <div class="alert alert-info">You are an affiliate and can share your referral code!
         <div class="mb-3">
            <label class="form-label">Your Referral Code</label>
            <input readonly class="form-control-plaintext" value="<?=htmlspecialchars($referral_code ?: 'Not available')?>" />
            <div class="input-group mb-2">
                <input type="text" class="form-control" value="<?= $referral_link ?>" id="refLink" readonly>
                <button class="btn btn-warning" type="button" onclick="copyLink()">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
</svg>
                    Copy Link
                </button>
            </div>

<script>
function copyLink() {
    var copyText = document.getElementById("refLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Referral link copied!");
}
</script>

        </div>
    </div>
<?php elseif ($pending_request): ?>
    <button class="btn btn-secondary" disabled>Affiliate request pending approval</button>
<?php else: ?>
    <form method="POST" action="request_affiliate.php">
        <button type="submit" class="btn btn-success">Request to Become Affiliate</button>
    </form>
<?php endif; ?>

    <form method="POST" action="my_account.php" class="mb-4" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input required type="text" class="form-control" id="name" name="name" value="<?=htmlspecialchars($name)?>" />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input required type="email" class="form-control" id="email" name="email" value="<?=htmlspecialchars($email)?>" />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" />
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" />
        </div>

        <div class="mb-3">
            <label class="form-label">Your Referral Code</label>
            <input readonly class="form-control-plaintext" value="<?=htmlspecialchars($referral_code ?: 'Not available')?>" />
        </div>

        <div class="mb-3">
            <label for="referred_by_code" class="form-label">Referral Code You Entered</label>
            <input type="text" class="form-control" id="referred_by_code" name="referred_by_code" value="<?=htmlspecialchars($referred_by_code)?>" placeholder="Enter referral code if any" />
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

</div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
