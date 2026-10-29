<?php
require_once 'functions.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid login details or inactive account.';
}
?>
<!doctype html>
  <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign in | KCB Bank Juba</title>
        <link rel="stylesheet" href="styles.css">
    </head>
 <body class="login-page">
    <div class="login-art">
        <div class="login-brand">
            <span class="brand-icon">K</span>
            <div>
                <b>KCB BANK</b>
                <small>JUBA · SOUTH SUDAN</small>
            </div>
        </div>
    <div class="hero-copy">
        <span class="eyebrow light">BANKING MADE SIMPLE</span>
        <h1>Your money.<br>Managed with confidence.</h1>
        <p>Access your account, review transactions,
             and manage your banking securely.
        </p>
        <div class="hero-pills">
            <span>● Secure access</span>
            <span>● Account overview</span>
            <span>● Easy transfers</span>
        </div>
    </div>
    <div class="login-foot">KCB Bank Juba · Banking Management Portal</div>
</div>
<div class="login-panel">
    <form class="login-card" method="post">

    <div class="mini-mark">K</div>
    <span class="eyebrow">WELCOME BACK</span>
    <h2>Sign in to your account</h2>
    <p class="muted">Enter your credentials to continue.</p>
    <?php if ($error): ?><div class="alert error"><?=e($error)?>
 </div><?php endif; ?>

<input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
<label>Email address</label>
<input type="email" name="email" required autocomplete="username" placeholder="you@example.com">
<label>Password</label>
<input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
<button class="btn primary full" type="submit">Sign in securely →</button>
<p class="login-note">Demo project only. Ask your administrator for an account.</p>
</form>
</div>
</body>
</html>
