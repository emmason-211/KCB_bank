<?php
require_once 'functions.php'; require_role(['admin','employee']);
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $name=trim($_POST['full_name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $address=trim($_POST['address']??''); $password=$_POST['password']??'';
 if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8){$error='Enter a name, valid email, and password of at least 8 characters.';}
 else {try{$pdo->beginTransaction();$s=$pdo->prepare("INSERT INTO users(full_name,email,phone,address,password_hash,role) VALUES(?,?,?,?,?,'customer')");$s->execute([$name,$email,$phone,$address,password_hash($password,PASSWORD_DEFAULT)]);$id=(int)$pdo->lastInsertId();$num='KCB'.date('ym').str_pad((string)$id,6,'0',STR_PAD_LEFT);$a=$pdo->prepare("INSERT INTO accounts(user_id,account_number,balance) VALUES(?,?,0)");$a->execute([$id,$num]);$pdo->commit();flash('Customer and bank account created successfully.');header('Location: customers.php');exit;}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='Could not create customer. The email may already be registered.';}}
}
header_view('Register customer');
?>
<section class="panel form-panel">
    <div class="panel-heading"><div>
        <h3>New customer profile</h3><p class="muted">A bank account is created automatically.</p>
    </div></div>
    <?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><div>
                <label>Full name</label>
                <input name="full_name" required maxlength="120"></div><div>
                <label>Email address</label>
                <input type="email" name="email" required></div><div>
                <label>Phone number</label><input name="phone" maxlength="30"></div><div>
                <label>Initial password</label>
                <input type="password" name="password" required minlength="8"></div>
                <div class="span-2">
                <label>Address</label>
                <input name="address" maxlength="255"></div>
                <div class="span-2 form-actions">
                <button class="btn primary">Create customer</button>
                <a class="btn secondary" href="customers.php">Cancel</a></div>
            </form>
</section>
<?php footer_view(); ?>
