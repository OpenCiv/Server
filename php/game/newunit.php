<?php
require '../init.php';
get_player();

if (!$params || !isset($params->x) || !isset($params->y)) {
   send_result('Parameter missing', 400);
}

$query = $db->execute('SELECT surplus FROM players WHERE id = ?', 'i', $playerId);
$surplus = (float)$query[0];
if ($surplus < 1) {
   send_result(false);
}

$queryString = 'SELECT type FROM actions WHERE unit_id IN (SELECT id FROM units WHERE player_id  = ? AND x = ? AND y = ?)';
$query = $db->execute($queryString, 'iii', $playerId, $params->x, $params->y);
$hasSettler = false;
$hasNew = false;
foreach ($query as $action) {
   $hasSettler |= $action[0] === 'settle';
   $hasNew |= $action[0] === 'new';
}

if (!$hasSettler || $hasNew) {
   send_result(false);
}

$db->execute('INSERT INTO units (player_id, x, y) VALUES (?, ?, ?)', 'iii', $playerId, $params->x, $params->y);
$query = $db->first('SELECT LAST_INSERT_ID()');
$unitId = (int)$query[0];
$db->execute("INSERT INTO actions (unit_id, ordering, type, parameter) VALUES (?, 1, 'new', '')", 'i', $unitId);
$db->execute('UPDATE players SET surplus = ? WHERE id = ?', 'di', $surplus - 1, $playerId);
send_result($unitId);
?>