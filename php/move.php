<?php
require 'init.php';
get_player();

if (!$params || !$params->id || !$params->direction) {
   send_result('Parameter missing', 400);
}

$query = $db->first('SELECT player_id, x, y FROM units WHERE id = ?', 'i', $params->id);
if (!$query) {
   send_result('Unit not found', 400);
}

if ($query[0] != $playerId) {
   send_result('Not the player\'s unit', 403);
}

$x = (int)$query[1];
$y = (int)$query[2];

if (strpos($params->direction, 'up') !== false) {
   $y--;
}

if (strpos($params->direction, 'down') !== false) {
   $y++;
}

if (strpos($params->direction, 'left') !== false) {
   $x--;
}

if (strpos($params->direction, 'right') !== false) {
   $x++;
}

$query = $db->first('SELECT type FROM terrain WHERE game_id = ? AND x = ? AND y = ?', 'iii', $gameId, $x, $y);
if (!$query) {
   send_result('Off the map', 400);
}

if ($query[0] === 'water') {
   send_result(false);
}

$query = $db->execute('UPDATE units SET x = ?, y = ? WHERE id = ?', 'iii', $x, $y, $params->id);
send_result(true);
?>