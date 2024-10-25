<?php
session_start();

// Include the database connection file
include 'db.php'; // Adjust the path if needed

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $role);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role; // Set role based on retrieved value

            // Redirect to index.php after successful login
            header("Location: index.php");
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: login.php");
        exit();
    }
}

// Close connection
$conn->close();
?>
