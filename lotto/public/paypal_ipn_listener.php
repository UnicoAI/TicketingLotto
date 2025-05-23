<?php
// Include database connection
include 'connection.php';

// Set up logging
$log_file = 'transaction_log.txt';  // Log file path

// Function to log messages
function log_message($message) {
    global $log_file;
    error_log($message . "\n", 3, $log_file);
}

// Check if the IPN data is received via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Capture POST data from PayPal IPN response
    $payment_status = $_POST['payment_status'] ?? null;
    $txn_id = $_POST['txn_id'] ?? null;
    $payer_email = $_POST['payer_email'] ?? null;
    $amount = $_POST['mc_gross'] ?? null;
    $currency = $_POST['mc_currency'] ?? 'GBP'; // Default to GBP if not provided

    // Validate essential parameters
    if ($payment_status === 'Completed' && $txn_id && $payer_email && $amount) {

        // Extract user ID from email address
        $sqlUserId = "SELECT id FROM users WHERE email = ?";
        $stmtUserId = $conn->prepare($sqlUserId);
        if ($stmtUserId) {
            $stmtUserId->bind_param("s", $payer_email);
            $stmtUserId->execute();
            $stmtUserId->bind_result($userId);
            $stmtUserId->fetch();
            $stmtUserId->close();
        }

        if ($userId) {

            // Initialize variables
            $stmtCheck = $stmtUpdate = $stmtInsert = null;
            $count = 0;

            try {
                // Start transaction
                $conn->begin_transaction();

                // Check if the transaction is already processed
                $sqlCheck = "SELECT COUNT(*) FROM transactions WHERE txn_id = ?";
                $stmtCheck = $conn->prepare($sqlCheck);
                if ($stmtCheck) {
                    $stmtCheck->bind_param("s", $txn_id);
                    $stmtCheck->execute();
                    $stmtCheck->bind_result($count);
                    $stmtCheck->fetch();
                    $stmtCheck->close(); // Close after fetch
                }

                if ($count > 0) {
                    // If the transaction is already processed, log and show an error
                    log_message("Transaction {$txn_id} already processed. User ID: {$userId}.");
                    echo "Transaction already processed.";
                } else {
                    // Update the user's balance
                    $sqlUpdate = "UPDATE users SET balance = balance + ? WHERE id = ?";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    if ($stmtUpdate) {
                        $stmtUpdate->bind_param("di", $amount, $userId);
                        $stmtUpdate->execute();
                        $stmtUpdate->close(); // Close after execution
                    }

                    // Insert a new transaction record
                    $sqlInsert = "INSERT INTO transactions (user_id, customer_name, txn_id, paid_amount, paid_amount_currency, payment_status, created, modified) 
                                  VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
                    $stmtInsert = $conn->prepare($sqlInsert);
                    if ($stmtInsert) {
                        $stmtInsert->bind_param("isssds", $userId,  $payer_email, $txn_id, $amount, $currency, $payment_status);
                        $stmtInsert->execute();
                        $stmtInsert->close(); // Close after execution
                    }

                    // Commit the transaction
                    $conn->commit();

                    // Log success
                    log_message("Deposit successful. User ID: {$userId}, Amount: {$currency} {$amount}, Transaction ID: {$txn_id}.");
                    echo "Deposit successful.";
                }
            } catch (Exception $e) {
                // Rollback the transaction if something went wrong
                $conn->rollback();
                log_message("Transaction failed. Error: " . $e->getMessage());
                echo "Transaction failed: " . $e->getMessage();
            }

        } else {
            // User not found, log and show an error
            log_message("User with email {$payer_email} not found.");
            echo "User not found.";
        }

    } else {
        // Missing parameters, log and show an error
        log_message("Missing required parameters. Payment status: {$payment_status}, Transaction ID: {$txn_id}, Payer Email: {$payer_email}");
        echo "Invalid IPN data.";
    }

} else {
    // Not a POST request, log and show an error
    log_message("Invalid request method.");
    echo "Invalid request method.";
}

// Close the database connection
$conn->close();
?>
