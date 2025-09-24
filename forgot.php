<?php
// forgot.php
session_start();
include 'db.php';
$error = $message = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
 
    if (empty($email)) {
        $error = 'Please enter your email.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
 
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            $reset_link = "http://yourdomain.com/reset.php?token=" . $token; // Replace with actual domain
            $message = "Reset token generated. For demo: Use this link - <a href='reset.php?token=$token'>Reset Password</a>. In production, send via email.";
        } else {
            $message = "If email exists, reset instructions sent. (Demo mode: No email sent.)";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .forgot-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 30px; color: #202124; }
        input[type="email"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #dadce0; border-radius: 4px; font-size: 16px; }
        input:focus { outline: none; border-color: #1a73e8; }
        button { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1557b0; }
        .error { color: #d93025; text-align: center; margin-top: 10px; }
        .message { color: #137333; text-align: center; margin-top: 10px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #1a73e8; text-decoration: none; }
        @media (max-width: 480px) { .forgot-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="forgot-container">
        <h2>Reset password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <button type="submit">Send reset link</button>
        </form>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($message): ?><p class="message"><?= $message ?></p><?php endif; ?>
        <div class="links">
            <a href="login.php">Back to sign in</a>
        </div>
    </div>
    <script>
        // Internal JS
        console.log('Forgot password form ready.');
    </script>
</body>
</html>
