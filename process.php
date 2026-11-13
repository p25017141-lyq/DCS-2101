<?php
require __DIR__ . '/common.php';
$user = requireRole('user');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showError('Please submit the review form.', 'events.php', 405);
}
csrfCheck();
$token = $_POST['checkout_token'] ?? '';
if (!is_string($token)) showError('Please review your booking again.');
if (!isset($_SESSION['pending'])) {
    if (isset($_SESSION['last_token']) && hash_equals($_SESSION['last_token'], $token)) {
        header('Location: ticket.php?id=' . $_SESSION['last_booking_id'], true, 303);
        exit;
    }
    showError('Please start a new booking.');
}
if (!hash_equals($_SESSION['checkout_token'], $token)) {
    showError('A newer booking was opened. Please review that booking before confirming.');
}
if (($_POST['payment_consent'] ?? '') !== 'yes') {
    showError('Please acknowledge the simulated payment notice.');
}
try {
    $pdo->beginTransaction();
    $pending = $_SESSION['pending'];
    $query = $pdo->prepare('SELECT session_id FROM event_sessions WHERE session_id = ? FOR UPDATE');
    $query->execute([$pending['input']['session_id']]);
    if (!$query->fetch()) throw new RuntimeException('This session is not available.');
    $query = $pdo->prepare('SELECT booking_id FROM bookings WHERE visitor_key = ? AND checkout_token = ?');
    $query->execute([$_SESSION['visitor'], $token]);
    $id = $query->fetchColumn();
    if (!$id) {
        // Lock the account first: two different sessions cannot spend one reward together.
        $q=$pdo->prepare('SELECT user_id FROM users WHERE user_id=? FOR UPDATE');
        $q->execute([$user['user_id']]);
        $selection = checkSelection($pdo, $pending['input']);
        if ($selection['total'] !== $pending['total']) {
            throw new RuntimeException('The price changed. Please select and review again.');
        }

        $customerId = $_SESSION['customer_id'] ?? 0;
        $query = $pdo->prepare('SELECT customer_id FROM customers WHERE customer_id = ? AND full_name = ? AND email = ? AND phone = ?');
        $query->execute([$customerId, $selection['full_name'], $selection['email'], $selection['phone']]);
        if (!$query->fetchColumn()) {
            $query = $pdo->prepare('INSERT INTO customers (full_name,email,phone) VALUES (?,?,?)');
            $query->execute([$selection['full_name'], $selection['email'], $selection['phone']]);
            $customerId = (int) $pdo->lastInsertId();
        }
        $promoId = null;
        if ($selection['promo']) $promoId = $selection['promo']['promo_id'];
        if ($promoId) {
            $q=$pdo->prepare('UPDATE promo_codes SET used_at=NOW() WHERE promo_id=? AND user_id=? AND used_at IS NULL');
            $q->execute([$promoId,$user['user_id']]);
            if ($q->rowCount()!==1) throw new RuntimeException('This promo has already been used.');
        }
        $reference = 'ES-' . strtoupper(bin2hex(random_bytes(6)));
        $query = $pdo->prepare('INSERT INTO bookings
            (reference,visitor_key,checkout_token,customer_id,session_id,promo_id,subtotal,discount,booking_fee,total_amount,user_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $query->execute([$reference, $_SESSION['visitor'], $token, $customerId,
            $selection['session']['session_id'], $promoId, $selection['subtotal'],
            $selection['discount'], $selection['fee'], $selection['total'], $user['user_id']]);
        $id = (int) $pdo->lastInsertId();
        $query = $pdo->prepare('INSERT INTO booking_seats (booking_id,session_id,seat_id,ticket_price) VALUES (?,?,?,?)');
        foreach ($selection['seats'] as $seat) {
            $query->execute([$id, $selection['session']['session_id'], $seat['seat_id'], $seat['price']]);
        }
    }
    $pdo->commit();
    if (isset($customerId)) $_SESSION['customer_id'] = $customerId;
    $_SESSION['last_token'] = $token;
    $_SESSION['last_booking_id'] = $id;
    unset($_SESSION['pending'], $_SESSION['checkout_token']);
    header('Location: ticket.php?id=' . $id, true, 303);
    exit;
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $message = $error->getMessage();
    if ($error instanceof PDOException) {
        $message = 'The booking could not be saved. A seat may have just been booked. Please select again.';
    }
    showError($message);
}

