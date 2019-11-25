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

// Check if the user exists
$query = $db->query('SELECT `id`, `password` FROM `users` WHERE `email` = ?', 's', $params->username);
if (empty($query)) {
   send_result([
      'message' => 'E-mail address not found',
      'success' => false
   ]);
}

$userId = $query['id'];

// Check password
if (!password_verify($params->password, $query['password'])) {
   send_result([
      'message' => 'Password incorrect',
      'success' => false
   ]);
} else {

   // Create a new token and store in the database and as a cookie
   $token = generate_token();
   $db->query('INSERT INTO `tokens` (`user_id`, `value`, `user_agent`) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
   setcookie('token', $token, $timestamp + 31622400, '/');

   // Set session variables
   $_SESSION['user_id'] = $userId;
}

// Report success
send_result([
   'message' => 'Inloggen gelukt',
   'success' => true
]);
?>