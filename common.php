<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
ini_set('display_errors', '0');
session_name('eventseat_proposal');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
]);
if (empty($_SESSION['visitor'])) {
    $_SESSION['visitor'] = bin2hex(random_bytes(32));
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/auth.php';

function h($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}
function money($sen) {
    return 'RM' . number_format($sen / 100, 2);
}
function pageStart($title) {
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . h($title) . ' | EventSeat</title><link rel="stylesheet" href="style.css"></head><body>';
    echo '<header><a class="logo" href="index.php">Event<span>Seat</span></a><nav><a href="events.php">Events</a>';
    $user=currentUser();
    if ($user && $user['role']==='admin') echo '<a href="dashboard.php">Admin dashboard</a><a href="admin.php?tab=users">Users</a>';
    elseif ($user) echo '<a href="tickets.php">My tickets</a><a href="account.php">My account & rewards</a>';
    if ($user) echo '<form method="post" action="logout.php" class="inline"><input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'"><button class="secondary">Log out</button></form>';
    else echo '<a href="login.php">Log in</a><a class="button" href="register.php">Create account</a>';
    echo '</nav></header><main>';

}
function pageEnd() {
    echo '</main><footer>EventSeat · Prices in MYR</footer><script src="script.js"></script></body></html>';
}
function showError($message, $back = 'events.php', $status = 422) {
    http_response_code($status);
    pageStart('Please check');
    echo '<section class="panel"><h1>Please check your booking</h1>';
    echo '<p role="alert">' . h($message) . '</p><a class="button" href="' . h($back) . '">Go back</a></section>';
    pageEnd();
    exit;
}
function csrfCheck() {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        showError('Your form has expired. Please open the booking page again.', 'events.php', 403);
    }
}
function inputString($data, $key, $maximum) {
    $value = $data[$key] ?? '';
    if (!is_string($value)) {
        throw new RuntimeException('Please check the ' . $key . ' field.');
    }
    $value = trim($value);
    if (mb_strlen($value) > $maximum || preg_match('/[\\x00-\\x1F\\x7F]/', $value)) {
        throw new RuntimeException('Please check the ' . $key . ' field.');
    }
    return $value;
}
function positiveId($value) {
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        throw new RuntimeException('Please choose a valid event or session.');
    }
    return $id;
}
function unavailableSeatIds($pdo, $sessionId) {
    $query = $pdo->prepare('SELECT seat_id FROM booking_seats WHERE session_id = ?');
    $query->execute([$sessionId]);
    $ids = [];
    foreach ($query as $row) {
        $ids[] = (int) $row['seat_id'];
    }
    return $ids;
}

