<?php
session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if($_SESSION["user_type"] === "admin"){
        header("location: admin.php");
    } else {
        header("location: user.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop - Appointment System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <a href="index.php" class="nav-brand">Car Workshop</a>
        <nav class="nav-items">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-primary">Register</a>
        </nav>
    </header>

    <div class="landing-container">
        <div class="content-wrapper">
            <h1>Welcome to Car Workshop</h1>
            <p>Book your car service appointment online</p>   
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Car Workshop. All rights reserved.</p>
    </footer>
</body>
</html>
