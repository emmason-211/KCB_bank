<?php
require_once 'functions.php'; require_role(['admin']);
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=trim($_POST['full_name']??'');$email=trim($_POST['email']??'');$password=$_POST['password']??'';$role=$_POST['role']??'employee';
 if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8||!in_array($role,['employee','admin'],true))$error='Enter valid details and a password of at least 8 characters.';
 else{try{$s=$pdo->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,?)");$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role]);flash('Staff account created.');header('Location: employees.php');exit;}catch(Throwable $e){$error='Could not create staff account. Check whether the email already exists.';}}
}
$rows=$pdo->query("SELECT id,full_name,email,role,status,created_at FROM users WHERE role IN ('admin','employee') ORDER BY created_at DESC")->fetchAll();
header_view('Employees');
?>
<div class="two-col"><section class="panel form-panel"><div class="panel-heading"><div><h3>Add staff user</h3><p class="muted">Administrator access should be limited.</p></div></div><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Full name</label><input name="full_name" required><label>Email address</label><input type="email" name="email" required><label>Temporary password</label><input type="password" name="password" minlength="8" required><label>Role</label><select name="role"><option value="employee">Employee</option><option value="admin">Administrator</option></select><button class="btn primary">Create staff user</button></form></section><section class="panel"><div class="panel-heading"><div><h3>Staff directory</h3><p class="muted">Employees and administrators</p></div></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['full_name'])?></td><td><?=e($r['email'])?></td><td><?=e(ucfirst($r['role']))?></td><td><?=e(ucfirst($r['status']))?></td></tr><?php endforeach;?></tbody></table></div></section></div>
<?php footer_view(); ?>
