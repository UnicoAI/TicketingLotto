<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

session_start();
include '../includes/header.php';
include '../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Email not registered.";
    }
}
?>


<div class="d-flex justify-content-center align-items-center vh-100">


<?php if (!empty($error)) { ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php } ?>
<div class="card p-4" style="width: 320px;">
<form method="post" action="login.php">
    <h2>Login</h2>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" required class="form-control" />
    </div>
    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" required class="form-control" />
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>
    <p class="mt-3">Don't have an account? <a href="register.php">Register</a></p>
    <p>
        <a href="../index.php">
            <i class="bi bi-house-door"></i>Home
        </a>
    </p>
</div>
</div>
<?php include '../includes/footer.php'; ?>
