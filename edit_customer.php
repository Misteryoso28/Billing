<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'header.html';
include 'db.php'; // Include your database connection script

// Fetch customer details by ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $customer = $conn->query("SELECT * FROM concessionaires WHERE concessionaire_id = $id")->fetch_assoc();
}

// Handle customer update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_customer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $zone_id = mysqli_real_escape_string($conn, $_POST['zone_id']);
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $address = mysqli_real_escape_string($conn, $_POST['address']); // Add address

    $sql = "UPDATE concessionaires SET name='$name', zone_id='$zone_id', account_number='$account_number', category='$category', address='$address' WHERE concessionaire_id=$id";
    if ($conn->query($sql) === TRUE) {
        $message = "Customer updated successfully.";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Center the form container */
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        /* Form style */
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 1);
            width: 100%;
            max-width: 600px; /* Adjust the width of the form */
            box-sizing: border-box;
        }

        /* Form field labels */
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        /* Form input fields */
        form input, form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Makes padding included in the width */
        }

        /* Submit button style */
        form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }

        form button:hover {
            background-color: #45a049;
        }

        .back-button {
            border: none;
            margin-bottom: 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        #address {
        width: 100%; /* Ensure it takes the full available width */
        min-width: 300px; /* Optionally, specify a minimum width */
        border: 2px solid #ccc;
        }

    </style>
   
</head>
<body>

    <?php include 'navbar.php'; ?>
    <h1>Edit Customer</h1>
    
    <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>

    <div class="back-button-container">
        <button class="button back-button" onclick="window.history.back();">Back</button>
    </div>
    
    <!-- Form to Edit Customer -->
    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
        
        <label for="zone_id">Zone ID:</label>
        <input type="number" id="zone_id" name="zone_id" value="<?php echo htmlspecialchars($customer['zone_id']); ?>" required>
        
        <label for="account_number">Account Number:</label>
        <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($customer['account_number']); ?>" required>
        
        <label for="category">Category:</label>
        <select id="category" name="category" required>
            <option value="Residential" <?php echo ($customer['category'] == 'Residential') ? 'selected' : ''; ?>>Residential</option>
            <option value="Commercial A" <?php echo ($customer['category'] == 'Commercial A') ? 'selected' : ''; ?>>Commercial A</option>
            <option value="Commercial B" <?php echo ($customer['category'] == 'Commercial B') ? 'selected' : ''; ?>>Commercial B</option>
            <option value="Commercial C" <?php echo ($customer['category'] == 'Commercial C') ? 'selected' : ''; ?>>Commercial C</option>
        </select>
        
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" required>
        
        <button type="submit" name="update_customer">Update Customer</button>
    </form>

</body>
</html>
