<?php
require_once 'functions.php'; require_login();
$role = $_SESSION['role']; $uid = (int)$_SESSION['user_id'];
if ($role === 'customer') {
    $stmt = $pdo->prepare("SELECT COUNT(*) customers, COALESCE(SUM(balance),0) balance FROM accounts WHERE user_id=? AND status='active'");
    $stmt->execute([$uid]); $stats = $stmt->fetch();
    $t = $pdo->prepare("SELECT t.*, a.account_number FROM transactions t JOIN accounts a ON a.id=t.account_id WHERE a.user_id=? ORDER BY t.created_at DESC LIMIT 8"); $t->execute([$uid]);
} else {
    $stats = $pdo->query("SELECT (SELECT COUNT(*)
    FROM users WHERE role='customer' AND status='active') customers,
    (SELECT COUNT(*) FROM accounts WHERE status='active') accounts, (SELECT COALESCE(SUM(balance),0)
    FROM accounts WHERE status='active') balance, (SELECT COUNT(*) FROM transactions WHERE created_at >= CURDATE()) today_transactions")->fetch();
    $t = $pdo->query("SELECT t.*, a.account_number, u.full_name FROM transactions t
    JOIN accounts a ON a.id=t.account_id
    JOIN users u ON u.id=a.user_id
    ORDER BY t.created_at
    DESC LIMIT 8");
}
header_view('Dashboard');
?>
<div class="welcome"><div>
    <span class="eyebrow light">YOUR FINANCIAL OVERVIEW</span>

       <h2>welcome, <?=e($_SESSION['name'])?> 👋</h2>
          <p>Here is what is happening with your banking today.</p>
</div>

     <div class="welcome-icon">KCB</div>
</div>
 <div class="stats-grid">
 <div class="stat-card">
    <div class="stat-icon blue">▣</div>
<span class="stat-label"><?=$role==='customer'?'Available balance':'Active accounts'?></span>
   <strong><?=$role==='customer'?money($stats['balance']):number_format((int)$stats['accounts'])?>
</strong>

    <small><?=$role==='customer'?'Across your active accounts':'Accounts currently active'?></small>

</div>
<?php if ($role !== 'customer'): ?><div class="stat-card"><div class="stat-icon green">♙</div><span class="stat-label">Active customers</span><strong><?=number_format((int)$stats['customers'])?></strong><small>Registered customer profiles</small></div><div class="stat-card"><div class="stat-icon gold">⇄</div><span class="stat-label">Transactions today</span><strong><?=number_format((int)$stats['today_transactions'])?></strong><small>All recorded transaction entries</small></div><?php else: ?><div class="stat-card"><div class="stat-icon green">⇄</div><span class="stat-label">Quick transfer</span><strong>Easy & fast</strong><small>Transfer to another active account</small><a class="text-link" href="transfer.php">Make a transfer →</a></div><?php endif; ?>
<div class="stat-card"><div class="stat-icon purple">↕</div>
<span class="stat-label">Banking actions</span>
<strong>24/7 portal</strong><small>Review and manage activity</small>
<a class="text-link" href="transactions.php">View transactions →</a></div>
</div>
<div class="quick-actions"><div>

  <h3>Quick actions</h3>
     <p class="muted">Choose a banking service to continue.</p>
</div><div class="action-row">
    <a class="action-tile" href="deposit.php">
        <span class="action-symbol green">↓</span>
        <b>Deposit</b><small>Add funds to an account</small>
    </a>
    <a class="action-tile" href="withdraw.php">
        <span class="action-symbol blue">↑</span>
        <b>Withdraw</b><small>Withdraw available funds</small>
    </a><a class="action-tile" href="transfer.php">
        <span class="action-symbol purple">⇄</span>
        <b>Transfer</b><small>Move funds between accounts</small>
    </a>
</div>
</div>
<section class="panel"><div class="panel-heading">
    <div>
        <h3>Recent transactions</h3>
        <p class="muted">Latest recorded account activity.
        </p>
    </div>
<a class="text-link" href="transactions.php">View all →</a>
</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Transaction</th>
                <th>Account</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
    <tbody><?php foreach($t as $row): ?>
        <tr>
            <td>
                <b><?=e($row['description'] ?: ucfirst($row['type']))?></b>
                <?php if($role!=='customer'): ?>
                    <small class="table-sub"><?=e($row['full_name'] ?? '')?></small>
                    <?php endif; ?>
          </td>
          <td><?=e($row['account_number'])?>
        </td>
        <td>
            <span class="pill <?=e($row['type'])?>"><?=e(ucfirst($row['type']))?></span>
        </td>
        <td class="amount"><?=money($row['amount'])?></td>
        <td><?=e($row['created_at'])?></td>
    </tr><?php endforeach; ?><?php if(!$t->rowCount()): ?><tr>
        <td colspan="5" class="empty">No transactions recorded yet.</td>
    </tr><?php endif; ?>
</tbody>
</table>
</div>
</section>
<?php footer_view(); ?>
