<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $winning_price = floatval($_POST['winning_price']);
     $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    // Handle image upload
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $filetmp = $_FILES['photo']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
        } else {
            $newName = uniqid('lottery_').'.'.$ext;
            $uploadPath = '../uploads/'.$newName;
            if (move_uploaded_file($filetmp, $uploadPath)) {
                $photo = $newName;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if (!$error) {
        $sql = "INSERT INTO lotteries (title, winning_price, price, description, photo, created_at) VALUES ('$title', $winning_price, $price, '$description', ";
        $sql .= $photo ? "'$photo'" : "NULL";
        $sql .= ", NOW())";
        if (mysqli_query($conn, $sql)) {
            header('Location: lotteries.php');
            exit();
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2>Add New Lottery</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="add_lottery.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Lottery Title</label>
            <input type="text" name="title" id="title" class="form-control" required maxlength="255" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
        </div>
         <div class="mb-3">
            <label for="price" class="form-label">Price (per ticket price)</label>
            <input type="number" step="0.01" name="winning_price" id="winning_price" class="form-control" required min="0" value="<?= isset($_POST['winning_price']) ? htmlspecialchars($_POST['winning_price']) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="winning_price" class="form-label">Winning Price</label>
            <input type="number" step="0.01" name="winning_price" id="winning_price" class="form-control" required min="0" value="<?= isset($_POST['winning_price']) ? htmlspecialchars($_POST['winning_price']) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Lottery Image (optional)</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Add Lottery</button>
        <a href="lotteries.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
