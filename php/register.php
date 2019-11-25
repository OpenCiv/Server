<?php
require 'init.php';

// A login attempt should come as a post method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   send_result('Incorrect request method', 405);
}

if (empty($params->name)) {
   send_result('Name missing', 400);
}

if (empty($params->email)) {
   send_result('E-mail address missing', 400);
}

if (empty($params->password)) {
   send_result('Password missing', 400);
}

// Check if the e-mail address is not in use already
$query = $db->execute('SELECT EXISTS (SELECT * FROM `users` WHERE `email` = ?)', 's', $params->email);
if (!empty($query)) {
   send_result('E-mail address already in use');
}

// Create the account
$password = password_hash($params->password, PASSWORD_BCRYPT);
$db->execute('INSERT INTO `users` (`name`, `email`, `password`, `verified`) VALUES (?, ?, ?, 0)', 'sss', $params->name, $params->email, $password);

// Get the user ID and store in the session
$query = $db->execute('SELECT LAST_INSERT_ID()');
$userId = (int)$query[0];
$_SESSION['user_id'] = $userId;

// Create a new token
$token = generate_token();
$db->execute('INSERT INTO `tokens` (`value`, `user_id`, `user_agent`) VALUES (?, ?, ?)', 'sis', $token, $userId, $_SERVER['HTTP_USER_AGENT']);
setcookie('token', $token, $timestamp + 31622400, '/');

// Report success
send_result(false);
?>