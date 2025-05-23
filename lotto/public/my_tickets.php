<?php include '../config/connection.php'; include '../includes/header.php';
$user_id = 1;
$tickets = mysqli_query($conn, "SELECT tickets.*, lotteries.title FROM tickets JOIN lotteries ON tickets.lottery_id = lotteries.id WHERE user_id=$user_id");
?>

<h3>My Tickets</h3>
<table class="table">
    <thead><tr><th>Lottery</th><th>Ticket</th><th>Claimed</th></tr></thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($tickets)) { ?>
        <tr>
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['ticket_number']; ?></td>
            <td><?php echo $row['claimed'] ? 'Yes' : 'No'; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
