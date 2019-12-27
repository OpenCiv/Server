<?php
require 'init.php';
$userId = get_user();
if (!$params || !$params->email) {
   send_result('Parameter missing', 400);
}

$query = $db->execute('SELECT name FROM users WHERE id = ?', 'i', $userId);
$db->execute('UPDATE users SET email = ?, verified = 0 WHERE id = ?', 'si', $params->email, $userId);
send_verification_email($userId, $params->email, $query[0]);
send_result(true);
?>