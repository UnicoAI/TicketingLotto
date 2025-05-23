<?php include '../config/connection.php'; include '../includes/header.php';
$user_id = 1;
$cart = mysqli_query($conn, "SELECT cart.*, lotteries.title, lotteries.price FROM cart JOIN lotteries ON cart.lottery_id = lotteries.id WHERE user_id=$user_id");
?>

<h3>Your Cart</h3>
<form method="post" action="update_cart.php">
<table class="table">
    <thead>
        <tr><th>Lottery</th><th>Quantity</th><th>Total</th><th>Remove</th></tr>
    </thead>
    <tbody>
        <?php $total = 0; while ($row = mysqli_fetch_assoc($cart)) {
            $item_total = $row['price'] * $row['quantity'];
            $total += $item_total;
        ?>
        <tr>
            <td><?php echo $row['title']; ?></td>
            <td><input type="number" name="qty[<?php echo $row['id']; ?>]" value="<?php echo $row['quantity']; ?>"></td>
            <td>$<?php echo number_format($item_total, 2); ?></td>
            <td><a href="remove_from_cart.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">X</a></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<button type="submit" class="btn btn-primary">Update Cart</button>
<a href="../paypal/process_payment.php" class="btn btn-success">Checkout - PayPal ($<?php echo number_format($total, 2); ?>)</a>
</form>
<?php include '../includes/footer.php'; ?>
