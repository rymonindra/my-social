<?php

session_start();
include "db.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // SQL Injection Vulnerable Query
        $sql = "SELECT id, username FROM users WHERE username = '$username' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid Login. SQL Error: " . $conn->error;
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Social Media Login</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .welcome-text { font-size: 28px; font-weight: bold; color: #1877f2; margin-bottom: 20px; }
        .auth-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; }
        h2 { color: #1877f2; margin-top: 0; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: #1877f2; outline: none; }
        button { width: 100%; background: #1877f2; color: white; border: none; padding: 12px; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 16px; margin-top: 10px; }
        button:hover { background: #166fe5; }
        .error { color: #f02849; font-size: 14px; word-break: break-all; margin-top: 10px; }
        p { font-size: 14px; margin-top: 20px; text-align: center; color: #606770; }
        a { color: #1877f2; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="welcome-text">Welcome to MySocial</div>

<div class="auth-box">
    <h2>Login</h2>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" autocomplete="off">
        <input type="text"
               name="username"
               placeholder="Username"
               required>

        <input type="password"
               name="password"
               placeholder="Password"
               autocomplete="current-password"
               required>

        <button type="submit">Log In</button>
    </form>

    
   
    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
