<?php
require_once 'functions.php'; require_role(['admin']); if($_SERVER['REQUEST_METHOD']!=='POST')
{header('Location: customers.php');exit;} verify_csrf();
$id=(int)($_POST['id']??0);
$s=$pdo->prepare("SELECT COUNT(*) FROM accounts WHERE user_id=? AND balance<>0");$s->execute([$id]);
if((int)$s->fetchColumn()>0)

    {flash('Customer has a non-zero account balance. Settle the balance before deactivation.','error');}
else{$s=$pdo->prepare("UPDATE users SET status='inactive' 
     WHERE id=? AND role='customer'");
     $s->execute([$id]);$s=$pdo->prepare("UPDATE accounts SET status='inactive' WHERE user_id=?");
      $s->execute([$id]);flash('Customer and related accounts deactivated. Records were retained for audit history.');}
header('Location: customers.php');exit;
