<?php require __DIR__.'/common.php'; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') { csrfCheck(); try {
 $name=inputString($_POST,'full_name',80); $email=strtolower(inputString($_POST,'email',120)); $phone=inputString($_POST,'phone',40); $pass=inputString($_POST,'password',200);
 if (mb_strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || !preg_match('/^[+0-9 ().-]{7,40}$/D',$phone)) throw new RuntimeException('Enter a valid name, email and phone number.');
 if (strlen($pass)<10 || strlen($pass)>72 || $pass!==($_POST['confirm_password']??'')) throw new RuntimeException('Passwords must match and contain 10 to 72 bytes.');
 $pdo->beginTransaction(); $q=$pdo->prepare('INSERT INTO users (full_name,email,phone,password_hash) VALUES (?,?,?,?)');
 $q->execute([$name,$email,$phone,password_hash($pass,PASSWORD_DEFAULT)]); issuePromo((int)$pdo->lastInsertId()); $pdo->commit(); redirectTo('login.php?registered=1');
 } catch(RuntimeException $e) { if($pdo->inTransaction())$pdo->rollBack(); $error=$e instanceof PDOException?'Registration could not be completed. Try another email.':$e->getMessage(); }
}
pageStart('Create account'); ?>
<section class="panel auth"><p class="label">YOUR NEXT EXPERIENCE</p><h1>Create your account</h1><p>Get a unique welcome promo code when you register.</p><p class="error" role="alert"><?=h($error)?></p><form method="post"><?php csrfField(); ?>
<label>Full name</label><input name="full_name" maxlength="80" autocomplete="name" required><label>Email</label><input name="email" type="email" maxlength="120" autocomplete="email" required><label>Phone</label><input name="phone" type="tel" maxlength="40" autocomplete="tel" required><label>Password (10–72 bytes)</label><input name="password" type="password" minlength="10" maxlength="72" autocomplete="new-password" required><label>Confirm password</label><input name="confirm_password" type="password" maxlength="72" autocomplete="new-password" required><p><button>Create account</button></p></form><a href="login.php">Already have an account? Log in</a></section><?php pageEnd(); ?>
