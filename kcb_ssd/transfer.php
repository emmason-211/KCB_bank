<?php
require_once 'functions.php'; require_role(['admin','employee','customer']);
$error='';$role=$_SESSION['role'];
if($role==='customer'){$s=$pdo->prepare("SELECT id,account_number,balance FROM accounts WHERE user_id=? AND status='active'");$s->execute([$_SESSION['user_id']]);$sourceAccounts=$s->fetchAll();$accounts=$pdo->query("SELECT id,account_number,balance FROM accounts WHERE status='active' ORDER BY account_number")->fetchAll();}
else{$accounts=$pdo->query("SELECT id,account_number,balance FROM accounts WHERE status='active' ORDER BY account_number")->fetchAll();$sourceAccounts=$accounts;}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$from=(int)($_POST['from_account']??0);$to=(int)($_POST['to_account']??0);$amount=filter_var($_POST['amount']??null,FILTER_VALIDATE_FLOAT);$desc=trim($_POST['description']??'Account transfer');
 if($from===$to)$error='Source and destination accounts must be different.';
 elseif(!$amount||$amount<=0||$amount>100000000)$error='Enter a valid transfer amount.';
 elseif(!can_access_account($pdo,$from))$error='You cannot transfer from that account.';
 else{try{$pdo->beginTransaction();$ids=[$from,$to];sort($ids,SORT_NUMERIC);$locked=[];$s=$pdo->prepare("SELECT id,balance,status FROM accounts WHERE id=? FOR UPDATE");foreach($ids as $id){$s->execute([$id]);$locked[$id]=$s->fetch();}if(!$locked[$from]||!$locked[$to]||$locked[$from]['status']!=='active'||$locked[$to]['status']!=='active')throw new RuntimeException();if((float)$locked[$from]['balance']<(float)$amount)throw new DomainException();$s=$pdo->prepare("UPDATE accounts SET balance=balance-? WHERE id=?");$s->execute([$amount,$from]);$s=$pdo->prepare("UPDATE accounts SET balance=balance+? WHERE id=?");$s->execute([$amount,$to]);post_transaction($pdo,$from,'transfer_out',(float)$amount,$desc?:'Transfer sent',$to);post_transaction($pdo,$to,'transfer_in',(float)$amount,$desc?:'Transfer received',$from);$pdo->commit();flash('Transfer completed successfully.');header('Location: transactions.php');exit;}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();$error='Insufficient funds. No money was transferred.';}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='Transfer failed. Check both accounts and try again.';}}
}
header_view('Transfer funds');
?>
<section class="panel form-panel">
    <div class="panel-heading"><div>
        <h3>Transfer between accounts</h3>
        <p class="muted">Both account updates are processed in one database transaction.</p>
    </div>
        <span class="form-icon purple">⇄
        </span>
    </div>
    <?php if($error):?>
        <div class="alert error"><?=e($error)?></div>
        <?php endif;?><form method="post" class="form-grid">

        <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
        <div>
            <label>From account</label>
              <select name="from_account" required>
                <option value="">Select source</option>
                    <?php foreach($sourceAccounts as $a):?>
                        <option value="<?=(int)$a['id']?>"><?=e($a['account_number'].' — '.money($a['balance']))?>
                    </option>
                        <?php endforeach;?>
                    </select>
                </div>

                        <div>
                            <label>To account</label>
                              <select name="to_account" required>
                                <option value="">Select destination</option>
                                <?php foreach($accounts as $a):?>
                                    <option value="<?=(int)$a['id']?>"><?=e($a['account_number'])?>
                                </option><?php endforeach;?>
                            </select>
                        </div>

                        <div class="span-2">
                            <label>Amount (SSP)</label>
                               <input type="number" name="amount" min="0.01" max="100000000" step="0.01" required>
                            </div>

                            <div class="span-2">
                                <label>Transfer description</label>
                                <input name="description" maxlength="255" value="Account transfer">
                            </div>
                            <div class="span-2 notice">Please verify the destination account number before confirming. This demonstration app does not connect to a real banking network.

                            </div><div class="span-2 form-actions">
                                <button class="btn primary">Confirm transfer
                                </button>
        </div>
    </form>
</section>
<?php footer_view(); ?>
