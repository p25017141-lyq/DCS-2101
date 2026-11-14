<?php require __DIR__.'/common.php'; $error='';
$loginRole = $_SERVER['REQUEST_METHOD']==='POST' ? ($_POST['login_role']??'') : ($_GET['role']??'user');
if (!is_string($loginRole) || !in_array($loginRole,['user','admin'],true)) {
 if ($_SERVER['REQUEST_METHOD']==='POST') showError('Choose User Login or Admin Login.','login.php',400);
 $loginRole='user';
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrfCheck();
 try {
  $email=inputString($_POST,'email',120); $password=inputString($_POST,'password',200);
  $q=$pdo->prepare('SELECT * FROM users WHERE email=?'); $q->execute([$email]); $u=$q->fetch();
  $valid=password_verify($password,$u['password_hash']??'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi');
  if (!$u || !$valid || $u['status']!=='active' || ($u['locked_until'] && strtotime($u['locked_until'])>time())) {
   if ($u) { $q=$pdo->prepare('UPDATE users SET login_failures=login_failures+1, locked_until=IF(login_failures>=5,DATE_ADD(NOW(),INTERVAL 15 MINUTE),locked_until) WHERE user_id=?'); $q->execute([$u['user_id']]); }
   throw new RuntimeException('Unable to log in. Check your details. After repeated failures, wait 15 minutes.');
  }
  if ($u['role'] !== $loginRole) throw new RuntimeException('This account cannot use the selected login. Choose the correct User or Admin entrance.');
  $pdo->prepare('UPDATE users SET login_failures=0,locked_until=NULL WHERE user_id=?')->execute([$u['user_id']]);
  $returnEvent=(int)($_SESSION['return_event']??0); $returnSession=(int)($_SESSION['return_session']??0);
  $_SESSION=[]; session_regenerate_id(true); $_SESSION['user_id']=$u['user_id']; $_SESSION['auth_version']=(int)($u['auth_version']??0); $_SESSION['visitor']=bin2hex(random_bytes(32)); $_SESSION['csrf']=bin2hex(random_bytes(32));
  redirectTo($u['role']==='admin'?'dashboard.php':($returnEvent>0?'booking.php?event='.$returnEvent.($returnSession>0?'&session='.$returnSession:''):'events.php'));
 } catch(RuntimeException $e) { $error=$e instanceof PDOException?'Unable to log in. Please retry.':$e->getMessage(); }
}
pageStart($loginRole==='admin'?'Admin Login':'User Login'); ?>
<section class="panel auth">
<nav class="login-switch" aria-label="Choose login type">
 <a class="button <?=$loginRole==='user'?'':'secondary'?>" href="login.php?role=user" <?=$loginRole==='user'?'aria-current="page"':''?>>User Login</a>
 <a class="button <?=$loginRole==='admin'?'':'secondary'?>" href="login.php?role=admin" <?=$loginRole==='admin'?'aria-current="page"':''?>>Admin Login</a>
</nav>
<p class="label"><?=$loginRole==='admin'?'ADMINISTRATOR ACCESS':'WELCOME BACK'?></p>
<?php if(isset($_GET['reset'])): ?><p class="success" role="status">Your password has been reset. Log in with your new password.</p><?php endif; ?>
<h1><?=$loginRole==='admin'?'Admin Login':'User Login'?></h1>
<p><?=$loginRole==='admin'?'Sign in to manage events, users and website content.':'Sign in to book your seats, view tickets and use your rewards.'?></p>
<?php if(isset($_GET['registered']) && $loginRole==='user'): ?><p class="success">Account created. Log in to view your welcome reward.</p><?php endif; ?>
<p class="error" role="alert"><?=h($error)?></p>
<form method="post" action="login.php?role=<?=h($loginRole)?>">
<?php csrfField(); ?><input type="hidden" name="login_role" value="<?=h($loginRole)?>">
<label for="login-email"><?=$loginRole==='admin'?'Admin email':'Email'?></label>
<input id="login-email" name="email" type="email" maxlength="120" autocomplete="username" required>
<label for="login-password">Password</label>
<input id="login-password" name="password" type="password" maxlength="200" autocomplete="current-password" required>
<p><button>Log in as <?=$loginRole==='admin'?'Admin':'User'?></button></p>
</form>
<p><a href="forgot_password.php?role=<?=h($loginRole)?>">Forgot password?</a></p>
<?php if($loginRole==='user'): ?><p>New here? <a href="register.php">Create a user account</a></p>
<?php else: ?><p class="small">Use an existing administrator account. Ordinary user accounts cannot access this entrance.</p><?php endif; ?>
</section><?php pageEnd(); ?>
