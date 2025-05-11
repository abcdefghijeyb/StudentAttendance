<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body.dark { background-color: #18181b; color: #e5e7eb; }
        .dark .bg-white { background-color: #23272f !important; color: #e5e7eb; }
        .dark .text-gray-800 { color: #e5e7eb !important; }
        .dark .border-gray-200 { border-color: #334155 !important; }
        .dark .bg-gray-100 { background-color: #18181b !important; }
        .dark .hover\:bg-green-700:hover { background-color: #0891b2 !important; }
        .dark .hover\:bg-red-600:hover { background-color: #b91c1c !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>!</h2>
            <button id="dark-toggle" class="ml-4 text-xl" title="Toggle dark mode">🌙</button>
        </div>
        <div class="mb-6 flex gap-2 justify-center">
            <a href="student_attendance.php" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Mark Attendance</a>
            <a href="attendance_history.php" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">Attendance History</a>
            <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Logout</a>
        </div>
        <p class="mb-6">You are logged in.</p>
    </div>
    <script>
        // Dark mode toggle
        const darkToggle = document.getElementById('dark-toggle');
        function setDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark');
                darkToggle.textContent = '☀️';
            } else {
                document.body.classList.remove('dark');
                darkToggle.textContent = '🌙';
            }
        }
        const savedTheme = localStorage.getItem('theme');
        setDarkMode(savedTheme === 'dark');
        darkToggle.addEventListener('click', function() {
            const isDark = !document.body.classList.contains('dark');
            setDarkMode(isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>
</html> 