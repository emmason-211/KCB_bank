<?php
require_once 'functions.php'; require_login();
if($_SESSION['role']==='customer'){$s=$pdo->prepare("SELECT a.*,u.full_name FROM accounts a JOIN users u ON u.id=a.user_id WHERE a.user_id=? ORDER BY a.created_at DESC");$s->execute([$_SESSION['user_id']]);$rows=$s->fetchAll();}
else{$rows=$pdo->query("SELECT a.*,u.full_name FROM accounts a JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC")->fetchAll();}
header_view($_SESSION['role']==='customer'?'My account':'Bank accounts');
?>
<?php if(in_array($_SESSION['role'],['admin','employee'],true)):?><div class="page-tools"><span class="muted">Manage customer bank accounts</span><a class="btn primary" href="add_account.php">＋ Open account</a></div><?php endif;?>
<section class="panel"><div class="panel-heading"><div><h3>Account directory</h3><p class="muted">Balances and account status</p></div></div><div class="table-wrap"><table><thead><tr><th>Account number</th><th>Account holder</th><th>Balance</th><th>Status</th><th>Opened</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['account_number'])?></b></td><td><?=e($r['full_name'])?></td><td class="amount"><?=money($r['balance'])?></td><td><span class="pill <?=e($r['status'])?>"><?=e(ucfirst($r['status']))?></span></td><td><?=e($r['created_at'])?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="5" class="empty">No accounts found.</td></tr><?php endif;?></tbody></table></div></section>
<?php footer_view(); ?>
