<?php
require 'init.php';

// Check if the user is still logged in
if ($_SESSION['user_id']) {
   send_result($_SESSION['game_id'] ?: true);
}

// Check if a token cookie is set
if (!isset($_COOKIE['token'])) {
   send_result(false);
}

// Find the token...
$query = $db->first('SELECT timestamp, user_id, user_agent FROM tokens WHERE value = ?', 's', $_COOKIE['token']);
if (!$query) {
   send_result(false);
}

// ...and delete it
$db->execute('DELETE FROM tokens WHERE value = ?', 's', $_COOKIE['token']);

// Check if the token from the same user agent (browser, OS) and is not outdated
$tokenTime = strtotime($query[0]);
if ($query[2] !== $_SERVER['HTTP_USER_AGENT'] || $tokenTime < $_SERVER['REQUEST_TIME'] - 31622400) {
   send_result(false);
}

// Set the main session variable
$_SESSION['user_id'] = (int)$query[1];

// Create a new token
$token = generate_token();
$db->execute('INSERT INTO tokens (user_id, value, user_agent) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
setcookie('token', $token, $_SERVER['REQUEST_TIME'] + 31622400, '/');

// Send response
send_result(true);
?>