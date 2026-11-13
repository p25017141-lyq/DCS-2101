<?php
// CLI only: no administrator signup is exposed on the website.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/db.php';
if($argc!==3){exit("Usage: php create_admin.php admin@example.com 'Your-long-password'\n");}
if(!filter_var($argv[1],FILTER_VALIDATE_EMAIL)||strlen($argv[1])>120||strlen($argv[2])<12||strlen($argv[2])>72)exit("Use a valid email and a password of 12–72 bytes.\n");
$q=$pdo->prepare('INSERT INTO users(full_name,email,phone,password_hash,role) VALUES(?,?,?,?,?)');
$q->execute(['Administrator',$argv[1],'',password_hash($argv[2],PASSWORD_DEFAULT),'admin']);
echo "Administrator created. Open login.php to sign in.\n";
