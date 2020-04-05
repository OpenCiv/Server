<?php
require 'init.php';
get_player();

$db->execute('UPDATE players SET finished = 1 WHERE id = ?', 'i', $playerId);

// Check if other players still need to finish their turn
$query = $db->first('SELECT EXISTS (SELECT * FROM players WHERE finished = 0 AND id = ?)', 'i', $gameId);
if ($query[0]) {
   send_result(false);
}

get_map();

// Get all units in the game
$query = $db->execute('SELECT id, x, y FROM units WHERE player_id IN (SELECT id FROM players WHERE game_id = ?)', 'i', $gameId);
$units = [];
foreach ($query as $row) {
   $units[(int)$row[0]] = ['x' => (int)$row[1], 'y' => (int)$row[2]];
}

// Resolve all unit actions
foreach ($units as $unitId => $unit) {
   $query = $db->first('SELECT type, parameters FROM actions WHERE ordering = 1 AND unit_id = ?', 'i', $unitId);
   if (!$query) {
      continue;
   }

   $type = $query[0];
   $parameter = $query[1];

   // The result is true if the action is complete
   // The result is null if the action was successful, but not yet complete
   // The result is false if the action could not be executed
   $result = false;
   switch ($type) {
      case 'move':
         $destination = explode(',', $parameter);
         $path = get_path($map, $unit['x'], $unit['y'], (int)$destination[0], (int)$destination[1]);
         if ($path) {
            $db->execute('UPDATE units SET x = ?, y = ? WHERE id = ?', 'iii', $path[0]['x'], $path[0]['y'], $unitId);

            // If the path consists of only one step, the unit has reached its destination
            $result = count($path) === 1 ? true : null;
         }
      break;

      case 'build':
         $query = $db->first('SELECT type, completion FROM improvements WHERE game_id = ? AND x = ? AND y = ?', 'iii', $gameId, $unit['x'], $unit['y']);
         if (!$query) {
            $db->execute('INSERT INTO improvements (game_id, x, y, type, completion) VALUES (?, ?, ?, ?, ?)', 'iiisd', $gameId, $unit['x'], $unit['y'], $parameter, 0.2);
            $result = null;
         } elseif ($query[0] === $parameter) {
            $completion = min(1, (float)$query[1] + 0.2);
            $db->execute('UPDATE improvements SET completion = ? WHERE game_id = ? AND x = ? AND y = ?', 'diii', $completion, $gameId, $unit['x'], $unit['y']);
            $result = $completion === 1 ? true : null;
         }
      break;
   }

   // If the action was completed, it will be removed and the other actions move up a step
   if ($result === true) {
      $db->execute('DELETE FROM actions WHERE ordering = 1 AND unit_id = ?', 'i', $unitId);
      $db->execute('UPDATE actions SET ordering = ordering - 1 WHERE unit_id = ?', 'i', $unitId);

   // If the action could not be executed, all other actions will be cancelled
   } elseif ($result === false) {
      $db->execute('DELETE FROM actions WHERE unit_id = ?', 'i', $unitId);
   }
}

$db->execute('UPDATE players SET finished = 0 WHERE game_id = ?', 'i', $gameId);
$db->execute('UPDATE games SET turn = turn + 1 WHERE id = ?', 'i', $gameId);
send_result(true);
?>