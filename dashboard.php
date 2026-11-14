<?php require __DIR__.'/common.php';requireRole('admin');require __DIR__.'/event_catalogue.php';
$totalUsers=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalAdmins=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$totalEvents=(int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$totalBookings=(int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$counts=['upcoming'=>0,'past'=>0,'unscheduled'=>0];foreach($pdo->query(eventCatalogueSql()) as $event)$counts[eventPeriod($event)]++;
pageStart('Admin dashboard'); ?>
<p class="label">ADMIN OVERVIEW</p><h1>Dashboard</h1><p>See your totals, then open Users or Events to view the full lists.</p>
<div class="cards">
<a class="panel metric" href="admin_users.php"><span>Registered users</span><strong><?=$totalUsers?></strong><span>View user list →</span></a>
<a class="panel metric" href="admin_events.php"><span>Total events</span><strong><?=$totalEvents?></strong><span>View event list →</span></a>
<a class="panel metric" href="admin.php?tab=bookings"><span>Bookings</span><strong><?=$totalBookings?></strong><span>View bookings →</span></a>
</div><h2>Event overview</h2><div class="cards"><?php foreach(['upcoming'=>'Upcoming events','past'=>'Past events'] as $key=>$label): ?><a class="panel metric" href="admin_events.php?period=<?=h($key)?>"><span><?=h($label)?></span><strong><?=$counts[$key]?></strong><span>View list →</span></a><?php endforeach; ?></div>
<p><?=$totalAdmins?> administrator account(s). Registered users counts customer login accounts, including blocked users.</p><section class="panel"><h2>Management workspace</h2><p>Add, edit or delete records and maintain the website in the separate Manage area.</p><a class="button" href="admin_manage.php">Open Manage</a></section><?php pageEnd(); ?>
