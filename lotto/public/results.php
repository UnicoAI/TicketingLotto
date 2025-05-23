<?php
include '../config/connection.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Show lotteries with winner info if drawn

$result = mysqli_query($conn, "SELECT l.*, lw.ticket_id, lw.winning_amount, lw.winner_user_id FROM lotteries l LEFT JOIN lottery_winners lw ON l.id = lw.lottery_id ORDER BY l.created_at DESC");

?>

<h2>Lottery Results</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Lottery</th>
            <th>Winning Ticket</th>
            <th>Winner</th>
            <th>Winning Amount</th>
            <th>Drawn At</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)):
            $winner_name = null;
            if ($row['winner_user_id']) {
                $u_res = mysqli_query($conn, "SELECT name FROM users WHERE id=" . intval($row['winner_user_id']));
                if ($u = mysqli_fetch_assoc($u_res)) {
                    $winner_name = $u['name'];
                }
            }
            ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['ticket_id'] ? htmlspecialchars($row['ticket_id']) : 'Not drawn yet' ?></td>
                <td><?= $winner_name ?? '-' ?></td>
                <td><?= $row['winning_amount'] ? '$'.number_format($row['winning_amount'], 2) : '-' ?></td>
                <td><?= $row['drawn_at'] ?? '-' ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
