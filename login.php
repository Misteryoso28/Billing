<?php
session_start();
    include 'header.html';
    include 'db.php'; // Include your database connection script

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $sql = "SELECT * FROM users WHERE BINARY username = '$username' AND status = 'Active'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php'); // Redirect after successful login
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username or account inactive.";
    }
}
?>
    <title>Login</title>
    <style>
        /* Basic reset */
        * {
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Align items at the top */
            height: 100vh; /* Full viewport height */
            margin: 0; /* Remove default margin */
            background-image: url('bg.jpg'); /* Add your background image */
            background-size: cover; /* Ensure the image covers the entire background */
            background-position: center; /* Center the image */
            background-repeat: no-repeat;
            padding-top: 100px; /* Space for the fixed navbar *//
        }

        /* Navbar style */
        .navbar {
            width: 100%;
            background-color: #555;
            overflow: auto;
            position: fixed; /* Fix the navbar to the top */
            top: 0; /* Align it to the top of the page */
            z-index: 1000; /* Ensure it stays above other content */
        }

        /* Navbar links */
        .navbar a {
            float: left;
            text-align: center;
            padding: 12px;
            color: white;
            text-decoration: none;
            font-size: 17px;
        }

        /* Navbar links on mouse-over */
        .navbar a:hover {
            background-color: #000;
        }

        /* Current/active navbar link */
        .active {
            background-color: #04AA6D;
        }

        /* Form styling */
        .form {
            --bg-light: #efefef;
            --bg-dark: #707070;
            --clr: #58bc82;
            --clr-alpha: #9c9c9c60;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            width: 100%;
            max-width: 300px; /* Width of the form */
            padding: 20px; /* Padding inside the form */
            background-color: white; /* Background color of the form */
            border-radius: 15px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 1); /* Subtle shadow for depth */
        }

        .form .input-span {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form input[type="username"],
        .form input[type="password"] {
            border-radius: 0.5rem;
            padding: 1rem 0.75rem;
            width: 100%;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--clr-alpha);
            outline: 2px solid var(--bg-dark);
        }

        .form input[type="email"]:focus,
        .form input[type="password"]:focus {
            outline: 2px solid #04a9cf;
        }

        .label {
            align-self: flex-start;
            color: black;
            font-weight: 600;
        }

        .form .submit {
            padding: 1rem 0.75rem;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 3rem;
            background-color: var(--bg-dark);
            color: var(--bg-light);
            border: none;
            cursor: pointer;
            transition: all 300ms;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form .submit:hover {
            background-color: #04a9cf;
            color: black;
        }

        .span {
            text-decoration: none;
            color: black;
        }

        .span a {
            color: black;
        }

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
            margin-right: 20px;
        }

        .navbar-title {
            color: white; /* Title text color */
            font-size: 24px; /* Font size for title */
            font-weight: bold; /* Bold title */
            flex-grow: 1; /* Allow title to grow and take available space */
        }
    </style>
</head>
<body>
    <h1>SNWD Billing System</h1>
    <div class="navbar">
    <img src="logo.png" alt="Logo" class="logo">
    <div class="navbar-title">SAN NARCISO WATER DISTRICT</div>
    </div>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <form class="form" method="POST" action="">
        <span class="input-span">
            <label for="username" class="label">Username</label>
            <input type="username" name="username" id="username" required />
        </span>
        <span class="input-span">
            <label for="password" class="label">Password</label>
            <input type="password" name="password" id="password" required />
        </span>
        <input class="submit" type="submit" value="Log in" />
    </form>  
</body>
</html>
