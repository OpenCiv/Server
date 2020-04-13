<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->order)) {
   send_result('Parameter missing', 400);
}

$unitId = $params->id;

// Get the unit ordered to build
$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $unitId);
if (!$query) {
   send_result('Unit not found', 400);
}

if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

// If there are no other move actions, the starting point is the unit's current location
$oldX = (int)$query[1];
$oldY = (int)$query[2];

$db->execute('DELETE FROM actions WHERE unit_id = ? AND ordering = ?', 'ii', $params->id, $params->order);
$db->execute('UPDATE actions SET ordering = ordering - 1 WHERE ordering > ?', 'i', $params->order);
$actions = get_actions();
send_result($actions);
?>