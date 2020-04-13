<?php
require '../init.php';
get_user();
if (!$params || !$params->name) {
   send_result('Parameter missing', 400);
}

// Create a new game
$db->execute('INSERT INTO games (name, x, y) VALUES (?, 32, 16)', 's', $params->name);
$query = $db->first('SELECT LAST_INSERT_ID()');
$gameId = (int)$query[0];

// Add the user as player
$db->execute('INSERT INTO players (user_id, game_id, name) VALUES (?, ?, (SELECT name from users where id = ?))', 'iii', $userId, $gameId, $userId);

send_result($gameId);
?>