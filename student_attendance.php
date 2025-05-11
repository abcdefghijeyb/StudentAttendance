<?php
session_start();
// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

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
$date_today = date('Y-m-d');
$time_now = date('H:i:s');
$class_start = strtotime($date_today . ' 08:00:00');
$now = strtotime($date_today . ' ' . $time_now);
$late_limit = strtotime('+10 minutes', $class_start);
$message = '';
$marked_time = '';
$status = '';

// Check if already marked
$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->bind_param("is", $user_id, $date_today);
$stmt->execute();
$result = $stmt->get_result();
$already_marked = $result->num_rows > 0;
if ($already_marked) {
    $row = $result->fetch_assoc();
    $status = $row['status'];
    $marked_time = $row['time_marked'];
    $message = '<div class="info-message text-center mb-4">You already marked attendance today as <b>' . htmlspecialchars($status) . '</b> at <b>' . htmlspecialchars($marked_time) . '</b>.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_marked) {
    // Set status automatically
    $status = ($now <= $late_limit) ? 'Present' : 'Late';
    $marked_time = $time_now;
    $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, status, time_marked) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $date_today, $status, $marked_time);
    if ($stmt->execute()) {
        $message = '<div class="success-message text-center mb-4">Attendance marked as <b>' . htmlspecialchars($status) . '</b> at <b>' . htmlspecialchars($marked_time) . '</b>!</div>';
        $already_marked = true;
    } else {
        $message = '<div class="error-message text-center mb-4">Error: ' . htmlspecialchars($stmt->error) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
        }
        .attendance-card {
            animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1) both;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .success-message {
            background: #064e3b;
            color: #6ee7b7;
            border: 1px solid #34d399;
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .error-message {
            background: #7f1d1d;
            color: #fee2e2;
            border: 1px solid #b91c1c;
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .info-message {
            background: rgba(59,130,246,0.3);
            color: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .status-box, .time-box {
            background: none;
            box-shadow: none;
            border: none;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 0;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 transition-colors">
      <!-- Top right Profile button -->
      <div class="absolute top-0 right-0 m-6 z-50 flex gap-2">
        <a href="profile.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Profile</a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Logout</a>
      </div>
      <div class="bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-md flex flex-col items-center attendance-card">
        <div class="mb-8 w-full flex flex-col items-center">
          <div class="bg-blue-600 rounded-full px-6 py-2 mb-4 shadow-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">Welcome, <?php echo htmlspecialchars(strtoupper($first_name . ' ' . $last_name)); ?></h2>
          <p class="text-white mb-2">Date: <span class="font-semibold"><?php echo $date_today; ?></span></p>
        </div>
        <div class="w-full mb-4">
          <?php echo $message; ?>
          <?php if (!$already_marked): ?>
          <form method="POST" class="space-y-4">
            <div class="text-center">
              <span class="block font-semibold mb-1 text-white">Time Now:</span>
              <span class="time-box text-white" id="current-time"><?php echo date('h:i:s A'); ?></span>
            </div>
            <div class="text-center">
              <span class="block font-semibold mb-1 text-white">Status:</span>
              <span id="status-display" class="status-box <?php echo ($now <= $late_limit) ? 'text-green-400' : 'text-yellow-400'; ?>">
                <?php echo ($now <= $late_limit) ? 'Present' : 'Late'; ?>
              </span>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-lg">Mark Attendance</button>
          </form>
          <?php else: ?>
          <div class="text-center mt-4">
            <a href="attendance_history.php" class="w-full block bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">View Attendance History</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <script>
        // Live time update
        function updateTimeAndStatus() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            let displayHour = hours % 12;
            displayHour = displayHour ? displayHour : 12;
            document.getElementById('current-time').textContent = `${displayHour}:${minutes}:${seconds} ${ampm}`;
            // Status update
            const statusDisplay = document.getElementById('status-display');
            if (!statusDisplay) return;
            const classStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 8, 0, 0);
            const lateLimit = new Date(classStart.getTime() + 10 * 60000);
            if (now <= lateLimit) {
                statusDisplay.textContent = 'Present';
                statusDisplay.className = 'status-box text-green-400';
            } else {
                statusDisplay.textContent = 'Late';
                statusDisplay.className = 'status-box text-yellow-400';
            }
        }
        setInterval(updateTimeAndStatus, 1000);
        updateTimeAndStatus();
    </script>
</body>
</html>
