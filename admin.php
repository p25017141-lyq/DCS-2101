<?php
require __DIR__.'/common.php'; $admin=requireRole('admin');
$tab=$_GET['tab']??'users';
$config=[
 'users'=>['users','user_id',['full_name','email','phone','status']],
 'sessions'=>['event_sessions','session_id',['event_id','session_date','session_time','session_status']],
 'seats'=>['seats','seat_id',['price','status']],
 'settings'=>['site_settings','setting_key',['setting_value']],
];
$error='';$notice='';
if ($tab==='bookings') {
 pageStart('All bookings');
 $rows=$pdo->query('SELECT b.reference,u.email,e.event_name,b.total_amount,b.booking_date FROM bookings b LEFT JOIN users u ON u.user_id=b.user_id JOIN event_sessions es ON es.session_id=b.session_id JOIN events e ON e.event_id=es.event_id ORDER BY b.booking_id DESC LIMIT 200')->fetchAll();
 echo '<h1>All bookings</h1><p>Latest 200 bookings. Payment is simulated.</p>'; renderTable($rows); pageEnd(); exit;
}
if(!isset($config[$tab])) $tab='users';
[$table,$pk,$fields]=$config[$tab];
$id=$_GET['id']??null; if(!is_string($id)&&$id!==null) showError('Invalid record.');
if($_SERVER['REQUEST_METHOD']==='POST') {
 csrfCheck();
 try {
  $id=inputString($_POST,'id',60);$data=[];
  foreach($fields as $f) $data[$f]=inputString($_POST,$f,$tab==='settings'?2000:150);
  if($tab==='users') {
   if($id===(string)$admin['user_id']) throw new RuntimeException('You cannot modify your own administrator account here.');
   if(mb_strlen($data['full_name'])<2||mb_strlen($data['full_name'])>80||strlen($data['email'])>120||strlen($data['phone'])>40||!filter_var($data['email'],FILTER_VALIDATE_EMAIL)||!in_array($data['status'],['active','blocked'],true)) throw new RuntimeException('Check the account details and status.');
  }
  if($tab==='sessions') {
   $data['event_id']=positiveId($data['event_id']);
   $date=DateTime::createFromFormat('!Y-m-d',$data['session_date']);
   if(!$date||$date->format('Y-m-d')!==$data['session_date']||!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D',$data['session_time'])||!in_array($data['session_status'],['active','inactive'],true)) throw new RuntimeException('Enter a valid date, time and status.');
  }
  if($tab==='seats') {
   $price=filter_var($data['price'],FILTER_VALIDATE_INT); if($price===false||$price<0||$price>10000000||!in_array($data['status'],['available','unavailable'],true)) throw new RuntimeException('Enter a price in sen and a valid seat status.');
  }
  if($tab==='settings') {
   if(!in_array($id,['title','intro','contact','promo_percent'],true)) throw new RuntimeException('Unknown website setting.');
   if($id==='promo_percent' && (filter_var($data['setting_value'],FILTER_VALIDATE_INT)===false || (int)$data['setting_value']<0 || (int)$data['setting_value']>100)) throw new RuntimeException('Discount must be 0 to 100.');
  }
  $pdo->beginTransaction();
  if($id!=='') {
   $q=$pdo->prepare("SELECT * FROM $table WHERE $pk=? FOR UPDATE");$q->execute([$id]);$old=$q->fetch();if(!$old)throw new RuntimeException('Record not found.');
   if($tab==='sessions') {
    $q=$pdo->prepare('SELECT COUNT(*) FROM bookings WHERE session_id=?');$q->execute([$id]);
    if($q->fetchColumn()>0) throw new RuntimeException('Sessions with bookings cannot be edited. Create a new session instead.');
   }
   $sets=implode(',',array_map(fn($f)=>"$f=?",$fields));
   $q=$pdo->prepare("UPDATE $table SET $sets WHERE $pk=?");$q->execute(array_merge(array_values($data),[$id]));
  } else {
   if($tab!=='sessions') throw new RuntimeException('Choose an existing record.');
   $q=$pdo->prepare('INSERT INTO event_sessions (event_id,session_date,session_time,session_status) VALUES (?,?,?,?)');$q->execute(array_values($data));
  }
  $pdo->commit();redirectTo('admin.php?tab='.$tab.'&saved=1');
 } catch(RuntimeException $e) { if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof PDOException?'Could not save. Check for a duplicate email or invalid event ID.':$e->getMessage(); }
}
$record=null;
if($id!==null&&$id!=='') {$q=$pdo->prepare("SELECT * FROM $table WHERE $pk=?");$q->execute([$id]);$record=$q->fetch();}
if($id===''&&$tab==='sessions')$record=['event_id'=>'','session_date'=>'','session_time'=>'','session_status'=>'active'];
pageStart('Manage '.ucfirst($tab));
?>
<h1>Manage <?=h(ucfirst($tab))?></h1><?php if($tab==='users'): ?><p><a class="button" href="admin_password_reset.php">Generate password reset link</a></p><?php endif; ?><p><a href="dashboard.php">← Dashboard</a> · <a href="manage.php?entity=events">Events</a> · <a href="admin.php?tab=users">Users</a> · <a href="admin.php?tab=sessions">Sessions</a> · <a href="admin.php?tab=seats">Seats</a> · <a href="admin.php?tab=settings">Website</a> · <a href="admin.php?tab=bookings">Bookings</a></p>
<?php if(isset($_GET['saved'])): ?><p class="success">Saved successfully.</p><?php endif; ?><p class="error" role="alert"><?=h($error)?></p>
<?php if($tab==='users'): ?><p>Block or reactivate accounts and edit contact details. Public registration always creates a user account. Administrators are created through the local setup command.</p><?php endif; ?>
<?php if($tab==='sessions'): ?><p><a class="button" href="?tab=sessions&amp;id=">Add session</a></p><p>Available events: <?php foreach($pdo->query('SELECT event_id,event_name FROM events') as $ev) echo h($ev['event_id'].' = '.$ev['event_name']).' · '; ?></p><?php endif; ?>
<?php if($tab==='seats'): ?><p>Prices are stored in sen: 18000 = RM180.00. Existing tickets retain their original price.</p><?php endif; ?>
<?php if($tab==='settings'): ?><p>Edit the homepage title, introduction, support contact and welcome discount. The discount applies to newly issued rewards; each code is randomly generated.</p><?php endif; ?>
<?php if($record): ?><section class="panel"><h2>Edit record <?=h($id)?></h2><form method="post"><?php csrfField(); ?><input type="hidden" name="id" value="<?=h($id)?>">
<?php foreach($fields as $f): ?><label><?=h(ucwords(str_replace('_',' ',$f)))?></label>
<?php if(in_array($f,['status','session_status'],true)): ?><select name="<?=h($f)?>"><?php foreach($tab==='seats'?['available','unavailable']:['active',($tab==='users'?'blocked':'inactive')] as $option): ?><option <?=$record[$f]===$option?'selected':''?>><?=h($option)?></option><?php endforeach; ?></select>
<?php else: ?><input name="<?=h($f)?>" value="<?=h($record[$f])?>" maxlength="<?=$tab==='settings'?2000:150?>" type="<?=$f==='session_date'?'date':($f==='session_time'?'time':($f==='price'||$f==='event_id'?'number':'text'))?>" required><?php endif; ?>
<?php endforeach; ?><p><button>Save changes</button> <a href="?tab=<?=h($tab)?>">Cancel</a></p></form></section><?php endif; ?>
<?php
$columns=$tab==='users'? 'user_id,full_name,email,phone,role,status,created_at':'*';
$rows=$pdo->query("SELECT $columns FROM $table ORDER BY $pk DESC LIMIT 200")->fetchAll();
renderTable($rows,$tab,$pk);pageEnd();
function renderTable($rows,$tab=null,$pk=null) {
 if(!$rows){echo '<p>No records yet.</p>';return;}
 echo '<div class="table-scroll"><table><thead><tr>';
 foreach(array_keys($rows[0]) as $key)echo '<th>'.h(ucwords(str_replace('_',' ',$key))).'</th>';
 if($tab)echo '<th>Action</th>'; echo '</tr></thead><tbody>';
 foreach($rows as $row){echo '<tr>';foreach($row as $v)echo '<td>'.h($v).'</td>';if($tab)echo '<td><a href="?tab='.h($tab).'&amp;id='.h($row[$pk]).'">Edit</a></td>';echo '</tr>';}
 echo '</tbody></table></div>';
}
