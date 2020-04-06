<?php
require 'init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->x) || !isset($params->y)) {
   send_result('Parameter missing', 400);
}

$unitId = $params->id;
$newX = $params->x;
$newY = $params->y;

// Get the unit ordered to move
$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $unitId);
if (!$query) {
   send_result('Unit not found', 400);
}

if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

$oldX = (int)$query[1];
$oldY = (int)$query[2];

// The place of departure is the destination of the last move action
$query = $db->first("SELECT parameters FROM actions WHERE unit_id = ? AND type = 'move' ORDER BY ordering DESC", 'i', $unitId);
if ($query !== null) {
   $moveParams = explode(',', $query[0]);
   $oldX = (int)$moveParams[0];
   $oldY = (int)$moveParams[1];
}

// If the location is the same, no action is needed
if ($newX === $oldX && $newY === $oldY) {
   send_result(false);
}

// Check if the unit can reach the intended destination
$path = get_path($oldX, $oldY, $newX, $newY);
if (!$path) {
   send_result(false);
}

// Add the move action
$query = $db->first('SELECT MAX(ordering) FROM actions WHERE unit_id = ?', 'i', $unitId);
$order = $query ? (int)$query[0] + 1 : 0;
$db->execute("INSERT INTO actions (unit_id, ordering, type, parameters) VALUES (?, ?, 'move', ?)", 'iis', $unitId, $order, $newX . ',' . $newY);
send_result($order);
?>