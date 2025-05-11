<?php
session_start();
// Simple hardcoded admin credentials for demo (replace with DB in production)
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123';
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$error = '';
require_once 'database.php';
// Handle login
if (isset($_POST['admin_user'], $_POST['admin_pass'])) {
    if ($_POST['admin_user'] === $ADMIN_USER && $_POST['admin_pass'] === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $admin_logged_in = true;
    } else {
        $error = 'Invalid admin credentials.';
    }
}
// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin.php');
    exit();
}
// Handle CSV download
if ($admin_logged_in && isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="all_attendance.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student Name', 'Student Number', 'Date', 'Status', 'Time Marked']);
    $sql = "SELECT s.first_name, s.last_name, s.student_number, a.date, a.status, a.time_marked FROM attendance a JOIN users u ON a.user_id = u.id JOIN students s ON u.student_number = s.student_number ORDER BY a.date DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['first_name'] . ' ' . $row['last_name'],
            $row['student_number'],
            $row['date'],
            $row['status'],
            $row['time_marked']
        ]);
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="loading.css">
    <script src="loading.js"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
        }
        .admin-card {
            animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1) both;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .error-message {
            background: #7f1d1d;
            color: #fee2e2;
            border: 1px solid #b91c1c;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .table-header { background: #3730a3; color: #e0e7ff; }
        .table-row { transition: background 0.2s; }
        .table-row:hover { background: #23272f; }
    </style>
</head>
<body>
    <?php if (!$admin_logged_in): ?>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 transition-colors">
      <div class="admin-card bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-md flex flex-col items-center">
        <div class="mb-6 flex flex-col items-center">
          <div class="bg-indigo-500 rounded-full p-3 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">Admin Login</h2>
        </div>
        <?php if ($error): ?>
            <div class="error-message text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="admin.php" method="POST" class="w-full space-y-4">
          <div>
            <label for="admin_user" class="block font-semibold mb-1 text-white">Username</label>
            <input type="text" id="admin_user" name="admin_user" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <div>
            <label for="admin_pass" class="block font-semibold mb-1 text-white">Password</label>
            <input type="password" id="admin_pass" name="admin_pass" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">Login</button>
        </form>
      </div>
    </div>
    <?php else: ?>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 transition-colors">
      <!-- Top right logout -->
      <div class="absolute top-0 right-0 m-6 z-50 flex gap-2">
        <a href="admin.php?logout=1" class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md px-6 py-2 transition text-center">Logout</a>
      </div>
      <div class="admin-card bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-5xl flex flex-col items-center">
        <div class="mb-8 w-full flex flex-col items-center">
          <div class="bg-blue-600 rounded-full px-6 py-2 mb-4 shadow-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">Admin Dashboard</h2>
        </div>
        <div class="mb-4 w-full flex flex-col md:flex-row gap-2 justify-between items-center">
          <form method="get" class="flex gap-2 flex-wrap">
            <input type="text" name="student" placeholder="Student Number or Name" class="border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white" value="<?php echo htmlspecialchars($_GET['student'] ?? ''); ?>">
            <input type="date" name="date" class="border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>">
            <select name="status" class="border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
              <option value="">All Status</option>
              <option value="Present" <?php if (($_GET['status'] ?? '') === 'Present') echo 'selected'; ?>>Present</option>
              <option value="Late" <?php if (($_GET['status'] ?? '') === 'Late') echo 'selected'; ?>>Late</option>
              <option value="Absent" <?php if (($_GET['status'] ?? '') === 'Absent') echo 'selected'; ?>>Absent</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md px-4 py-2 transition">Filter</button>
          </form>
          <a href="admin.php?download=csv" class="bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md px-4 py-2 transition">Download CSV</a>
        </div>
        <div class="overflow-x-auto w-full">
          <table class="min-w-full border border-gray-700 rounded-lg overflow-hidden">
            <thead>
              <tr class="table-header">
                <th class="px-4 py-2 border-b border-gray-700 text-white">Student Name</th>
                <th class="px-4 py-2 border-b border-gray-700 text-white">Student Number</th>
                <th class="px-4 py-2 border-b border-gray-700 text-white">Date</th>
                <th class="px-4 py-2 border-b border-gray-700 text-white">Status</th>
                <th class="px-4 py-2 border-b border-gray-700 text-white">Time Marked</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Filtering
              $where = [];
              $params = [];
              if (!empty($_GET['student'])) {
                  $where[] = "(s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
                  $params[] = '%' . $_GET['student'] . '%';
                  $params[] = '%' . $_GET['student'] . '%';
                  $params[] = '%' . $_GET['student'] . '%';
              }
              if (!empty($_GET['date'])) {
                  $where[] = "a.date = ?";
                  $params[] = $_GET['date'];
              }
              if (!empty($_GET['status'])) {
                  $where[] = "a.status = ?";
                  $params[] = $_GET['status'];
              }
              $sql = "SELECT s.first_name, s.last_name, s.student_number, a.date, a.status, a.time_marked FROM attendance a JOIN users u ON a.user_id = u.id JOIN students s ON u.student_number = s.student_number";
              if ($where) {
                  $sql .= " WHERE " . implode(' AND ', $where);
              }
              $sql .= " ORDER BY a.date DESC";
              $stmt = $conn->prepare($sql);
              if ($params) {
                  $types = str_repeat('s', count($params));
                  $stmt->bind_param($types, ...$params);
              }
              $stmt->execute();
              $result = $stmt->get_result();
              if ($result->num_rows === 0) {
                  echo '<tr><td colspan="5" class="text-center py-4 text-white">No records found.</td></tr>';
              } else {
                  while ($row = $result->fetch_assoc()) {
                      echo '<tr class="table-row">';
                      echo '<td class="px-4 py-2 border-b border-gray-700 text-white">' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
                      echo '<td class="px-4 py-2 border-b border-gray-700 text-white">' . htmlspecialchars($row['student_number']) . '</td>';
                      echo '<td class="px-4 py-2 border-b border-gray-700 text-white">' . htmlspecialchars($row['date']) . '</td>';
                      echo '<td class="px-4 py-2 border-b border-gray-700 text-white">';
                      if ($row['status'] === 'Present') echo '<span class="font-bold text-green-400">Present</span>';
                      elseif ($row['status'] === 'Late') echo '<span class="font-bold text-yellow-400">Late</span>';
                      else echo '<span class="font-bold text-red-400">Absent</span>';
                      echo '</td>';
                      echo '<td class="px-4 py-2 border-b border-gray-700 text-white">' . htmlspecialchars($row['time_marked']) . '</td>';
                      echo '</tr>';
                  }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
</body>
</html>
