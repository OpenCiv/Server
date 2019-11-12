<?php
require 'utils.php';
session_start();
$_SESSION['userId'] = $userId;
$token = GeneratePassword(64);
setcookie('token', $token, time() + 31622400, '/');
$statement = $db->stmt_init();
$statement->prepare('INSERT INTO `tokens` (`Value`, `UserId`) VALUES (?, ?);');
$statement->bind_param('si', $token, $userId);
$statement->execute();
$statement->close();
?>