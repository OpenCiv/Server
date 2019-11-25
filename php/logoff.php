<?php
require 'init.php';

// Delete the existing token from the database
if (isset($_COOKIE['token'])) {
   $db->query('DELETE FROM `tokens` WHERE `value` = ?', 's', $_COOKIE['token']);
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