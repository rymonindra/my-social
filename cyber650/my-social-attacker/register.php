<?php
include "db.php";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    if (!empty($username) && !empty($password)) {
        $check_sql = "SELECT id FROM users WHERE username = '$username'";
        $result = $conn->query($check_sql);

        if ($result && $result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
            if ($conn->query($sql)) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "SQL Error: " . $conn->error;
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Social Media Register</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; }
        h2 { color: #1877f2; margin-top: 0; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: #1877f2; outline: none; }
        button { width: 100%; background: #42b72a; color: white; border: none; padding: 12px; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 16px; margin-top: 10px; }
        button:hover { background: #36a420; }
        .error { color: #f02849; font-size: 14px; word-break: break-all; }
        .success { color: #42b72a; font-size: 14px; font-weight: bold; }
        p { font-size: 14px; margin-top: 20px; text-align: center; color: #606770; }
        a { color: #1877f2; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-box">
    <h2>Register</h2>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <?php if($success) echo "<p class='success'>$success</p>"; ?>
    <form method="POST" autocomplete="off">
    <input type="text"
           name="username"
           placeholder="Username"
           required>

    <input type="password"
           name="password"
           placeholder="Password"
           autocomplete="new-password"
           required>

    <button type="submit">Sign Up</button>
</form>



    
   
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
