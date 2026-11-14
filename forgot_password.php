<?php require __DIR__.'/common.php';require __DIR__.'/reset_helpers.php';
$role=resetRole($_SERVER['REQUEST_METHOD']==='POST'?($_POST['role']??'user'):($_GET['role']??'user'));
$error='';$notice='';$config=resetMailConfig();
if($_SERVER['REQUEST_METHOD']==='POST') {
 csrfCheck();
 try {
  $email=inputString($_POST,'email',120); if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Enter a valid email address.');
  if(!empty($config['email_enabled']) && time()-(int)($_SESSION['reset_requested_at']??0)>=60) {
   $_SESSION['reset_requested_at']=time();
   $pdo->beginTransaction();
   $q=$pdo->prepare('SELECT * FROM users WHERE email=? AND role=? AND status="active" FOR UPDATE');$q->execute([$email,$role]);$u=$q->fetch();$token=null;
   if($u){
    $q=$pdo->prepare('SELECT COUNT(*) FROM password_resets WHERE user_id=? AND created_at>DATE_SUB(NOW(),INTERVAL 1 HOUR)');$q->execute([$u['user_id']]);
    if((int)$q->fetchColumn()<3)$token=issueResetToken($u['user_id']);
   }
   $pdo->commit();
   if($token && !deliverResetEmail($u['email'],$token,$role))error_log('EventSeat: reset email transport failed.');
  }
  $notice=!empty($config['email_enabled'])?'If an eligible account matches, a reset email will be requested. Check your inbox and spam folder. Requests are limited; contact an administrator if it does not arrive.':'Email reset is not enabled on this installation. Contact an administrator to verify your identity and receive a reset link.';
 } catch(RuntimeException $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof PDOException?'Password reset is unavailable. Please contact an administrator.':$e->getMessage();}
}
pageStart('Forgot password'); ?>
<section class="panel auth"><p class="label"><?=h(strtoupper($role))?> ACCOUNT RECOVERY</p><h1>Forgot your password?</h1><p>Enter the email you use for your <?=$role==='admin'?'administrator':'user'?> account.</p>
<?php if(empty($config['email_enabled'])): ?><p class="notice">For this local installation, an administrator can provide a secure reset link after verifying your identity.</p><?php endif; ?>
<p class="error" role="alert"><?=h($error)?></p><p class="success" role="status"><?=h($notice)?></p>
<form method="post"><?php csrfField(); ?><input type="hidden" name="role" value="<?=h($role)?>"><label for="reset-email">Account email</label><input id="reset-email" type="email" name="email" maxlength="120" autocomplete="email" required><p><button><?=$config['email_enabled']?'Request reset email':'Show recovery instructions'?></button></p></form><a href="login.php?role=<?=h($role)?>">← Back to <?=$role==='admin'?'Admin':'User'?> Login</a></section><?php pageEnd(); ?>
