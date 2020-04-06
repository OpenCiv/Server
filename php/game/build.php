<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->improvement)) {
   send_result('Parameter missing', 400);
}

$unitId = $params->id;
$improvement = $params->improvement;

// Get the unit ordered to build
$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $unitId);
if (!$query) {
   send_result('Unit not found', 400);
}

if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

$query = $db->first('SELECT type, parameters, ordering FROM actions WHERE unit_id = ? ORDER BY ordering DESC', 'i', $unitId);
if ($query) {
   if ($query[0] === 'build' && $query[1] === $improvement) {
      send_result(false);
   }

   $order = (int)$query[2] + 1;
} else {
   $order = 1;
}

$db->execute("INSERT INTO actions (unit_id, ordering, type, parameters) VALUES (?, ?, 'build', ?)", 'iis', $unitId, $order, $improvement);
send_result($order);
?>