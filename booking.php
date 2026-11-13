<?php
require __DIR__ . '/common.php';
$user = requireRole('user');
try {
    $eventId = positiveId($_GET['event'] ?? '');
    $query = $pdo->prepare('SELECT e.*, v.venue_name, v.location FROM events e
        JOIN venues v ON v.venue_id = e.venue_id WHERE e.event_id = ? AND e.status = ?');
    $query->execute([$eventId, 'active']);
    $event = $query->fetch();
    if (!$event) throw new RuntimeException('This event was not found.');
    $query = $pdo->prepare('SELECT * FROM event_sessions WHERE event_id = ? AND session_status = ?
        AND CONCAT(session_date, ?, session_time) > ? ORDER BY session_date, session_time');
    $query->execute([$eventId, 'active', ' ', date('Y-m-d H:i:s')]);
    $sessions = $query->fetchAll();
    if (!$sessions) throw new RuntimeException('This event has no upcoming sessions.');
    $sessionId = positiveId($_GET['session'] ?? $sessions[0]['session_id']);
    $session = null;
    foreach ($sessions as $item) {
        if ((int) $item['session_id'] === $sessionId) $session = $item;
    }
    if (!$session) throw new RuntimeException('Choose a valid session for this event.');
    $query = $pdo->prepare('SELECT * FROM seats WHERE venue_id = ? ORDER BY seat_id');
    $query->execute([$event['venue_id']]);
    $seats = $query->fetchAll();
    $taken = unavailableSeatIds($pdo, $sessionId);
    $query = $pdo->prepare('SELECT promo_code, discount_percent FROM promo_codes
        WHERE status = ? AND expiry_date >= ? AND discount_percent BETWEEN 0 AND 100 AND user_id = ? AND used_at IS NULL');
    $query->execute(['active', date('Y-m-d'), $user['user_id']]);
    $promos = $query->fetchAll();
} catch (RuntimeException $error) {
    $message = $error instanceof PDOException ? 'Please check the database setup.' : $error->getMessage();
    showError($message);
}
pageStart($event['event_name']); bookingSteps(1);
?>
<a href="events.php">← Back to events</a>
<section class="panel">
    <p class="label"><?php echo h($event['category']); ?></p>
    <h1><?php echo h($event['event_name']); ?></h1>
    <p><?php echo h($event['description']); ?></p>
    <p><?php echo h($event['venue_name']); ?> · <?php echo h($event['location']); ?></p>

    <form method="get">
        <input type="hidden" name="event" value="<?php echo h($eventId); ?>">
        <label for="session">Choose a date and time</label>
        <select id="session" name="session" onchange="this.form.submit()">
            <?php foreach ($sessions as $item): ?>
                <option value="<?php echo h($item['session_id']); ?>" <?php if ($item['session_id'] == $sessionId) echo 'selected'; ?>>
                    <?php echo h($item['session_date'] . ' · ' . substr($item['session_time'], 0, 5)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <noscript><button>Choose session</button></noscript>
    </form>
    <p class="small">Choose the session first. Changing it starts a new selection.</p>
</section>

<form id="booking-form" action="checkout.php" method="post" onsubmit="return validateBooking()">
    <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
    <input type="hidden" name="event_id" value="<?php echo h($eventId); ?>">
    <input type="hidden" name="session_id" value="<?php echo h($sessionId); ?>">

    <section class="panel">
        <h2>Choose your seats</h2>
        <p>Choose 1 to 4 seats. Each seat shows its category and current price.</p>
        <div class="stage">STAGE</div>
        <div class="seat-grid" id="seat-grid">
            <?php foreach ($seats as $seat):
                $blocked = $seat['status'] !== 'available' || in_array((int) $seat['seat_id'], $taken, true);
            ?>
                <label id="seat-box-<?php echo h($seat['seat_id']); ?>" class="seat-option <?php if ($blocked) echo 'blocked'; ?>">
                    <input type="checkbox" name="seat_ids[]" value="<?php echo h($seat['seat_id']); ?>"
                        data-price="<?php echo h($seat['price']); ?>" data-label="<?php echo h($seat['seat_number']); ?>"
                        onchange="updateSeat(this)" <?php if ($blocked) echo 'disabled'; ?>>
                    <strong><?php echo h($seat['seat_number']); ?></strong>
                    <span><?php echo h($seat['category']); ?></span>
                    <span><?php echo money($seat['price']); ?></span>
                    <?php if ($blocked): ?><span>Unavailable</span><?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <p class="legend">Outlined: available · Purple: selected · Grey: unavailable</p>
        <p id="selected-seats" aria-live="polite">No seats selected</p>
        <button type="button" class="secondary" onclick="clearSelection()">Clear selection</button>
    </section>

    <section class="panel">
        <h2>Promo code</h2><p><a href="account.php" target="_blank" rel="noopener">View your personal rewards</a> · Each code can be used once.</p>
        <label for="promo-code">Optional code</label>
        <div class="promo-row">
            <input id="promo-code" name="promo_code" maxlength="30" oninput="clearPromo()" placeholder="Your personal code from My account">
            <button type="button" onclick="applyPromo()">Apply</button>
        </div>
        <select id="promo-rules" hidden aria-hidden="true">
            <?php foreach ($promos as $promo): ?>
                <option value="<?php echo h($promo['promo_code']); ?>" data-percent="<?php echo h($promo['discount_percent']); ?>"></option>
            <?php endforeach; ?>
        </select>
        <p id="promo-message" role="status"></p>
        <p id="subtotal">Subtotal: RM0.00</p>
        <p id="discount">Discount: RM0.00</p>
        <p>Booking fee: RM0.00</p>
        <p id="total" class="total" aria-live="polite">Total: RM0.00</p>
    </section>

    <section class="panel"><input type="hidden" id="full-name" name="full_name" value="<?=h($user['full_name'])?>"><input type="hidden" id="email" name="email" value="<?=h($user['email'])?>"><input type="hidden" id="phone" name="phone" value="<?=h($user['phone'])?>"><p id="name-error" class="error"></p><p id="phone-error" class="error"></p><p id="form-message" class="error" role="status"></p><button>Continue to checkout →</button></section>
</form>
<?php pageEnd(); ?>

