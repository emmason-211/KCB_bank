<?php
require_once __DIR__ . '/db.php';

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}
function require_role(array $roles): void {
    require_login();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Access denied: your account does not have permission to perform this action.');
    }
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token. Please go back, refresh the page, and try again.');
    }
}
function money($amount): string {
    return number_format((float)$amount, 2) . ' SSP';
}
function header_view(string $title): void {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $role = $_SESSION['role'] ?? '';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' | KCB Bank Juba</title><link rel="stylesheet" href="styles.css"></head><body>';
    echo '<aside class="sidebar"><a class="brand" href="dashboard.php"><span class="brand-icon">K</span><span><b>KCB BANK</b><small>JUBA · SOUTH SUDAN</small></span></a>';
    echo '<nav><a href="dashboard.php">▦ <span>Dashboard</span></a>';
    if (in_array($role, ['admin','employee'], true)) {
        echo '<a href="customers.php">♙ <span>Customers</span></a><a href="accounts.php">▣ <span>Bank accounts</span></a>';
        echo '<a href="add_account.php">＋ <span>Open account</span></a>';
    } else {
        echo '<a href="accounts.php">▣ <span>My account</span></a>';
    }
    echo '<a href="deposit.php">↓ <span>Deposit</span></a><a href="withdraw.php">↑ <span>Withdraw</span></a><a href="transfer.php">⇄ <span>Transfer</span></a><a href="transactions.php">☷ <span>Transactions</span></a>';
    if ($role === 'admin') echo '<a href="employees.php">♧ <span>Employees</span></a>';
    echo '</nav><div class="sidebar-bottom"><div class="user-chip"><span class="avatar">' . e(strtoupper(substr($_SESSION['name'] ?? 'U',0,1))) . '</span><span><b>' . e($_SESSION['name'] ?? 'User') . '</b><small>' . e(ucfirst($role)) . '</small></span></div><a class="logout" href="logout.php">↪ Sign out</a></div></aside>';
    echo '<main class="main"><header class="topbar"><div><small class="eyebrow">KCB BANKING PORTAL</small><h1>' . e($title) . '</h1></div><div class="topbar-right"><span class="secure-dot"></span> Secure session</div></header>';
    if ($flash) echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}
function footer_view(): void {
    echo '<footer>© ' . date('Y') . ' KCB Bank Juba · Training/demo application. Do not use for real customer funds.</footer></main></body></html>';
}
function user_account(PDO $pdo, int $userId): ?array {
    $q = $pdo->prepare("SELECT * FROM accounts WHERE user_id = ? AND status = 'active' ORDER BY id LIMIT 1");
    $q->execute([$userId]);
    $row = $q->fetch();
    return $row ?: null;
}
function can_access_account(PDO $pdo, int $accountId): bool {
    if (($_SESSION['role'] ?? '') !== 'customer') return true;
    $q = $pdo->prepare('SELECT id FROM accounts WHERE id=? AND user_id=?');
    $q->execute([$accountId, $_SESSION['user_id']]);
    return (bool)$q->fetch();
}
function post_transaction(PDO $pdo, int $accountId, string $type, float $amount, string $description, ?int $relatedAccount = null): void {
    $q = $pdo->prepare('INSERT INTO transactions (account_id, related_account_id, type, amount, description, performed_by) VALUES (?,?,?,?,?,?)');
    $q->execute([$accountId, $relatedAccount, $type, $amount, $description, $_SESSION['user_id']]);
}
