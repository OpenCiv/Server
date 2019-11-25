<?php
require 'init.php';
$db = new database();

// Delete the existing token from the database
if (isset($_COOKIE['token'])) {
   $statement = $db->stmt_init();
   $statement->prepare('DELETE FROM `tokens` WHERE `value` = ?');
   $statement->bind_param('s', $_COOKIE['token']);
   $statement->execute();
   $statement->close();
}

// Remove all session variables
$_SESSION = array();

// Delete the session and token cookies
setcookie(session_name(), null, $timestamp - 42000, '/');
setcookie('token', null, $timestamp - 42000, '/');

// End the session
session_destroy();

// Send response
send_result([
   'message' => 'Logged off',
   'success' => true
]);
?>