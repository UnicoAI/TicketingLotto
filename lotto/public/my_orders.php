<?php
//session_start();
include '../config/connection.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Filtering by ticket status
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$whereClause = "WHERE user_id = '$user_id'";
if ($filter == 'winners') {
    $whereClause .= " AND id IN (SELECT DISTINCT order_id FROM order_items WHERE lottery_id IN 
                   (SELECT lottery_id FROM tickets WHERE user_id = '$user_id' AND is_winner = 1))";
} elseif ($filter == 'losers') {
    $whereClause .= " AND id IN (SELECT DISTINCT order_id FROM order_items WHERE lottery_id IN 
                   (SELECT lottery_id FROM tickets WHERE user_id = '$user_id' AND is_winner = 2))";
}

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_orders_query = "SELECT COUNT(*) as total FROM orders $whereClause";
$total_result = mysqli_query($conn, $total_orders_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_orders = $total_row['total'];
$total_pages = ceil($total_orders / $limit);

// Fetch paginated orders
$order_sql = "SELECT * FROM orders $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$order_result = mysqli_query($conn, $order_sql);

$has_winner = false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .ticket-print-btn { margin-top: 10px; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">My Orders</h2>

        <!-- Filter -->
        <form method="get" class="mb-4">
            <div class="form-inline">
                <label class="mr-2">Filter:</label>
                <select name="filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="winners" <?= $filter == 'winners' ? 'selected' : '' ?>>Only Winners</option>
                    <option value="losers" <?= $filter == 'losers' ? 'selected' : '' ?>>Only Losers</option>
                </select>
                <input type="hidden" name="page" value="1">
            </div>
        </form>

        <?php if (mysqli_num_rows($order_result) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($order_result)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <strong>Order #<?= $order['id'] ?></strong> 
                        | Date: <?= $order['created_at'] ?> 
                        | Status: <?= ucfirst($order['payment_status']) ?> 
                        | Total: $<?= number_format($order['total'], 2) ?>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Lottery</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Ticket Number(s)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order_id = $order['id'];
                                $items_sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
                                $items_result = mysqli_query($conn, $items_sql);

                                while ($item = mysqli_fetch_assoc($items_result)):
                                    $lottery_id = $item['lottery_id'];

                                    $tickets_sql = "SELECT * FROM tickets 
                                                    WHERE lottery_id = '$lottery_id' 
                                                    AND user_id = '$user_id'";
                                    $tickets_result = mysqli_query($conn, $tickets_sql);

                                    $ticket_numbers = [];
                                    $ticket_statuses = [];

                                    while ($ticket = mysqli_fetch_assoc($tickets_result)) {
                                        $ticket_numbers[] = $ticket['ticket_number'];
                                        if ($ticket['is_winner'] == 1) {
                                            $ticket_statuses[] = '<span class="badge badge-success">Winner</span>';
                                            $has_winner = true;
                                        } elseif ($ticket['is_winner'] == 2) {
                                            $ticket_statuses[] = '<span class="badge badge-danger">Lost</span>';
                                        } else {
                                            $ticket_statuses[] = '<span class="badge badge-secondary">Pending</span>';
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['title']) ?></td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>$<?= number_format($item['total'], 2) ?></td>
                                        <td>
                                            <?= implode(', ', $ticket_numbers) ?>
                                            <?php if (count($ticket_numbers)): ?>
                                                <br>
                                                <button class="btn btn-sm btn-info ticket-print-btn" onclick="downloadTickets('<?= implode(', ', $ticket_numbers) ?>')">Download</button>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= implode('<br>', $ticket_statuses) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if ($has_winner): ?>
                <div class="alert alert-success text-center">
                    🎉 <strong>Congratulations!</strong> One or more of your tickets is a winner. We will surprise you shortly!
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?filter=<?= $filter ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php else: ?>
            <div class="alert alert-info">No orders found for this filter.</div>
        <?php endif; ?>
    </div>

    <script>
    function downloadTickets(ticketNumbers) {
        const blob = new Blob([ticketNumbers], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "tickets.txt";
        a.click();
        URL.revokeObjectURL(url);
    }
    </script>
<?php include '../includes/footer.php'; ?>
