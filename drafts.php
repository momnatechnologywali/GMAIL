<?php
// drafts.php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
 
// Handle delete draft
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $email_id = (int)$_POST['email_id'];
    $stmt = $pdo->prepare("DELETE FROM emails WHERE id = ? AND owner_id = ? AND folder = 'draft'");
    $stmt->execute([$email_id, $user_id]);
    echo "<script>window.location.href='drafts.php';</script>";
    exit;
}
 
$search = $_GET['search'] ?? '';
$where = "owner_id = ? AND folder = 'draft'";
$params = [$user_id];
if (!empty($search)) {
    $where .= " AND (subject LIKE ? OR body LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
 
$stmt = $pdo->prepare("SELECT * FROM emails WHERE $where ORDER BY timestamp DESC");
$stmt->execute($params);
$emails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drafts - Email Platform</title>
    <style>
        /* Same styles as previous */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; flex-direction: column; height: 100vh; }
        header { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .back { color: #5f6368; text-decoration: none; }
        nav { background: #ffffff; border-right: 1px solid #dadce0; width: 200px; overflow-y: auto; }
        nav ul { list-style: none; }
        nav li { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f3f4; color: #5f6368; }
        nav li:hover, nav li.active { background: #e8f0fe; color: #1a73e8; }
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .top-bar { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 16px; display: flex; justify-content: space-between; align-items: center; }
        .search-box input[type="text"] { width: 100%; max-width: 300px; padding: 8px; border: 1px solid #dadce0; border-radius: 4px; }
        .compose-btn { background: #1a73e8; color: white; border: none; padding: 10px 24px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .email-list { flex: 1; overflow-y: auto; }
        .email-item { display: flex; padding: 12px 16px; border-bottom: 1px solid #f1f3f4; cursor: pointer; align-items: center; }
        .email-item:hover { background: #f8f9fa; }
        .recipient { flex: 1; font-weight: 500; }
        .subject { flex: 2; color: #5f6368; }
        .snippet { color: #5f6368; font-size: 14px; }
        .timestamp { color: #5f6368; font-size: 12px; margin-left: auto; }
        .delete-btn { background: #d93025; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        @media (max-width: 768px) { nav { width: 150px; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="back">← Dashboard</a>
        <a href="compose.php" class="compose-btn">New Draft</a>
    </header>
    <div style="display: flex; flex: 1;">
        <nav>
            <ul>
                <li onclick="window.location.href='inbox.php'">Inbox</li>
                <li onclick="window.location.href='sent.php'">Sent</li>
                <li class="active" onclick="window.location.href='drafts.php'">Drafts</li>
                <li onclick="window.location.href='trash.php'">Trash</li>
            </ul>
        </nav>
        <div class="main-content">
            <div class="top-bar">
                <form method="GET" style="display: inline; width: 100%; max-width: 300px;">
                    <input type="text" name="search" placeholder="Search drafts..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
            <div class="email-list">
                <?php if (empty($emails)): ?>
                    <p style="padding: 20px; text-align: center; color: #5f6368;">No drafts.</p>
                <?php else: ?>
                    <?php foreach ($emails as $email): ?>
                        <div class="email-item">
                            <div style="flex: 1;">
                                <div class="recipient">To: <?= htmlspecialchars($email['to_email']) ?></div>
                                <div class="subject"><?= htmlspecialchars($email['subject']) ?></div>
                                <div style="display: flex; justify-content: space-between;">
                                    <div class="snippet"><?= htmlspecialchars(substr($email['body'], 0, 50)) ?>...</div>
                                    <div class="timestamp"><?= date('M j', strtotime($email['timestamp'])) ?></div>
                                </div>
                            </div>
                            <div>
                                <a href="compose.php?edit=<?= $email['id'] ?>" style="margin-right: 10px; color: #1a73e8; text-decoration: none;">Edit</a>
                                <button class="delete-btn" onclick="deleteDraft(<?= $email['id'] ?>)">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function deleteDraft(id) {
            if (confirm('Delete this draft?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="delete">
                                 <input type="hidden" name="email_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
