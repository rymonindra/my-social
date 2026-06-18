<?php
session_start();

$is_sqlmap = isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'sqlmap') !== false;

if (!isset($_SESSION['user_id']) && !$is_sqlmap) {
    header("Location: login.php");
    exit();
}

include "db.php";

if (!isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $current_logged_in_id = $_SESSION['user_id'];
    header("Location: index.php?id=" . $current_logged_in_id);
    exit();
}

// 🔍 সার্চ ফিল্টার লজিক (VULNERABLE TO SQLi)
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

$sql = "
SELECT posts.id, posts.content, posts.created_at, users.username
FROM posts
JOIN users ON posts.user_id = users.id
";

$sql_error = "";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (!empty($search_query)) {
        // VULNERABLE SQL: সার্চ বক্সে ' দিলে SQLi এরর হবে
        $sql .= " WHERE (users.id = '$id' OR '$id' = '$id') AND posts.content LIKE '%$search_query%'";
    } else {
        $sql .= " WHERE users.id = '$id' OR '$id' = '$id'";
    }
}

$sql .= " ORDER BY posts.created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    $sql_error = $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SecLab - Error-Based SQLi Environment</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 0; transition: background 0.3s ease; }
        .navbar { background: #1877f2; color: white; padding: 15px 25px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .brand { font-size: 22px; letter-spacing: 0.5px; }
        .navbar a { color: black; background: #e4e6eb; padding: 6px 12px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; }
        .container { display: flex; margin: 25px; gap: 25px; }
        .sidebar { width: 25%; background: white; padding: 20px; border-radius: 12px; height: fit-content; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .feed { width: 75%; }
        .post { background: white; padding: 20px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .user { font-weight: bold; color: #1877f2; font-size: 16px; }
        .time { font-size: 12px; color: #65676b; margin-top: 2px; }
        textarea { width: 100%; height: 80px; border: 1px solid #ced4da; border-radius: 8px; padding: 12px; box-sizing: border-box; resize: none; font-size: 14px; margin-bottom: 10px; }
        button { background: #1877f2; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 14px; transition: background 0.2s; }
        button:hover { background: #1565c0; }

        /* 🔍 সার্চ বক্স স্টাইল */
        .search-box { background: white; padding: 15px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; gap: 10px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }

        /* 💬 কমেন্ট সেকশন স্টাইল */
        .comment-section { margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; }
        .comment-list { background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 14px; }
        .comment-user { font-weight: bold; color: #333; }
        .comment-form { display: flex; gap: 10px; margin-top: 10px; }
        .comment-form input { flex: 1; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 20px; font-size: 13px; }
        .comment-form button { padding: 6px 15px; font-size: 12px; border-radius: 20px; background: #42b72a; }

        <?php if (!empty($sql_error)): ?>
        body { background: #0c0f12; }
        .navbar { background: #1a1f26; border-bottom: 2px solid #ef5350; }
        .sidebar, .post, .search-box, .comment-list { background: #151b22; color: #c9d1d9; border: 1px solid #21262d; box-shadow: none; }
        .comment-list { background: #1c2128; }
        .sidebar h3 { color: #f0f6fc; }
        .user { color: #58a6ff; }
        textarea, .search-box input, .comment-form input { background: #0d1117; color: #c9d1d9; border: 1px solid #30363d; }
        <?php endif; ?>

        .error-dashboard { background: #1a0f11; border-left: 5px solid #f44336; border-radius: 8px; padding: 20px; margin-bottom: 25px; color: #ffcdd2; font-family: 'Courier New', Courier, monospace; }
        .error-header { display: flex; align-items: center; font-size: 16px; font-weight: bold; color: #ef5350; margin-bottom: 10px; }
        .error-body { background: #12080a; padding: 15px; border-radius: 6px; border: 1px solid #4a1518; font-size: 14px; white-space: pre-wrap; word-break: break-all; }
        .query-debug { margin-top: 10px; color: #8b949e; font-size: 12px; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand">MySocial <span style="font-size:12px; font-weight:normal; color:#ddd;">(Lab Mode)</span></div>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <div class="sidebar">
        <h3>Profile</h3>
        <p>Welcome, <strong><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'LabUser' ?></strong>!</p>
    </div>

    <div class="feed">
        <!-- SQL Injection এরর ড্যাশবোর্ড -->
        <?php if (!empty($sql_error)): ?>
            <div class="error-dashboard">
                <div class="error-header"><span>⚠️</span> SQL INJECTION ERROR DETECTED</div>
                <div class="error-body"><?= $sql_error ?></div>
                <div class="query-debug"><strong>Executed Query:</strong> <?= htmlspecialchars($sql) ?></div>
            </div>
        <?php endif; ?>

        <!-- 🔍 সার্চ বক্স (নন-ভ্যালনারেবল ইন্টারফেস, ব্যাকএন্ড ভ্যালনারেবল) -->
        <div class="search-box">
            <form method="GET" style="display:flex; width:100%; gap:10px;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">
                <input type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit">Search</button>
            </form>
        </div>
               <!-- 🎯 REFLECTED XSS VULNERABILITY (এই অংশটুকু নতুন যোগ করবেন) -->
<?php if (!empty($search_query)): ?>
    <div style="margin-bottom: 20px; font-style: italic; color: #555;">
        <!-- VULNERABLE: htmlspecialchars ছাড়া সরাসরি ইউজার ইনপুট স্ক্রিনে প্রিন্ট করা হচ্ছে -->
        Showing results for: <?= $search_query ?>
    </div>
<?php endif; ?>
        
        
 

        <!-- Create Post -->
        <div class="post">
            <form action="post.php" method="POST">
                <textarea name="content" placeholder="What's on your mind?" required></textarea>
                <button type="submit">Post</button>
            </form>
        </div>

        <!-- Posts Display -->
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="post">
                    <div class="user"><?= $row['username'] ?></div>
                    <div class="time"><?= $row['created_at'] ?></div>
                    <p style="margin-top:10px; line-height:1.5;"><?= $row['content'] ?></p> 

                    <!-- 💬 কমেন্ট সেকশন -->
                    <div class="comment-section">
                        <!-- কমেন্ট দেখানোর লজিক -->
                        <?php
                        $post_id = $row['id'];
                        // কমেন্ট লোড করার কুয়েরি
                        $comment_sql = "SELECT comments.comment_text, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = $post_id ORDER BY comments.created_at ASC";
                        $comment_result = $conn->query($comment_sql);
                        if ($comment_result && $comment_result->num_rows > 0) {
                            while ($c_row = $comment_result->fetch_assoc()) {
                                echo "<div class='comment-list'>";
                                echo "<span class='comment-user'>" . $c_row['username'] . ": </span>";
                                // VULNERABLE TO XSS: কমেন্ট টেক্সট সরাসরি প্রিন্ট হচ্ছে
                                echo $c_row['comment_text'];
                                echo "</div>";
                            }
                        }
                        ?>
                        
                        <!-- কমেন্ট সাবমিট করার ফর্ম -->
                        <form action="post.php" method="POST" class="comment-form">
                            <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                            <input type="text" name="comment_text" placeholder="Write a comment..." required>
                            <button type="submit" name="submit_comment">Comment</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php elseif (empty($sql_error)): ?>
            <div class="post" style="text-align: center; color: #65676b;">No posts found.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
