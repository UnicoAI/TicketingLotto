<?php
include '../config/connection.php';

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get total number of results
$total_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lottery_winners"));
$total_pages = ceil($total_result['total'] / $limit);

// Fetch results with JOINs
$query = "
    SELECT lw.*, l.title, t.ticket_number, u.email 
    FROM lottery_winners lw
    JOIN lotteries l ON lw.lottery_id = l.id
    JOIN tickets t ON lw.ticket_id = t.id
    JOIN users u ON lw.winner_user_id = u.id
    ORDER BY lw.drawn_at DESC
    LIMIT $limit OFFSET $offset
";
$results = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lottery Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h2 class="mb-4">Lottery Draw Results</h2>
            <a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

            <?php if (mysqli_num_rows($results) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Lottery Title</th>
                                <th>Winner Email</th>
                                <th>Ticket Number</th>
                                <th>Winning Amount</th>
                                <th>Drawn At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($results)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                <td>£<?= number_format($row['winning_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($row['drawn_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            <?php else: ?>
                <div class="alert alert-warning">No lottery results found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
