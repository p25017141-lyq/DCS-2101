<?php
// An event remains Upcoming while it has an active future session.
// It becomes Past only when all active sessions have passed; no active sessions = Not scheduled.
function eventCatalogueSql() {
 return "SELECT e.*,v.venue_name,v.location,dates.next_session,dates.last_session,
 (SELECT MIN(price) FROM seats WHERE venue_id=e.venue_id AND status='available') starting_price
 FROM events e JOIN venues v ON v.venue_id=e.venue_id
 LEFT JOIN (SELECT event_id,
 MIN(CASE WHEN CONCAT(session_date,' ',session_time)>NOW() THEN CONCAT(session_date,' ',session_time) END) next_session,
 MAX(CONCAT(session_date,' ',session_time)) last_session
 FROM event_sessions WHERE session_status='active' GROUP BY event_id) dates ON dates.event_id=e.event_id";
}
function eventPeriod($event) {
 if($event['next_session']!==null)return 'upcoming';
 if($event['last_session']!==null)return 'past';
 return 'unscheduled';
}
function periodCondition($period) {
 if($period==='upcoming')return 'dates.next_session IS NOT NULL';
 if($period==='past')return 'dates.next_session IS NULL AND dates.last_session IS NOT NULL';
 if($period==='unscheduled')return 'dates.last_session IS NULL';
 return '1=1';
}
function catalogueFilters($admin=false) {
 $search=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,100):'';
 $category=is_string($_GET['category']??null)?$_GET['category']:'';
 $period=is_string($_GET['period']??null)?$_GET['period']:($admin?'all':'upcoming');
 if(!in_array($period,['all','upcoming','past'],true))$period=$admin?'all':'upcoming';
 if(!in_array($category,eventCategories(),true))$category='';
 return [$search,$category,$period];
}
function catalogueForm($search,$category,$period,$target) { ?>
<form class="search panel" method="get" action="<?=h($target)?>"><label for="catalogue-search">Search events or venues<input id="catalogue-search" name="q" maxlength="100" value="<?=h($search)?>" placeholder="Event, venue or location"></label><label for="catalogue-category">Category<select id="catalogue-category" name="category"><option value="">All categories</option><?php foreach(eventCategories() as $cat): ?><option value="<?=h($cat)?>" <?=$category===$cat?'selected':''?>><?=h($cat)?></option><?php endforeach; ?></select></label><label for="catalogue-period">Event dates<select id="catalogue-period" name="period"><?php foreach(['all'=>'All dates','upcoming'=>'Upcoming','past'=>'Past'] as $value=>$label): ?><option value="<?=h($value)?>" <?=$period===$value?'selected':''?>><?=h($label)?></option><?php endforeach; ?></select></label><label for="catalogue-sort">Sort<select id="catalogue-sort" name="sort"><?php $sort=is_string($_GET['sort']??null)?$_GET['sort']:'date';foreach(['date'=>'Session date','name'=>'Event name','price'=>'Price: low to high'] as $v=>$label): ?><option value="<?=h($v)?>" <?=$sort===$v?'selected':''?>><?=h($label)?></option><?php endforeach; ?></select></label><button>Filter</button><a href="<?=h($target)?>">Reset</a></form>
<?php }
function fetchCatalogue($admin=false) {
 global $pdo;[$search,$category,$period]=catalogueFilters($admin);
 $sql=eventCatalogueSql().' WHERE '.($admin?'1=1':"e.status='active'").' AND '.periodCondition($period);$values=[];
 if($search!==''){$sql.=' AND (e.event_name LIKE ? OR v.venue_name LIKE ? OR v.location LIKE ?)';$values=array_fill(0,3,'%'.$search.'%');}
 if($category!==''){$sql.=' AND e.category=?';$values[]=$category;}
 $sort=is_string($_GET['sort']??null)?$_GET['sort']:'date';
 $sql.=$sort==='price'?' ORDER BY starting_price,e.event_id':($sort==='name'?' ORDER BY e.event_name':" ORDER BY (dates.next_session IS NULL),dates.next_session,dates.last_session DESC,e.event_id");
 $q=$pdo->prepare($sql);$q->execute($values);return $q->fetchAll();
}