function checkSelection($pdo, $data) {
    $eventId = positiveId($data['event_id'] ?? '');
    $sessionId = positiveId($data['session_id'] ?? '');
    $name = inputString($data, 'full_name', 80);
    $email = inputString($data, 'email', 120);
    $phone = inputString($data, 'phone', 40);
    $promoCode = strtoupper(inputString($data, 'promo_code', 30));
    if (mb_strlen($name) < 2) {
        throw new RuntimeException('Enter your full name with at least two characters.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Enter a valid email address.');
    }
    $digits = preg_replace('/\\D/', '', $phone);
    if (!preg_match('/^[+0-9 ().-]+$/D', $phone) || strlen($digits) < 7 || strlen($digits) > 15) {
        throw new RuntimeException('Enter a phone number with 7 to 15 digits.');
    }

    $query = $pdo->prepare('SELECT e.*, v.venue_name, v.location FROM events e
        JOIN venues v ON v.venue_id = e.venue_id WHERE e.event_id = ? AND e.status = ?');
    $query->execute([$eventId, 'active']);
    $event = $query->fetch();
    if (!$event) {
        throw new RuntimeException('This event is not available.');
    }
    $query = $pdo->prepare('SELECT * FROM event_sessions WHERE session_id = ? AND event_id = ? AND session_status = ?');
    $query->execute([$sessionId, $eventId, 'active']);
    $session = $query->fetch();
    if (!$session || strtotime($session['session_date'] . ' ' . $session['session_time']) <= time()) {
        throw new RuntimeException('Please choose an upcoming session.');
    }

    $selected = $data['seat_ids'] ?? [];
    if (!is_array($selected) || count($selected) < 1 || count($selected) > 4) {
        throw new RuntimeException('Please select 1 to 4 seats.');
    }
    $ids = [];
    foreach ($selected as $value) {
        $id = positiveId($value);
        if (in_array($id, $ids, true)) {
            throw new RuntimeException('The same seat cannot be selected twice.');
        }
        $ids[] = $id;
    }
    sort($ids);
    $places = implode(',', array_fill(0, count($ids), '?'));
    $query = $pdo->prepare('SELECT * FROM seats WHERE venue_id = ? AND seat_id IN (' . $places . ') ORDER BY seat_id');
    $query->execute(array_merge([$event['venue_id']], $ids));
    $seats = $query->fetchAll();
    if (count($seats) !== count($ids)) {
        throw new RuntimeException('A selected seat does not belong to this venue.');
    }
    $taken = unavailableSeatIds($pdo, $sessionId);
    $subtotal = 0;
    foreach ($seats as $seat) {
        if ($seat['status'] !== 'available' || in_array((int) $seat['seat_id'], $taken, true)) {
            throw new RuntimeException('A selected seat is no longer available. Please choose again.');
        }
        $subtotal += (int) $seat['price'];
    }

    $promo = null;
    $discount = 0;
    if ($promoCode !== '') {
        $query = $pdo->prepare('SELECT * FROM promo_codes WHERE promo_code = ? AND status = ? AND expiry_date >= ? AND user_id = ? AND used_at IS NULL');
        $query->execute([$promoCode, 'active', date('Y-m-d'), $_SESSION['user_id']]);
        $promo = $query->fetch();
        if (!$promo || $promo['discount_percent'] < 0 || $promo['discount_percent'] > 100) {
            throw new RuntimeException('This code is invalid, expired, already used, or belongs to another account.');
        }
        $discount = (int) round($subtotal * $promo['discount_percent'] / 100);
    }
    return [
        'event' => $event, 'session' => $session, 'seats' => $seats,
        'full_name' => $name, 'email' => $email, 'phone' => $phone,
        'promo' => $promo, 'subtotal' => $subtotal, 'discount' => $discount,
        'fee' => 0, 'total' => $subtotal - $discount,
        'input' => ['event_id' => $eventId, 'session_id' => $sessionId,
                    'seat_ids' => $ids, 'full_name' => $name,
                    'email' => $email, 'phone' => $phone, 'promo_code' => $promoCode]
    ];
}

function bookingSummary($selection) {
    $labels = [];
    foreach ($selection['seats'] as $seat) {
        $labels[] = $seat['seat_number'] . ' (' . $seat['category'] . ')';
    }
    echo '<h2>' . h($selection['event']['event_name']) . '</h2>';
    echo '<p>' . h($selection['event']['venue_name']) . ' · ' . h($selection['event']['location']) . '</p>';
    echo '<p>' . h($selection['session']['session_date']) . ' · ' . h(substr($selection['session']['session_time'], 0, 5)) . '</p>';
    echo '<p>Seats: ' . h(implode(', ', $labels)) . '</p>';
    echo '<p>Subtotal: ' . money($selection['subtotal']) . '</p>';
    echo '<p>Discount: −' . money($selection['discount']) . '</p>';
    echo '<p>Booking fee: ' . money($selection['fee']) . '</p>';
    echo '<p class="total">Total: ' . money($selection['total']) . '</p>';
}

function unexpectedError($error) {
    showError('The request could not be completed. Please check the database setup and try again.', 'index.html', 503);
}
set_exception_handler('unexpectedError');

try {
    require __DIR__ . '/db.php';
} catch (PDOException $error) {
    showError('Database unavailable. Start MySQL and check the database settings in db.php.', 'index.html', 503);
}

