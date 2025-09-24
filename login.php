<?php
// login.php
session_start();
include 'db.php';
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
 
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            echo "<script>window.location.href='index.php';</script>";
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 30px; color: #202124; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #dadce0; border-radius: 4px; font-size: 16px; }
        input[type="email"]:focus, input[type="password"]:focus { outline: none; border-color: #1a73e8; }
        button { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1557b0; }
        .error { color: #d93025; text-align: center; margin-top: 10px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #1a73e8; text-decoration: none; margin: 0 10px; }
        @media (max-width: 480px) { .login-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Sign in</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($error ? $_POST['email'] ?? '' : '') ?>">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign in</button>
        </form>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <div class="links">
            <a href="signup.php">Create account</a>
            <a href="forgot.php">Forgot password?</a>
        </div>
    </div>
    <script>
        // Internal JS for any client-side validation if needed
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.querySelector('input[type="email"]').value;
            const pass = document.querySelector('input[type="password"]').value;
            if (!email || !pass) {
                e.preventDefault();
                alert('Please fill in all fields.');
            }
        });
    </script>
</body>
</html>
