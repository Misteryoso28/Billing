<div class="navbar">
    <a href="index.php">
        <img src="logo.png" alt="Logo" class="logo"/> <!-- Logo with class for styling -->
    </a>
    <div class="navbar-title">SAN NARCISO WATER DISTRICT</div>
    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
</div>

<style>
    /* Style the navigation bar */
    .navbar {
        width: 100%;
        height: 90px;
        background-color: #555;
        overflow: auto;
        position: fixed;
        top: 0;
        z-index: 1000; /* Ensure it stays above other content */
        display: flex; /* Flexbox for alignment */
        align-items: center; /* Vertically center items */
        padding: 0 20px; /* Add padding for left and right spacing */
    }

    .logo {
        height: 80px; /* Set logo height */
        margin-left: 20px; /* Space between logo and title */
    }

    .navbar-title {
        color: white; /* Title text color */
        font-size: 24px; /* Font size for title */
        font-weight: bold; /* Bold title */
        flex-grow: 1; /* Allow title to grow and take available space */
        text-align: center; /* Center the title text */
    }

    /* Style the logout button */
    .logout-btn {
        background-color: #d9534f; /* Red background */
        color: white; /* Text color */
        padding: 10px 20px; /* Add padding for size */
        border: none; /* Remove default border */
        border-radius: 20px; /* Rounded corners for button */
        font-size: 16px; /* Font size */
        font-weight: bold; /* Bold text */
        text-decoration: none; /* Remove underline */
        margin-right: 40px; /* Space between title and logout button */
        cursor: pointer; /* Change cursor to pointer on hover */
        transition: background-color 0.3s; /* Smooth transition for hover effect */
    }

    .logout-btn:hover {
        background-color: #c9302c; /* Darker red on hover */
    }
</style>
