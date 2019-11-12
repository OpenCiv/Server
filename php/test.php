<?php
require 'init.php';
if (empty($params->name)) {
   send_result([
      'message' => 'Name missing',
      'success' => false
   ]);
}

$db = new database();
$statement = $db->stmt_init();
$statement->prepare('SELECT EXISTS (SELECT * FROM `Users`)');
$statement->execute();
$statement->bind_result($result);
$statement->fetch();
send_result(['message' => $result]);
?>