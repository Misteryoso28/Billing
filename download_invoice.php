<?php
session_start();
require('fpdf/fpdf.php');
require 'vendor/autoload.php';
include 'db.php';

use setasign\Fpdi\Fpdi;

if (isset($_GET['billing_id'])) {
    $billing_id = intval($_GET['billing_id']);

    // Check if billing ID is valid
    if ($billing_id <= 0) {
        echo "Invalid billing ID.";
        exit;
    }

    // Prepare the database query to fetch data from both billing and concessionaires tables
    $stmt = $conn->prepare("SELECT b.*, c.name AS concessionaire_name, c.category AS category, c.address, c.account_number 
                         FROM billing b 
                         JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id 
                         WHERE b.billing_id = ?");

    if (!$stmt) {
        echo "Database error: Unable to prepare the statement.";
        exit;
    }

    $stmt->bind_param("i", $billing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the result has any rows
    if ($result->num_rows > 0) {
        $billing = $result->fetch_assoc();

        // Create an FPDI instance
        $pdf = new Fpdi();

        // Load the template PDF
        $templateFile = 'receipt.pdf'; // Specify your template file path here
        if (!file_exists($templateFile)) {
            echo "Template file not found.";
            exit;
        }

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
        $pdf->Cell(0, 10, $billing['concessionaire_name']); // Concessionaire Name

        $pdf->SetXY(28, 22);
        $pdf->Cell(0, 10, htmlspecialchars($billing['address'])); // Address

        $pdf->SetXY(33, 29.5);
        $pdf->Cell(0, 10, $billing['account_number']); // Account Number from concessionaires table

        $pdf->SetXY(58, 46);
        $pdf->Cell(0, 10, date("m/d/Y", strtotime($billing['billing_date']))); // Billing Date

        $pdf->SetXY(85, 46);
        $pdf->Cell(0, 10, number_format($billing['previous_reading'], 2)); // Previous Reading

        $pdf->SetXY(105, 46);
        $pdf->Cell(0, 10, number_format($billing['current_reading'], 2)); // Current Reading

        $pdf->SetXY(128, 46);
        $pdf->Cell(0, 10, number_format($billing['consumption'], 2)); // Consumption

        $pdf->SetXY(164, 45);
        $pdf->Cell(0, 10, date("m/d/Y", strtotime($billing['due_date']))); // Billing Date

        $pdf->SetXY(154, 54);
        $pdf->Cell(0, 10, number_format($billing['initial_bill'], 2)); // Initial Bill

        $pdf->SetXY(154, 82);
        $pdf->Cell(0, 10, number_format($billing['total_bill'], 2)); // Total Bill

        $pdf->SetFont('Arial', 'B', 14); // Set font to bold and increase size
        $pdf->SetTextColor(255, 0, 0); // Set text color to red

        $pdf->SetXY(165, 91);
        $pdf->Cell(0, 10, $billing['billing_id']); 

        // Output the PDF to the browser or save it to a file
        $pdf->Output('D', $billing['concessionaire_name'] . '_Invoice.pdf'); // Download the PDF with the concessionaire's name as filename
    } else {
        echo "No invoice found for the given billing ID.";
    }
} else {
    echo "Invalid billing ID.";
}
?>
