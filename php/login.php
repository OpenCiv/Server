<?php
require 'init.php';

// A login attempt should come as a post method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   send_result('Incorrect request method', 405);
}

// Check if parameters are not empty
if (empty($params->username)) {
   send_result('E-mail address missing', 400);
}

if (empty($params->password)) {
   send_result('Password missing', 400);
}

// Check if the user exists
$query = $db->execute('SELECT `id`, `password` FROM `users` WHERE `email` = ?', 's', $params->username);
if (empty($query)) {
   send_result('E-mail address not found');
}

$userId = (int)$query[0][0];

// Check password
if (!password_verify($params->password, $query[0][1])) {
   send_result('Password incorrect');
} else {

   // Create a new token and store in the database and as a cookie
   $token = generate_token();
   $db->execute('INSERT INTO `tokens` (`user_id`, `value`, `user_agent`) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
   setcookie('token', $token, $timestamp + 31622400, '/');

   // Set session variables
   $_SESSION['user_id'] = $userId;
}

// Report success
send_result(false);
?>