<?php
// signup.php
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
include 'db.php';
$error = $success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed])) {
                $success = 'Account created! Redirecting to login...';
                echo "<script>setTimeout(() => { window.location.href='login.php'; }, 2000);</script>";
            } else {
                $error = 'Signup failed. Try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Email Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f3f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .signup-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 30px; color: #202124; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #dadce0; border-radius: 4px; font-size: 16px; }
        input:focus { outline: none; border-color: #1a73e8; }
        button { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1557b0; }
        .error { color: #d93025; text-align: center; margin-top: 10px; }
        .success { color: #137333; text-align: center; margin-top: 10px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #1a73e8; text-decoration: none; }
        @media (max-width: 480px) { .signup-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create account</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create account</button>
        </form>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <div class="links">
            <a href="login.php">Already have an account? Sign in</a>
        </div>
    </div>
    <script>
        // Internal JS
        document.querySelector('form').addEventListener('submit', function(e) {
            const pass = document.querySelector('input[type="password"]').value;
            if (pass.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters.');
            }
        });
    </script>
</body>
</html>
