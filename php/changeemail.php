<?php
require 'init.php';
$userId = get_user();
if (!$params || !$params->email) {
   send_result('Parameter missing', 400);
}

// Check if the e-mail address is not in use already
$query = $db->first('SELECT EXISTS (SELECT * FROM users WHERE email = ?)', 's', $params->email);
if ($query[0]) {
   send_result('E-mail address already in use');
}

$query = $db->first('SELECT name FROM users WHERE id = ?', 'i', $userId);
$db->execute('UPDATE users SET email = ?, verified = 0 WHERE id = ?', 'si', $params->email, $userId);
send_verification_email($userId, $params->email, $query[0]);
send_result(true);
?>