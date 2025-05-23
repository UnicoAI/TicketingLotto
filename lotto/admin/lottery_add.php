<?php
require 'config.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
     $price = (float)($_POST['price'] ?? 0);
    $winning_price = (float)($_POST['winning_price'] ?? 0);
    $money_to_raise = (float)($_POST['money_to_raise'] ?? 0);
     $category = $_POST['category'] ?? '';
     $expiry_date = $_POST['expiry_date'] ?? null;  // New expiry date field
    $error = null;
    $photoPath = null;

    if (!$title || $winning_price <= 0 || $money_to_raise <= 0) {
        $error = "Please fill all required fields with valid values.";
    }

    // Handle file upload
    if (empty($error)) {
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileTmpPath = $_FILES['photo']['tmp_name'];
            $fileName = basename($_FILES['photo']['name']);
            $fileType = $_FILES['photo']['type'];

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
            } else {
                $destPath = $uploadDir . $fileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $photoPath = '/../uploads/' . $fileName;
                } else {
                    $error = "Error uploading the file.";
                }
            }
        } else {
            $error = "Please upload a photo.";
        }
    }

    if (empty($error)) {
        $stmt = $mysqli->prepare("INSERT INTO lotteries (title, category, description, photo, price, winning_price, money_to_raise, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddds", $title, $category, $description, $photoPath, $price, $winning_price, $money_to_raise, $expiry_date);
        if ($stmt->execute()) {
            header("Location: lotteries.php");
            exit;
        } else {
            $error = "Database error: " . $mysqli->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Add Lottery</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h2>Add New Lottery</h2>
    <a href="lotteries.php" class="btn btn-secondary mb-3">Back to Lotteries</a>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" />
        </div>
          <div class="mb-3">
            <label class="form-label">Category *</label>
            
            <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="ending-soon" <?= ($_POST['category'] ?? '') == 'Champions League' ? 'selected' : '' ?>>Ending Soon</option>
                <option value="instant-wins" <?= ($_POST['category'] ?? '') == 'Europa League' ? 'selected' : '' ?>>Instant Wins</option>
                <option value="cars-and-bikes" <?= ($_POST['category'] ?? '') == 'Meciul Zilei' ? 'selected' : '' ?>>Cars&Bikes</option>
                <option value="cash" <?= ($_POST['category'] ?? '') == 'Biletul Zilei' ? 'selected' : '' ?>>Cash</option>
               <option value="tech-and-luxury" <?= ($_POST['category'] ?? '') == 'Biletul Zilei' ? 'selected' : '' ?>>Tech-and-luxury</option>
        </select>
         
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Photo *</label>
            <input type="file" name="photo" class="form-control" accept="image/*" required />
        </div>
           <div class="mb-3">
            <label for="price" class="form-label">Price (per ticket price)</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" required min="0" value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Winning Price *</label>
            <input type="number" step="0.01" name="winning_price" class="form-control" required value="<?= htmlspecialchars($_POST['winning_price'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label class="form-label">Money to Raise (Goal) *</label>
            <input type="number" step="0.01" name="money_to_raise" class="form-control" required value="<?= htmlspecialchars($_POST['money_to_raise'] ?? '') ?>" />
        </div>
        <div class="mb-3">
    <label for="expiry_date" class="form-label">Expiry Date</label>
    <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
           value="<?= htmlspecialchars($_POST['expiry_date'] ?? '') ?>" />
</div>

        <button type="submit" class="btn btn-primary">Add Lottery</button>
    </form>
</div>
</body>
</html>
