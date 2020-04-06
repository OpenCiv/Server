<?php
require '../init.php';
if (!$params || !$params->email) {
   send_result('Parameter missing', 400);
}

$query = $db->first('SELECT id, name FROM users WHERE email = ?', 's', $params->email);
if (!$query) {
   send_result(false);
}

$userId = (int)$query[0];
$name = $query[1];
$password = substr(generate_token(), 0, 8);
$db->execute('UPDATE users SET password = ? WHERE id = ?', 'si', password_hash($password, PASSWORD_BCRYPT), $userId);
$body = "Hello $name," . PHP_EOL . PHP_EOL .
'As requested, here is your new password: ' . $password . PHP_EOL . PHP_EOL .
'Kind regards,' . PHP_EOL .
'The Open Civ team';
$success = send_mail($params->email, $name, 'Open Civ: new password', $body);
send_result($success);
?>