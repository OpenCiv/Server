<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->order)) {
   send_result('Parameter missing', 400);
}

$unitId = $params->id;

// Check if the unit exists
$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $unitId);
if (!$query) {
   send_result('Unit not found', 400);
}

// Check if the unit is owned by the player
if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

// Delete the action
$db->execute('DELETE FROM actions WHERE unit_id = ? AND ordering = ?', 'ii', $unitId, $params->order);

// This should not be necessary, but it feels neat to keep the order from 1 up to the number of orders
$orders = $db->execute('SELECT ordering FROM actions WHERE unit_id = ? ORDER BY ordering', 'i', $unitId);
for ($index = 1; $index <= count($orders); $index++) {
   if ($orders[$index] !== $index) {
      $db->execute('UPDATE actions SET ordering = ? WHERE unit_id = ? AND ordering = ?', 'iii', $index, $unitId, $orders[$index]);
   }
}

// Send the remaining actions
$actions = get_actions();
send_result($actions);
?>