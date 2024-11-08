<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}   
    include 'header.html'
?>
    <title>Dashboard</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Align items at the top */
            height: 100vh; /* Full viewport height */
            margin: 0; /* Remove default margin */
            background-image: url('SNWD.png'); /* Add your background image */
            background-size: cover; /* Ensure the image covers the entire background */
            background-position: center; /* Center the image */
            background-repeat: no-repeat;
            padding-top: 100px; /* Space for the fixed navbar */
        }

        h1 {
            text-align: center;
            color: black; /* Text color */
            margin: 20px 0; /* Margin for spacing */
            padding: 10px; /* Padding inside the h1 element */
            background-color: white; /* Light blue background */
            border-radius: 8px; /* Rounded corners */
            width: 50%;
        }   

        .logout-btn {
            background-color: #d9534f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-btn:hover {
            background-color: #c9302c;
        }

        /* Tab Navigation */
        .component-title {
            width: 100%;
            position: absolute;
            z-index: 999;
            top: 30px;
            left: 0;
            padding: 0;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #888;
            text-align: center;
        }

        .tab-container {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            padding: 2px;
            background-color: #dadadb;
            border-radius: 9px;
            justify-content: center; /* Center the tab container */
            margin: 20px 0; /* Optional: Add some vertical spacing */
        }

        .tab_label {
            width: 160px; /* Increased width of each tab */
            height: 25px; /* Increased height for better spacing */
            position: relative;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            font-size: 1rem; /* Adjust font size for readability */
            opacity: 0.8; /* Slightly increased opacity for better visibility */
            cursor: pointer;
            transition: opacity 0.2s ease, background-color 0.2s ease; /* Added background color transition */
            text-decoration: none; /* Remove underline from links */
            color: black; /* Tab text color */
            padding: 5px; /* Added padding for better spacing */
            margin: 0 5px; /* Added margin between tabs */
            border-radius: 5px; /* Rounded corners for better appearance */
        }

        .tab_label:hover {
            opacity: 1; /* Increase opacity on hover */
            background-color: #13b2fb; /* Change background on hover for visual feedback */
        }

        /* Optional: Add styles for active tab if needed */
        .tab_label.active {
            font-weight: bold;
            opacity: 1; /* Make active tab fully opaque */
            background-color: #ffffff; /* White background for active tab */
            border: 1px solid #ccc; /* Optional border for active tab */
        }


        /* Tab Content */
        .tab-content {
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        /* Simple table styling for lists */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

    <!-- Tabs -->
    <div class="tab-container">
        <a href="customers.php" class="tab_label">Customers</a>
        <a href="billing.php" class="tab_label">Billing</a>
    </div>
</body>
</html>
