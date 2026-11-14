<?php require __DIR__.'/common.php';requireRole('admin');
$search=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,100):'';
$status=is_string($_GET['status']??null)?$_GET['status']:'';if(!in_array($status,['active','blocked'],true))$status='';
$role=is_string($_GET['role']??null)?$_GET['role']:'user';if(!in_array($role,['user','admin','all'],true))$role='user';
$sql='SELECT user_id,full_name,email,phone,role,status,created_at FROM users WHERE 1=1';$params=[];
if($role!=='all'){$sql.=' AND role=?';$params[]=$role;}
if($status!==''){$sql.=' AND status=?';$params[]=$status;}
if($search!==''){$sql.=' AND (full_name LIKE ? OR email LIKE ?)';$params[]='%'.$search.'%';$params[]='%'.$search.'%';}
$q=$pdo->prepare($sql.' ORDER BY user_id DESC');$q->execute($params);$rows=$q->fetchAll();
$total=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();pageStart('User list'); ?>
<p class="label">ADMIN · VIEW RECORDS</p><h1>Users</h1><p><strong><?=$total?></strong> registered users in total. Showing <?=count($rows)?> account(s) matching your filters.</p>
<form class="search panel" method="get"><label for="user-search">Search name or email<input id="user-search" name="q" maxlength="100" value="<?=h($search)?>"></label><label for="user-role">Account type<select id="user-role" name="role"><?php foreach(['user'=>'Users','admin'=>'Administrators','all'=>'All accounts'] as $v=>$label): ?><option value="<?=h($v)?>" <?=$role===$v?'selected':''?>><?=h($label)?></option><?php endforeach; ?></select></label><label for="user-status">Status<select id="user-status" name="status"><option value="">All statuses</option><?php foreach(['active','blocked'] as $v): ?><option <?=$status===$v?'selected':''?>><?=h($v)?></option><?php endforeach; ?></select></label><button>Filter</button><a href="admin_users.php">Reset</a></form>
<div class="table-scroll"><table><caption>User directory · View only</caption><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Registered</th></tr></thead><tbody><?php foreach($rows as $u): ?><tr><?php foreach($u as $v): ?><td><?=h($v)?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7">No accounts match these filters.</td></tr><?php endif; ?></tbody></table></div><p>To edit accounts, open <a href="admin_manage.php">Manage</a> → Manage users.</p><?php pageEnd(); ?>
