<?php
require 'init.php';
verify_user_id($userId);
if (!$params || !$params->request || !$userId) {
   send_result(false, 400);
}

switch ($params->request) {
   case 'getuser':
      $query = $db->first('SELECT email, name, verified FROM users WHERE id = ?', 'i', $userId);
      $user = ['email' => $query[0], 'name' => $query[1], 'verified' => (bool)$query[2]];
      send_result($user);

   case 'resend':
      $query = $db->first('SELECT email, name FROM users WHERE id = ?', 'i', $userId);
      send_verification_email($userId, $query[0], $query[1]);
      send_result(true);

   case 'getgames':
      $query = $db->execute('SELECT game.id, game.name FROM games game INNER JOIN players player ON (game.id = player.game_id) WHERE player.user_id = ?', 'i', $userId);
      $games = [];
      foreach ($query as $game) {
         $games[] = ['id' => (int)$game[0], 'name' => $game[1]];
      }

      send_result($games);

   default:
      send_result(false, 400);
}
?>