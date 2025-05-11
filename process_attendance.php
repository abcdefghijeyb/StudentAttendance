<?php
// Include the database configuration file
require_once('db_config.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the student number and attendance date from the POST data
    $studentNumber = $_POST['student_number'];
    $attendanceDate = $_POST['attendance_date'];
    $status = $_POST['status']; // Added to get the status

    // Validate the student number (you can add more robust validation here)
    if (empty($studentNumber)) {
        echo "Error: Student number is required.";
        exit;
    }

    // Check if the student's attendance has already been recorded for the given date
    $checkQuery = "SELECT * FROM attendance WHERE student_number = '$studentNumber' AND attendance_date = '$attendanceDate'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo "Error: Attendance already recorded for this student on this date.";
        exit;
    } else {
        // Insert the attendance record into the database
        $insertQuery = "INSERT INTO attendance (student_number, attendance_date, status) VALUES ('$studentNumber', '$attendanceDate', '$status')"; //added status in query
        if ($conn->query($insertQuery) === TRUE) {
            echo "Success: Attendance marked successfully!";
        } else {
            echo "Error: " . $insertQuery . "<br>" . $conn->error;
        }
    }

    // Close the database connection
    $conn->close();
} else {
    // If the request method is not POST, return an error
    echo "Error: Invalid request method.";
}
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ... iyong ibang code ...
?>
