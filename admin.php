<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "admin") {
    header("location: login.php");
    exit;
}

// Get all appointments with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sort_field = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'appointment_date';
$sort_order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Total count with filter
$total_query = "SELECT COUNT(*) as total FROM appointments";
if ($status_filter) {
    $total_query .= " WHERE status = '$status_filter'";
}
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

// Get appointments with filtering and sorting
$appointments_query = "SELECT a.*, m.name as mechanic_name 
    FROM appointments a 
    JOIN mechanics m ON a.mechanic_id = m.id";

$conditions = [];
if ($status_filter) {
    $conditions[] = "a.status = '$status_filter'";
}

if (!empty($conditions)) {
    $appointments_query .= " WHERE " . implode(" AND ", $conditions);
}

$appointments_query .= " ORDER BY a.$sort_field $sort_order LIMIT $offset, $limit";
$appointments_result = mysqli_query($conn, $appointments_query);

// Get mechanics for dropdown
$mechanics_query = "SELECT * FROM mechanics WHERE status = 'active' ORDER BY name ASC";
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
            <span>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-content">
                <?php if (isset($_GET['status_changed'])): ?>
                    <?php if ($_GET['status_changed'] == 'true'): ?>
                        <div class="alert success">
                            <strong>Success!</strong> The appointment status has been updated.
                        </div>
                    <?php else: ?>
                        <div class="alert error">
                            <strong>Error!</strong> There was an issue updating the status.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <div class="section">
                <div class="section-header">
                    <h2>Appointments Management</h2>
                    <div class="filters">
                        <select id="statusFilter" class="filter-select" onchange="applyFilter()">
                            <option value="" <?php echo $status_filter == '' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button id="refreshData" class="btn btn-refresh">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="appointments-list">
                    <?php if (mysqli_num_rows($appointments_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Phone</th>
                                        <th>Car License</th>
                                        <th>Mechanic</th>
                                        <th>Status</th>
                                        <th>Action</th>
<<<<<<< HEAD
                                        <th></th>
=======
>>>>>>> f9dced423438deb0dc4703b61423d14624fc79c3
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appointment = mysqli_fetch_assoc($appointments_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['car_license']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['mechanic_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                            <td>
<<<<<<< HEAD
                                                <form method="POST" action="update_status.php">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="approved" <?php echo $appointment['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                        <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </form>
</td>

                                            <td>
                                                <a href="edit.php?id=<?php echo $appointment['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to Edit this appointment?');">
                                                    <i class="fas fa-trash-alt"></i> Edit
=======
                                                <a href="delete_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this appointment?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
>>>>>>> f9dced423438deb0dc4703b61423d14624fc79c3
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"
                                   class="btn btn-page <?php echo $page == $i ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <p>No appointments found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function applyFilter() {
        const status = document.getElementById('statusFilter').value;
        window.location.href = '?status=' + status;
    }
    </script>
</body>
</html>
