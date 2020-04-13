<?php
require '../init.php';

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

// Set session variable
$_SESSION['user_id'] = $userId;

// Create a new token and store in the database and as a cookie
set_token();

// Report success
send_result(true);
?>