<?php require __DIR__.'/common.php';requireRole('admin');pageStart('Manage'); ?>
<p class="label">ADMIN · MANAGEMENT WORKSPACE</p><h1>Manage</h1><p>Choose the records you want to maintain. Use Users and Events in the navigation to view their lists.</p><div class="cards"><?php foreach([
 ['manage.php?entity=events','Manage events','Create, edit and delete event records.'],
 ['admin.php?tab=users','Manage users','Update account details and block or reactivate users.'],
 ['admin.php?tab=sessions','Manage sessions','Add event dates and times.'],
 ['venues.php','Manage venues','Create, edit or delete venues and generate seats.'],
 ['admin.php?tab=seats','Manage seats','Adjust seat prices and availability.'],
 ['admin.php?tab=settings','Website settings','Edit homepage text and welcome reward settings.'],
 ['manage.php?entity=customers','Booking contacts','Maintain customer contact records.'],
 ['admin_password_reset.php','Password recovery','Generate a reset link for a verified account owner.']
] as [$url,$title,$description]): ?><section class="panel management-card"><h2><?=h($title)?></h2><p><?=h($description)?></p><a class="button" href="<?=h($url)?>">Open</a></section><?php endforeach; ?></div><?php pageEnd(); ?>
