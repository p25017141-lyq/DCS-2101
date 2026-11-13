<?php
require __DIR__ . '/common.php';
requireRole('admin');
$totals = [];
foreach (['events','customers','bookings','booking_seats'] as $table) $totals[$table] = (int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
$query = $pdo->prepare('SELECT COUNT(*) FROM event_sessions WHERE session_status = ? AND CONCAT(session_date, ?, session_time) > ?');
$query->execute(['active',' ',date('Y-m-d H:i:s')]);
$upcoming = $query->fetchColumn();
pageStart('Dashboard');
?>
<h1>Management dashboard</h1><p><a class="button" href="venues.php">Manage venues</a></p><div class="actions"><a class="button" href="admin.php?tab=users">Users</a><a class="button" href="admin.php?tab=sessions">Sessions</a><a class="button" href="admin.php?tab=seats">Seat prices</a><a class="button" href="admin.php?tab=settings">Website settings</a><a class="button" href="admin.php?tab=bookings">Bookings</a></div>
<p>Live overview of your events and bookings.</p>
<div class="actions"><a class="button" href="manage.php?entity=events">Manage Events</a><a class="button" href="manage.php?entity=customers">Manage Customers</a></div>
<div class="cards">
<?php foreach ($totals as $label=>$total): ?><section class="panel"><h2><?php echo h(ucwords(str_replace('_',' ',$label))); ?></h2><p class="total"><?php echo h($total); ?></p></section><?php endforeach; ?>
<section class="panel"><h2>Upcoming sessions</h2><p class="total"><?php echo h($upcoming); ?></p></section>
</div>
<p>Create events, then add their sessions below. Referenced records are protected from deletion.</p>
<?php pageEnd(); ?>
