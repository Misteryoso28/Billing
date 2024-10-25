<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php'; // Include database connection

// Initialize variables
$message = '';
$customer = null;
$current_bill = null;
$arrears = null;

// Handle customer search
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_customer'])) {
    $search_query = $_POST['search_query'];

    // Search for customer by name or account number
    $stmt = $conn->prepare("SELECT * FROM concessionaires WHERE name LIKE ? OR account_number LIKE ?");
    $like_query = "%$search_query%";
    $stmt->bind_param("ss", $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        // Fetch current bill
        $billing_stmt = $conn->prepare("SELECT * FROM billing WHERE concessionaire_id = ? AND payment_status = 'Pending' ORDER BY billing_month DESC LIMIT 1");
        $billing_stmt->bind_param("i", $customer['concessionaire_id']);
        $billing_stmt->execute();
        $current_bill = $billing_stmt->get_result()->fetch_assoc();

        // Fetch arrears (any previous unpaid bills)
        $arrears_stmt = $conn->prepare("SELECT SUM(amount_due) AS arrears FROM billing WHERE concessionaire_id = ? AND payment_status = 'Pending' AND billing_month < ?");
        $arrears_stmt->bind_param("is", $customer['concessionaire_id'], $current_bill['billing_month']);
        $arrears_stmt->execute();
        $arrears = $arrears_stmt->get_result()->fetch_assoc()['arrears'];
    } else {
        $message = "No customer found with that name or account number.";
    }
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payment'])) {
    $billing_id = $_POST['billing_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];

    // Insert payment into payments table
    $stmt = $conn->prepare("INSERT INTO payments (billing_id, amount_paid, payment_date) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $billing_id, $amount_paid, $payment_date);
    $stmt->execute();

    // Update the payment status in the billing table if fully paid
    $stmt = $conn->prepare("UPDATE billing SET payment_status = 'Paid' WHERE billing_id = ? AND amount_due <= ?");
    $stmt->bind_param("id", $billing_id, $amount_paid);
    $stmt->execute();

    $message = "Payment added successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>Payments</h1>

    <?php if ($message) echo "<p style='color: green;'>$message</p>"; ?>

    <!-- Form to search for a customer -->
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search by Name or Account Number" required>
        <button type="submit" name="search_customer">Search</button>
    </form>

    <?php if ($customer): ?>
        <h2>Customer: <?php echo $customer['name']; ?> (Account No: <?php echo $customer['account_number']; ?>)</h2>
        
        <?php if ($current_bill): ?>
            <h3>Current Bill for <?php echo $current_bill['billing_month']; ?>: <?php echo number_format($current_bill['amount_due'], 2); ?></h3>
        <?php endif; ?>

        <?php if ($arrears): ?>
            <h3>Outstanding Arrears: <?php echo number_format($arrears, 2); ?></h3>
        <?php endif; ?>

        <!-- Form to make a payment -->
        <form method="POST" action="">
            <input type="hidden" name="billing_id" value="<?php echo $current_bill['billing_id']; ?>">
            <label for="amount_paid">Amount to Pay:</label>
            <input type="number" step="0.01" name="amount_paid" placeholder="Amount" required>
            <label for="payment_date">Payment Date:</label>
            <input type="date" name="payment_date" required>
            <button type="submit" name="add_payment">Add Payment</button>
        </form>
    <?php elseif (isset($search_query)): ?>
        <p>No customer found.</p>
    <?php endif; ?>

    <!-- Optionally: Display recent payments -->
    <h2>Recent Payments</h2>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Customer Name</th>
                <th>Billing Month</th>
                <th>Amount Paid</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch recent payments
            $recent_payments = $conn->query("SELECT p.payment_id, p.amount_paid, p.payment_date, b.billing_month, c.name 
                                             FROM payments p
                                             JOIN billing b ON p.billing_id = b.billing_id
                                             JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id
                                             ORDER BY p.payment_date DESC LIMIT 10");

            while ($row = $recent_payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['payment_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['billing_month']; ?></td>
                    <td><?php echo $row['amount_paid']; ?></td>
                    <td><?php echo $row['payment_date']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
