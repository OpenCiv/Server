<?php
require 'init.php';
$userId = get_user();
$query = $db->execute('SELECT game.id, game.name FROM games game INNER JOIN players player ON (game.id = player.game_id) WHERE player.user_id = ? OR game.id = 1', 'i', $userId);
$games = [];
foreach ($query as $game) {
   $games[] = ['id' => (int)$game[0], 'name' => $game[1]];
}

send_result($games);
?>