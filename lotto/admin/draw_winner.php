<?php
include '../config/connection.php';

$lottery_id = intval($_GET['id']);

$tickets_query = mysqli_query($conn, "SELECT id, user_id FROM tickets WHERE lottery_id = $lottery_id");
$tickets = [];
while ($row = mysqli_fetch_assoc($tickets_query)) {
    $tickets[] = $row;
}

if (count($tickets) > 0) {
    $winner_ticket = $tickets[array_rand($tickets)];
    $winner_ticket_id = $winner_ticket['id'];
    $winner_user_id = $winner_ticket['user_id'];

    $lottery_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT winning_price FROM lotteries WHERE id = $lottery_id"));
    $winning_amount = $lottery_info['winning_price'] ?? 0;

    $drawn_at = date('Y-m-d H:i:s');
    $insert_query = "INSERT INTO lottery_winners (lottery_id, ticket_id, winner_user_id, winning_amount, drawn_at) 
                     VALUES ($lottery_id, $winner_ticket_id, $winner_user_id, $winning_amount, '$drawn_at')";

    if (!mysqli_query($conn, $insert_query)) {
        echo "❌ Error inserting winner: " . mysqli_error($conn);
        exit;
    }

    if (!mysqli_query($conn, "UPDATE tickets SET is_winner = 1 WHERE id = $winner_ticket_id")) {
        echo "❌ Error updating ticket: " . mysqli_error($conn);
        exit;
    }

    if (!mysqli_query($conn, "UPDATE lotteries SET is_active = 0 WHERE id = $lottery_id")) {
        echo "❌ Error updating lottery: " . mysqli_error($conn);
        exit;
    }

    echo "✅ Winner drawn successfully!<br>🎟 Ticket ID: $winner_ticket_id<br>🏆 Winner User ID: $winner_user_id<br>💰 Winning Amount: $winning_amount";
} else {
    echo "⚠️ No tickets sold for this lottery.";
}
?>
