<?php
require 'init.php';
$userId = get_user();
$gameId = get_game();
$playerId = $_SESSION['player_id'];

// Getting game details
$query = $db->first('SELECT name, x, y FROM games WHERE id = ?', 'i', $gameId);
if (!$query) {
   send_result('Game not found', 400);
}

$result['game']['name'] = $query[0];
$result['game']['x'] = (int)$query[1];
$result['game']['y'] = (int)$query[2];

$query = $db->execute('SELECT id FROM players WHERE game_id = ? AND user_id = ?', 'ii', $gameId, $userId);

// Getting player info
$result['players'] = [];
$query = $db->execute('SELECT id, user_id, name FROM players WHERE game_id = ?', 'i', $gameId);
foreach ($query as $player) {
   $player_result = ['id' => (int)$player[0], 'name' => $player[2]];
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
   $result['map'][(int)$tile[1]][(int)$tile[0]]['type'] = $tile[2];
   $result['map'][(int)$tile[1]][(int)$tile[0]]['resources'] = [];
   $result['map'][(int)$tile[1]][(int)$tile[0]]['improvements'] = [];
   $result['map'][(int)$tile[1]][(int)$tile[0]]['units'] = [];
}

// Getting the resources
$query = $db->execute('SELECT id, x, y, type, quantity FROM resources WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $result['map'][(int)$tile[2]][(int)$tile[1]]['resources'][] = ['id' => (int)$tile[0], 'type' => $tile[3], 'quantity' => (double)$tile[4]];
}

// Getting the improvements
$query = $db->execute('SELECT x, y, type FROM improvements WHERE game_id = ?', 'i', $gameId);
foreach ($query as $improvement) {
   $result['map'][(int)$improvement[1]][(int)$improvement[0]]['improvements'][] = ['type' => $improvement[2]];
}

// Getting the units
$query = $db->execute('SELECT unit.id, unit.x, unit.y, unit.player_id, unit.action FROM units unit INNER JOIN players player ON (player.id = unit.player_id) WHERE player.game_id = ?', 'i', $gameId);
foreach ($query as $unit) {
   $result['map'][(int)$unit[2]][(int)$unit[1]]['units'][] = ['id' => $unit[0], 'player_id' => $unit[3], 'action' => $unit[4]];
}

// Send all data
send_result($result);
?>