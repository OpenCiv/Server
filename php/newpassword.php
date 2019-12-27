<?php
require 'init.php';
if (!$params || !$params->email) {
   send_result('Parameter missing', 400);
}

$query = $db->first('SELECT name FROM users WHERE email = ?', 'i', $params->email);
if (!$query) {
   send_result(false);
}

$name = $query[0];
$password = substr(generate_token(), 0, 8);
$db->execute('UPDATE users SET password = ? WHERE id = ?', 'si', password_hash($password, PASSWORD_BCRYPT), $userId);
$body = "Hello $name," . PHP_EOL . PHP_EOL .
'As requested, here is your new password: ' . $password . PHP_EOL . PHP_EOL .
'Kind regards,' . PHP_EOL .
'The Open Civ team';
send_mail($email, $name, 'Open Civ: new password');
send_result(true);
?>