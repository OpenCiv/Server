<?php
require '../init.php';
get_player();

if (!$params || !isset($params->x) || !isset($params->y)) {
   send_result('Parameter missing', 400);
}

$queryString = 'SELECT unit.id FROM units unit INNER JOIN actions action ON unit.id = action.unit_id WHERE unit.player_id = ? AND unit.x = ? AND unit.y = ? AND action.type = ?';
$query = $db->first($queryString, 'iiis', $playerId, $params->x, $params->y, 'new');
if (!$query) {
   send_result(false);
}

$unitId = (int)$query[0];
$db->execute('DELETE FROM actions WHERE unit_id = ?', 'i', $unitId);
$db->execute('DELETE FROM units WHERE id = ?', 'i', $unitId);
$db->execute('UPDATE players SET surplus = surplus + 1 WHERE player = ?', 'i', $playerId);
send_result(true);
?>