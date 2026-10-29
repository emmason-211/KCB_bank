<?php
require_once 'functions.php'; require_role(['admin','employee']);
if(isset($_GET['new'])){header('Location: register.php');exit;}
$q=trim($_GET['q']??'');
$sql="SELECT u.id,u.full_name,u.email,u.phone,u.status,COUNT(a.id) account_count,COALESCE(SUM(CASE WHEN a.status='active' THEN a.balance ELSE 0 END),0) balance FROM users u LEFT JOIN accounts a ON a.user_id=u.id WHERE u.role='customer' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?) GROUP BY u.id ORDER BY u.created_at DESC";
$s=$pdo->prepare($sql);$like="%$q%";$s->execute([$like,$like,$like]);$rows=$s->fetchAll();
header_view('Customers');
?>
<div class="page-tools"><form class="search-form" method="get"><input name="q" value="<?=e($q)?>" placeholder="Search name, email or phone"><button class="btn secondary">Search</button></form><a class="btn primary" href="register.php">＋ Add customer</a></div>
<section class="panel"><div class="panel-heading"><div><h3>Customer directory</h3><p class="muted"><?=count($rows)?> customer record(s) found</p></div></div><div class="table-wrap"><table><thead><tr><th>Customer</th><th>Contact</th><th>Accounts</th><th>Balance total</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['full_name'])?></b><small class="table-sub">Customer #<?=e($r['id'])?></small></td><td><?=e($r['email'])?><small class="table-sub"><?=e($r['phone'])?></small></td><td><?=e($r['account_count'])?></td><td><?=money($r['balance'])?></td><td><span class="pill <?=e($r['status'])?>"><?=e(ucfirst($r['status']))?></span></td><td class="actions"><a href="edit_customer.php?id=<?=(int)$r['id']?>">Edit</a><form method="post" action="delete_customer.php" onsubmit="return confirm('Deactivate this customer?');"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button class="link-danger">Deactivate</button></form></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6" class="empty">No matching customers.</td></tr><?php endif;?></tbody></table></div></section>
<?php footer_view(); ?>
