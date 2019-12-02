<?php
require 'verify.php';

// Getting game details
$query = $db->execute('SELECT name, x, y FROM games WHERE id = ?', 'i', $gameId);
if (empty($query)) {
   send_result('Game not found', 400);
}

$result['game']['name'] = $query[0][0];
$result['game']['x'] = (int)$query[0][1];
$result['game']['y'] = (int)$query[0][2];

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

// First the terrain
$query = $db->execute('SELECT x, y, type FROM terrain WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $result['map'][(int)$tile[1]][(int)$tile[0]]['type'] = $tile[2];
   $result['map'][(int)$tile[1]][(int)$tile[0]]['resources'] = [];
   $result['map'][(int)$tile[1]][(int)$tile[0]]['improvements'] = [];
   $result['map'][(int)$tile[1]][(int)$tile[0]]['units'] = [];
}

$query = $db->execute('SELECT x, y, type FROM resources WHERE game_id = ?', 'i', $gameId);
foreach ($query as $tile) {
   $result['map'][(int)$tile[1]][(int)$tile[0]]['resources'][] = $tile[2];
}

// Then the improvements
$query = $db->execute('SELECT x, y, type FROM improvements WHERE game_id = ?', 'i', $gameId);
foreach ($query as $improvement) {
   $result['map'][(int)$improvement[1]][(int)$improvement[0]]['improvements'][] = $improvement[2];
}

// Finally the units
$query = $db->execute('SELECT unit.id, unit.x, unit.y, unit.player_id, unit.action FROM units unit INNER JOIN players player ON (player.id = unit.player_id) WHERE player.game_id = ?', 'i', $gameId);
foreach ($query as $unit) {
   $result['map'][(int)$unit[2]][(int)$unit[1]]['units'][] = ['id' => $unit[0], 'player_id' => $unit[3], 'action' => $unit[4]];
}

send_result($result);
?>