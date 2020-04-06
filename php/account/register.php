<?php
require '../init.php';
if (!$params || !$params->name || !$params->email || !$params->password) {
   send_result(false, 400);
}

// Check if the e-mail address is not in use already
$query = $db->first('SELECT EXISTS (SELECT * FROM users WHERE email = ?)', 's', $params->email);
if ($query[0]) {
   send_result('E-mail address already in use');
}

// Create the account
$password = password_hash($params->password, PASSWORD_BCRYPT);
$db->execute('INSERT INTO users (name, email, password, verified) VALUES (?, ?, ?, 0)', 'sss', $params->name, $params->email, $password);

// Get the user ID and store in the session
$query = $db->first('SELECT LAST_INSERT_ID()');
$userId = (int)$query[0];
$_SESSION['user_id'] = $userId;
set_token();
send_verification_email($userId, $params->email, $params->name);

// Report success
send_result(false);
?>