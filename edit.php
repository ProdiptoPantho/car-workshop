<?php
session_start();
require_once "config.php";

// Check if admin is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "admin") {
    header("location: login.php");
    exit;
}

$appointment_id = $_GET['id'] ?? null; // Get appointment ID from the URL
if (!$appointment_id) {
    $_SESSION['error'] = "No appointment ID provided.";
    header("location: admin.php"); // Redirect to the admin dashboard
    exit;
}

// Fetch the appointment details from the database
$query = "SELECT a.*, m.name AS mechanic_name FROM appointments a JOIN mechanics m ON a.mechanic_id = m.id WHERE a.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $appointment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appointment = mysqli_fetch_assoc($result);

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("location: admin.php");
    exit;
}

// Get available mechanics
$mechanics_query = "SELECT id, name FROM mechanics WHERE status = 'active'";
$mechanics_result = mysqli_query($conn, $mechanics_query);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    // Process the form data to update the appointment
    $client_name = $_POST['client_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $car_license = $_POST['car_license'];
    $car_engine = $_POST['car_engine'];
    $appointment_date = $_POST['appointment_date'];
    $mechanic_id = $_POST['mechanic_id'];

    $update_query = "UPDATE appointments SET client_name = ?, address = ?, phone = ?, car_license = ?, car_engine = ?, appointment_date = ?, mechanic_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssssssii", $client_name, $address, $phone, $car_license, $car_engine, $appointment_date, $mechanic_id, $appointment_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Appointment updated successfully!";
        header("location: admin.php");
        exit;
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Car Workshop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="nav-brand">Car Workshop Admin</a>
        <div class="nav-items">
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="edit-appointment-container">
        <h2>Edit Appointment</h2>
        
        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Appointment editing form -->
        <form action="edit.php?id=<?php echo $appointment['id']; ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">

            <div class="form-group">
                <label for="client_name">Client Name</label>
                <input type="text" id="client_name" name="client_name" value="<?php echo $appointment['client_name']; ?>" readonly>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo $appointment['address']; ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $appointment['phone']; ?>" required pattern="[0-9]{11}">
            </div>

            <div class="form-group">
                <label for="car_license">Car License Number</label>
                <input type="text" id="car_license" name="car_license" value="<?php echo $appointment['car_license']; ?>" required>
            </div>

            <div class="form-group">
                <label for="car_engine">Car Engine Number</label>
                <input type="text" id="car_engine" name="car_engine" value="<?php echo $appointment['car_engine']; ?>" required>
            </div>

            <div class="form-group">
                <label for="appointment_date">Appointment Date</label>
                <input type="date" id="appointment_date" name="appointment_date" value="<?php echo $appointment['appointment_date']; ?>" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="mechanic_id">Select Mechanic</label>
                <select id="mechanic_id" name="mechanic_id" required>
                    <?php while ($mechanic = mysqli_fetch_assoc($mechanics_result)): ?>
                        <option value="<?php echo $mechanic['id']; ?>" <?php echo $mechanic['id'] == $appointment['mechanic_id'] ? 'selected' : ''; ?>>
                            <?php echo $mechanic['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="action" value="update" class="btn btn-primary">Update Appointment</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Car Workshop. All rights reserved.</p>
    </footer>
</body>
</html>

<?php mysqli_close($conn); ?>
