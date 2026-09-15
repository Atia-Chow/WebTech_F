<?php
session_start();
require 'includes/db.php'; // Connect to the database

// Security check: Make sure a user is actually logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Did they submit a bio?
    if (isset($_POST['bio'])) {
        $bio = htmlspecialchars(trim($_POST['bio']));
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->execute([$bio, $userId]);
    }
    
    // 2. Did they submit skills?
    if (isset($_POST['skills'])) {
        $skills = htmlspecialchars(trim($_POST['skills']));
        $stmt = $pdo->prepare("UPDATE users SET skills = ? WHERE id = ?");
        $stmt->execute([$skills, $userId]);
    }
    
    // 3. Did they submit a cause?
    if (isset($_POST['causes'])) {
        $causes = htmlspecialchars(trim($_POST['causes']));
        $stmt = $pdo->prepare("UPDATE users SET causes = ? WHERE id = ?");
        $stmt->execute([$causes, $userId]);
    }

    // Redirect back to the profile page after saving
    header("Location: volunteer-profile.php?updated=success");
    exit();
} else {
    // If someone tries to access this file directly without submitting a form
    header("Location: volunteer-profile.php");
    exit();
}
?>