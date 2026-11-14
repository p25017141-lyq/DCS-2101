<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/db.php';require __DIR__.'/reset_helpers.php';
if($argc!==2||!filter_var($argv[1],FILTER_VALIDATE_EMAIL))exit("Usage: php create_reset_link.php account@example.com\n");
try{
 $pdo->beginTransaction();$q=$pdo->prepare('SELECT user_id,role FROM users WHERE email=? AND status="active" FOR UPDATE');$q->execute([$argv[1]]);$u=$q->fetch();if(!$u)throw new RuntimeException('Active account not found.');
 $token=issueResetToken($u['user_id']);$pdo->commit();$config=resetMailConfig();echo rtrim($config['base_url'],'/').'/reset_password.php?token='.$token.'&role='.$u['role']."\n";
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,"Unable to create reset link. Check the account and database migration.\n");exit(1);}
