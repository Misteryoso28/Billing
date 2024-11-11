<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'header.html';
include 'db.php';

function calculate_initial_bill($consumption, $category_rate) {
    if ($category_rate == 'Residential') {
        if ($consumption <= 10) {
            return 250;
        } elseif ($consumption <= 20) {
            return 250 + (25.50 * ($consumption - 10));
        } elseif ($consumption <= 30) {
            return 505 + (26.10 * ($consumption - 20));
        } elseif ($consumption <= 40) {
            return 766 + (26.85 * ($consumption - 30));
        } elseif ($consumption <= 50) {
            return 1034.50 + (27.75 * ($consumption - 40));
        } else {
            return 1312.00 + (28.75 * ($consumption - 50));
        }
    } elseif ($category_rate == 'Commercial A') {
        if ($consumption <= 10) {
            return 437.50;
        } elseif ($consumption <= 20) {
            return 437.50 + (51 * ($consumption - 10));
        } elseif ($consumption <= 30) {
            return 947.50 + (52.2 * ($consumption - 20));
        } elseif ($consumption <= 40) {
            return 1469.50 + (53.7 * ($consumption - 30));
        } elseif ($consumption <= 50) {
            return 2006.50 + (55.5 * ($consumption - 40));
        } else {
            return 2561.50 + (57.5 * ($consumption - 50));
        }
    } elseif ($category_rate == 'Commercial B') {
        if ($consumption <= 10) {
            return 375;
        } elseif ($consumption <= 20) {
            return 375 + (44.63 * ($consumption - 10));
        } elseif ($consumption <= 30) {
            return 821.30 + (45.68 * ($consumption - 20));
        } elseif ($consumption <= 40) {
            return 1278.10 + (46.99 * ($consumption - 30));
        } elseif ($consumption <= 50) {
            return 1748 + (48.56 * ($consumption - 40));
        } else {
            return 2233.60 + (50.31 * ($consumption - 50));
        }
    } elseif ($category_rate == 'Commercial C') {
        if ($consumption <= 10) {
            return 312.50;
        } elseif ($consumption <= 20) {
            return 312.50 + (31.88 * ($consumption - 10));
        } elseif ($consumption <= 30) {
            return 631.30 + (32.63 * ($consumption - 20));
        } elseif ($consumption <= 40) {
            return 957.60 + (33.56 * ($consumption - 30));
        } elseif ($consumption <= 50) {
            return 1293.20 + (34.69 * ($consumption - 40));
        } else {
            return 1640.10 + (35.94 * ($consumption - 50));
        }
    }

    return 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_billing'])) {
    $concessionaire_name = mysqli_real_escape_string($conn, $_POST['concessionaire_name']);
    $billing_month = mysqli_real_escape_string($conn, $_POST['billing_month']) . "-01";
    $previous_reading = mysqli_real_escape_string($conn, $_POST['previous_reading']);
    $current_reading = mysqli_real_escape_string($conn, $_POST['current_reading']);

    // Validate that the current reading is greater than the previous reading
    if ($current_reading <= $previous_reading) {
        $_SESSION['error'] = "Current reading must be greater than previous reading.";
    } else {
        // Calculate consumption and proceed with billing
        $consumption = $current_reading - $previous_reading;

        // Fetch concessionaire info
        $result = $conn->query("SELECT concessionaire_id, category, SC_discount FROM concessionaires WHERE name = '$concessionaire_name'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $concessionaire_id = $row['concessionaire_id'];
            $category = $row['category'];
            $sc_discount = $row['SC_discount'];

            // Calculate the initial bill
            $initial_bill = calculate_initial_bill($consumption, $category);

            // Apply discount if eligible
            if ($sc_discount === 'yes' && $consumption <= 30) {
                $total_bill = $initial_bill * 0.95; // Apply 5% discount
            } else {
                $total_bill = $initial_bill;
            }

            // Insert into billing table
            $sql = "INSERT INTO billing 
                    (concessionaire_id, billing_month, previous_reading, current_reading, consumption, initial_bill, total_bill, payment_status) 
                    VALUES ('$concessionaire_id', '$billing_month', '$previous_reading', '$current_reading', '$consumption', '$initial_bill', '$total_bill', 'Pending')";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['message'] = "Billing record added successfully.";
            } else {
                $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Error: Concessionaire not found.";
        }
    }
}


$search_query = "";
if (isset($_POST['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search_query']);
}

