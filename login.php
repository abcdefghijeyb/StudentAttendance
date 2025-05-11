<?php
session_start();
$error = '';
$logout_success = isset($_GET['logout']) && $_GET['logout'] == '1';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'database.php';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['First_Name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['student_number'] = $user['student_number'] ?? '';
        header('Location: student_attendance.php');
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
        }
        .login-card {
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
        .success-message {
            background: #064e3b;
            color: #6ee7b7;
            border: 1px solid #34d399;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 transition-colors">
      <div class="login-card bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-md flex flex-col items-center">
        <div class="mb-6 flex flex-col items-center">
          <div class="bg-indigo-500 rounded-full p-3 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-2xl font-bold text-white">Student Attendance</h2>
        </div>
        <?php if ($logout_success): ?>
            <div class="success-message text-center">You have been logged out successfully.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-message text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" class="w-full space-y-4">
          <div>
            <label for="email" class="block font-semibold mb-1 text-white">Email</label>
            <input type="email" id="email" name="email" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white input-focus">
          </div>
          <div>
            <label for="password" class="block font-semibold mb-1 text-white">Password</label>
            <input type="password" id="password" name="password" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white input-focus">
          </div>
          <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">Login</button>
        </form>
        <p class="mt-6 text-center text-sm text-white">Don't have an account? <a href="register.php" class="text-indigo-400 hover:underline">Register here</a></p>
      </div>
    </div>
</body>
</html> 