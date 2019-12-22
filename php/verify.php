<?php
require 'init.php';

// Circumventing verification
$gameId = 1;
$userId = 1;
$playerId = 1;
$playerName = 'Knut';
return;

if (!verify_user_id($userId)) {
   send_result('User not verified', 403);
}

// Check the game
if (!$_SESSION['game_id']) {

   // Check if the game ID is passed as a parameter
   if ($params->game) {
      $_SESSION['game_id'] = intval($params->game);

   // Otherwise check if the game ID is in the URL
   } elseif ($_SERVER['HTTP_REFERER']) {
      $start = strpos($_SERVER['HTTP_REFERER'], 'game') + 5;
      $end = strpos($_SERVER['HTTP_REFERER'], '/', $start);
      if ($start !== false && $end !== false) {
         $_SESSION['game_id'] = intval(substr($_SERVER['HTTP_REFERER'], $start, $end - $start));
      }
   }

   // If neither the game cannot be determined
   if (!$_SESSION['game_id']) {
      unset($_SESSION['game_id']);
      send_result('Game unknown', 400);
   }

   // Check if the game exists
   $query = $db->execute('SELECT EXISTS (SELECT * FROM games WHERE game_id = ?)', 'i', $_SESSION['game_id']);
   if ($query[0][0] == false) {
      send_result('Game not found', 400);
   }
}

$gameId = &$_SESSION['game_id'];

// Retrieve the player ID
if (!$_SESSION['player_id']) {
   $query = $db->execute('SELECT id FROM players WHERE game_id = ? AND user_id = ?', 'ii', $gameId, $userId);
   if (empty($query)) {
      send_result('Not a player in this game', 400);
   }

   $_SESSION['player_id'] = (int)$query[0][0];
}

$playerId = &$_SESSION['player_id'];
?>