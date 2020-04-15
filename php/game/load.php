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
   $result['map'][$y][$x]['units'] = [];
}

// Getting the vegetation
$query = $db->execute('SELECT x, y, type FROM vegetation WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $x = (int)$tile[0];
   $y = (int)$tile[1];
   $result['map'][$y][$x]['vegetation'] = $tile[2];
}

// Getting the resources
$query = $db->execute('SELECT id, x, y, type, quantity FROM resources WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $x = (int)$tile[1];
   $y = (int)$tile[2];
   $result['map'][$y][$x]['resources'][] = ['id' => (int)$tile[0], 'type' => $tile[3], 'quantity' => (double)$tile[4]];
}

// Getting the improvements
$query = $db->execute('SELECT x, y, type, completion FROM improvements WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $x = (int)$tile[0];
   $y = (int)$tile[1];
   $result['map'][$y][$x]['improvement'] = ['type' => $tile[2], 'completion' => (float)$tile[3]];
}

// Getting the units
$unitQuery = $db->execute('SELECT id, player_id, x, y FROM units WHERE player_id IN (SELECT id FROM players WHERE game_id = ?)', 'i', $gameId);
foreach ($unitQuery as $unit) {
   $unitId = (int)$unit[0];
   $unitPlayerId = (int)$unit[1];
   $x = (int)$unit[2];
   $y = (int)$unit[3];

   // Only the actions of the player's units are loaded
   if ($unitPlayerId === $playerId) {
      $oldX = $x;
      $oldY = $y;
      $actions = get_actions();
      $result['map'][$y][$x]['units'][] = ['id' => $unitId, 'x' => $x, 'y' => $y, 'player_id' => $unitPlayerId, 'actions' => $actions];
   } else {
      $result['map'][$y][$x]['units'][] = ['id' => $unitId, 'x' => $x, 'y' => $y, 'player_id' => $unitPlayerId];
   }
}

// Send all data
send_result($result);
?>