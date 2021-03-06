<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->type)) {
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

// If there are no other move actions, the starting point is the unit's current location
$oldX = (int)$query[1];
$oldY = (int)$query[2];

// Get the last action to determine the order number
$query = $db->first("SELECT ordering, type, parameter FROM actions WHERE unit_id = ? ORDER BY ordering DESC", 'i', $unitId);
if ($query !== null) {

   // Check if the intended action differs from the last action, or if the unit is just placed
   if (($query[1] === $params->type && (!$params->parameter || $query[2] === $params->parameter)) || $query[1] === 'new') {
      send_result(false);
   }

   $newOrder = (int)$query[0] + 1;
} else {
   $newOrder = 1;
}

// This will also set $oldX and $oldY to the destination of the last move action
$actions = get_actions();

// A move needs be checked for feasibility
if ($params->type === 'move') {
   if (!isset($params->parameter)) {
      send_result('Parameter missing', 400);
   }
   
   // Get the path to the intended destination
   $coordinates = explode(',', $params->parameter);
   $path = get_path($oldX, $oldY, (int)$coordinates[0], (int)$coordinates[1]);
   if (!$path) {
      send_result(false);
   }
   
   // The path is sent back to the client...
   $actions[] = ['order' => $newOrder, 'type' => 'move', 'parameter' => $path];
} else {
   $actions[] = ['order' => $newOrder, 'type' => $params->type, 'parameter' => $params->parameter];
}

//...while only the destination is stored in the database
if ($params->parameter) {
   $db->execute('INSERT INTO actions (unit_id, ordering, type, parameter) VALUES (?, ?, ?, ?)', 'iiss', $unitId, $newOrder, $params->type, $params->parameter);
} else {
   $db->execute('INSERT INTO actions (unit_id, ordering, type) VALUES (?, ?, ?)', 'iis', $unitId, $newOrder, $params->type);
}

// Send all actions
send_result($actions);
?>