<?php
require 'init.php';
$userId = get_user();
$db->execute('UPDATE players SET user_id = NULL WHERE user_id = ?', 'i', $userId);
$db->execute('DELETE FROM tokens WHERE user_id = ?', 'i', $userId);
$db->execute('DELETE FROM users WHERE id = ?', 'i', $userId);
logoff();
send_result(true);
?>