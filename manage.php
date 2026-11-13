<?php
require __DIR__ . '/common.php';
// Classroom management is available only from this computer.
requireRole('admin');
$entity = $_GET['entity'] ?? 'events';
if (!in_array($entity, ['events', 'customers'], true)) $entity = 'events';
$isEvent = $entity === 'events';
$key = $isEvent ? 'event_id' : 'customer_id';
$fields = $isEvent ? ['event_name','venue_id','category','description','image','status'] : ['full_name','email','phone'];
$record = array_fill_keys($fields, '');
if ($isEvent) { $record['status'] = 'active'; $record['image'] = 'event-0.svg'; $record['category'] = 'Music'; }
$id = 0;
$error = '';
$notice = $_SESSION['management_notice'] ?? '';
unset($_SESSION['management_notice']);
$delete = false;
$venues = $pdo->query('SELECT venue_id,venue_name FROM venues ORDER BY venue_name')->fetchAll();
try {
    if (isset($_GET['id'])) {
        $id = positiveId($_GET['id']);
        $query = $pdo->prepare("SELECT * FROM $entity WHERE $key = ?");
        $query->execute([$id]);
        $found = $query->fetch();
        if (!$found) throw new RuntimeException('Record not found.');
        $record = $found;
        $delete = ($_GET['action'] ?? '') === 'delete';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrfCheck();
        $action = $_POST['action'] ?? '';
        $new = $action === 'save' && ($_POST['new_record'] ?? '') === 'yes';
        $id = $new ? 0 : positiveId($_POST['id'] ?? '');
        if ($action === 'delete') {
            if (($_POST['confirm'] ?? '') !== 'yes') throw new RuntimeException('Tick the confirmation box before deleting.');
            $pdo->beginTransaction();
            $query = $pdo->prepare("DELETE FROM $entity WHERE $key = ?");
            $query->execute([$id]);
            if ($query->rowCount() !== 1) throw new RuntimeException('Record not found.');
            $pdo->commit();
            $_SESSION['management_notice'] = 'Record deleted successfully.';
        } elseif ($action === 'save') {
            foreach ($fields as $field) $record[$field] = inputString($_POST, $field, $field === 'description' ? 5000 : 150);
            if ($isEvent) {
                if (mb_strlen($record['event_name']) < 2 || mb_strlen($record['event_name']) > 120) throw new RuntimeException('Event name must contain 2 to 120 characters.');
                $record['venue_id'] = positiveId($record['venue_id']);
                if (!in_array($record['category'], eventCategories(), true) || !in_array($record['status'], ['active','inactive'], true)) throw new RuntimeException('Choose a valid category and status.');
                if ($record['description'] === '') throw new RuntimeException('Enter an event description.');
                if (!in_array($record['image'], ['event-0.svg','event-1.svg','event-2.svg'], true)) throw new RuntimeException('Choose a listed image.');
                $query = $pdo->prepare('SELECT venue_id FROM venues WHERE venue_id = ?');
                $query->execute([$record['venue_id']]);
                if (!$query->fetch()) throw new RuntimeException('Choose an existing venue.');
            } else {
                if (mb_strlen($record['full_name']) < 2 || mb_strlen($record['full_name']) > 80) throw new RuntimeException('Name must contain 2 to 80 characters.');
                if (strlen($record['email']) > 120 || !filter_var($record['email'], FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
                if (strlen($record['phone']) > 40 || !preg_match('/^[+0-9 ().-]+$/D', $record['phone']) || strlen(preg_replace('/\D/', '', $record['phone'])) < 7 || strlen(preg_replace('/\D/', '', $record['phone'])) > 15) throw new RuntimeException('Enter a phone number containing 7 to 15 digits.');
            }
            $pdo->beginTransaction();
            // Serialize management inserts because existing event IDs are not AUTO_INCREMENT.
            $pdo->query('SELECT venue_id FROM venues ORDER BY venue_id FOR UPDATE')->fetchAll();
            if (!$new) {
                $query = $pdo->prepare("SELECT * FROM $entity WHERE $key = ? FOR UPDATE");
                $query->execute([$id]); $previous = $query->fetch();
                if (!$previous) throw new RuntimeException('Record not found.');
                if ($isEvent && (int)$previous['venue_id'] !== (int)$record['venue_id']) {
                    $query = $pdo->prepare('SELECT COUNT(*) FROM event_sessions WHERE event_id = ?');
                    $query->execute([$id]);
                    if ($query->fetchColumn() > 0) throw new RuntimeException('The venue cannot change after sessions have been created.');
                }
            }
            $query = $pdo->prepare($isEvent ? 'SELECT event_id FROM events WHERE event_name = ? AND venue_id = ? AND event_id <> ?' : 'SELECT customer_id FROM customers WHERE email = ? AND customer_id <> ?');
            $query->execute($isEvent ? [$record['event_name'], $record['venue_id'], $new ? 0 : $id] : [$record['email'], $new ? 0 : $id]);
            if ($query->fetch()) throw new RuntimeException($isEvent ? 'That event name already exists at this venue.' : 'That email already belongs to a customer record.');
            $values = []; foreach ($fields as $field) $values[] = $record[$field];
            if ($new) {
                if ($isEvent) {
                    $id = (int)$pdo->query('SELECT COALESCE(MAX(event_id),0)+1 FROM events')->fetchColumn();
                    $query = $pdo->prepare('INSERT INTO events (event_name,venue_id,category,description,image,status,event_id) VALUES (?,?,?,?,?,?,?)');
                    $values[] = $id;
                } else $query = $pdo->prepare('INSERT INTO customers (full_name,email,phone) VALUES (?,?,?)');
            } else {
                $parts = []; foreach ($fields as $field) $parts[] = "$field = ?";
                $query = $pdo->prepare("UPDATE $entity SET " . implode(',', $parts) . " WHERE $key = ?");
                $values[] = $id;
            }
            $query->execute($values); $pdo->commit();
            $_SESSION['management_notice'] = $new ? 'Record added successfully.' : 'Record updated successfully.';
        } else throw new RuntimeException('Choose a valid action.');
        header('Location: manage.php?entity=' . $entity, true, 303); exit;
    }
} catch (Throwable $problem) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $error = $problem instanceof PDOException ? 'This change could not be saved. Records linked to sessions or bookings cannot be deleted.' : $problem->getMessage();
    if (($_POST['action'] ?? '') === 'delete') $delete = true;
}
$search = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
$column = $isEvent ? 'event_name' : 'full_name';
$query = $pdo->prepare("SELECT * FROM $entity WHERE $column LIKE ? ORDER BY $key DESC");
$query->execute(['%' . $search . '%']); $rows = $query->fetchAll();
pageStart('Manage ' . ucfirst($entity));
?>
<h1>Manage <?php echo h(ucfirst($entity)); ?></h1>
<p><a href="dashboard.php">Dashboard</a> · <a href="manage.php?entity=events">Events</a> · <a href="manage.php?entity=customers">Customers</a></p>
<p class="small">Administrator workspace. Customer records are booking contact details; login accounts are managed under Users.</p>
<?php if ($notice): ?><p class="success" role="status"><?php echo h($notice); ?></p><?php endif; ?>
<?php if ($error): ?><p class="error" role="alert"><?php echo h($error); ?></p><?php endif; ?>
<section class="panel">
<h2><?php echo $delete ? 'Confirm deletion' : ($id && !isset($_POST['new_record']) ? 'Edit record' : 'Add record'); ?></h2>
<form method="post" action="manage.php?entity=<?php echo h($entity); ?>" onsubmit="return validateManagement(this)">
<input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
<input type="hidden" name="id" value="<?php echo h($id); ?>">
<input type="hidden" name="action" value="<?php echo $delete ? 'delete' : 'save'; ?>">
<?php if (!$id || isset($_POST['new_record'])): ?><input type="hidden" name="new_record" value="yes"><?php endif; ?>
<?php if ($delete): ?>
<p>Delete record #<?php echo h($id); ?>: <?php echo h($record[$column] ?? ''); ?>?</p>
<p>Deletion is blocked if other records depend on this record.</p>
<label class="check"><input type="checkbox" name="confirm" value="yes" required> I confirm this deletion.</label>
<button type="submit">Delete record</button>
<?php else: ?>
<?php foreach ($fields as $field): ?>
<label for="<?php echo h($field); ?>"><?php echo h(ucwords(str_replace('_',' ',$field))); ?></label>
<?php if ($isEvent && in_array($field,['venue_id','category','image','status'],true)): ?>
<select id="<?php echo h($field); ?>" name="<?php echo h($field); ?>" required>
<?php $options = []; if ($field === 'venue_id') { foreach ($venues as $venue) $options[$venue['venue_id']] = $venue['venue_name']; } elseif ($field === 'category') $options = array_combine(eventCategories(),eventCategories()); elseif ($field === 'status') $options = ['active'=>'Active','inactive'=>'Inactive']; else $options = ['event-0.svg'=>'Purple','event-1.svg'=>'Orange','event-2.svg'=>'Gold']; ?>
<?php foreach ($options as $value=>$label): ?><option value="<?php echo h($value); ?>" <?php if ((string)$record[$field] === (string)$value) echo 'selected'; ?>><?php echo h($label); ?></option><?php endforeach; ?>
</select>
<?php elseif ($field === 'description'): ?>
<textarea id="description" name="description" maxlength="5000" required><?php echo h($record[$field]); ?></textarea>
<?php else: ?>
<input id="<?php echo h($field); ?>" name="<?php echo h($field); ?>" type="<?php echo $field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text'); ?>" value="<?php echo h($record[$field]); ?>" maxlength="<?php echo $field === 'full_name' ? 80 : ($field === 'phone' ? 40 : 120); ?>" required>
<?php endif; endforeach; ?>
<p><button type="submit">Save record</button></p>
<?php endif; ?>
<a href="manage.php?entity=<?php echo h($entity); ?>">Cancel / New record</a>
</form></section>
<form method="get"><input type="hidden" name="entity" value="<?php echo h($entity); ?>"><label for="management-search">Search by name</label><input id="management-search" name="q" value="<?php echo h($search); ?>" maxlength="100"><button>Search</button></form>
<div class="table-scroll"><table><caption><?php echo count($rows); ?> matching record(s)</caption><thead><tr><th>ID</th><th>Name</th><th><?php echo $isEvent ? 'Category / Status' : 'Email / Phone'; ?></th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?php echo h($row[$key]); ?></td><td><?php echo h($row[$column]); ?></td><td><?php echo h($isEvent ? $row['category'].' / '.$row['status'] : $row['email'].' / '.$row['phone']); ?></td><td><a href="?entity=<?php echo h($entity); ?>&amp;id=<?php echo h($row[$key]); ?>">Edit</a> · <a href="?entity=<?php echo h($entity); ?>&amp;id=<?php echo h($row[$key]); ?>&amp;action=delete">Delete</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php pageEnd(); ?>
