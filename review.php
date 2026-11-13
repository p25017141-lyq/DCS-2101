<?php
require __DIR__ . '/common.php';
$user = requireRole('user');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showError('Please choose an event and use the booking form.', 'events.php', 405);
}
csrfCheck();
try {
    $selection = checkSelection($pdo, $_POST);
} catch (RuntimeException $error) {
    $message = $error instanceof PDOException ? 'Please check the database setup.' : $error->getMessage();
    showError($message);
}
$_SESSION['pending'] = $selection;
$_SESSION['checkout_token'] = bin2hex(random_bytes(16));
pageStart('Review booking'); bookingSteps(3);
?>
<section class="panel">
    <h1>Review your booking</h1>
    <?php bookingSummary($selection); ?>
    <h2>Your details</h2>
    <p>Name: <?php echo h($selection['full_name']); ?></p>
    <p>Email: <?php echo h($selection['email']); ?></p>
    <p>Phone: <?php echo h($selection['phone']); ?></p>
    <p class="notice">Simulated payment — no money will be charged and no email is sent.</p>
    <form action="process.php" method="post">
        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
        <input type="hidden" name="checkout_token" value="<?php echo h($_SESSION['checkout_token']); ?>">
        <label class="check"><input type="checkbox" name="payment_consent" value="yes" required> I understand that no money will be charged.</label>
        <button type="submit">Confirm booking</button>
    </form>
    <p><a href="booking.php?event=<?php echo h($selection['event']['event_id']); ?>&amp;session=<?php echo h($selection['session']['session_id']); ?>">Start selection again</a></p>
</section>
<?php pageEnd(); ?>

