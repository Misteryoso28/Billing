<?php
include 'db.php';

if (isset($_GET['billing_id'])) {
    $billing_id = intval($_GET['billing_id']);
    $result = $conn->query("SELECT b.*, c.name AS concessionaire_name 
                            FROM billing b 
                            JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id 
                            WHERE b.billing_id = $billing_id");

    if ($result->num_rows > 0) {
        $billing = $result->fetch_assoc();
        
        echo "<h1>Invoice for " . htmlspecialchars($billing['concessionaire_name']) . "</h1>";
        echo "<p>Billing Month: " . date("F Y", strtotime($billing['billing_month'])) . "</p>";
        echo "<p>Previous Reading: " . number_format($billing['previous_reading'], 2) . "</p>";
        echo "<p>Current Reading: " . number_format($billing['current_reading'], 2) . "</p>";
        echo "<p>Consumption: " . number_format($billing['consumption'], 2) . "</p>";
        echo "<p>Initial Bill: $" . number_format($billing['initial_bill'], 2) . "</p>";
        echo "<p>Total Bill: $" . number_format($billing['total_bill'], 2) . "</p>";
        echo "<p>Status: " . htmlspecialchars($billing['payment_status']) . "</p>";
    } else {
        echo "No invoice found.";
    }
} else {
    echo "Invalid billing ID.";
}
?>
