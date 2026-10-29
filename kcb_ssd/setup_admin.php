<?php
require_once 'db.php';
$email = 'admin@kcbjuba.test';
$password = 'ChangeMe123!';
$name = 'KCB System Administrator';
$stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
$stmt->execute([$email]);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,'admin')");
    $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
    echo 'Demo administrator created.<br>Email: ' . htmlspecialchars($email) . '<br>Password: ' . htmlspecialchars($password);
} else {
    echo 'Administrator already exists. If you need to reset it, update the password_hash using password_hash() from a secure PHP script.';
}
echo '<p><strong>Security:</strong> Delete setup_admin.php immediately after creating the admin user.</p>';
