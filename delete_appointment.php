<?php
session_start();
require_once "config.php";

// Ensure the user is logged in and has admin privileges
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "admin") {
    header("location: login.php");
    exit;
}

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $appointment_id = (int)$_GET['id'];

    // Prepare and execute the delete query
    $delete_query = "DELETE FROM appointments WHERE id = $appointment_id";
    if (mysqli_query($conn, $delete_query)) {
        // Redirect back to the appointments page
        header("Location: admin.php");
        exit;
    } else {
        echo "Error deleting appointment: " . mysqli_error($conn);
    }
} else {
    // Redirect if no ID is provided
    header("Location: admin.php");
    exit;
}
?>
