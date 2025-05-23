<?php
include '../config/connection.php';

$lottery_id = intval($_GET['id']);
$tickets = mysqli_query($conn, "SELECT id FROM tickets WHERE lottery_id = $lottery_id");

$ticket_ids = [];
while ($row = mysqli_fetch_assoc($tickets)) {
  $ticket_ids[] = $row['id'];
}

if (count($ticket_ids) > 0) {
  $winner_ticket_id = $ticket_ids[array_rand($ticket_ids)];
  mysqli_query($conn, "UPDATE lotteries SET winner_ticket_id = $winner_ticket_id, is_drawn = 1 WHERE id = $lottery_id");
  echo "Winner drawn successfully. Ticket ID: $winner_ticket_id";
} else {
  echo "No tickets sold for this lottery.";
}
?>
