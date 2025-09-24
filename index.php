<?php
// index.php - Dashboard/Homepage
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];
 
// Get unread count
$stmt = $pdo->prepare("SELECT COUNT(*) as unread FROM emails WHERE owner_id = ? AND folder = 'inbox' AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetch()['unread'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($user_name) ?> - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; color: #202124; display: flex; flex-direction: column; height: 100vh; }
        header { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 20px; font-weight: bold; color: #1a73e8; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-name { font-weight: 500; }
        .logout { color: #5f6368; text-decoration: none; padding: 5px 10px; border-radius: 4px; }
        .logout:hover { background: #f8f9fa; }
        nav { background: #ffffff; border-right: 1px solid #dadce0; width: 200px; overflow-y: auto; }
        nav ul { list-style: none; }
        nav li { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f3f4; color: #5f6368; }
        nav li:hover { background: #f8f9fa; }
        nav li.active { background: #e8f0fe; color: #1a73e8; font-weight: 500; }
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .top-bar { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 16px; display: flex; justify-content: space-between; align-items: center; }
        .compose-btn { background: #1a73e8; color: white; border: none; padding: 10px 24px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .compose-btn:hover { background: #1557b0; }
        .folder-stats { display: flex; gap: 20px; color: #5f6368; }
        .content-area { flex: 1; padding: 20px; overflow-y: auto; }
        .welcome { text-align: center; color: #5f6368; }
        @media (max-width: 768px) { nav { width: 150px; } .folder-stats { flex-direction: column; } }
    </style>
</head>
<body>
    <header>
        <div class="logo">Email</div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
            <a href="logout.php" class="logout">Sign out</a>
        </div>
    </header>
    <div style="display: flex; flex: 1;">
        <nav>
            <ul>
                <li class="active" onclick="window.location.href='index.php'">Dashboard</li>
                <li onclick="window.location.href='compose.php'">Compose</li>
                <li onclick="window.location.href='inbox.php'">Inbox (<?= $unread ?>)</li>
                <li onclick="window.location.href='sent.php'">Sent</li>
                <li onclick="window.location.href='drafts.php'">Drafts</li>
                <li onclick="window.location.href='trash.php'">Trash</li>
            </ul>
        </nav>
        <div class="main-content">
            <div class="top-bar">
                <h2>Welcome, <?= htmlspecialchars($user_name) ?>!</h2>
                <button class="compose-btn" onclick="window.location.href='compose.php'">Compose</button>
            </div>
            <div class="content-area">
                <div class="welcome">
                    <h3>Your Email Dashboard</h3>
                    <p>Manage your emails efficiently. Check Inbox for new messages.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Internal JS for navigation (simulate click for active class, but since redirect, use URL check)
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('nav li');
            links.forEach(link => {
                if (link.textContent.includes('Dashboard')) link.classList.add('active');
            });
            // Add smooth transitions if needed
        });
    </script>
</body>
</html>
