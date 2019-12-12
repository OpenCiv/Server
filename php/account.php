<?php
require 'verify.php';

if (!$params || !$params->request || !$userId) {
   send_result(false, 400);
}

switch ($params->request) {
   case 'getuser':
      $query = $db->execute('SELECT name, email, verified FROM users WHERE id = ?', 'i', $userId);
      $user = ['name' => $query[0][0], 'email' => $query[0][1], 'verified' => (bool)$query[0][2]];
      send_result($user);

   default:
      send_result(false, 400);
}
?>