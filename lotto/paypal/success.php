<?php
include '../config/connection.php';
$user_id = 1;
$cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id");

while ($row = mysqli_fetch_assoc($cart)) {
    for ($i = 0; $i < $row['quantity']; $i++) {
        $ticket_number = uniqid("TKT");
        mysqli_query($conn, "INSERT INTO tickets(user_id, lottery_id, ticket_number, amount) VALUES ($user_id, {$row['lottery_id']}, '$ticket_number', 0)");
    }
}
mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id");
echo "<h2>Payment successful. Tickets added to your account.</h2><a href='/public/my_tickets.php'>View Tickets</a>";
