<?php
function resetRole($value) { return is_string($value) && $value==='admin' ? 'admin' : 'user'; }
// Call inside a transaction after locking the user row. Store only a token hash.
function issueResetToken($userId) {
 global $pdo;
 $q=$pdo->prepare('UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL');$q->execute([$userId]);
 $token=bin2hex(random_bytes(32));
 $q=$pdo->prepare('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 30 MINUTE))');
 $q->execute([$userId,hash('sha256',$token)]);return $token;
}
function resetMailConfig(){ return require __DIR__.'/reset_config.php'; }
function deliverResetEmail($email,$token,$role) {
 $config=resetMailConfig(); if(empty($config['email_enabled']))return false;
 $base=rtrim($config['base_url']??'','/');$from=$config['from_email']??'';
 if(!filter_var($base,FILTER_VALIDATE_URL)||!preg_match('~^https?://~',$base)||!filter_var($from,FILTER_VALIDATE_EMAIL)||!filter_var($email,FILTER_VALIDATE_EMAIL)) return false;
 $url=$base.'/reset_password.php?token='.rawurlencode($token).'&role='.rawurlencode($role);
 $body="A password reset was requested for your EventSeat account.\n\nOpen this link within 30 minutes:\n".$url."\n\nIf you did not request this, ignore this email. Your password has not changed.\n";
 return @mail($email,'Reset your EventSeat password',$body,['From'=>$from,'Content-Type'=>'text/plain; charset=UTF-8']);
}
