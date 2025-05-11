<?php
session_start();
// If user confirms logout, destroy session and redirect
if (isset($_GET['confirm']) && $_GET['confirm'] === '1') {
    session_unset();
    session_destroy();
    header('Location: login.php?logout=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script>
      // Show confirmation dialog
      window.onload = function() {
        if (confirm('Are you sure you want to logout?')) {
          window.location.href = 'logout.php?confirm=1';
        } else {
          window.history.back();
        }
      }
    </script>
</head>
<body style="background: #18181b;"></body>
</html>