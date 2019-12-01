<?php
require 'init.php';
$userId = 1;

$result = [];
$query = $db->execute('SELECT x, y, type FROM terrain WHERE game_id = ?', 'i', $params->game);
foreach ($query as $tile) {
   $result[(int)$tile[1]][(int)$tile[0]]['type'] = $tile[2];
   $result[(int)$tile[1]][(int)$tile[0]]['improvements'] = [];
   $result[(int)$tile[1]][(int)$tile[0]]['units'] = [];
}

$query = $db->execute('SELECT x, y, type FROM improvements WHERE game_id = ?', 'i', $params->game);
foreach ($query as $improvement) {
   $result[(int)$improvement[1]][(int)$improvement[0]]['improvements'][] = $improvement[2];
}

$query = $db->execute('SELECT unit.id, unit.x, unit.y, unit.player_id, action FROM units unit INNER JOIN players player ON (player.id = unit.player_id) WHERE player.game_id = ?', 'i', $params->game);
foreach ($query as $unit) {
   $result[(int)$unit[2]][(int)$unit[1]]['units'][] = ['id' => $unit[0], 'player_id' => $unit[3]];
}

send_result($result);
?>