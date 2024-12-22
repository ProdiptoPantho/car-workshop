<?php
session_start();
require_once "config.php";

// Check authentication for admin actions
function checkAdminAuth() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "admin") {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Unauthorized access"]);
        exit;
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle new appointment booking
    if(isset($_POST['client_name']) && !isset($_POST['action'])) {
        $client_name = $_POST['client_name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $car_license = $_POST['car_license'];
        $car_engine = $_POST['car_engine'];
        $appointment_date = $_POST['appointment_date'];
        $mechanic_id = $_POST['mechanic_id'];

        // Check if client already has appointment on the same date
        $check_query = "SELECT id FROM appointments WHERE client_name = ? AND appointment_date = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $client_name, $appointment_date);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "You already have an appointment on this date.";
            header("location: user.php");
            exit;
        }

        // Check mechanic availability
        $check_mechanic_query = "SELECT COUNT(*) as booked_slots 
            FROM appointments 
            WHERE mechanic_id = ? 
            AND appointment_date = ? 
            AND status != 'cancelled'";

        $stmt = mysqli_prepare($conn, $check_mechanic_query);
        mysqli_stmt_bind_param($stmt, "is", $mechanic_id, $appointment_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if($row['booked_slots'] >= 4) {
            $_SESSION['error'] = "Selected mechanic is fully booked for this date.";
            header("location: user.php");
            exit;
        }

        // Insert appointment
        $insert_query = "INSERT INTO appointments (client_name, address, phone, car_license, 
            car_engine, appointment_date, mechanic_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssssssi", $client_name, $address, $phone, 
            $car_license, $car_engine, $appointment_date, $mechanic_id);

        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Appointment booked successfully!";
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
        }
        header("location: user.php");
        exit;
    }

    // Handle admin actions via POST (fallback for PUT/DELETE)
    if(isset($_POST['action'])) {
        checkAdminAuth();
        
        switch($_POST['action']) {
            case 'update_status':
                $query = "UPDATE appointments SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $_POST['status'], $_POST['id']);
                echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
                break;

            case 'update_mechanic':
                $query = "UPDATE appointments SET mechanic_id = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $_POST['mechanic_id'], $_POST['id']);
                echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
                break;

            case 'delete':
                $query = "DELETE FROM appointments WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $_POST['id']);
                echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
                break;
        }
        exit;
    }
}

// Handle PUT requests
if($_SERVER["REQUEST_METHOD"] == "PUT") {
    checkAdminAuth();
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(isset($data['action'])) {
        switch($data['action']) {
            case 'update_date':
                $query = "UPDATE appointments SET appointment_date = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $data['date'], $data['id']);
                echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
                break;

            case 'update_mechanic':
                $query = "UPDATE appointments SET mechanic_id = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $data['mechanic_id'], $data['id']);
                echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
                break;

            case 'update_status':
                $query = "UPDATE appointments SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $data['status'], $data['id']);
                echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
                break;
        }
    }
}

// Handle DELETE requests
if($_SERVER["REQUEST_METHOD"] == "DELETE") {
    checkAdminAuth();
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(isset($data['id'])) {
        $query = "DELETE FROM appointments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $data['id']);
        echo json_encode(['success' => mysqli_stmt_execute($stmt)]);
    }
}

// Close database connection
mysqli_close($conn);
?>