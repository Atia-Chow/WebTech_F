<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $workshop_id= intval($_POST['workshop_id'] ?? 0);

    if (empty($student_id) || empty($full_name) || empty($email) || empty($department) || $workshop_id <= 0) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO workshop_registrations (student_id, full_name, email, department, workshop_id) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssi", $student_id, $full_name, $email, $department, $workshop_id);

    if (mysqli_stmt_execute($stmt)) {
        $registration_id = mysqli_insert_id($conn);

        $_SESSION['student_id'] = $student_id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['registration_id'] = $registration_id;

 
        setcookie("remembered_student_id", $student_id, time() + (86400 * 30), "/");

        echo json_encode(["status" => "success", "message" => "Registration completed successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_stmt_error($stmt)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}
?>