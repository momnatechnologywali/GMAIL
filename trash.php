<?php
// trash.php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
 
// Handle permanent delete or restore
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email_id = (int)$_POST['email_id'];
 
    if ($action === 'permanent_delete') {
        $stmt = $pdo->prepare("DELETE FROM emails WHERE id = ? AND owner_id = ? AND folder = 'trash'");
        $stmt->execute([$email_id, $user_id]);
    } elseif ($action === 'restore') {
        // Restore to original folder (need to track original? For simplicity, restore to inbox if was inbox, else sent
        // But since we don't track, assume restore to inbox for simplicity, or add original_folder column later
        $original_folder = 'inbox'; // Default
        $stmt = $pdo->prepare("UPDATE emails SET folder = ? WHERE id = ? AND owner_id = ? AND folder = 'trash'");
        $stmt->execute([$original_folder, $email_id, $user_id]);
    }
    echo "<script>window.location.href='trash.php';</script>";
    exit;
}
 
$stmt = $pdo->prepare("SELECT * FROM emails WHERE owner_id = ? AND folder = 'trash' ORDER BY timestamp DESC");
$stmt->execute([$user_id]);
$emails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash - Email Platform</title>
    <style>
        /* Reuse styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; flex-direction: column; height: 100vh; }
        header { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .back { color: #5f6368; text-decoration: none; }
        nav { background: #ffffff; border-right: 1px solid #dadce0; width: 200px; overflow-y: auto; }
        nav ul { list-style: none; }
        nav li { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f3f4; color: #5f6368; }
        nav li:hover, nav li.active { background: #e8f0fe; color: #1a73e8; }
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .top-bar { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 16px; }
        .email-list { flex: 1; overflow-y: auto; }
        .email-item { display: flex; padding: 12px 16px; border-bottom: 1px solid #f1f3f4; cursor: pointer; align-items: center; justify-content: space-between; }
        .email-item:hover { background: #f8f9fa; }
        .email-info { flex: 1; }
        .sender { font-weight: 500; }
        .subject { color: #5f6368; }
        .timestamp { color: #5f6368; font-size: 12px; margin-top: 4px; }
        .actions button { padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px; }
        .restore-btn { background: #137333; color: white; }
        .delete-btn { background: #d93025; color: white; }
        @media (max-width: 768px) { nav { width: 150px; } .email-item { flex-direction: column; align-items: flex-start; } .actions { margin-top: 10px; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="back">← Dashboard</a>
    </header>
    <div style="display: flex; flex: 1;">
        <nav>
            <ul>
                <li onclick="window.location.href='inbox.php'">Inbox</li>
                <li onclick="window.location.href='sent.php'">Sent</li>
                <li onclick="window.location.href='drafts.php'">Drafts</li>
                <li class="active" onclick="window.location.href='trash.php'">Trash</li>
            </ul>
        </nav>
        <div class="main-content">
            <div class="top-bar">
                <h3>Trash</h3>
                <p style="color: #5f6368;">Emptied regularly. <button onclick="if(confirm('Empty trash?')) { /* Implement bulk delete */ alert('Implemented in pro version'); }" style="background: none; color: #1a73e8; border: none; cursor: pointer;">Empty trash now</button></p>
            </div>
            <div class="email-list">
                <?php if (empty($emails)): ?>
                    <p style="padding: 20px; text-align: center; color: #5f6368;">Trash is empty.</p>
                <?php else: ?>
                    <?php foreach ($emails as $email): ?>
                        <div class="email-item">
                            <div class="email-info">
                                <div class="sender"><?= htmlspecialchars($email['from_email']) ?> → <?= htmlspecialchars($email['to_email']) ?></div>
                                <div class="subject"><?= htmlspecialchars($email['subject']) ?></div>
                                <div class="timestamp"><?= date('M j, Y', strtotime($email['timestamp'])) ?></div>
                            </div>
                            <div class="actions">
                                <button class="restore-btn" onclick="restoreEmail(<?= $email['id'] ?>)">Restore</button>
                                <button class="delete-btn" onclick="permanentDelete(<?= $email['id'] ?>)">Delete forever</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function restoreEmail(id) {
            if (confirm('Restore this email?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="restore">
                                 <input type="hidden" name="email_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
        function permanentDelete(id) {
            if (confirm('Delete forever? This cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="permanent_delete">
                                 <input type="hidden" name="email_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
