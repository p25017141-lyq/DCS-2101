<?php
require __DIR__ . '/common.php';
$user = requireRole('user');
$tab = $_GET['tab'] ?? 'upcoming';
if (!in_array($tab, ['upcoming','past','all'], true)) $tab = 'upcoming';
$sql = 'SELECT b.booking_id,b.reference,b.total_amount,e.event_name,
    es.session_date,es.session_time,v.venue_name
    FROM bookings b JOIN event_sessions es ON es.session_id=b.session_id
    JOIN events e ON e.event_id=es.event_id JOIN venues v ON v.venue_id=e.venue_id
    WHERE b.user_id = ?';
$values = [$user['user_id']];
if ($tab === 'upcoming') {
    $sql .= ' AND CONCAT(es.session_date, ?, es.session_time) >= ?';
    $values[] = ' '; $values[] = date('Y-m-d H:i:s');
} elseif ($tab === 'past') {
    $sql .= ' AND CONCAT(es.session_date, ?, es.session_time) < ?';
    $values[] = ' '; $values[] = date('Y-m-d H:i:s');
}
$sql .= ' ORDER BY b.booking_id DESC';
$query = $pdo->prepare($sql); $query->execute($values); $bookings = $query->fetchAll();
pageStart('My Tickets');
?>
<h1>My Tickets</h1>
<p>Your bookings are saved to your account across logins.</p>
<div class="actions">
    <a class="button <?php if ($tab !== 'upcoming') echo 'secondary'; ?>" href="?tab=upcoming">Upcoming</a>
    <a class="button <?php if ($tab !== 'past') echo 'secondary'; ?>" href="?tab=past">Past</a>
    <a class="button <?php if ($tab !== 'all') echo 'secondary'; ?>" href="?tab=all">All</a>
</div>
<?php foreach ($bookings as $booking): ?>
    <article class="panel">
        <p class="label"><?php echo h($booking['reference']); ?></p>
        <h2><?php echo h($booking['event_name']); ?></h2>
        <p><?php echo h($booking['session_date'] . ' · ' . substr($booking['session_time'],0,5)); ?></p>
        <p><?php echo h($booking['venue_name']); ?></p>
        <p><?php echo money($booking['total_amount']); ?></p>
        <a class="button" href="ticket.php?id=<?php echo h($booking['booking_id']); ?>">View ticket</a>
    </article>
<?php endforeach; ?>
<?php if (!$bookings): ?><section class="panel"><p>No tickets here yet.</p><a href="events.php">Browse events</a></section><?php endif; ?>
<?php pageEnd(); ?>

