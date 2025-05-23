<?php
require 'config.php';
redirectIfNotAdmin();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header("Location: lotteries.php");
    exit;
}

$error = null;

// Fetch existing lottery
$stmt = $mysqli->prepare("SELECT * FROM lotteries WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$lottery = $result->fetch_assoc();

if (!$lottery) {
    header("Location: lotteries.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $winning_price = (float) ($_POST['winning_price'] ?? 0);
    $money_to_raise = (float) ($_POST['money_to_raise'] ?? 0);
     $category = $_POST['category'] ?? '';
         $expiry_date = $_POST['expiry_date'] ?? null;  // New expiry date field

    if (!$title || $winning_price <= 0 || $money_to_raise <= 0) {
        $error = "Please fill all required fields with valid values.";
    }

    $photoPath = $lottery['photo']; // keep old photo if not updated

    if (empty($error)) {
        // Handle photo upload if a new file is provided
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            $fileTmpPath = $_FILES['photo']['tmp_name'];
            $fileName = basename($_FILES['photo']['name']);
            $fileType = $_FILES['photo']['type'];

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
            } else {
                $destPath = $uploadDir . $fileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Delete old photo file if exists
                    if ($lottery['photo'] && file_exists(__DIR__ . '/' . $lottery['photo'])) {
                        unlink(__DIR__ . '/' . $lottery['photo']);
                    }
                    $photoPath = '/../uploads/' . $fileName;
                } else {
                    $error = "Error uploading the file.";
                }
            }
        }
    }

    if (empty($error)) {
        $stmt = $mysqli->prepare("UPDATE lotteries SET title = ?, category = ?, description = ?, photo = ?, price = ?, winning_price = ?, money_to_raise = ?, expiry_date = ? WHERE id = ?");
        $stmt->bind_param("ssssdddsi", $title, $category, $description, $photoPath, $price, $winning_price, $money_to_raise, $expiry_date, $id);
        if ($stmt->execute()) {
            header("Location: lotteries.php");
            exit;
        } else {
            $error = "Database error: {$mysqli->error}";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Edit Lottery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h2>Edit Lottery</h2>
        <a href="lotteries.php" class="btn btn-secondary mb-3">Back to Lotteries</a>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required
                    value="<?= htmlspecialchars($_POST['title'] ?? $lottery['title']) ?>" />
            </div>
            <div class="mb-3">
                <label class="form-label">Category *</label>
                <select name="category" class="form-select" required>
                    <option value="">Select category</option>
                    <option value="ending-soon" <?= (($_POST['category'] ?? $lottery['category']) == 'ending-soon') ? 'selected' : '' ?>>Ending Soon</option>
                    <option value="instant-wins" <?= (($_POST['category'] ?? $lottery['category']) == 'instant-wins') ? 'selected' : '' ?>>Instant Wins</option>
                    <option value="cars-and-bikes" <?= (($_POST['category'] ?? $lottery['category']) == 'cars-and-bikes') ? 'selected' : '' ?>>Cars &amp; Bikes</option>
                    <option value="cash" <?= (($_POST['category'] ?? $lottery['category']) == 'cash') ? 'selected' : '' ?>>Cash</option>
                    <option value="tech-and-luxury" <?= (($_POST['category'] ?? $lottery['category']) == 'tech-and-luxury') ? 'selected' : '' ?>>Tech and Luxury</option>
                </select>
            </div>
        </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description"
                    class="form-control"><?= htmlspecialchars($_POST['description'] ?? $lottery['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Current Photo</label><br />
               <?php if ($lottery['photo']): ?>
            <img src="../<?= htmlspecialchars($lottery['photo']) ?>" class="card-img-top" alt="Lottery photo">
            <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Change Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*" />
                <small class="form-text text-muted">Leave empty to keep current photo.</small>

            </div>

            <div class="mb-3"> <label class="form-label">Price *</label> <input type="number" step="0.01"
                    name="price" class="form-control" required
                    value="<?= htmlspecialchars($_POST['price'] ?? $lottery['price']) ?>" /> </div>
            <div class="mb-3"> <label class="form-label">Winning Price *</label> <input type="number" step="0.01"
                    name="winning_price" class="form-control" required
                    value="<?= htmlspecialchars($_POST['winning_price'] ?? $lottery['winning_price']) ?>" /> </div>
            <div class="mb-3"> <label class="form-label">Money to Raise (Goal) *</label> <input type="number"
                    step="0.01" name="money_to_raise" class="form-control" required
                    value="<?= htmlspecialchars($_POST['money_to_raise'] ?? $lottery['money_to_raise']) ?>" /> </div>
                  <div class="mb-3"> <label class="form-label">Expiry Date</label> <input type="date" name="expiry_date" id="expiry_date" class="form-control"  required
                    value="<?= htmlspecialchars($_POST['expiry_date'] ?? $lottery['expiry_date']) ?>" /> </div>
                    <button type="submit" class="btn btn-primary">Update Lottery</button>
        </form>
    </div>
</body>

</html>