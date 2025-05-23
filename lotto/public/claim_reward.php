<?php
include '../config/connection.php';
session_start();
$user_id = 1; // Replace with actual session user ID

$ticket_id = intval($_GET['ticket_id']);
$ticket = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tickets WHERE id = $ticket_id AND user_id = $user_id"));

if ($ticket) {
  $lottery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM lotteries WHERE id = {$ticket['lottery_id']}"));
  if ($lottery['winner_ticket_id'] == $ticket_id && !$ticket['claimed']) {
    mysqli_query($conn, "UPDATE tickets SET claimed = 1 WHERE id = $ticket_id");
    echo "Reward claimed successfully!";
  } else {
    echo "This ticket is not a winning ticket or reward already claimed.";
  }
} else {
  echo "Ticket not found.";
}
?>
