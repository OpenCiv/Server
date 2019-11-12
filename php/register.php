<?php
require 'init.php';
if (empty($params->name)) {
   send_result([
      'message' => 'Name missing',
      'success' => false
   ]);
}

if (empty($params->email)) {
   send_result([
      'message' => 'E-mail address missing',
      'success' => false
   ]);
}

if (empty($params->password)) {
   send_result([
      'message' => 'Password missing',
      'success' => false
   ]);
}

$db = new database();

// Check if the e-mail address is not in use already
$statement = $db->stmt_init();
$statement->prepare('SELECT EXISTS (SELECT * FROM `Users` WHERE `Email` = ?)');
$statement->bind_param('s', $params->email);
$statement->execute();
$statement->bind_result($exists);
$statement->fetch();
if ($exists) {
   send_result([
      'message' => 'E-mail address already used',
      'success' => false
   ]);
}

$statement->close();

// Create the account
$statement = $db->stmt_init();
$statement->prepare('INSERT INTO `Users` (`Name`, `Email`, `Password`, `Verified`) VALUES (?, ?, ?, 0)');
$password = password_hash($params->password, PASSWORD_BCRYPT);
$statement->bind_param('sss', $params->name, $params->email, $password);
$statement->execute();
$statement->close();

// Get the user ID
$statement = $db->stmt_init();
$statement->prepare('SELECT LAST_INSERT_ID()');
$statement->execute();
$statement->bind_result($userId);
$statement->fetch();
$statement->close();

// Set the main session variable
$_SESSION['userId'] = $userId;

// Create a new token
$token = generate_token();
$statement = $db->stmt_init();
$statement->prepare('INSERT INTO `Tokens` (`Value`, `UserId`, `UserAgent`) VALUES (?, ?, ?)');
$statement->bind_param('sis', $token, $userId, $_SERVER['HTTP_USER_AGENT']);
$statement->execute();
$statement->close();
setcookie('token', $token, time() + 31622400, '/');

// Report success
send_result([
   'message' => 'Account created',
   'success' => true
]);
?>