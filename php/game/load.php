<?php
require '../init.php';
get_player();

// Getting game details
$query = $db->first('SELECT name, x, y, turn FROM games WHERE id = ?', 'i', $gameId);
if (!$query) {
   send_result('Game not found', 400);
}

$result['game']['name'] = $query[0];
$result['game']['x'] = (int)$query[1];
$result['game']['y'] = (int)$query[2];
$result['game']['turn'] = (int)$query[3];

// Getting player info
$result['players'] = [];
$query = $db->execute('SELECT id, user_id, name, color, icon FROM players WHERE game_id = ?', 'i', $gameId);
foreach ($query as $player) {
   $player_result = ['id' => (int)$player[0], 'name' => $player[2], 'color' => $player[3], 'icon' => $player[4]];
   $result['players'][] = $player_result;
   if ($player[1] == $playerId) {
      $result['player'] = $player_result;
   }
}

// Getting the map
$result['map'] = [];

/* Note that the y-coordinate comes before the x-coordinate for CSS reasons */

// Getting the terrain
$query = $db->execute('SELECT x, y, type FROM terrain WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $x = (int)$tile[0];
   $y = (int)$tile[1];
   $result['map'][$y][$x]['x'] = $x;
   $result['map'][$y][$x]['y'] = $y;
   $result['map'][$y][$x]['type'] = $tile[2];
   $result['map'][$y][$x]['resources'] = [];
   $result['map'][$y][$x]['improvements'] = [];
   $result['map'][$y][$x]['units'] = [];
}

// Getting the resources
$query = $db->execute('SELECT id, x, y, type, quantity FROM resources WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $x = (int)$tile[1];
   $y = (int)$tile[2];
   $result['map'][$y][$x]['resources'][] = ['id' => (int)$tile[0], 'x' => $x, 'y' => $y, 'type' => $tile[3], 'quantity' => (double)$tile[4]];
}

// Getting the improvements
$query = $db->execute('SELECT x, y, type, completion FROM improvements WHERE game_id = ?', 'i', $gameId);
foreach ($query as $improvement) {
   $x = (int)$improvement[0];
   $y = (int)$improvement[1];
   $result['map'][$y][$x]['improvements'][] = ['type' => $improvement[2], 'x' => $x, 'y' => $y, 'completion' => (float)$improvement[3]];
}

// Getting the units
$unitQuery = $db->execute('SELECT id, x, y, player_id FROM units WHERE player_id = ?', 'i', $playerId);
foreach ($unitQuery as $unit) {
   $unitId = (int)$unit[0];

   // Getting the unit's actions
   $actions = [];
   $actionQuery = $db->execute('SELECT ordering, type, parameters FROM actions WHERE unit_id = ? ORDER BY ordering', 'i', $unitId);
   foreach ($actionQuery as $action) {
      $type = $action[1];
      if ($type === 'move') {
         if (!$oldX) {
            $locationQuery = $db->first('SELECT x, y FROM units WHERE id = ?', 'i', $unitId);
            $oldX = (int)$locationQuery[0];
            $oldY = (int)$locationQuery[1];
         }

         $destination = explode(',', $action[2]);
         $newX = (int)$destination[0];
         $newY = (int)$destination[1];
         $parameter = get_path($oldX, $oldY, $newX, $newY);
         $oldX = $newX;
         $oldY = $newY;
      } else {
         $parameter = $action[2];
      }

      $actions[] = ['order' => (int)$action[0], 'type' => $type, 'parameter' => $parameter];
   }

   $x = (int)$unit[1];
   $y = (int)$unit[2];
   $result['map'][$y][$x]['units'][] = ['id' => $unitId, 'x' => $x, 'y' => $y, 'player_id' => (int)$unit[3], 'actions' => $actions];
}

// Send all data
send_result($result);
?>