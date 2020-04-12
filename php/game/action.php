<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->type) || !isset($params->parameter)) {
   send_result('Parameter missing', 400);
}

$unitId = $params->id;
$type = $params->type;

// Get the unit ordered to build
$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $unitId);
if (!$query) {
   send_result('Unit not found', 400);
}

if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

// A move needs be checked for feasibility
if ($type === 'move') {
   if (strpos($params->parameter, ',') === false) {
      send_result('Coordinates missing', 400);
   }

   $oldX = (int)$query[1];
   $oldY = (int)$query[2];
   $parameter = explode(',', $params->parameter);
   $newX = (int)$parameter[0];
   $newY = (int)$parameter[1];

   // The place of departure is the destination of the last move action
   $query = $db->first("SELECT parameters FROM actions WHERE unit_id = ? AND type = 'move' ORDER BY ordering DESC", 'i', $unitId);
   if ($query !== null) {
      $parameter = explode(',', $query[0]);
      $oldX = (int)$parameter[0];
      $oldY = (int)$parameter[1];
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

   // The path is resolved, so we can set the parameter and save the order
   $parameter = $newX . ',' . $newY;
} else {
   $parameter = $params->parameter;
}

// Get the last action
$query = $db->first('SELECT type, parameters, ordering FROM actions WHERE unit_id = ? ORDER BY ordering DESC', 'i', $unitId);
if ($query) {

   // Check if the same order is not 
   if ($query[0] === $type && $query[1] === $parameter) {
      send_result(false);
   }

   if ($query[0] === 'settle') {
      $order = (int)$query[2];
      $db->execute('UPDATE actions SET type = ?, parameters = ? WHERE unit_id = ? AND ordering = ?', 'ssii', $type, $parameter, $unitId, $order);
      send_result($order);
   }

   $order = (int)$query[2] + 1;
} else {
   $order = 1;
}

$db->execute('INSERT INTO actions (unit_id, ordering, type, parameters) VALUES (?, ?, ?, ?)', 'iiss', $unitId, $order, $type, $parameter);
if ($path) {
   $parameter = $path;
}

send_result(['order' => $order, 'type' => $type, 'parameter' => $parameter]);
?>