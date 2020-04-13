<?php
require '../init.php';
get_user();
if (!$params || !$params->oldpass || !$params->newpass) {
   send_result('Parameter missing', 400);
}

$query = $db->first('SELECT password FROM users WHERE id = ?', 'i', $userId);
if (!password_verify($params->oldpass, $query[0])) {
   send_result(false);
}

$db->execute('UPDATE users SET password = ? WHERE id = ?', 'si', password_hash($params->newpass, PASSWORD_BCRYPT), $userId);
send_result(true);
?>