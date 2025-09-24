<?php
// compose.php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
 
// Handle POST for save draft or send
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to']);
    $subject = trim($_POST['subject']);
    $body = $_POST['body'];
    $action = $_POST['action'] ?? 'draft';
 
    if (empty($subject) && empty($body)) {
        echo "<script>alert('Subject and body cannot both be empty.'); window.location.href='compose.php';</script>";
        exit;
    }
 
    // Check if to is valid user
    if ($action === 'send' && !empty($to)) {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$to]);
        $recipient = $stmt->fetch();
        if (!$recipient) {
            echo "<script>alert('Recipient email not found.'); window.location.href='compose.php';</script>";
            exit;
        }
        $recipient_id = $recipient['id'];
        $recipient_email = $recipient['email'];
    }
 
    // Save as draft
    if ($action === 'draft') {
        $stmt = $pdo->prepare("INSERT INTO emails (owner_id, folder, from_email, to_email, subject, body) VALUES (?, 'draft', ?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_email, $to, $subject, $body]);
        echo "<script>alert('Draft saved!'); window.location.href='drafts.php';</script>";
        exit;
    }
 
    // Send: update draft or insert new for sent, insert for inbox
    // For simplicity, always insert new for sent, and for inbox
    if ($action === 'send') {
        // Insert for sender's sent
        $stmt = $pdo->prepare("INSERT INTO emails (owner_id, folder, from_email, to_email, subject, body, is_read) VALUES (?, 'sent', ?, ?, ?, ?, 1)");
        $stmt->execute([$user_id, $user_email, $recipient_email, $subject, $body]);
 
        // Insert for receiver's inbox
        $stmt = $pdo->prepare("INSERT INTO emails (owner_id, folder, from_email, to_email, subject, body, is_read) VALUES (?, 'inbox', ?, ?, ?, ?, 0)");
        $stmt->execute([$recipient_id, $user_email, $recipient_email, $subject, $body]);
 
        echo "<script>alert('Email sent!'); window.location.href='sent.php';</script>";
        exit;
    }
}
 
// If editing draft, load it - but for simplicity, new compose always, add edit later if needed
$edit_id = $_GET['edit'] ?? '';
if (!empty($edit_id)) {
    // Load draft for edit
    $stmt = $pdo->prepare("SELECT * FROM emails WHERE id = ? AND owner_id = ? AND folder = 'draft'");
    $stmt->execute([$edit_id, $user_id]);
    $draft = $stmt->fetch();
    if ($draft) {
        $to = $draft['to_email'];
        $subject = $draft['subject'];
        $body = $draft['body'];
    } else {
        echo "<script>window.location.href='compose.php';</script>";
        exit;
    }
} else {
    $to = $subject = $body = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; }
        header { background: #ffffff; border-bottom: 1px solid #dadce0; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .back { color: #5f6368; text-decoration: none; }
        .actions { display: flex; gap: 10px; }
        button { padding: 8px 16px; border: 1px solid #dadce0; background: white; border-radius: 4px; cursor: pointer; }
        .send-btn { background: #1a73e8; color: white; border-color: #1a73e8; }
        .send-btn:hover { background: #1557b0; }
        .form-container { max-width: 800px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        form { padding: 20px; }
        input[type="email"], input[type="text"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #dadce0; border-radius: 4px; font-size: 16px; }
        textarea { width: 100%; height: 300px; padding: 12px; margin-bottom: 15px; border: 1px solid #dadce0; border-radius: 4px; font-size: 14px; resize: vertical; }
        @media (max-width: 768px) { .actions { flex-direction: column; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="back">← Back to Dashboard</a>
        <div class="actions">
            <button onclick="document.querySelector('form').action.value='draft'; document.querySelector('form').submit();">Save Draft</button>
            <button class="send-btn" onclick="if(confirm('Send email?')) { document.querySelector('form').action.value='send'; document.querySelector('form').submit(); }">Send</button>
        </div>
    </header>
    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="action" value="">
            <input type="email" name="to" placeholder="To" value="<?= htmlspecialchars($to) ?>" required>
            <input type="text" name="subject" placeholder="Subject" value="<?= htmlspecialchars($subject) ?>">
            <textarea name="body" placeholder="Compose your message..."><?= htmlspecialchars($body) ?></textarea>
        </form>
    </div>
    <script>
        // Internal JS for real-time char count or validation
        const textarea = document.querySelector('textarea');
        textarea.addEventListener('input', function() {
            // Auto-resize
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        // Validate to email on blur
        document.querySelector('input[name="to"]').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                alert('Please enter a valid email.');
                this.focus();
            }
        });
    </script>
</body>
</html>
