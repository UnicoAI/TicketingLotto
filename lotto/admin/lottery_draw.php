<?php
require 'config.php';
redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get lottery info
$res = $mysqli->query("SELECT * FROM lotteries WHERE id=$id");
if (!$res || $res->num_rows == 0) {
    die("Lottery not found.");
}
$lottery = $res->fetch_assoc();

// Get participants who paid for tickets for this lottery
$participantsRes = $mysqli->query("SELECT user_id FROM tickets WHERE lottery_id=$id");
$participants = [];
while ($row = $participantsRes->fetch_assoc()) {
    $participants[] = $row['user_id'];
}

if (!$participants) {
    die("No participants for this lottery.");
}

// Draw a random winner
$winnerId = $participants[array_rand($participants)];

// Get winner info
$winnerRes = $mysqli->query("SELECT * FROM users WHERE id=$winnerId");
$winner = $winnerRes->fetch_assoc();

// Update lottery with winner info and deactivate lottery (optional)
$mysqli->query("UPDATE lotteries SET is_active=0 WHERE id=$id");


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Lottery Draw</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-4">
    <h2>Winner Drawn for Lottery: <?= htmlspecialchars($lottery['title']) ?></h2>
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ Winner drawn: Ticket #<?= htmlspecialchars($_GET['ticket_id']) ?> — User #<?= htmlspecialchars($_GET['user_id']) ?>
    </div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        ⚠️ <?= htmlspecialchars($_GET['error']) === 'already_drawn' ? 'Winner already drawn.' :
               ($_GET['error'] === 'no_tickets' ? 'No tickets sold for this lottery.' :
               'Something went wrong.') ?>
    </div>
<?php endif; ?>

    <div class="alert alert-success">
        <h4>Winner:</h4>
        <p><strong>Name:</strong> <?= htmlspecialchars($winner['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($winner['email']) ?></p>
    </div>
    <a href="lotteries.php" class="btn btn-primary">Back to Lotteries</a>
</div>
</body>
</html>
