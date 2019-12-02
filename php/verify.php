<?php
require 'init.php';

// Circumventing verification
$gameId = 1;
$userId = 1;
$playerId = 1;
$playerName = 'Knut';
return;

// Check if a session exists
if (!isset($_SESSION['user_id'])) {

   // The session may have expired
   if (!isset($_COOKIE['token'])) {
      send_result('Not logged in', 401);
   }

   // Find the token
   $query = $db->execute('SELECT `timestamp`, `user_id`, `user_agent` FROM `tokens` WHERE `value` = ?', 's', $_COOKIE['token']);

   // Check if the token is found...
   if (empty($query)) {
      send_result('Token not found', 401);
   }

   // ...and delete it
   $db->execute('DELETE FROM `tokens` WHERE `value` = ?', 's', $_COOKIE['token']);

   // Check if the token from the same user agent (browser, OS) and is not outdated
   $tokenTime = strtotime($query[0][0]);
   if ($query[0][2] !== $_SERVER['HTTP_USER_AGENT'] || $tokenTime < $timestamp - 31622400) {
      send_result('Invalid token', 401);
   }

   // Set the main session variable
   $_SESSION['user_id'] = (int)$query[0][1];
   $query = $db->execute('SELECT `email`, `name`, `verified` FROM `users` WHERE `id` = ?', 'i', $_SESSION['user_id']);
   if (empty($query)) {
      unset($_SESSION['user_id']);
      send_result('User not found', 400);
   }

   if (!$query[0][2]) {
      send_result('User not verified', 403);
   }

   $_SESSION['user_email'] = $query[0][0];
   $_SESSION['user_name'] = $query[0][1];
}

$userId = &$_SESSION['user_id'];
$userEmail = &$_SESSION['user_email'];
$userName = &$_SESSION['user_name'];

// Check the game
if (!$_SESSION['game_id']) {
   if (!$params->game) {
      send_result('Game unknown', 400);
   }

   $_SESSION['game_id'] = intval($params->game);
   if ($_SESSION['game_id'] <= 0) {
      unset($_SESSION['game_id']);
      send_result('Invalid game identifier', 400);
   }

   $query = $db->execute('SELECT name, x, y FROM games WHERE game_id = ?', 'i', $gameId);
   if (empty($query)) {
      send_result('Game not found', 400);
   }

   $_SESSION['game_name'] = $query[0][0];
   $_SESSION['game_x'] = (int)$query[0][1];
   $_SESSION['game_y'] = (int)$query[0][2];
}

$gameId = &$_SESSION['game_id'];
$gameName = &$_SESSION['game_name'];
$gameX = &$_SESSION['game_x'];
$gameY = &$_SESSION['game_y'];

// Check the player info
if (!$_SESSION['player_id']) {
   $query = $db->execute('SELECT id, name FROM players WHERE game_id = ? AND user_id = ?', 'ii', $gameId, $userId);
   if (empty($query)) {
      send_result('Not a player in this game', 401);
   }

   $_SESSION['player_id'] = (int)$query[0][0];
   $_SESSION['player_name'] = $query[0][1];
}

$playerId = &$_SESSION['player_id'];
$playerName = &$_SESSION['player_name'];
?>