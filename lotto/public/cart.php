<?php
include '../config/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['user_id'];
    $lottery_id = intval($_POST['lottery_id']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $expiry_date = $_POST['expiry_date'];

    if ($lottery_id > 0 && $quantity > 0 && $price > 0 && strtotime($expiry_date)) {
        // Check if this lottery already exists in the user's cart
        $checkQuery = "SELECT id FROM cart WHERE user_id = ? AND lottery_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $user_id, $lottery_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity if already exists
            $updateQuery = "UPDATE cart SET quantity = quantity + ?, added_at = NOW() WHERE user_id = ? AND lottery_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iii", $quantity, $user_id, $lottery_id);
        } else {
            // Insert new cart entry
            $insertQuery = "INSERT INTO cart (user_id, lottery_id, price, quantity, expiry_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iidis", $user_id, $lottery_id, $price, $quantity, $expiry_date);
        }

        if ($stmt->execute()) {
            header('Location: view_cart.php');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid input.";
    }
} else {
    header('Location: lotteries.php');
    exit();
}
?>
