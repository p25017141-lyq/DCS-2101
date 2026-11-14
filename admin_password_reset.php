<?php require __DIR__.'/common.php';require __DIR__.'/reset_helpers.php';$admin=requireRole('admin');header('Referrer-Policy: no-referrer');$error='';$link='';$target=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrfCheck();
 try{
  if(($_POST['verified']??'')!=='yes')throw new RuntimeException('Verify the account owner before generating a link.');
  $email=inputString($_POST,'email',120);if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Enter a valid account email.');
  $pdo->beginTransaction();$q=$pdo->prepare('SELECT * FROM users WHERE email=? AND status="active" FOR UPDATE');$q->execute([$email]);$target=$q->fetch();if(!$target)throw new RuntimeException('Active account not found.');
  $token=issueResetToken($target['user_id']);$pdo->commit();$link='reset_password.php?token='.$token.'&role='.$target['role'];
 }catch(RuntimeException $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof PDOException?'Could not generate a reset link. Check the password-reset database migration.':$e->getMessage();}
}
pageStart('Account recovery'); ?>
<section class="panel auth"><h1>Generate password reset link</h1><p><a href="admin.php?tab=users">← Manage users</a></p><p>Verify the account owner before giving them a link. It expires in 30 minutes; generating a new link invalidates their previous links.</p><p class="error" role="alert"><?=h($error)?></p><form method="post"><?php csrfField(); ?><label for="recovery-account">Account email</label><input id="recovery-account" type="email" name="email" maxlength="120" required><label class="check"><input type="checkbox" name="verified" value="yes" required>I verified the account owner's identity.</label><button>Generate reset link</button></form>
<?php if($link): ?><section class="notice"><p>Reset link for <?=h($target['email'])?> (<?=h($target['role'])?>):</p><a id="recovery-link" href="<?=h($link)?>">Open password reset</a><p>Copy this link and give it only to the verified account owner.</p><label for="recovery-url">Full reset URL</label><input id="recovery-url" readonly value="<?=h($link)?>"><script>document.getElementById('recovery-url').value=new URL(document.getElementById('recovery-link').getAttribute('href'),document.baseURI).href;</script></section><?php endif; ?></section><?php pageEnd(); ?>
