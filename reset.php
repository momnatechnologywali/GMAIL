<?php
// reset.php
session_start();
include 'db.php';
$error = $success = '';
 
$token = $_GET['token'] ?? '';
if (empty($token)) {
    $error = 'Invalid or missing token.';
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
 
    if (empty($password) || empty($confirm)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
 
        if ($user) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
            if ($stmt->execute([$hashed, $user['id']])) {
                $success = 'Password reset successfully! Redirecting to login...';
                echo "<script>setTimeout(() => { window.location.href='login.php'; }, 2000);</script>";
            } else {
                $error = 'Reset failed. Try again.';
            }
        } else {
            $error = 'Invalid or expired token.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .reset-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 30px; color: #202124; }
        input[type="password"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #dadce0; border-radius: 4px; font-size: 16px; }
        input:focus { outline: none; border-color: #1a73e8; }
        button { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1557b0; }
        .error { color: #d93025; text-align: center; margin-top: 10px; }
        .success { color: #137333; text-align: center; margin-top: 10px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #1a73e8; text-decoration: none; }
        @media (max-width: 480px) { .reset-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset password</h2>
        <?php if ($error && empty($_POST['password'])): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="New password" required>
            <input type="password" name="confirm" placeholder="Confirm password" required>
            <button type="submit">Reset password</button>
        </form>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <div class="links">
            <a href="login.php">Back to sign in</a>
        </div>
    </div>
    <script>
        // Internal JS
        document.querySelector('form').addEventListener('submit', function(e) {
            const pass1 = document.querySelector('input[name="password"]').value;
            const pass2 = document.querySelector('input[name="confirm"]').value;
            if (pass1 !== pass2) {
                e.preventDefault();
                alert('Passwords do not match.');
            } else if (pass1.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters.');
            }
        });
    </script>
</body>
</html>
