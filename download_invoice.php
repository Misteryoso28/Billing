<?php
session_start();
require('fpdf/fpdf.php');
require 'vendor/autoload.php';
include 'db.php';

use setasign\Fpdi\Fpdi;

if (isset($_GET['billing_id'])) {
    $billing_id = intval($_GET['billing_id']);
    $stmt = $conn->prepare("SELECT b.*, c.name AS concessionaire_name, c.category AS category 
                            FROM billing b 
                            JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id 
                            WHERE b.billing_id = ?");
    $stmt->bind_param("i", $billing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $billing = $result->fetch_assoc();

        // Create an FPDI instance
        $pdf = new Fpdi();

        // Load the template PDF
        $templateFile = 'receipt.pdf'; // Specify your template file path here
        $pageCount = $pdf->setSourceFile($templateFile);
        
        // Import the first page of the template
        $templatePage = $pdf->importPage(1);
        
        // Use the imported page as the background
        $pdf->AddPage();
        $pdf->useTemplate($templatePage, 0, 0, 210); // Adjust positioning and size as needed

        // Set font and add overlay text
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        // Add the invoice data over the template
        $pdf->SetXY(40, 15); // Adjust X, Y coordinates as per your template layout
        $pdf->Cell(0, 10, $billing['concessionaire_name']);

        $pdf->SetXY(58, 46);
        $pdf->Cell(0, 10, date("m/d/Y", strtotime($billing['billing_month'])));

        $pdf->SetXY(85, 46);
        $pdf->Cell(0, 10, number_format($billing['previous_reading'], 2));

        $pdf->SetXY(105,46);
        $pdf->Cell(0, 10, number_format($billing['current_reading'], 2));

        $pdf->SetXY(128, 46);
        $pdf->Cell(0, 10, number_format($billing['consumption'], 2));

        $pdf->SetXY(20, 100);
        $pdf->Cell(0, 10, 'Initial Bill: $' . number_format($billing['initial_bill'], 2));

        $pdf->SetXY(20, 110);
        $pdf->Cell(0, 10, 'Total Bill: $' . number_format($billing['total_bill'], 2));

        $pdf->SetXY(20, 120);
        $pdf->Cell(0, 10, 'Status: ' . htmlspecialchars($billing['payment_status']));

        // Output the PDF to the browser or save it to a file
        $pdf->Output('D', $billing['concessionaire_name'] . '_Invoice.pdf'); // Use $billing['concessionaire_name'] here
    } else {
        echo "No invoice found.";
    }
} else {
    echo "Invalid billing ID.";
}
?>
