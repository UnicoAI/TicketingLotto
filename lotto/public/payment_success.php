<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Empty the cart
$conn->query("DELETE FROM cart WHERE user_id = $user_id");

// Send confirmation email
$user_res = $conn->query("SELECT email FROM users WHERE id = $user_id");
$user = $user_res->fetch_assoc();

$to = $user['email'];
$subject = "Payment Confirmation - Lottery Ticket Purchase";
$message = "Dear User,\n\nThank you for your payment. Your lottery tickets have been successfully purchased.\n\nBest regards,\nUnixDex Team";
$headers = "From: noreply@unixdex.com";

mail($to, $subject, $message, $headers);

// Redirect user or show confirmation
echo "<h2>Thank you for your payment!</h2>";
echo "<p>A confirmation email has been sent to {$to}.</p>";
echo "<p><a href='https://unixdex.com/wallet'>Go to Wallet</a></p>";
?>
