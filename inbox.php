<?php
// inbox.php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
 
// Handle actions: delete, move to trash, mark read/star
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email_id = (int)$_POST['email_id'];
 
    if ($action === 'delete') {
        $stmt = $pdo->prepare("UPDATE emails SET folder = 'trash' WHERE id = ? AND owner_id = ? AND folder = 'inbox'");
        $stmt->execute([$email_id, $user_id]);
    } elseif ($action === 'star') {
        $is_starred = $_POST['is_starred'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE emails SET is_starred = ? WHERE id = ? AND owner_id = ?");
        $stmt->execute([$is_starred, $email_id, $user_id]);
    } elseif ($action === 'read') {
        $stmt = $pdo->prepare("UPDATE emails SET is_read = 1 WHERE id = ? AND owner_id = ?");
        $stmt->execute([$email_id, $user_id]);
    }
    echo "<script>window.location.href='inbox.php';</script>";
    exit;
}
 
// Search and filters
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$where = "owner_id = ? AND folder = 'inbox'";
$params = [$user_id];
 
if (!empty($search)) {
    $where .= " AND (subject LIKE ? OR body LIKE ? OR from_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter === 'unread') {
    $where .= " AND is_read = 0";
} elseif ($filter === 'starred') {
    $where .= " AND is_starred = 1";
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
    <title>Inbox - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; flex-direction: column; height: 100vh; }
        header { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .back { color: #5f6368; text-decoration: none; }
        nav { background: #ffffff; border-right: 1px solid #dadce0; width: 200px; overflow-y: auto; }
        nav ul { list-style: none; }
        nav li { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f3f4; color: #5f6368; }
        nav li:hover, nav li.active { background: #e8f0fe; color: #1a73e8; }
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .top-bar { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .search-box { flex: 1; max-width: 400px; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #dadce0; border-radius: 4px; }
        .filters select { padding: 8px; border: 1px solid #dadce0; border-radius: 4px; }
        .compose-btn { background: #1a73e8; color: white; border: none; padding: 10px 24px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .email-list { flex: 1; overflow-y: auto; }
        .email-item { display: flex; padding: 12px 16px; border-bottom: 1px solid #f1f3f4; cursor: pointer; align-items: center; }
        .email-item:hover { background: #f8f9fa; }
        .email-item.unread { background: #f8f9fa; font-weight: 500; }
        .checkbox { margin-right: 10px; }
        .star { margin-right: 10px; color: #5f6368; cursor: pointer; }
        .starred { color: #fbbd06; }
        .sender { flex: 1; font-weight: 500; }
        .subject { flex: 2; color: #5f6368; }
        .snippet { color: #5f6368; font-size: 14px; }
        .timestamp { color: #5f6368; font-size: 12px; margin-left: auto; }
        @media (max-width: 768px) { nav { width: 150px; } .top-bar { flex-direction: column; align-items: stretch; } .email-item { flex-direction: column; align-items: flex-start; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="back">← Dashboard</a>
        <a href="compose.php" class="compose-btn">Compose</a>
    </header>
    <div style="display: flex; flex: 1;">
        <nav>
            <ul>
                <li onclick="window.location.href='inbox.php'">Inbox</li>
                <li onclick="window.location.href='sent.php'">Sent</li>
                <li onclick="window.location.href='drafts.php'">Drafts</li>
                <li onclick="window.location.href='trash.php'">Trash</li>
            </ul>
        </nav>
        <div class="main-content">
            <div class="top-bar">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="search-box">
                        <form method="GET" style="display: inline;">
                            <input type="text" name="search" placeholder="Search mail..." value="<?= htmlspecialchars($search) ?>">
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        </form>
                    </div>
                    <form method="GET" style="display: inline;">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <select name="filter" onchange="this.form.submit()">
                            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                            <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Unread</option>
                            <option value="starred" <?= $filter === 'starred' ? 'selected' : '' ?>>Starred</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="email-list">
                <?php if (empty($emails)): ?>
                    <p style="padding: 20px; text-align: center; color: #5f6368;">No emails found.</p>
                <?php else: ?>
                    <?php foreach ($emails as $email): ?>
                        <div class="email-item <?= $email['is_read'] ? '' : 'unread' ?>" onclick="window.location.href='view_email.php?id=<?= $email['id'] ?>'">
                            <input type="checkbox" class="checkbox">
                            <span class="star <?= $email['is_starred'] ? 'starred' : '' ?>" onclick="event.stopPropagation(); toggleStar(<?= $email['id'] ?>, <?= $email['is_starred'] ?>);">★</span>
                            <div class="sender"><?= htmlspecialchars($email['from_email']) ?></div>
                            <div class="subject"><?= htmlspecialchars($email['subject']) ?></div>
                            <div style="display: flex; flex: 1; justify-content: space-between;">
                                <div class="snippet"><?= htmlspecialchars(substr($email['body'], 0, 50)) ?>...</div>
                                <div class="timestamp"><?= date('M j', strtotime($email['timestamp'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Internal JS for star toggle (uses POST via fetch for simplicity, but since no AJAX lib, use form or simple)
        function toggleStar(id, current) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="star">
                             <input type="hidden" name="email_id" value="${id}">
                             <input type="hidden" name="is_starred" value="${current ? 0 : 1}">`;
            document.body.appendChild(form);
            form.submit();
        }
        // For bulk actions, etc., extend as needed
    </script>
</body>
</html>
