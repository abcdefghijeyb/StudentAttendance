<?php
session_start();
// Session timeout: 30 minutes
$timeout = 30 * 60;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?logout=1');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();
// Session hijacking protection
if (!isset($_SESSION['USER_AGENT'])) {
    $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header('Location: login.php?logout=1');
    exit();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Connect to login_register for user info
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die('Connection failed (login_register): ' . $conn->connect_error);
}
$user_id = $_SESSION['user_id'];
// Fetch user info from users table
$stmt = $conn->prepare('SELECT First_Name, Last_Name, email, student_number FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$student_number = $user['student_number'];
// Fetch student info from students table
$stmt2 = $conn->prepare('SELECT * FROM students WHERE student_number = ?');
$stmt2->bind_param('s', $student_number);
$stmt2->execute();
$student_result = $stmt2->get_result();
$student = $student_result->fetch_assoc();
// Fetch attendance summary
$summary = ['Present' => 0, 'Late' => 0, 'Absent' => 0];
$stmt3 = $conn->prepare('SELECT status, COUNT(*) as count FROM attendance WHERE user_id = ? GROUP BY status');
$stmt3->bind_param('i', $user_id);
$stmt3->execute();
$res3 = $stmt3->get_result();
while ($row = $res3->fetch_assoc()) {
    $summary[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
        }
        .profile-card {
            animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1) both;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .summary-box {
            border-radius: 0.75rem;
            padding: 1rem 0.5rem;
            min-width: 80px;
        }
        .summary-label {
            font-size: 0.95rem;
            color: #a1a1aa;
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 transition-colors">
      <!-- Top right navigation -->
      <div class="absolute top-0 right-0 m-6 z-50 flex gap-2">
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Logout</a>
      </div>
      <div class="profile-card bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-md flex flex-col items-center">
        <div class="mb-8 w-full flex flex-col items-center">
          <div class="bg-blue-600 rounded-full px-6 py-2 mb-4 shadow-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-3xl font-bold text-white mb-2">Profile</h2>
        </div>
        <div class="mb-4 w-full text-white">
          <div class="mb-2"><span class="font-semibold text-white">Student Number:</span> <?php echo htmlspecialchars($student['student_number'] ?? ''); ?></div>
          <div class="mb-2"><span class="font-semibold text-white">Name:</span> <?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . (isset($student['middle_name']) ? strtoupper($student['middle_name'][0]) . '. ' : '') . ($student['last_name'] ?? '')); ?></div>
          <div class="mb-2"><span class="font-semibold text-white">Grade Level:</span> <?php echo htmlspecialchars($student['grade_level'] ?? ''); ?></div>
          <div class="mb-2"><span class="font-semibold text-white">Section:</span> <?php echo htmlspecialchars($student['section'] ?? ''); ?></div>
          <div class="mb-2"><span class="font-semibold text-white">Email:</span> <?php echo htmlspecialchars($user['email']); ?></div>
        </div>
        <div class="mb-4 w-full">
          <h3 class="font-semibold mb-2 text-white">Attendance Summary</h3>
          <div class="flex gap-4 justify-center">
            <div class="summary-box bg-green-900 text-center">
              <span class="block text-green-400 font-bold text-lg"><?php echo $summary['Present']; ?></span>
              <span class="summary-label">Present</span>
            </div>
            <div class="summary-box bg-yellow-900 text-center">
              <span class="block text-yellow-400 font-bold text-lg"><?php echo $summary['Late']; ?></span>
              <span class="summary-label">Late</span>
            </div>
            <div class="summary-box bg-red-900 text-center">
              <span class="block text-red-400 font-bold text-lg"><?php echo $summary['Absent']; ?></span>
              <span class="summary-label">Absent</span>
            </div>
          </div>
        </div>
        <div class="mb-4 w-full flex flex-col gap-2">
          <a href="student_attendance.php" class="w-full block bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Mark Attendance</a>
          <a href="attendance_history.php" class="w-full block bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Attendance History</a>
          <a href="download_attendance.php" class="w-full block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Download Attendance Report</a>
        </div>
      </div>
    </div>
</body>
</html> 