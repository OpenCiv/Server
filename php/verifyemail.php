<?php
require 'init.php';

// Check if the token is passed
if (!$params->token) {
   send_result(false, 400);
}

// Find the token...
$query = $db->first('SELECT user_id FROM tokens WHERE value = ?', 's', $params->token);
if (empty($query)) {
   send_result(false, 400);
}

$userId = (int)$query[0];

// ...and delete it
$db->execute('DELETE FROM tokens WHERE value = ?', 's', $params->token);

// Set session and token, if not set already
if (!isset($_SESSION['user_id'])) {
   $_SESSION['user_id'] = $userId;
   if (!isset($_COOKIE['token'])) {
      $token = generate_token();
      $db->execute('INSERT INTO tokens (user_id, value, user_agent) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
      setcookie('token', $token, $_SERVER['REQUEST_TIME'] + 31622400, '/');
   }
}

// Mark user as verified
$db->execute('UPDATE users SET verified = 1 WHERE id = ?', 'i', $userId);
send_result(true);
?>