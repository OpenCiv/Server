<?php
require 'init.php';

// A login attempt should come as a post method
if (!$params || !$params->username || !$params->password) {
   send_result('Parameter missing', 400);
}

// Check if the user exists
$query = $db->first('SELECT id, password FROM users WHERE email = ?', 's', $params->username);
if (!$query) {
   send_result('E-mail address not found');
}

$userId = (int)$query[0];

// Check password
if (!password_verify($params->password, $query[1])) {
   send_result('Password incorrect');
}

// Create a new token and store in the database and as a cookie
$token = generate_token();
$db->execute('INSERT INTO tokens (user_id, value, user_agent) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
setcookie('token', $token, $_SERVER['REQUEST_TIME'] + 31622400, '/');

// Set session variable
$_SESSION['user_id'] = $userId;

// Report success
send_result(false);
?>
