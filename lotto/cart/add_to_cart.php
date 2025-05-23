<?php
include '../config/connection.php';
session_start();
$user_id = 1; // Replace with $_SESSION['user_id']
$lottery_id = $_POST['lottery_id'];
$quantity = $_POST['quantity'];

$check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND lottery_id=$lottery_id");
if (mysqli_num_rows($check)) {
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id=$user_id AND lottery_id=$lottery_id");
} else {
    mysqli_query($conn, "INSERT INTO cart(user_id, lottery_id, quantity) VALUES ($user_id, $lottery_id, $quantity)");
}
header("Location: view_cart.php");
