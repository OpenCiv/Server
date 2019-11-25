<?php
require 'init.php';
if (empty($params->name)) {
   send_result([
      'message' => 'Name missing',
      'success' => false
   ]);
}

if (empty($params->email)) {
   send_result([
      'message' => 'E-mail address missing',
      'success' => false
   ]);
}

if (empty($params->password)) {
   send_result([
      'message' => 'Password missing',
      'success' => false
   ]);
}

// Check if the e-mail address is not in use already
$query = $db->query('SELECT EXISTS (SELECT * FROM `users` WHERE `email` = ?)', 's', $params->email);
if (!empty($query)) {
   send_result([
      'message' => 'E-mail address already used',
      'success' => false
   ]);
}

// Create the account
$password = password_hash($params->password, PASSWORD_BCRYPT);
$db->query('INSERT INTO `users` (`name`, `email`, `password`, `verified`) VALUES (?, ?, ?, 0)', 'sss', $params->name, $params->email, $password);

// Get the user ID and store in the session
$query = $db->query('SELECT LAST_INSERT_ID()');
$userId = (int)$query[0];
$_SESSION['user_id'] = $userId;

// Create a new token
$token = generate_token();
$db->query('INSERT INTO `tokens` (`value`, `user_id`, `user_agent`) VALUES (?, ?, ?)', 'sis', $token, $userId, $_SERVER['HTTP_USER_AGENT']);
setcookie('token', $token, $timestamp + 31622400, '/');

// Report success
send_result([
   'message' => 'Account created',
   'success' => true
]);
?>