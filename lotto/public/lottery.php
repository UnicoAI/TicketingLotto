<?php include '../config/connection.php'; include '../includes/header.php';
$id = $_GET['id'];
$lottery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM lotteries WHERE id=$id"));
?>

<h2><?php echo $lottery['title']; ?></h2>
<img src="/uploads/<?php echo $lottery['image']; ?>" height="200">
<p><?php echo $lottery['description']; ?></p>
<p><strong>Price per ticket: $<?php echo $lottery['price']; ?></strong></p>

<form method="post" action="../cart/add_to_cart.php">
    <input type="hidden" name="lottery_id" value="<?php echo $id; ?>">
    <div class="form-group">
        <label>Number of Tickets</label>
        <input type="number" name="quantity" min="1" value="1" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Add to Cart</button>
</form>

<?php include '../includes/footer.php'; ?>
