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
require_once 'database.php';
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Fetch attendance records
$stmt = $conn->prepare("SELECT date, status, time_marked FROM attendance WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
        }
        .history-card {
            animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1) both;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .table-header { background: #3730a3; color: #e0e7ff; }
        .table-row { transition: background 0.2s; }
        .table-row:hover { background: #23272f; }
        .error-message {
            background: #7f1d1d;
            color: #fee2e2;
            border: 1px solid #b91c1c;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 transition-colors">
      <!-- Top right navigation -->
      <div class="absolute top-0 right-0 m-6 z-50 flex gap-2">
        <a href="profile.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Profile</a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Logout</a>
      </div>
      <div class="history-card bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-2xl mt-12 flex flex-col items-center">
        <div class="mb-8 w-full flex flex-col items-center">
          <div class="bg-blue-600 rounded-full px-6 py-2 mb-4 shadow-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">Attendance History for <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h2>
        </div>
        <div class="mb-4 flex gap-2 justify-center w-full">
          <a href="student_attendance.php" class="w-full block bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Mark Attendance</a>
        </div>
        <div class="overflow-x-auto w-full">
          <table class="min-w-full border border-gray-700 rounded-lg overflow-hidden">
            <thead>
              <tr class="table-header">
                <th class="px-4 py-2 border-b border-gray-700 text-white">Date</th>
                <th class="px-4 py-2 border-b border-gray-700 text-white">Status</th>
                <th class="px-4 py-2 border-b border-gray-700 text-white">Time Marked</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($records) === 0): ?>
                <tr><td colspan="3" class="text-center py-4 text-white">No attendance records found.</td></tr>
              <?php else: ?>
                <?php foreach ($records as $rec): ?>
                <tr class="table-row">
                  <td class="px-4 py-2 border-b border-gray-700 text-white"><?php echo htmlspecialchars($rec['date']); ?></td>
                  <td class="px-4 py-2 border-b border-gray-700 text-white">
                    <?php
                      $status = $rec['status'];
                      if ($status === 'Present') echo '<span class="font-bold text-green-400">Present</span>';
                      elseif ($status === 'Late') echo '<span class="font-bold text-yellow-400">Late</span>';
                      else echo '<span class="font-bold text-red-400">Absent</span>';
                    ?>
                  </td>
                  <td class="px-4 py-2 border-b border-gray-700 text-white"><?php echo htmlspecialchars($rec['time_marked']); ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</body>
</html> 