<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}   
include 'header.html';
include 'db.php'; // Include your database connection script

// Handle customer addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $zone_id = mysqli_real_escape_string($conn, $_POST['zone_id']);
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $address = mysqli_real_escape_string($conn, $_POST['address']); // New address field

    $sql = "INSERT INTO concessionaires (name, zone_id, account_number, category, address) VALUES ('$name', '$zone_id', '$account_number', '$category', '$address')"; // Include address
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Customer added successfully.";
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle customer search
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search_query']);
}

// Fetch customers based on search query with ordering by concessionaire_id
if ($search_query) {
    $customers = $conn->query("SELECT * FROM concessionaires WHERE name LIKE '%$search_query%' OR account_number LIKE '%$search_query%' ORDER BY concessionaire_id ASC");
} else {
    // Fetch all customers without restrictions
    $customers = $conn->query("SELECT * FROM concessionaires ORDER BY concessionaire_id ASC");
}

// Display message after redirection
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear messages after displaying
unset($_SESSION['message']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="styles.css"> <!-- Your styles -->
    <style>
        /* Existing styles... */
        h1 {
            text-align: center;
            color: black;
            margin: 20px 0;
            padding: 10px;
            background-color: white;
            border-radius: 8px;
            width: 50%;
        }
        .button-container {
            display: flex;
            justify-content: space-between; /* Space between buttons */
            width: 55%; /* Full width */
            margin-bottom: 20px; /* Margin for spacing */
        }
        .add-button {
            background-color: green; /* Green background */
            color: white; /* White text color */
            border: none; /* No border */
            padding: 10px 20px; /* Padding for button */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
        }
        .back-button {
            background-color: lightgray; /* Example back button color */
            color: black; /* Text color */
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal {
            display: none; /* Hidden by default */
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.7); /* Darker background for the modal */
            padding-top: 60px;
        }
        .modal-content {
            background-color: #ffffff; /* White background */
            margin: 10% auto; /* Center the modal vertically */
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px; /* Rounded corners */
            width: 85%; /* Width of modal */
            max-width: 500px; /* Max width for larger screens */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add a shadow for depth */
        }
        .close {
            color: #aaa; /* Close button color */
            float: right; /* Align close button to the right */
            font-size: 28px; /* Font size for close button */
            font-weight: bold; /* Bold font for emphasis */
        }
        .close:hover,
        .close:focus {
            color: black; /* Change color on hover */
            text-decoration: none; /* Remove underline */
            cursor: pointer; /* Pointer cursor on hover */
        }
        select {
            width: 97%; /* Full width for dropdown */
            padding: 10px; /* Padding for dropdown */
            margin: 8px 0; /* Margin for spacing */
            border: 1px solid #ccc; /* Light border */
            border-radius: 4px; /* Rounded corners for dropdown */
            background-color: #fff; /* White background for dropdown */
            color: #333; /* Dark text for dropdown */
            appearance: none; /* Remove default dropdown arrow */
            -moz-appearance: none; /* Firefox */
            -webkit-appearance: none; /* Safari */
        }
        input[type="text"],
        input[type="number"] {
            width: 97%; /* Full width inputs */
            padding: 10px; /* Padding for inputs */
            margin: 8px 0; /* Margin for spacing */
            border: 1px solid #ccc; /* Light border */
            border-radius: 4px; /* Rounded corners for inputs */
        }
        button[type="submit"] {
            background-color: green; /* Green submit button */
            color: white; /* White text */
            border: none; /* No border */
            padding: 10px 15px; /* Padding for submit button */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
        }
        button[type="submit"]:hover {
            background-color: darkgreen; /* Darker green on hover */
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
    <h1>Customer Management</h1>

    <?php if ($message) echo "<p style='color: green;'>$message</p>"; ?>
    <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>
    
    <div class="search-container">
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search by Name or Account Number" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" name="search">Search</button>
    </form>
    </div>

    <!-- Button Container -->
    <div class="button-container">
        <button class="button back-button" onclick="window.history.back();">Back</button>
        <button class="button add-button" onclick="document.getElementById('addCustomerModal').style.display='block'">Add Customer</button>
    </div>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('addCustomerModal').style.display='none'" class="close" style="cursor:pointer;">&times;</span>
            <h2>Add New Customer</h2>
            <form method="POST" action="">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br>
                <label for="zone_id">Zone ID:</label>
                <input type="number" id="zone_id" name="zone_id" required><br>
                <label for="account_number">Account Number:</label>
                <input type="text" id="account_number" name="account_number" required><br>
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="Residential">Residential</option>
                    <option value="Commercial A">Commercial A</option>
                    <option value="Commercial B">Commercial B</option>
                    <option value="Commercial C">Commercial C</option>
                </select><br> <!-- Dropdown for category -->
                <label for="address">Address:</label> <!-- New address field -->
                <input type="text" id="address" name="address" required><br>
                <button type="submit" name="add_customer">Add Customer</button>
            </form>
        </div>
    </div>
    <!-- Customer Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Zone ID</th>
                    <th>Account Number</th>
                    <th>Category</th>
                    <th>Address</th> <!-- New Address column -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers->num_rows > 0): ?>
                    <?php while ($row = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['concessionaire_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['zone_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td> <!-- Display address -->
                            <td>
                                <a href="edit_customer.php?id=<?php echo $row['concessionaire_id']; ?>" class="button edit-button">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Close modal if clicked outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('addCustomerModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
