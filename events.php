<?php
require __DIR__ . '/common.php';
$q = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';
$category = is_string($_GET['category'] ?? null) ? $_GET['category'] : '';
$sort = is_string($_GET['sort'] ?? null) ? $_GET['sort'] : 'name';

$sql = "SELECT e.*, v.venue_name, v.location,
        (SELECT MIN(CONCAT(es.session_date,' ',es.session_time)) FROM event_sessions es WHERE es.event_id=e.event_id AND es.session_status='active' AND CONCAT(es.session_date,' ',es.session_time)>NOW()) AS next_session,
        (SELECT MIN(price) FROM seats WHERE venue_id = e.venue_id AND status = ?) AS starting_price
        FROM events e JOIN venues v ON v.venue_id = e.venue_id WHERE e.status = ?";
$values = ['available', 'active'];
if ($q !== '') {
    $sql .= ' AND (e.event_name LIKE ? OR v.venue_name LIKE ?)';
    $values[] = '%' . $q . '%';
    $values[] = '%' . $q . '%';
}
if ($category !== '') {
    $sql .= ' AND e.category = ?';
    $values[] = $category;
}
if ($sort === 'price') {
    $sql .= ' ORDER BY starting_price, e.event_id';
} else {
    $sql .= ' ORDER BY e.event_name';
}
$query = $pdo->prepare($sql);
$query->execute($values);
$events = $query->fetchAll();
pageStart('Browse Events');
?>
<h1>Browse Events</h1>
<p>Find an event, choose a session and pick your seats.</p>
<form class="search" method="get">
    <label>Search
        <input name="q" value="<?php echo h($q); ?>" maxlength="100" placeholder="Event or venue">
    </label>
    <label>Category
        <select name="category">
            <option value="">All</option>
            <?php foreach(eventCategories() as $cat): ?><option value="<?=h($cat)?>" <?=$category===$cat?'selected':''?>><?=h($cat)?></option><?php endforeach; ?>
        </select>
    </label>
    <label>Sort
        <select name="sort">
            <option value="name">Name</option>
            <option value="price" <?php if ($sort === 'price') echo 'selected'; ?>>Price: low to high</option>
        </select>
    </label>
    <button type="submit">Search</button>
    <a href="events.php">Reset</a>
</form>
<p><?php echo count($events); ?> event(s) found</p>
<div class="cards">
    <?php foreach ($events as $event): ?>
        <article class="card">
            <img src="assets/<?php echo h($event['image']); ?>" alt="">
            <div class="card-body">
                <p class="label"><?php echo h($event['category']); ?></p>
                <h2><?php echo h($event['event_name']); ?></h2>
                <p><?php echo h($event['venue_name']); ?> · <?php echo h($event['location']); ?></p>
                <p><?=h($event['next_session'] ?? 'More dates coming soon')?></p>
                <p>From <?php echo money($event['starting_price']); ?></p>
                <a class="button" href="event.php?event=<?php echo h($event['event_id']); ?>">View event</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php if (!$events): ?><p>No events found. Try another search.</p><?php endif; ?>
<?php pageEnd(); ?>

