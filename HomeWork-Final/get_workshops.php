<?php
include 'db.php';

header('Content-Type: application/json');

$query = "SELECT workshop_id, title, instructor, schedule FROM workshops ORDER BY title ASC";
$result = mysqli_query($conn, $query);

$workshops = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $workshops[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $workshops]);
} else {
    echo json_encode(["status" => "error", "message" => "Unable to fetch workshops."]);
}

mysqli_close($conn);
?>