<?php
include '../config/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $cart_id => $qty) {
        $cart_id = intval($cart_id);
        $qty = max(1, intval($qty));

        $query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $qty, $cart_id, $_SESSION['user_id']);
        $stmt->execute();
    }
}

header('Location: view_cart.php');
exit();
?>
