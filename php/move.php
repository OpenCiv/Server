<?php
require 'init.php';
get_player();

if (!$params || !$params->id || !$params->x || !$params->y) {
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

$xDiff = abs($x - $params->x);
$yDiff = abs($y - $params->y);
if ($xDiff > 1 || $yDiff > 1 || $xDiff + $yDiff === 0) {
   send_result(false);
}

$query = $db->first('SELECT type FROM terrain WHERE game_id = ? AND x = ? AND y = ?', 'iii', $gameId, $params->x, $params->y);
if (!$query) {
   send_result('Off the map', 400);
}

if ($query[0] === 'water') {
   send_result(false);
}

$query = $db->execute('UPDATE units SET x = ?, y = ? WHERE id = ?', 'iii', $params->x, $params->y, $params->id);
send_result(true);
?>