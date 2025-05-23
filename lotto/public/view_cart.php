<?php
//session_start();
include '../config/connection.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

function generateTicketNumber($lottery_id) {
    return strtoupper($lottery_id . '-' . uniqid());
}

if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['tx'])) {
    // Fetch cart items again before saving order
    $query = "SELECT c.*, l.title, l.photo, l.expiry_date, l.price as lottery_price FROM cart c 
              JOIN lotteries l ON c.lottery_id = l.id 
              WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['lottery_price'] * $row['quantity'];
    }

    if (!empty($cart_items)) {
        // Save order
        $insertOrder = $conn->prepare("INSERT INTO orders (user_id, total, payment_status) VALUES (?, ?, 'Completed')");
        $insertOrder->bind_param("id", $user_id, $total);
        $insertOrder->execute();
        $order_id = $insertOrder->insert_id;

        // Prepare insert statements
        $insertItem = $conn->prepare("INSERT INTO order_items (order_id, lottery_id, title, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
        $insertTicket = $conn->prepare("INSERT INTO tickets (lottery_id, ticket_number, user_id, purchase_date, price, is_winner) VALUES (?, ?, ?, NOW(), ?, 0)");
        $updateLottery = $conn->prepare("UPDATE lotteries SET total_raised = total_raised + ? WHERE id = ?");

        foreach ($cart_items as $item) {
            $item_total = $item['lottery_price'] * $item['quantity'];

            // Save order item
            $insertItem->bind_param("iisddi", $order_id, $item['lottery_id'], $item['title'], $item['lottery_price'], $item['quantity'], $item_total);
            $insertItem->execute();

            // Save each ticket
            for ($i = 0; $i < $item['quantity']; $i++) {
                $ticket_number = generateTicketNumber($item['lottery_id']);
                $insertTicket->bind_param("isid", $item['lottery_id'], $ticket_number, $user_id, $item['lottery_price']);
                $insertTicket->execute();
            }

            // Update total_raised
            $amount = $item['lottery_price'] * $item['quantity'];
            $updateLottery->bind_param("di", $amount, $item['lottery_id']);
            $updateLottery->execute();
        }

        // Clear cart
        $delCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $delCart->bind_param("i", $user_id);
        $delCart->execute();

        $order_saved = true;
    } else {
        $order_saved = false;
    }
} elseif (isset($_GET['status']) && $_GET['status'] === 'cancelled') {
    $order_saved = false;
}

// Fetch current cart if needed
if (!isset($order_saved) || !$order_saved) {
    $query = "SELECT c.*, l.title, l.photo, l.expiry_date, l.price as lottery_price FROM cart c 
              JOIN lotteries l ON c.lottery_id = l.id 
              WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['lottery_price'] * $row['quantity'];
    }
}
?>

<!-- ... Keep existing HTML/cart rendering exactly as you have it -->


<div class="container mt-5">
    <h2 class="mb-4">Your Cart</h2>

    <?php if (isset($order_saved) && $order_saved): ?>
        <div class="alert alert-success font-semibold">Your payment was successful! Order has been placed.</div>
        <h4>Order Details:</h4>
        <table class="table table-bordered align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Lottery</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td>£<?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>£<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                    <td>£<?= number_format($total, 2) ?></td>
                </tr>
            </tfoot>
        </table>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>

    <?php elseif (!empty($cart_items)): ?>
        <form method="post" action="update_cart.php">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Lottery</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody id="cart-table">
                        <?php foreach ($cart_items as $item): ?>
                            <tr data-id="<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">
                                <td>
                                    <img src="<?= $item['photo'] ? '../' . htmlspecialchars($item['photo']) : 'https://via.placeholder.com/100x80' ?>" style="width: 100px;" />
                                </td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td>£<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantities[<?= $item['id'] ?>]" class="form-control quantity-input" value="<?= $item['quantity'] ?>" min="1" style="width: 80px;" />
                                </td>
                                <td><?= htmlspecialchars($item['expiry_date']) ?></td>
                                <td class="item-total">£<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <a href="remove_cart_item.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                            <td id="grand-total">£<?= number_format($total, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-start mt-4">
                <button type="submit" class="btn btn-secondary">Update Cart</button>
            </div>
        </form>

        <!-- PayPal payment form -->
        <form id="paypalForm" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="d-flex flex-column align-items-end mt-4">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="unicobuildings@contractor.net">
            <input type="hidden" name="item_name" value="Lottery Ticket Purchase">
            <input type="hidden" name="currency_code" value="GBP">
            <input type="hidden" name="return" value="<?= htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?status=success') ?>">
            <input type="hidden" name="cancel_return" value="<?= htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?status=cancelled') ?>">
            <input type="hidden" name="custom" value="<?= $user_id ?>">

            <label for="amount">Total Amount (GBP):</label>
            <input type="text" name="amount" id="amount" readonly class="form-control bg-light rounded" value="<?= number_format($total, 2) ?>" style="width: 150px; text-align:right;">

            <div class="mt-2 d-flex align-items-center gap-2">
                <img src="paypal.png" width="30px" alt="PayPal Icon">
                <input type="submit" class="btn btn-primary" value="Pay Now">
            </div>
        </form>

    <?php else: ?>
        <div class="alert alert-info">No cart items.</div>
    <?php endif; ?>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'cancelled'): ?>
        <div class="alert alert-danger mt-4">Transaction was canceled. Please try again.</div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const grandTotalEl = document.getElementById('grand-total');
    const amountInput = document.getElementById('amount');

    function updateTotals() {
        let total = 0;
        document.querySelectorAll('#cart-table tr').forEach(row => {
            const price = parseFloat(row.dataset.price);
            const qtyInput = row.querySelector('.quantity-input');
            let qty = parseInt(qtyInput.value);
            if (isNaN(qty) || qty < 1) {
                qty = 1;
                qtyInput.value = 1;
            }
            const itemTotal = price * qty;
            row.querySelector('.item-total').textContent = '£' + itemTotal.toFixed(2);
            total += itemTotal;
        });
        grandTotalEl.textContent = '£' + total.toFixed(2);
        amountInput.value = total.toFixed(2);
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
