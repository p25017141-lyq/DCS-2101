<?php require __DIR__.'/common.php';require __DIR__.'/reset_helpers.php';
header('Referrer-Policy: no-referrer');
$role=resetRole($_SERVER['REQUEST_METHOD']==='POST'?($_POST['role']??'user'):($_GET['role']??'user'));
$token=$_SERVER['REQUEST_METHOD']==='POST'?($_POST['token']??''):($_GET['token']??'');
if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token))showError('This reset link is invalid or expired. Request a new link.','forgot_password.php?role='.$role);
$hash=hash('sha256',$token);$error='';
$q=$pdo->prepare('SELECT r.user_id FROM password_resets r JOIN users u ON u.user_id=r.user_id WHERE r.token_hash=? AND r.used_at IS NULL AND r.expires_at>NOW() AND u.status="active" AND u.role=?');$q->execute([$hash,$role]);$userId=$q->fetchColumn();
if(!$userId)showError('This reset link is invalid or expired. Request a new link.','forgot_password.php?role='.$role);
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrfCheck();
 try {
  $password=$_POST['password']??'';$confirm=$_POST['confirm_password']??'';
  if(!is_string($password)||!is_string($confirm)||strlen($password)<12||strlen($password)>72||$password!==$confirm)throw new RuntimeException('Use matching passwords of 12–72 bytes.');
  $newHash=password_hash($password,PASSWORD_DEFAULT);
  $pdo->beginTransaction();
  // All issuance/redemption paths lock the user first to avoid competing resets.
  $q=$pdo->prepare('SELECT user_id FROM users WHERE user_id=? AND role=? AND status="active" FOR UPDATE');$q->execute([$userId,$role]);if(!$q->fetchColumn())throw new RuntimeException('This account is unavailable.');
  $q=$pdo->prepare('SELECT reset_id FROM password_resets WHERE token_hash=? AND user_id=? AND used_at IS NULL AND expires_at>NOW() FOR UPDATE');$q->execute([$hash,$userId]);if(!$q->fetchColumn())throw new RuntimeException('This reset link is invalid or expired.');
  $q=$pdo->prepare('UPDATE users SET password_hash=?,auth_version=auth_version+1,login_failures=0,locked_until=NULL WHERE user_id=?');$q->execute([$newHash,$userId]);
  $q=$pdo->prepare('UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL');$q->execute([$userId]);
  $pdo->commit();$_SESSION=[];session_regenerate_id(true);$_SESSION['visitor']=bin2hex(random_bytes(32));$_SESSION['csrf']=bin2hex(random_bytes(32));redirectTo('login.php?role='.$role.'&reset=1');
 }catch(RuntimeException $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof PDOException?'Could not reset the password. Please retry.':$e->getMessage();}
}
pageStart('Set a new password'); ?>
<section class="panel auth"><p class="label">SECURE ACCOUNT RECOVERY</p><h1>Set a new password</h1><p>This link expires after 30 minutes and can only be used once.</p><p class="error" role="alert"><?=h($error)?></p><form method="post" action="reset_password.php"><?php csrfField(); ?><input type="hidden" name="role" value="<?=h($role)?>"><input type="hidden" name="token" value="<?=h($token)?>"><label for="new-password">New password (12–72 bytes)</label><input id="new-password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required><label for="confirm-password">Confirm password</label><input id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required><p><button>Save new password</button></p></form></section><?php pageEnd(); ?>
