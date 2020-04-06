<?php
require '../init.php';
get_user();
if (!$params || !$params->name) {
   send_result('Parameter missing', 400);
}

$db->execute('UPDATE users SET name = ? WHERE id = ?', 'si', $params->name, $userId);
send_result(true);
?>