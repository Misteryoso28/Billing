<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php'; // Include database connection

// Fetch total payments
$totalPayments = $conn->query("SELECT SUM(amount_paid) AS total_payments FROM payments")->fetch_assoc();

// Fetch outstanding balances
$outstandingBalances = $conn->query("SELECT SUM(amount_due) AS total_outstanding FROM billing WHERE payment_status = 'Pending'")->fetch_assoc();

// Fetch average consumption
$averageConsumption = $conn->query("SELECT AVG(consumption) AS avg_consumption FROM billing")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>Reports</h1>

    <div class="report-container">
        <h2>Financial Summary</h2>
        <p><strong>Total Payments:</strong> <?php echo number_format($totalPayments['total_payments'], 2); ?></p>
        <p><strong>Total Outstanding Balances:</strong> <?php echo number_format($outstandingBalances['total_outstanding'], 2); ?></p>

        <h2>Consumption Summary</h2>
        <p><strong>Average Consumption:</strong> <?php echo number_format($averageConsumption['avg_consumption'], 2); ?> m³</p>
    </div>
</body>
</html>
