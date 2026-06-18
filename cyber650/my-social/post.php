<?php
// ১. সেশন এবং এরর রিপোর্টিং অন করা (যেন কোনো এরর হাইড না থাকে)
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ২. sqlmap বা স্বয়ংক্রিয় স্ক্রিপ্ট বাইপাস চেকার
$is_sqlmap = isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'sqlmap') !== false;

if (!isset($_SESSION['user_id']) && !$is_sqlmap) {
    header("Location: login.php");
    exit();
}

include "db.php";

// ৩. মেইন প্রসেসিং ব্লক (Try-Catch এর ভেতরে রাখা হয়েছে যেন পেজ ক্র্যাশ না করে)
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

        // --- কন্ডিশন ১: কমেন্ট সাবমিট হ্যান্ডলার ---
        if (isset($_POST['submit_comment'])) {
            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
            $comment_text = isset($_POST['comment_text']) ? $_POST['comment_text'] : '';

            // VULNERABLE TO SQL INJECTION & STORED XSS
            $sql = "INSERT INTO comments (post_id, user_id, comment_text) VALUES ($post_id, $user_id, '$comment_text')";
            
            if ($conn->query($sql)) {
                header("Location: index.php");
                exit();
            } else {
                throw new Exception("Database Query Error: " . $conn->error);
            }
        }

        // --- কন্ডিশন ২: XML External Entity (XXE) হ্যান্ডলার ---
        elseif (isset($_POST['import_xml'])) {
            $xml_data = isset($_POST['xml_data']) ? $_POST['xml_data'] : '';
            
            if (empty($xml_data)) {
                throw new Exception("XML data is empty!");
            }

            $dom = new DOMDocument();
            // VULNERABLE TO XXE
            $dom->loadXML($xml_data, LIBXML_NOENT | LIBXML_DTDLOAD);
            $xml = simplexml_import_dom($dom);
            $bio = isset($xml->bio) ? (string)$xml->bio : "No bio found in XML";
            
            echo "
            <div style='font-family: Arial; background: #f0f2f5; height: 100vh; display: flex; justify-content: center; align-items: center; margin:0;'>
                <div style='background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px;'>
                    <h3 style='color: #1877f2; margin-top:0;'>XML Imported Successfully!</h3>
                    <p style='font-weight:bold; color:#555;'>Parsed Bio:</p>
                    <pre style='background: #f0f2f5; padding: 10px; border-radius: 6px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word;'>".htmlspecialchars($bio)."</pre>
                    <br>
                    <a href='index.php' style='display: inline-block; background: #1877f2; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold;'>Go Back Home</a>
                </div>
            </div>";
            exit();
        }

        // --- কন্ডিশন ৩: সাধারণ টেক্সট পোস্ট হ্যান্ডলার ---
        else {
            $content = isset($_POST['content']) ? $_POST['content'] : '';

            // VULNERABLE TO SQL INJECTION
            $sql = "INSERT INTO posts (user_id, content) VALUES ($user_id, '$content')";
            
            if ($conn->query($sql)) {
                header("Location: index.php");
                exit();
            } else {
                throw new Exception("Database Query Error: " . $conn->error);
            }
        }
    } else {
        // যদি কেউ সরাসরি post.php লিংকে ব্রাউজারে ঢুকে পড়ে
        header("Location: index.php");
        exit();
    }

} catch (Throwable $e) {
    // 💡 যদি কোডে কোনো ভুলও থাকে, ৫০০ এরর না দেখিয়ে স্ক্রিনে আসল সমস্যা প্রিন্ট করবে
    echo "<div style='font-family: monospace; background: #ffebe9; color: #b71c1c; padding: 20px; margin: 20px; border-radius: 8px; border: 1px solid #ffcdd2;'>";
    echo "<h3>[Lab Debugger] PHP Execution Error Caught:</h3>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "<br>";
    echo "<br><a href='index.php'>Go Back to Home</a>";
    echo "</div>";
    exit();
}
?>
