<?php
function currentUser() {
 global $pdo;
 if (empty($_SESSION['user_id'])) return null;
 $q=$pdo->prepare('SELECT * FROM users WHERE user_id=? AND status="active"');
 $q->execute([$_SESSION['user_id']]); $user=$q->fetch();
 if (!$user || (int)($user['auth_version']??0)!==(int)($_SESSION['auth_version']??0)) return null;
 return $user;
}
function requireRole($role=null) {
 $u=currentUser();
 if (!$u) { if(basename($_SERVER['SCRIPT_NAME'])==='booking.php' && isset($_GET['event'])) { $_SESSION['return_event']=(int)$_GET['event']; $_SESSION['return_session']=(int)($_GET['session']??0); } header('Location: login.php'); exit; }
 if ($role && $u['role']!==$role) showError('You do not have permission to access this page.', 'index.php',403);
 return $u;
}
function setting($key,$fallback='') {
 global $pdo; $q=$pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key=?');
 $q->execute([$key]); $v=$q->fetchColumn(); return $v===false ? $fallback : $v;
}
function issuePromo($userId) {
 global $pdo;
 $percent=max(0,min(100,(int)setting('promo_percent','20')));
 for ($i=0;$i<5;$i++) {
  try { $code='ES-'.strtoupper(bin2hex(random_bytes(8)));
   $q=$pdo->prepare('INSERT INTO promo_codes (promo_code,discount_percent,expiry_date,user_id) VALUES (?,?,?,?)');
   $q->execute([$code,$percent,date('Y-m-d',strtotime('+90 days')),$userId]); return;
  } catch(PDOException $e) { if ($e->getCode()!=='23000') throw $e; }
 }
 throw new RuntimeException('Could not generate a unique code. Please retry.');
}
function csrfField() { echo '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">'; }
function redirectTo($url) { header('Location: '.$url,true,303); exit; }

function eventCategories(){return ['Concert','Theatre','Movie','Seminar','Sports','Music','Comedy'];}
function bookingSteps($current){
 echo '<ol class="steps" aria-label="Booking progress">';
 foreach(['Event details','Seats','Checkout','Review','Ticket'] as $i=>$name) echo '<li'.($i===$current?' class="current" aria-current="step"':'').'>'.($i+1).' · '.h($name).'</li>';
 echo '</ol>';
}
