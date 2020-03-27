<?php
require 'init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->x) || !isset($params->y)) {
   send_result('Parameter missing', 400);
}

// Get the unit ordered to move
$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $params->id);
if (!$query) {
   send_result('Unit not found', 400);
}

if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

$oldX = (int)$query[1];
$oldY = (int)$query[2];
$newX = $params->x;
$newY = $params->y;

if ($newX === $oldX && $newY === $oldY) {
   send_result(false);
}

// Get the map size
$query = $db->first('SELECT x, y FROM games WHERE id = ?', 'i', $gameId);
$gameX = (int)$query[0];
$gameY = (int)$query[1];

// Check if the unit is passing the date line
if ($newX >= $gameX) {
   $newX -= $gameX;
}

if ($newX < 0) {
   $newX += $gameX;
}

// Check if the unit is not off the map or is passing the date line
if ($newY < 0 || $newY >= $gameY) {
   send_result(false);
}

/* PATHFINDING */

// First get the whole map
$query = $db->execute('SELECT x, y, type FROM terrain WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {

   // The map is filled with booleans indicating whether the tiles are passable by the unit
   $map[(int)$tile[0]][(int)$tile[1]] = $tile[2] !== 'water';
}

// Check if the tile can be entered by the unit at all
if (!$map[$newX][$newY]) {
   send_result(false);
}

// The starting point, i.e. the current location of the unit, has a range of zero
$range = 0;
$map[$oldX][$oldY] = $range;

// Added are the tiles that can be reached from the given range
$added = [['x' => $oldX, 'y' => $oldY]];
do {

   // The range is one tile further than the previous step
   $range++;

   // Continue from the tiles that we were able to reach the previous step
   $origins = $added;
   $added = [];
   foreach ($origins as $tile) {

      // Check every surrounding tile
      for ($testX = $tile['x'] - 1; $testX <= $tile['x'] + 1; $testX++) {
         for ($y = $tile['y'] - 1; $y <= $tile['y'] + 1; $y++) {
            if ($y < 0 || $y >= $gameY) {
               continue;
            }

            // Date line crossing
            $x = $testX === $gameX ? 0 : ($testX === -1 ? $gameX - 1 : $testX);

            // Set the tile range if the tile is passable and has not been reached yet
            if ($map[$x][$y] === true) {
               $map[$x][$y] = $range;
               $added[] = ['x' => $x, 'y' => $y];
            }
         }
      }
   }
}
while ($map[$newX][$newY] === true && count($added) > 0);

// If the destination tile is still a boolean, the destination cannot be reached
if ($map[$newX][$newY] === true) {
   send_result(false);
}

// Find a way back from the destination to the current location of the unit
$step = $map[$newX][$newY];
$path[$step] = ['x' => $newX, 'y' => $newY];
while (--$step > 0) {
   for ($testX = $path[$step + 1]['x'] - 1; $testX <= $path[$step + 1]['x'] + 1; $testX++) {
      for ($y = $path[$step + 1]['y'] - 1; $y <= $path[$step + 1]['y'] + 1; $y++) {

         // Off the map, or the way back has been found already
         if (isset($path[$step]) || $y < 0 || $y >= $gameY) {
            continue;
         }

         // Date line crossing
         $x = $testX === $gameX ? 0 : ($testX === -1 ? $gameX - 1 : $testX);

         // If a possible way back is found, mark it
         if ($map[$x][$y] === $step) {
            $path[$step] = ['x' => $x, 'y' => $y];
         }
      }
   }
}

$path[0] = ['x' => $oldX, 'y' => $oldY];

// Set the action of the unit
$action = sprintf('move(%d,%d)', $newX, $newY);
$query = $db->execute('UPDATE units SET action = ? WHERE id = ?', 'si', $action, $params->id);
send_result($action);
?>