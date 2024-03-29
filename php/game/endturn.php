<?php
require '../init.php';
get_player();

$db->execute('UPDATE players SET finished = 1 WHERE id = ?', 'i', $playerId);

// Check if other players still need to finish their turn
$query = $db->first('SELECT EXISTS (SELECT * FROM players WHERE finished = 0 AND id = ?)', 'i', $gameId);
if ($query[0]) {
   send_result(false);
}

$query = $db->first('SELECT turn FROM games WHERE id = ?', 'i', $gameId);
$turn = (int)$query[0];

get_map();

// Get all players
$query = $db->execute('SELECT id FROM players WHERE game_id = ?', 'i', $gameId);
foreach ($query as $player) {
   $players[(int)$player[0]] = ['techs' => [], 'techPoints' => 0];
}

// Get all stocks
$query = $db->execute('SELECT player_id, type, quantity FROM stocks WHERE player_id IN (SELECT id FROM players WHERE game_id = ?)', 'i', $gameId);
foreach ($query as $stock) {
   $players[(int)$stock[0]]['stocks'][$stock[1]] = (int)$stock[2];
}

// Get all units in the game
$query = $db->execute('SELECT id, player_id, x, y FROM units WHERE player_id IN (SELECT id FROM players WHERE game_id = ?)', 'i', $gameId);
foreach ($query as $unit) {
   $units[(int)$unit[0]] = ['player_id' => (int)$unit[1], 'x' => (int)$unit[2], 'y' => (int)$unit[3]];
}

// Get technogical progress
$query = $db->execute('SELECT player_id, name, progress, queue FROM techs');
foreach ($query as $tech) {
   $players[(int)$tech[0]]['techs'][(int)$tech[3]] = ['name' => $tech[1], 'progress' => (int)$tech[2]];
}

// Sort the techs in order of the queue, which is the key
foreach ($players as $player) {
   ksort($player['techs']);
}

// Resolve all unit actions
foreach ($units as $unitId => $unit) {
   $query = $db->first('SELECT type, parameter FROM actions WHERE unit_id = ? ORDER BY ordering', 'i', $unitId);
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
      case 'new':
         $result = true;
      break;

      case 'move':
         $destination = explode(',', $parameter);
         $path = get_path($unit['x'], $unit['y'], (int)$destination[0], (int)$destination[1]);
         if ($path && count($path) > 1) {
            $db->execute('UPDATE units SET x = ?, y = ? WHERE id = ?', 'iii', $path[1]['x'], $path[1]['y'], $unitId);

            // If the path consists of only two locations, the unit has reached its destination
            $result = count($path) === 2 ? true : null;
         } else {

            // The destination has become unreachable
            $result = false;
         }
      break;

      case 'earn':
         $players[$unit['player_id']]['stocks']['credits']++;
         $result = null;
      break;

      case 'research':

         // The increment will become larger once we worked out how one unit can give more than one research point
         $players[$unit['player_id']]['techPoints'] += 1;
      break;

      /*
      case 'build':
         $query = $db->first('SELECT type, completion FROM improvements WHERE game_id = ? AND x = ? AND y = ?', 'iii', $gameId, $unit['x'], $unit['y']);
         if (!$query) {
            $db->execute('INSERT INTO improvements (game_id, x, y, type, completion) VALUES (?, ?, ?, ?, ?)', 'iiisd', $gameId, $unit['x'], $unit['y'], $parameter, 0.2);
            $result = null;
         } elseif ($query[0] === $parameter) {
            $completion = min(1, (float)$query[1] + 0.2);
            $db->execute('UPDATE improvements SET completion = ? WHERE game_id = ? AND x = ? AND y = ?', 'diii', $completion, $gameId, $unit['x'], $unit['y']);
            if ($completion === 1) {
               $db->execute("DELETE action.* FROM actions action INNER JOIN units unit ON action.unit_id = unit.id WHERE action.ordering = 1 AND action.type = 'build' AND unit.x = ? AND unit.y = ?", 'ii', $unit['x'], $unit['y']);
               $result = true;
            } else {
               $result = null;
            }
         }
      break;

      case 'settle':
         switch($map[$unit['x']][$unit['y']]) {
            case 'grass':
               $players[$unit['player_id']]['surplus'] += 0.2;
            break;
         }

         $result = null;
      break;
      */
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

// Set player parameters
foreach ($players as $playerId => $player) {
   $db->execute('UPDATE players SET finished = 0 WHERE id = ?', 'i', $playerId);

   // Set stocks
   foreach ($player['stocks'] as $type => $quantity) {
      $query = $db->first('SELECT EXISTS (SELECT * FROM stocks WHERE player_id = ? AND type = ?)', 'is', $playerId, $type);
      if ($query) {
         $db->execute('UPDATE stocks SET quantity = ? WHERE player_id = ? AND type = ?', 'dsi', $quantity, $playerId, $type);
      } else {
         $db->execute('INSERT INTO stocks (player_id, type, quantity) VALUES (?, ?, ?)', 'isd', $playerId, $type, $quantity);
      }
   }

   // Technological advancement
   while ($player['techPoints'] > 0) {
      if (count($player['techs']) === 0) {
         $db->execute('INSERT INTO logs (player_id, turn, type, icon, message) VALUES (?, ?, ?, ?, ?)', 'iisss', $playerId, $turn, 'research', 'resources/science.svg', 'You\'re wasting science points! Choose what to research next.');
         break;
      }

      $tech = $player['techs'][array_key_first($player['techs'])];
      $metaTech = $metadata['advancements'][array_search($tech['name'], array_column($metadata['advancements'], 'id'))];
      $leftover = $metaTech['cost'] - $tech['progress'];
      if ($leftover < $player['techPoints']) {
         $tech['progress'] = $metaTech['cost'];
         $player['techPoints'] -= $leftover;
         array_shift($player['techs']);
         $message = 'You discovered ' . $tech['name'] . '!';
         if ($player['techs']) {
            $message .= ' Research is now focused on ' . $player['techs'][0]['name'] . '.';
         }

         $db->execute('INSERT INTO logs (player_id, turn, type, icon, message) VALUES (?, ?, ?, ?, ?)', 'iisss', $playerId, $turn, 'research', $message);
      } else {
         $tech['progress'] += $player['techPoints'];
         $player['techPoints'] = 0;
      }

      $db->execute('UPDATE techs SET progress = ? WHERE playerId = ? AND name = ?', 'iis', $tech['progress'], $playerId, $tech['name']);
   }
}

// Increase the turn number
$db->execute('UPDATE games SET turn = turn + 1 WHERE id = ?', 'i', $gameId);

// Send success indicator
send_result(true);
?>