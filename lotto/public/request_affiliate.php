<?php
session_start();
include '../config/connection.php';  // $conn is your connection variable

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user already requested or is affiliate
$stmt = $conn->prepare("SELECT can_refer FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($can_refer);
$stmt->fetch();
$stmt->close();

if ($can_refer) {
    // Already affiliate, redirect back or show message
    header("Location: my_account.php?msg=already_affiliate");
    exit();
}

// Check if already has a pending request
$stmt = $conn->prepare("SELECT id FROM referrals WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Already requested, redirect back or show message
    $stmt->close();
    header("Location: my_account.php?msg=request_pending");
    exit();
}
$stmt->close();

// Insert new request
$stmt = $conn->prepare("INSERT INTO referrals (user_id) VALUES (?)");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $stmt->close();
    header("Location: my_account.php?msg=request_submitted");
} else {
    $stmt->close();
    header("Location: my_account.php?msg=request_failed");
}
exit();
