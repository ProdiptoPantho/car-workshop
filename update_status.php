<?php
session_start();
require_once "config.php";

// Check if user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "admin") {
    header("location: login.php");
    exit;
}

// Check if the required POST data is set
if (isset($_POST['appointment_id']) && isset($_POST['status'])) {
    // Sanitize input to prevent SQL injection
    $appointment_id = mysqli_real_escape_string($conn, $_POST['appointment_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validate the status value (ensure it's one of the allowed statuses)
    $valid_statuses = ['pending', 'approved', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        echo "Invalid status value.";
        exit;
    }

    // Update the appointment status in the database
    $update_query = "UPDATE appointments SET status = '$status' WHERE id = '$appointment_id'";

    if (mysqli_query($conn, $update_query)) {
        // If successful, redirect back to the admin dashboard without the query parameter
        header("Location: admin.php?status_changed=true");
        exit;
    } else {
        // If there was an error during the update, show an error message
        echo "Error updating status: " . mysqli_error($conn);
        exit;
    }
} else {
    // If the required parameters are not set, redirect back to the admin dashboard
    header("Location: admin.php?status_changed=false");
    exit;
}
?>
