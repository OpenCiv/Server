<?php
require 'init.php';

// A login attempt should come as a post method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   send_result([
      'message' => 'Incorrect request method',
      'success' => false
   ], 405);
}

// Check if parameters are not empty
if (empty($params->username)) {
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

// Check if the user exists
$statement = $db->stmt_init();
$statement->prepare('SELECT `Id`, `Name`, `Password` FROM `Users` WHERE `Email` = ?');
$statement->bind_param('s', $params->username);
$statement->execute();
$statement->bind_result($userId, $userName, $password);
if (!$statement->fetch()) {
   send_result([
      'message' => 'E-mail address not found',
      'success' => false
   ]);
}

$statement->close();

// Check password
if (!password_verify($params->password, $password)) {
   send_result([
      'message' => 'Password incorrect',
      'success' => false
   ]);
} else {

   // Create a new token and store in the database and as a cookie
   $token = generate_token();
   $statement = $db->stmt_init();
   $statement->prepare('INSERT INTO `Tokens` (`UserId`, `Value`, `UserAgent`) VALUES (?, ?, ?)');
   $statement->bind_param('iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
   $statement->execute();
   $statement->close();
   setcookie('token', $token, $timestamp + 34560000, '/');

   // Set session variables
   $_SESSION['userId'] = $userId;
   $_SESSION['userEmail'] = $params->username;
   $_SESSION['userName'] = $userName;
}

// Report success
send_result([
   'message' => 'Inloggen gelukt',
   'success' => true
]);
?>