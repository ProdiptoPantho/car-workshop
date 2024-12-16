<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "admin"){
    header("location: login.php");
    exit;
}

// Get all appointments
$appointments_query = "SELECT a.*, m.name as mechanic_name 
    FROM appointments a 
    JOIN mechanics m ON a.mechanic_id = m.id 
    ORDER BY appointment_date DESC";
$appointments_result = mysqli_query($conn, $appointments_query);

// Get mechanics for dropdown
$mechanics_query = "SELECT * FROM mechanics WHERE status = 'active'";
$mechanics_result = mysqli_query($conn, $mechanics_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Workshop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="nav-brand">Car Workshop</a>
        <div class="nav-items">
            <span>Welcome, Admin</span>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="section">
                <h2>Appointments Management</h2>
                <div class="appointments-list">
                    <?php if(mysqli_num_rows($appointments_result) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Phone</th>
                                    <th>Car License</th>
                                    <th>Mechanic</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($appointment = mysqli_fetch_assoc($appointments_result)): ?>
                                    <tr>
                                        <td>
                                            <input type="date" 
                                                class="edit-appointment-date" 
                                                data-id="<?php echo $appointment['id']; ?>"
                                                value="<?php echo $appointment['appointment_date']; ?>">
                                        </td>
                                        <td><?php echo $appointment['client_name']; ?></td>
                                        <td><?php echo $appointment['phone']; ?></td>
                                        <td><?php echo $appointment['car_license']; ?></td>
                                        <td>
                                            <select class="edit-mechanic" 
                                                data-id="<?php echo $appointment['id']; ?>">
                                                <?php 
                                                mysqli_data_seek($mechanics_result, 0);
                                                while($mechanic = mysqli_fetch_assoc($mechanics_result)): 
                                                ?>
                                                    <option value="<?php echo $mechanic['id']; ?>"
                                                        <?php echo $mechanic['id'] == $appointment['mechanic_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $mechanic['name']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="edit-status" 
                                                data-id="<?php echo $appointment['id']; ?>">
                                                <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="approved" <?php echo $appointment['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-delete" 
                                                data-id="<?php echo $appointment['id']; ?>">
                                                Delete
                                            </button>
                                        </td>
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


    <footer>
        <p>&copy; 2024 Car Workshop. All rights reserved.</p>
    </footer>    
    <script src="script.js"></script>
</body>
</html>