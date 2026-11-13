<?php
// Enable only after configuring PHP's mail transport. Never trust the HTTP Host header for reset URLs.
return [
 'email_enabled' => false,
 'base_url' => 'http://localhost/EventSeat',
 'from_email' => 'noreply@example.com',
];
