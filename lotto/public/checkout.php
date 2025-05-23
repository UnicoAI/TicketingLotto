

<?php
include '../config/connection.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<h3>Your cart is empty. <a href='lotteries.php'>Browse Lotteries</a></h3>";
    include '../includes/footer.php';
    exit();
}

$cart = $_SESSION['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {

    // For simplicity, let's assume payment is done offline or always succeeds
    // In real world, integrate with PayPal or Stripe here

    foreach ($cart as $lottery_id => $quantity) {
        $lottery_id = intval($lottery_id);
        $quantity = intval($quantity);

        // Fetch ticket price
        $res = mysqli_query($conn, "SELECT winning_price FROM lotteries WHERE id=$lottery_id");
        if ($row = mysqli_fetch_assoc($res)) {
            $price_per_ticket = $row['winning_price'];

            // Insert tickets
            for ($i=0; $i < $quantity; $i++) {
                $ticket_number = strtoupper(bin2hex(random_bytes(4))) . rand(100,999);
                $price = $price_per_ticket;
                $stmt = mysqli_prepare($conn, "INSERT INTO tickets (lottery_id, ticket_number, user_id, price) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'isid', $lottery_id, $ticket_number, $user_id, $price);
                mysqli_stmt_execute($stmt);
            }

            // Update total_raised for lottery
            $total_added = $price_per_ticket * $quantity;
            mysqli_query($conn, "UPDATE lotteries SET total_raised = total_raised + $total_added WHERE id = $lottery_id");
        }
    }

    unset($_SESSION['cart']);
    echo "<div class='alert alert-success'>Purchase successful! Your tickets have been issued.</div>";
    echo "<a href='lotteries.php' class='btn btn-primary'>Back to Lotteries</a>";
    include '../includes/footer.php';
    exit();
}

?>

<h2>Checkout</h2>
<p>Please confirm your purchase:</p>

<ul>
    <?php
    $ids = implode(',', array_keys($cart));
    $res = mysqli_query($conn, "SELECT * FROM lotteries WHERE id IN ($ids)");
    $total_price = 0;
    while ($lottery = mysqli_fetch_assoc($res)) {
        $qty = $cart[$lottery['id']];
        $subtotal = $qty * $lottery['winning_price'];
        $total_price += $subtotal;
        echo "<li>" . htmlspecialchars($lottery['title']) . " x $qty tickets = $" . number_format($subtotal, 2) . "</li>";
    }
    ?>
</ul>

<h4>Total: $<?= number_format($total_price, 2) ?></h4>

<form method="post" action="checkout.php">
    <button type="submit" name="confirm" class="btn btn-success">Confirm Purchase</button>
    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
</form>

<?php include '../includes/footer.php'; ?>
