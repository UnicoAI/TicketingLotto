<?php
include '../config/connection.php';
session_start();
$user_id = 1; // Replace with actual session user ID

foreach ($_POST['qty'] as $cart_id => $quantity) {
  $cart_id = intval($cart_id);
  $quantity = intval($quantity);
  if ($quantity > 0) {
    mysqli_query($conn, "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id");
  } else {
    mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
  }
}
header("Location: view_cart.php");
?>
