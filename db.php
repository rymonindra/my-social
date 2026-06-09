<?php
$host = "localhost";
$user = "mintu";
$pass = "mintu123";
$db   = "social_media";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

?>
