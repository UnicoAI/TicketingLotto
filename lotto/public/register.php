<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
session_start();

include '../config/connection.php';


// Get referral from link (if any)
$referred_by_code = isset($_GET['ref']) ? mysqli_real_escape_string($conn, $_GET['ref']) : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Also get referral from hidden form input (in case link param is lost)
    $referred_by_code = isset($_POST['referred_by_code']) ? mysqli_real_escape_string($conn, $_POST['referred_by_code']) : null;

    if ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        $result = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($result) > 0) {
            $error = "Email already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Generate unique referral code for new user
            $referral_code = 'REF' . strtoupper(bin2hex(random_bytes(3)));

            // Insert user with referral data
            $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, is_admin, referral_code, can_refer, referred_by_code) VALUES (?, ?, ?, 0, ?, 1, ?)");
            $stmt->bind_param("sssss", $name, $email, $password_hash, $referral_code, $referred_by_code);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                header('Location: index.php');
                exit();
            } else {
                $error = "Registration failed, please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4" style="width: 320px;">
       
     
   
<h2>Register</h2>

<?php if (!empty($error)) { ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php } ?>

<form method="post" action="register.php<?php if ($referred_by_code) echo '?ref=' . urlencode($referred_by_code); ?>">
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" required class="form-control" />
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" required class="form-control" />
    </div>
    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" required class="form-control" />
    </div>
    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="password_confirm" required class="form-control" />
    </div>

    <!-- Hidden field to retain referral code -->
    <?php if ($referred_by_code): ?>
        <input type="hidden" name="referred_by_code" value="<?= htmlspecialchars($referred_by_code) ?>">
    <?php endif; ?>

    <button type="submit" class="btn btn-primary">Register</button>
</form>
<p class="mt-3">Already have an account? <a href="login.php">Login</a></p>
 <p>
        <a href="../index.php">
            <i class="bi bi-house-door"></i>Home
        </a>
    </p>
 </div>
 </div>
<?php include '../includes/footer.php'; ?>
