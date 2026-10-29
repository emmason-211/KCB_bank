<?php
require_once 'functions.php'; require_login();
if($_SESSION['role']==='customer'){$s=$pdo->prepare("SELECT t.*,a.account_number FROM transactions t JOIN accounts a ON a.id=t.account_id WHERE a.user_id=? ORDER BY t.created_at DESC LIMIT 500");$s->execute([$_SESSION['user_id']]);$rows=$s->fetchAll();}
else{$rows=$pdo->query("SELECT t.*,a.account_number,u.full_name FROM transactions t JOIN accounts a ON a.id=t.account_id JOIN users u ON u.id=a.user_id ORDER BY t.created_at DESC LIMIT 1000")->fetchAll();}
header_view('Transactions');
?>
<section class="panel">

   <div class="panel-heading"><div>
    <h3>Transaction history</h3>
    <p class="muted">Latest <?=count($rows)?> transaction entries</p></div></div>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Date</th><th>Account</th>
            <?php if($_SESSION['role']!=='customer'):?>
            <th>Customer</th><?php endif;?>
            <th>Type</th><th>Description</th>
            <th>Amount</th><th>Reference</th></tr></thead>

            <tbody><?php foreach($rows as $r):?>
              <tr><td><?=e($r['created_at'])?></td>
              <td><?=e($r['account_number'])?></td>
              <?php if($_SESSION['role']!=='customer'):?>
              <td><?=e($r['full_name'])?></td>

    <?php endif;?><td>
        <span class="pill <?=e($r['type'])?>"><?=e(str_replace('_',' ',ucfirst($r['type'])))?></span></td>
        <td><?=e($r['description'])?></td>
        <td class="amount"><?=money($r['amount'])?></td>
        <td>#<?=e($r['id'])?></td></tr><?php endforeach;?><?php if(!$rows):?>
            <tr><td colspan="7" class="empty">No transaction records yet.</td></tr><?php endif;?>
        </tbody>
    </table>
  </div>
</section>
<?php footer_view(); ?>
