<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "lottery_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if admin is logged in helper
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['is_admin'] == 1;
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: login.php");
        exit;
    }
}
?>
