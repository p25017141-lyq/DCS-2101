<?php
require __DIR__ . '/common.php';
$user = requireRole('user');
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$query = $pdo->prepare('SELECT b.*, c.full_name, c.email, c.phone, e.event_name,
    es.session_date, es.session_time, v.venue_name, v.location
    FROM bookings b JOIN customers c ON c.customer_id = b.customer_id
    JOIN event_sessions es ON es.session_id = b.session_id
    JOIN events e ON e.event_id = es.event_id
    JOIN venues v ON v.venue_id = e.venue_id
    WHERE b.booking_id = ? AND b.user_id = ?');
$query->execute([$id, $user['user_id']]);
$booking = $query->fetch();
if (!$booking) showError('Ticket not found.', 'tickets.php', 404);
$query = $pdo->prepare('SELECT s.seat_number,s.category,bs.ticket_price FROM booking_seats bs
    JOIN seats s ON s.seat_id = bs.seat_id WHERE bs.booking_id = ? ORDER BY s.seat_id');
$query->execute([$id]);
$seats = $query->fetchAll();
pageStart('Booking confirmed'); bookingSteps(4);
?>
<section class="panel ticket">
    <p class="label">BOOKING CONFIRMED</p>
    <h1><?php echo h($booking['event_name']); ?></h1>
    <p>Reference: <?php echo h($booking['reference']); ?></p>
    <p><?php echo h($booking['session_date'] . ' · ' . substr($booking['session_time'], 0, 5)); ?></p>
    <p><?php echo h($booking['venue_name'] . ' · ' . $booking['location']); ?></p>
    <p>Customer: <?php echo h($booking['full_name']); ?></p>
    <p>Email: <?php echo h($booking['email']); ?></p>
    <p>Phone: <?php echo h($booking['phone']); ?></p>
    <ul>
        <?php foreach ($seats as $seat): ?>
            <li><?php echo h($seat['seat_number'] . ' · ' . $seat['category']); ?> — <?php echo money($seat['ticket_price']); ?></li>
        <?php endforeach; ?>
    </ul>
    <p>Subtotal: <?php echo money($booking['subtotal']); ?></p>
    <p>Discount: −<?php echo money($booking['discount']); ?></p>
    <p>Booking fee: <?php echo money($booking['booking_fee']); ?></p>
    <p class="total">Total: <?php echo money($booking['total_amount']); ?></p>
    <p class="small">Booking copy · Not valid for entry. No money was charged and no email was sent.</p>
    <div class="actions">
        <button type="button" onclick="window.print()">Print / Save PDF</button>
        <a class="button secondary" href="tickets.php">My Tickets</a>
    </div>
</section>
<?php pageEnd(); ?>

