<?php require __DIR__.'/common.php';requireRole('admin');$error='';$id=0;$record=['venue_name'=>'','location'=>'','capacity'=>'12'];
try {
 if(isset($_GET['id'])) {$id=positiveId($_GET['id']);$q=$pdo->prepare('SELECT * FROM venues WHERE venue_id=?');$q->execute([$id]);$record=$q->fetch();if(!$record)throw new RuntimeException('Venue not found.');}
 if($_SERVER['REQUEST_METHOD']==='POST'){
  csrfCheck();$id=(int)($_POST['id']??0);$action=inputString($_POST,'action',20);$pdo->beginTransaction();
  if($id){$q=$pdo->prepare('SELECT venue_id FROM venues WHERE venue_id=? FOR UPDATE');$q->execute([$id]);if(!$q->fetch())throw new RuntimeException('Venue not found.');}
  if($action==='delete'){
   if(!$id||($_POST['confirm']??'')!=='yes')throw new RuntimeException('Confirm deletion first.');
   $q=$pdo->prepare('SELECT COUNT(*) FROM events WHERE venue_id=?');$q->execute([$id]);if($q->fetchColumn())throw new RuntimeException('This venue is used by an event. Remove that dependency first.');
   $pdo->prepare('DELETE FROM seats WHERE venue_id=?')->execute([$id]);$pdo->prepare('DELETE FROM venues WHERE venue_id=?')->execute([$id]);
  }elseif($action==='save'){
   $name=inputString($_POST,'venue_name',120);$location=inputString($_POST,'location',150);$capacity=filter_var($_POST['capacity']??'',FILTER_VALIDATE_INT);
   if(mb_strlen($name)<2||mb_strlen($location)<2||$capacity===false||$capacity<1||$capacity>200)throw new RuntimeException('Enter a name, location and capacity of 1–200 seats.');
   $q=$pdo->prepare('SELECT venue_id FROM venues WHERE venue_name=? AND location=? AND venue_id<>?');$q->execute([$name,$location,$id]);if($q->fetch())throw new RuntimeException('This venue already exists.');
   if($id){
    $q=$pdo->prepare('SELECT COUNT(*) FROM seats WHERE venue_id=?');$q->execute([$id]);if((int)$q->fetchColumn()!==$capacity)throw new RuntimeException('Capacity must match the existing seat count. Create a new venue for a different layout.');
    $pdo->prepare('UPDATE venues SET venue_name=?,location=?,capacity=? WHERE venue_id=?')->execute([$name,$location,$capacity,$id]);
   }else{
    $pdo->prepare('INSERT INTO venues(venue_name,location,capacity) VALUES(?,?,?)')->execute([$name,$location,$capacity]);$id=(int)$pdo->lastInsertId();
    $q=$pdo->prepare('INSERT INTO seats(venue_id,seat_number,category,price,status) VALUES(?,?,?,?,?)');
    for($i=0;$i<$capacity;$i++){$row=(int)floor($i/8);$cat=$row===0?'VIP':($row===1?'Premium':'Standard');$price=$row===0?18000:($row===1?15000:13000);$q->execute([$id,chr(65+$row).($i%8+1),$cat,$price,'available']);}
   }
  }else throw new RuntimeException('Unknown action.');
  $pdo->commit();redirectTo('venues.php?saved=1');
 }
}catch(RuntimeException $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof PDOException?'Could not save this venue. Referenced records are protected.':$e->getMessage();}
$qtext=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,100):'';$q=$pdo->prepare('SELECT * FROM venues WHERE venue_name LIKE ? OR location LIKE ? ORDER BY venue_id DESC');$q->execute(['%'.$qtext.'%','%'.$qtext.'%']);$rows=$q->fetchAll();pageStart('Manage venues'); ?>
<h1>Manage venues</h1><p><a href="dashboard.php">← Admin dashboard</a></p><p class="error" role="alert"><?=h($error)?></p><?php if(isset($_GET['saved'])): ?><p class="success">Venue saved successfully.</p><?php endif; ?>
<section class="panel"><h2><?=$id?'Edit venue':'Create venue'?></h2><form method="post"><?php csrfField(); ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($id)?>"><label for="venue-name">Venue name</label><input id="venue-name" name="venue_name" value="<?=h($record['venue_name']??'')?>" maxlength="120" required><label for="venue-location">Location</label><input id="venue-location" name="location" value="<?=h($record['location']??'')?>" maxlength="150" required><label for="capacity">Capacity</label><input id="capacity" name="capacity" type="number" min="1" max="200" value="<?=h($record['capacity']??12)?>" required><p class="small">New venues receive labelled seats automatically, up to eight per row. First row VIP, second Premium, remaining rows Standard. Adjust prices under Seat prices. Existing seat layouts retain their capacity.</p><button>Save venue</button> <a href="venues.php">New / Cancel</a></form></section>
<form method="get"><label for="venue-search">Search venues or locations</label><input id="venue-search" name="q" value="<?=h($qtext)?>"><p><button>Search</button></p></form><div class="table-scroll"><table><thead><tr><th>ID</th><th>Venue</th><th>Location</th><th>Capacity</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?=h($r['venue_id'])?></td><td><?=h($r['venue_name'])?></td><td><?=h($r['location'])?></td><td><?=h($r['capacity'])?></td><td><a href="?id=<?=h($r['venue_id'])?>">Edit</a><details><summary>Delete</summary><p>Delete this venue and its seats? Events using this venue will prevent deletion.</p><form method="post" onsubmit="return confirm('Delete this venue and its seats?')"><?php csrfField(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=h($r['venue_id'])?>"><label class="check"><input type="checkbox" name="confirm" value="yes" required>I confirm deletion</label><button>Delete venue</button></form></details></td></tr><?php endforeach; ?></tbody></table></div><?php pageEnd(); ?>
