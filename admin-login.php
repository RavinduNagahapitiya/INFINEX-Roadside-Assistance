<?php
$title='Administrator Login';
require __DIR__.'/../includes/header.php';
require __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/functions.php';
$error='';
if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') redirect(BASE_URL.'admin/dashboard.php');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $st=$pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin' LIMIT 1");
    $st->execute([$email]);
    $u=$st->fetch();
    if ($u && password_verify($password,$u['password_hash']) && $u['status']==='active') {
        session_regenerate_id(true);
        $_SESSION['user_id']=$u['id'];
        $_SESSION['role']='admin';
        $_SESSION['name']=$u['name'];
        redirect(BASE_URL.'admin/dashboard.php');
    }
    $error='Invalid administrator credentials or inactive administrator account.';
}
?>
<section class="section-pad"><div class="container"><div class="row justify-content-center"><div class="col-md-6 col-lg-5"><div class="card border-0 shadow-sm p-4 rounded-4">
<h2 class="fw-bold">Administrator Login</h2><p class="text-muted">Sign in to manage INFINEX.</p>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
<form method="post" autocomplete="off"><label class="form-label">Administrator email</label><input class="form-control mb-3" type="email" name="email" required autofocus><label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required><button class="btn btn-dark w-100">Administrator Login</button></form>
<hr><a href="login.php" class="text-center d-block">Back to main login</a>
</div></div></div></div></section>
<?php require __DIR__.'/../includes/footer.php'; ?>
