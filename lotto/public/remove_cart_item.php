<?php
include '../config/connection.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: view_cart.php');
    exit();
}

$cart_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

header("Location: view_cart.php");
exit();
?>
