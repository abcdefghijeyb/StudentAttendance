<?php
// process_login.php
session_start();
require_once 'database.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['First_Name'];
        $_SESSION['last_name'] = $user['Last_Name'];
        header('Location: student_attendance.php');
        exit();
    } else {
        echo "<div class='text-red-600 text-center mt-4'>Invalid password.</div>";
    }
} else {
    echo "<div class='text-red-600 text-center mt-4'>Email not found.</div>";
}

$stmt->close();
$conn->close();
?> 