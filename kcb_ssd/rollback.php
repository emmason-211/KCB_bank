<?php
require_once 'functions.php'; require_role(['admin']);
$result='';
if($_SERVER['REQUEST_METHOD']==='POST')
    {verify_csrf();try{$pdo->beginTransaction();
   $pdo->exec("INSERT INTO audit_logs(user_id,action,details) VALUES(".(int)$_SESSION['user_id'].
   ",'rollback_demo','This audit entry is intentionally rolled back')");
   $pdo->rollBack();$result='Rollback demonstration completed: the sample audit entry was not saved.';}catch(Throwable $e)

   {
   if($pdo->inTransaction())$pdo->rollBack();
   $result='Rollback demo failed. Confirm the database schema is imported.';
   }}
  header_view('Rollback demonstration');
?>
<section class="panel form-panel">
    <h3>Database rollback demonstration</h3>
    <p class="muted">A rollback cancels uncommitted changes.
        This page uses a harmless sample audit entry; it does
        not reverse a completed deposit, withdrawal, or transfer.
    </p> <?php if($result):?>
      <div class="alert success"><?=e($result)?>
       </div><?php endif;?>

 <form method="post">
      <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
        <button class="btn primary">Run rollback demo</button>
  </form>
</section>
<?php footer_view(); ?>
