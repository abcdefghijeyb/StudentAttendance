<?php
session_start();
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'database.php';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (First_Name, Last_Name, email, password) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Error: ' . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
        }
        .register-card {
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
      <div class="register-card bg-[#23272f] shadow-2xl rounded-3xl p-10 w-full max-w-md flex flex-col items-center">
        <div class="mb-6 flex flex-col items-center">
          <div class="bg-indigo-500 rounded-full p-3 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m15-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" /></svg>
          </div>
          <h2 class="text-3xl font-bold text-white">Register</h2>
        </div>
        <?php if ($error): ?>
            <div class="error-message text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message text-center"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST" class="w-full space-y-5">
          <div>
            <label for="first_name" class="block font-semibold mb-1 text-white">First Name</label>
            <input type="text" id="first_name" name="first_name" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <div>
            <label for="last_name" class="block font-semibold mb-1 text-white">Last Name</label>
            <input type="text" id="last_name" name="last_name" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <div>
            <label for="email" class="block font-semibold mb-1 text-white">Email</label>
            <input type="email" id="email" name="email" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <div>
            <label for="password" class="block font-semibold mb-1 text-white">Password</label>
            <input type="password" id="password" name="password" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <div>
            <label for="confirm_password" class="block font-semibold mb-1 text-white">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="w-full border border-gray-700 rounded-lg px-3 py-2 bg-gray-800 text-white">
          </div>
          <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">Register</button>
        </form>
        <p class="mt-6 text-center text-sm text-white">Already have an account? <a href="login.php" class="text-indigo-400 hover:underline">Login here</a></p>
      </div>
    </div>
</body>
</html> 