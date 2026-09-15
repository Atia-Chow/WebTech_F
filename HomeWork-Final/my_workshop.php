<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    die("No active registration found. Please <a href='register.php'>Register Here</a> first.");
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT r.student_id, r.full_name, r.email, r.department, r.registered_at, 
               w.title, w.instructor, w.schedule 
        FROM workshop_registrations r
        JOIN workshops w ON r.workshop_id = w.workshop_id
        WHERE r.student_id = ? 
        ORDER BY r.registration_id DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Workshop</title>
</head>
<body>
    <h2>Registered Workshop Details</h2>

    <?php if ($data): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Student ID</th>
                <td><?= htmlspecialchars($data['student_id']) ?></td>
            </tr>
            <tr>
                <th>Student Name</th>
                <td><?= htmlspecialchars($data['full_name']) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($data['email']) ?></td>
            </tr>
            <tr>
                <th>Department</th>
                <td><?= htmlspecialchars($data['department']) ?></td>
            </tr>
            <tr>
                <th>Workshop Title</th>
                <td><?= htmlspecialchars($data['title']) ?></td>
            </tr>
            <tr>
                <th>Instructor</th>
                <td><?= htmlspecialchars($data['instructor']) ?></td>
            </tr>
            <tr>
                <th>Schedule</th>
                <td><?= htmlspecialchars($data['schedule']) ?></td>
            </tr>
            <tr>
                <th>Registration Date</th>
                <td><?= $data['registered_at'] ?></td>
            </tr>
        </table>
    <?php else: ?>
        <p>No workshop records found for this student.</p>
    <?php endif; ?>

    <p><a href="register.php">Back to Registration</a></p>
</body>
</html>