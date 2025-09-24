<?php
// view_email.php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$email_id = (int)$_GET['id'];
 
$stmt = $pdo->prepare("SELECT * FROM emails WHERE id = ? AND owner_id = ?");
$stmt->execute([$email_id, $user_id]);
$email = $stmt->fetch();
 
if (!$email) {
    echo "<script>window.location.href='inbox.php';</script>";
    exit;
}
 
// Mark as read if inbox
if ($email['folder'] === 'inbox' && !$email['is_read']) {
    $stmt = $pdo->prepare("UPDATE emails SET is_read = 1 WHERE id = ?");
    $stmt->execute([$email_id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($email['subject']) ?> - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; }
        header { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .back { color: #5f6368; text-decoration: none; }
        .email-container { max-width: 800px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .header { padding: 20px; border-bottom: 1px solid #dadce0; }
        .subject { font-size: 24px; font-weight: 400; margin-bottom: 5px; }
        .from-to { color: #5f6368; margin-bottom: 10px; }
        .timestamp { color: #5f6368; font-size: 14px; }
        .body { padding: 20px; line-height: 1.5; white-space: pre-wrap; }
        .actions { padding: 0 20px 20px; display: flex; gap: 10px; }
        button { padding: 8px 16px; border: 1px solid #dadce0; background: white; border-radius: 4px; cursor: pointer; }
        .reply-btn { background: #1a73e8; color: white; border-color: #1a73e8; }
        @media (max-width: 768px) { .email-container { margin: 10px; } }
    </style>
</head>
<body>
    <header>
        <a href="inbox.php" class="back">← Back to Inbox</a>
        <div>
            <a href="compose.php" style="margin-right: 10px; color: #1a73e8;">Reply</a> <!-- Simplified, no prefill -->
            <a href="javascript:void(0)" onclick="deleteEmail(<?= $email['id'] ?>)" style="color: #d93025;">Delete</a>
        </div>
    </header>
    <div class="email-container">
        <div class="header">
            <div class="subject"><?= htmlspecialchars($email['subject']) ?></div>
            <div class="from-to">
                <?= $email['folder'] === 'inbox' ? 'From: ' : 'To: ' ?><?= htmlspecialchars($email['from_email']) ?>
                <?= $email['folder'] === 'inbox' ? 'To: ' . htmlspecialchars($_SESSION['user_email']) : 'From: ' . htmlspecialchars($_SESSION['user_email']) ?>
            </div>
            <div class="timestamp"><?= date('M j, Y g:i A', strtotime($email['timestamp'])) ?></div>
        </div>
        <div class="body"><?= nl2br(htmlspecialchars($email['body'])) ?></div>
        <div class="actions">
            <button class="reply-btn" onclick="window.location.href='compose.php'">Reply</button>
            <button onclick="window.location.href='inbox.php'">Back</button>
        </div>
    </div>
    <script>
        function deleteEmail(id) {
            if (confirm('Move to trash?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'inbox.php'; // Or dynamic based on folder
                form.innerHTML = `<input type="hidden" name="action" value="delete">
                                 <input type="hidden" name="email_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
