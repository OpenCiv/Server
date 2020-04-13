<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->type) || !isset($params->parameter)) {
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

$query = $db->first("SELECT ordering, type, parameter FROM actions WHERE unit_id = ? ORDER BY ordering DESC", 'i', $unitId);
if ($query !== null) {

   // Check if the intended action differs from the last action
   if ($query[1] === $params->type && $query[2] === $params->parameter) {
      send_result(false);
   }

   $newOrder = (int)$query[0] + 1;
} else {
   $newOrder = 1;
}

// This will set $oldX and $oldY to the destination of the last move action
$actions = get_actions();

// A move needs be checked for feasibility
if ($params->type === 'move') {

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
$db->execute('INSERT INTO actions (unit_id, ordering, type, parameter) VALUES (?, ?, ?, ?)', 'iiss', $unitId, $newOrder, $params->type, $params->parameter);

send_result($actions);
?>