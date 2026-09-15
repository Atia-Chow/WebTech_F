<?php
session_start();
require 'includes/db.php';

// Ensure only logged-in organizations can do this
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'organization') {
    http_response_code(403);
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = (int)$_POST['app_id'];
    $action = $_POST['action']; // Will be 'accept' or 'decline'
    
    // Determine the new status
    $newStatus = ($action === 'accept') ? 'Accepted' : 'Declined';
    
    // Update the database
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    
    if ($stmt->execute([$newStatus, $applicationId])) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Database update failed.";
    }
}
?>