<?php
include '../config/connection.php';
session_start();
$user_id = 1; // Replace with actual session user ID
$cart_id = intval($_GET['id']);

mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
header("Location: view_cart.php");
?>
