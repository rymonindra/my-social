<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content']; // No sanitization

    // Vulnerable Insert Query
    $sql = "INSERT INTO posts (user_id, content) VALUES ($user_id, '$content')";
    
    if ($conn->query($sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "SQL Error: " . $conn->error;
    }
}
?>
