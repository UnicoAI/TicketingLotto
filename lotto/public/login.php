<?php
include '../config/connection.php';
session_start();

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

<?php include '../includes/header.php'; ?>

<h2>Login</h2>

<?php if (!empty($error)) { ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php } ?>

<form method="post" action="login.php">
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

<?php include '../includes/footer.php'; ?>
