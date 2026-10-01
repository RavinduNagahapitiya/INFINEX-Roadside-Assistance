<?php
require __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/functions.php';
require_login('customer');
require __DIR__.'/../config/database.php';

$id=(int)($_POST['request_id']??0);
$st=$pdo->prepare("SELECT r.*,c.access_fee FROM assistance_requests r JOIN service_categories c ON c.id=r.category_id WHERE r.id=? AND r.customer_id=? LIMIT 1");
$st->execute([$id,$_SESSION['user_id']]);
$r=$st->fetch();
if(!$r) redirect(BASE_URL.'customer/dashboard.php');

$pdo->beginTransaction();
try {
    $existing=$pdo->prepare("SELECT id FROM payments WHERE request_id=? AND payment_status='success' LIMIT 1");
    $existing->execute([$id]);
    if($existing->fetchColumn()){
        $pdo->commit();
        redirect(BASE_URL.'customer/providers.php?request='.$id);
    }
    $ref='INF-'.date('YmdHis').'-'.random_int(1000,9999);
    $pdo->prepare("INSERT INTO payments(request_id,customer_id,amount,transaction_reference,payment_method,payment_status,paid_at) VALUES(?,?,?,?,?,'success',NOW())")
        ->execute([$id,$_SESSION['user_id'],$r['access_fee'],$ref,'DEMO']);
    $pdo->prepare("UPDATE assistance_requests SET status='paid' WHERE id=? AND customer_id=?")->execute([$id,$_SESSION['user_id']]);
    $pdo->commit();
    redirect(BASE_URL.'customer/providers.php?request='.$id);
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    exit('Payment processing failed. Please try again.');
}
