<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php'; // Include your database connection script

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

    $sql = "UPDATE concessionaires SET name='$name', zone_id='$zone_id', account_number='$account_number', category='$category' WHERE concessionaire_id=$id";
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
        .back-button-container {
            text-align: left; /* Align text to the left */
            width: 75%; /* Take full width for the container */
            margin-bottom: 20px; /* Margin for spacing */
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
    
    <form method="POST" action="">
        <input type="text" name="name" value="<?php echo $customer['name']; ?>" required>
        <input type="text" name="zone_id" value="<?php echo $customer['zone_id']; ?>" required>
        <input type="text" name="account_number" value="<?php echo $customer['account_number']; ?>" required>
        <input type="text" name="category" value="<?php echo $customer['category']; ?>" required>
        <button type="submit" name="update_customer">Update Customer</button>
    </form>
</body>
</html>
