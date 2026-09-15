<?php
$host = "localhost";
$user = "root";
$password = "";
$dbName = "workshop_system_db";

$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$createDbSql = "CREATE DATABASE IF NOT EXISTS $dbName";
if (!mysqli_query($conn, $createDbSql)) {
    die("Error creating database: " . mysqli_error($conn));
}

mysqli_select_db($conn, $dbName);

$workshopTable = "CREATE TABLE IF NOT EXISTS workshops (
    workshop_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    instructor VARCHAR(100) NOT NULL,
    schedule VARCHAR(100) NOT NULL
)";
mysqli_query($conn, $workshopTable);

$registrationTable = "CREATE TABLE IF NOT EXISTS workshop_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    workshop_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(workshop_id)
)";
mysqli_query($conn, $registrationTable);

$checkWorkshops = mysqli_query($conn, "SELECT COUNT(*) AS total FROM workshops");
$row = mysqli_fetch_assoc($checkWorkshops);
if ($row['total'] == 0) {
    $seedQuery = "INSERT INTO workshops (title, instructor, schedule) VALUES
        ('AI & Machine Learning Fundamentals', 'Dr. Tariq', 'Mon & Wed, 10:00 AM'),
        ('Full-Stack Web Development', 'Prof. Sarah', 'Tue & Thu, 02:00 PM'),
        ('Cybersecurity Essentials', 'Engr. Kamal', 'Friday, 09:00 AM')";
    mysqli_query($conn, $seedQuery);
}
?>