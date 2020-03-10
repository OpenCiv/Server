<?php
require 'init.php';
get_user();
$query = $db->first('SELECT email, name, verified FROM users WHERE id = ?', 'i', $userId);
$user = ['email' => $query[0], 'name' => $query[1], 'verified' => (bool)$query[2]];
send_result($user);
?>