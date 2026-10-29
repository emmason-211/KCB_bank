<?php
require_once 'functions.php'; require_role(['admin','employee']);
$id=(int)($_GET['id']??0);$s=$pdo->prepare("SELECT * FROM users WHERE id=? AND role='customer'");$s->execute([$id]);$u=$s->fetch();if(!$u){http_response_code(404);exit('Customer not found.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=trim($_POST['full_name']??'');$email=trim($_POST['email']??'');$phone=trim($_POST['phone']??'');$address=trim($_POST['address']??'');$status=$_POST['status']??'active';
 if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||!in_array($status,['active','inactive'],true))$error='Please check the submitted fields.';
 else{try{$s=$pdo->prepare("UPDATE users SET full_name=?,email=?,phone=?,address=?,status=? WHERE id=? AND role='customer'");$s->execute([$name,$email,$phone,$address,$status,$id]);flash('Customer updated.');header('Location: customers.php');exit;}catch(Throwable $e){$error='Update failed. Check whether the email is already used.';}}
}
header_view('Edit customer');
?>
<section class="panel form-panel"><div class="panel-heading"><div><h3>Edit customer details</h3><p class="muted">Update the selected customer profile.</p></div></div><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><div><label>Full name</label><input name="full_name" value="<?=e($u['full_name'])?>" required></div><div><label>Email</label><input type="email" name="email" value="<?=e($u['email'])?>" required></div><div><label>Phone</label><input name="phone" value="<?=e($u['phone'])?>"></div><div><label>Status</label><select name="status"><option value="active" <?=$u['status']==='active'?'selected':''?>>Active</option><option value="inactive" <?=$u['status']==='inactive'?'selected':''?>>Inactive</option></select></div><div class="span-2"><label>Address</label><input name="address" value="<?=e($u['address'])?>"></div><div class="span-2 form-actions"><button class="btn primary">Save changes</button><a class="btn secondary" href="customers.php">Cancel</a></div></form></section>
<?php footer_view(); ?>
