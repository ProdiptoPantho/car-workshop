<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "client"){
    header("location: login.php");
    exit;
}

// Get available mechanics
$mechanics_query = "SELECT m.id, m.name, m.max_appointments, 
    (SELECT COUNT(*) FROM appointments 
     WHERE mechanic_id = m.id AND appointment_date = CURDATE()) as current_appointments 
    FROM mechanics m 
    WHERE m.status = 'active'";
$mechanics_result = mysqli_query($conn, $mechanics_query);

// Get user's appointments
$user_appointments_query = "SELECT a.*, m.name as mechanic_name 
    FROM appointments a 
    JOIN mechanics m ON a.mechanic_id = m.id 
    WHERE client_name = ? 
    ORDER BY appointment_date DESC";
$stmt = mysqli_prepare($conn, $user_appointments_query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION["username"]);
mysqli_stmt_execute($stmt);
$user_appointments_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Car Workshop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="nav-brand">Car Workshop</a>
        <div class="nav-items">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="section">
                <h2>Book New Appointment</h2>
                <form id="appointmentForm" action="appointment.php" method="post">
                    <div class="form-group">
                        <input type="text" name="client_name" value="<?php echo $_SESSION["username"]; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <input type="text" name="address" placeholder="Address" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Phone Number" required pattern="[0-9]{11}">
                    </div>
                    <div class="form-group">
                        <input type="text" name="car_license" placeholder="Car License Number" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="car_engine" placeholder="Car Engine Number" required>
                    </div>
                    <div class="form-group">
                        <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <select name="mechanic_id" required>
                            <option value="">Select Mechanic</option>
                            <?php while($mechanic = mysqli_fetch_assoc($mechanics_result)): ?>
                                <?php $available_slots = $mechanic['max_appointments'] - $mechanic['current_appointments']; ?>
                                <option value="<?php echo $mechanic['id']; ?>" 
                                    <?php echo $available_slots <= 0 ? 'disabled' : ''; ?>>
                                    <?php echo $mechanic['name']; ?> 
                                    (<?php echo $available_slots; ?> slots available)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Book Appointment</button>
                </form>
            </div>

            <div class="section">
                <h2>Your Appointments</h2>
                <div class="appointments-list">
                    <?php if(mysqli_num_rows($user_appointments_result) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Mechanic</th>
                                    <th>Car License</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($appointment = mysqli_fetch_assoc($user_appointments_result)): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['mechanic_name']; ?></td>
                                        <td><?php echo $appointment['car_license']; ?></td>
                                        <td><?php echo ucfirst($appointment['status']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No appointments found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>