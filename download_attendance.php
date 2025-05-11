<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Status', 'Time Marked']);
$stmt = $conn->prepare('SELECT date, status, time_marked FROM attendance WHERE user_id = ? ORDER BY date DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['date'], $row['status'], $row['time_marked']]);
}
fclose($output);
exit(); 