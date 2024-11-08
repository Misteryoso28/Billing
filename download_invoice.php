<?php
require_once __DIR__ . '/vendor/autoload.php';  // Ensure correct path

include 'db.php';

if (isset($_GET['billing_id'])) {
    $billing_id = intval($_GET['billing_id']);
    $result = $conn->query("SELECT b.*, c.name AS concessionaire_name 
                            FROM billing b 
                            JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id 
                            WHERE b.billing_id = $billing_id");

    if ($result->num_rows > 0) {
        $billing = $result->fetch_assoc();
        
        // Sanitize concessionaire name for filename
        $concessionaire_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $billing['concessionaire_name']);
        
        // Create TCPDF object
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // HTML content for the invoice
        $html = "
            <h1>Invoice for {$billing['concessionaire_name']}</h1>
            <p>Billing Month: " . date("F Y", strtotime($billing['billing_month'])) . "</p>
            <p>Previous Reading: " . number_format($billing['previous_reading'], 2) . "</p>
            <p>Current Reading: " . number_format($billing['current_reading'], 2) . "</p>
            <p>Consumption: " . number_format($billing['consumption'], 2) . "</p>
            <p>Initial Bill: $" . number_format($billing['initial_bill'], 2) . "</p>
            <p>Total Bill: $" . number_format($billing['total_bill'], 2) . "</p>
            <p>Status: " . htmlspecialchars($billing['payment_status']) . "</p>";

        // Write HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Set headers for the browser to handle the PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $concessionaire_name . '_Invoice.pdf"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output the PDF directly to the browser for download
        $pdf->Output($concessionaire_name . '_Invoice.pdf', 'D'); // 'D' for download
    } else {
        echo "No invoice found.";
    }
} else {
    echo "Invalid billing ID.";
}
?>
