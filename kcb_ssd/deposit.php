<?php
require_once 'functions.php'; require_role(['admin','employee','customer']);
$error='';$role=$_SESSION['role'];
if($role==='customer'){$s=$pdo->prepare("SELECT id,account_number,balance FROM accounts WHERE user_id=? AND status='active'");$s->execute([$_SESSION['user_id']]);$accounts=$s->fetchAll();}
else{$accounts=$pdo->query("SELECT a.id,a.account_number,a.balance FROM accounts a JOIN users u ON u.id=a.user_id WHERE a.status='active' AND u.status='active' ORDER BY a.account_number")->fetchAll();}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$aid=(int)($_POST['account_id']??0);$amount=filter_var($_POST['amount']??null,FILTER_VALIDATE_FLOAT);$desc=trim($_POST['description']??'Deposit');
 if(!$amount||$amount<=0||$amount>100000000)$error='Enter an amount greater than zero and within the permitted limit.';
 elseif(!can_access_account($pdo,$aid))$error='You cannot access that account.';
 else{try{$pdo->beginTransaction();$s=$pdo->prepare("SELECT id FROM accounts WHERE id=? AND status='active' FOR UPDATE");$s->execute([$aid]);if(!$s->fetch())throw new RuntimeException('Account unavailable');$s=$pdo->prepare("UPDATE accounts SET balance=balance+? WHERE id=?");$s->execute([$amount,$aid]);post_transaction($pdo,$aid,'deposit',(float)$amount,$desc?:'Deposit');$pdo->commit();flash('Deposit recorded successfully.');header('Location: transactions.php');exit;}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='Deposit failed. Verify the account and try again.';}}
}
header_view('Deposit funds');
?>
<section class="panel form-panel"><div class="panel-heading"><div><h3>Deposit funds</h3><p class="muted">Record money added to an active account.</p></div><span class="form-icon green">↓</span></div><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><div class="span-2"><label>Destination account</label><select name="account_id" required><option value="">Select account</option><?php foreach($accounts as $a):?><option value="<?=(int)$a['id']?>"><?=e($a['account_number'].' — Current balance: '.money($a['balance']))?></option><?php endforeach;?></select></div><div class="span-2"><label>Amount (SSP)</label><input type="number" name="amount" min="0.01" max="100000000" step="0.01" required></div><div class="span-2"><label>Description</label><input name="description" maxlength="255" value="Cash deposit"></div><div class="span-2 form-actions"><button class="btn primary">Confirm deposit</button></div></form></section>
<?php footer_view(); ?>
