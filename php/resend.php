<?php
require 'init.php';
get_user();
$query = $db->first('SELECT email, name FROM users WHERE id = ?', 'i', $userId);
send_verification_email($userId, $query[0], $query[1]);
send_result(true);
?>