if ($search_query) {
    $billings = $conn->query("SELECT b.*, c.name AS concessionaire_name 
                              FROM billing b 
                              JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id 
                              WHERE c.name LIKE '%$search_query%' 
                              OR c.account_number LIKE '%$search_query%' 
                              ORDER BY b.concessionaire_id ASC");
} else {
    $billings = $conn->query("SELECT b.*, c.name AS concessionaire_name 
                              FROM billing b 
                              JOIN concessionaires c ON b.concessionaire_id = c.concessionaire_id 
                              ORDER BY c.concessionaire_id ASC");
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['message']);
unset($_SESSION['error']);

require_once 'vendor/autoload.php'; // Include Composer autoload

?>
    <title>Billing Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Your existing styles can be placed here or moved to styles.css */
        h1 {
            text-align: center;
            color: black;
            margin: 20px 0;
            padding: 10px;
            background-color: white;
            border-radius: 8px;
            width: 50%;
        }

        .back-button-container {
            text-align: left;
            width: 53%;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 150px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
        }

        .modal-content form label {
            display: block;
            text-align: center;
            margin-bottom: 5px;
        }

        .modal-content input,
        .modal-content select {
            width: 80%;
            margin: 10px auto;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
        }

        .modal-content button {
            width: 80%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            display: block;
            margin: 10px auto;
        }

        .modal-content button:hover {
            background-color: #45a049;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .table-container {
            margin-bottom: 20px;
        }

        #addBillingBtn {
            margin-top: 20px;
        }
        .back-button {
            background-color: lightgray; /* Example back button color */
            color: black; /* Text color */
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container {
        margin: 20px 0; /* Spacing around the search container */
        display: flex; /* Flexbox for alignment */
        justify-content: center; /* Center items horizontally */
        }

        .search-container input[type="text"] {
            width: 300px; /* Set a specific width for the search input */
            padding: 10px; /* Padding for the input */
            border: 1px solid #ccc; /* Light border for input */
            border-radius: 4px; /* Rounded corners for input */
        }

        .search-container button {
            background-color: green; /* Green button color */
            color: white; /* White text for the button */
            border: none; /* No border for button */
            padding: 10px 15px; /* Padding for button */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            margin-left: 10px; /* Space between input and button */
        }

        .search-container button:hover {
            background-color: darkgreen; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>Billing Management</h1>
    
    <?php if ($message) echo "<p style='color: green;'>$message</p>"; ?>
    <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>

    <!-- Search Form -->
    <div class="search-container">
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search by Name or Account Number" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" name="search">Search</button>
    </form>
    </div>
    <!-- Back Button -->
    <div class="back-button-container">
        <button class="button back-button" onclick="window.history.back();">Back</button>
    </div>
    
    <!-- Add Billing Button -->
    <button id="addBillingBtn" onclick="document.getElementById('addBillingModal').style.display='block'">Add Billing</button>
    
    <!-- Billing Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Billing ID</th>
                    <th>Concessionaire</th>
                    <th>Billing Month</th>
                    <th>Previous Reading</th>
                    <th>Current Reading</th>
                    <th>Initial Bill</th>
                    <th>Total Bill</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($billings->num_rows > 0): ?>
                    <?php while ($billing = $billings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $billing['billing_id']; ?></td>
                            <td><?php echo $billing['concessionaire_name']; ?></td>
                            <td><?php echo date("m/d/Y", strtotime($billing['billing_month'])); ?></td>
                            <td><?php echo number_format($billing['previous_reading'], 2); ?></td>
                            <td><?php echo number_format($billing['current_reading'], 2); ?></td>
                            <td><?php echo number_format($billing['initial_bill'], 2); ?></td>
                            <td><?php echo number_format($billing['total_bill'], 2); ?></td>
                            <td><?php echo $billing['payment_status']; ?></td>
                            <td>
                            <button onclick="window.open('generate_invoice.php?billing_id=<?php echo $billing['billing_id']; ?>', '_blank')">Print</button>
                                <a href="download_invoice.php?billing_id=<?php echo $billing['billing_id']; ?>" target="_blank">
                                    <button>Download</button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>    
        </table>
    </div>

    <!-- Add Billing Modal -->
    <div id="addBillingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addBillingModal').style.display='none'">&times;</span>
            <h2>Add Billing Record</h2>
            <form method="POST" action="">
                <label for="concessionaire_name">Concessionaire Name:</label>
                <input type="text" name="concessionaire_name" required>
                <label for="billing_month">Billing Month:</label>
                <input type="month" id="billing_month" name="billing_month" required>
                <label for="previous_reading">Previous Reading:</label>
                <input type="number" name="previous_reading" step="0.01" required>
                <label for="current_reading">Current Reading:</label>
                <input type="number" name="current_reading" step="0.01" required>
                <button type="submit" name="add_billing">Add Billing</button>
            </form>
        </div>
    </div>
    <script>
    function openPrintWindow(billingId) {
        const printWindow = window.open('generate_invoice.php?billing_id=' + billingId, '_blank');
        printWindow.print();
    }
    </script>

</body>
</html>